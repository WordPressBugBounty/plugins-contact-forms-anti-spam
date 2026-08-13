<?php

declare(strict_types=1);

namespace Maspik\Integrations\Support;

use Maspik\Domain\Check\HoneypotCheck;
use Maspik\Domain\Check\VerificationKeyCheck;

/**
 * Captures everything a form submitted, for the log.
 *
 * The engine only scans text-ish fields, and that is correct — a submit button
 * or an acceptance checkbox is not spam signal. But the log answers a different
 * question: was this a real customer? An owner cannot judge that from the text
 * fields alone when the form also asked which service, which branch, and on what
 * date. Version 2 logged the whole request for exactly this reason; keeping only
 * the scanned fields quietly took that away.
 *
 * Every form plugin structures its data differently — Gravity Forms posts
 * `input_1_3`, Divi posts `et_pb_contact_0_name_0`, WPForms nests under
 * `wpforms[fields][3]` — so this reads the request as a whole instead of
 * teaching each adapter a new trick.
 *
 * Pure by design (no WordPress), so the flattening, masking and capping can be
 * tested against the shapes real plugins actually post.
 */
final class RawPayload
{
    /** Values longer than this are clipped; a spam body can be megabytes. */
    private const MAX_VALUE_LENGTH = 5000;

    /** Hard cap on entries, so a crafted request cannot bloat the log table. */
    private const MAX_FIELDS = 200;

    /**
     * Request keys that are ours, not the visitor's. They carry no information
     * an owner could use to judge a submission, and the verification key is a
     * per-site secret that has no business being stored in a log row.
     */
    private const SKIP_KEYS = [
        HoneypotCheck::FIELD_NAME,
        VerificationKeyCheck::FIELD_NAME,
    ];

    /**
     * Form plumbing, never something a visitor typed.
     *
     * Matched at any depth, because plugins carry the same housekeeping inside
     * their own envelope: WPForms posts wpforms[nonce] and wpforms[token]
     * alongside the real answers. Nonces and tokens are the important ones —
     * they are single-use secrets with no business sitting in a log row — but
     * the rest is equally useless to an owner deciding whether a submission was
     * a real customer.
     */
    private const NOISE_KEYS = [
        'action',
        'security',
        'nonce',
        '_nonce',
        '_wpnonce',
        '_ajax_nonce',
        '_wp_http_referer',
        'token',
        'submit',
        'post_id',
        'page_id',
        'page_title',
        'url_referer',
        'url_referrer',
        'start_timestamp',
        'form_id',
        'gform_submit',
        'gform_unique_id',
        'gform_field_values',
    ];

    /**
     * Plumbing whose name carries the form id, so it cannot be listed exactly:
     * Gravity Forms posts is_submit_2, state_2, gform_target_page_number_2 and
     * friends alongside the answers.
     */
    private const NOISE_PREFIXES = [
        'is_submit_',
        'state_',
        // Gravity Forms namespaces every one of its own transport fields with
        // `gform_` and names the visitor's answers `input_*`, so the whole
        // prefix is plumbing: gform_submit, gform_theme, gform_style_settings,
        // gform_submission_method, gform_target_page_number… The worst of them
        // is state_N, a long base64 blob that the log picked as the submission's
        // "message" simply because it was the longest value on the row.
        'gform_',
    ];

    /**
     * @param array<string|int, mixed> $post typically $_POST, already unslashed
     * @return array<string, string>
     */
    public static function capture(array $post): array
    {
        $out = [];
        self::walk($post, '', $out);

        return $out;
    }

    /**
     * Flattens nested values into bracketed paths (`wpforms[fields][3]`) so a
     * plugin that nests its data keeps every value *and* the shape that explains
     * where the value came from.
     *
     * @param mixed $value
     * @param array<string, string> $out
     */
    private static function walk($value, string $path, array &$out): void
    {
        if (count($out) >= self::MAX_FIELDS) {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $name = (string) $key;
                if (self::isSkippable($name, $path === '')) {
                    continue;
                }
                self::walk($child, $path === '' ? $name : $path . '[' . $name . ']', $out);
            }

            return;
        }

        if ($path === '' || is_object($value)) {
            return;
        }

        // Some plugins post the whole form as one JSON string in a single field
        // — Ninja Forms sends `formData` that way. Stored verbatim it is an
        // unreadable wall that also buries the real answers, so it is expanded
        // like any other nested value: every submitted value stays, and each one
        // is legible on its own line.
        $unwrapped = self::decodeStructure($value);
        if ($unwrapped !== null) {
            self::walk($unwrapped, $path, $out);

            return;
        }

        // An expanded envelope repeats its own indexes back as data:
        // formData[fields][5][id] = 5 says nothing the key did not already say.
        if (self::echoesItsOwnKey($path, $value)) {
            return;
        }

        $out[$path] = self::clean($path, $value);
    }

    /**
     * True for a leaf that merely restates the key it hangs under, such as
     * `…[5][id] = 5`. Real answers are dropped only if a visitor typed a field's
     * own index into it, which costs nothing to lose.
     *
     * @param mixed $value
     */
    private static function echoesItsOwnKey(string $path, $value): bool
    {
        if (! is_scalar($value) || substr($path, -4) !== '[id]') {
            return false;
        }

        $parts = explode('[', rtrim($path, ']'));
        $parent = rtrim(end($parts) ?: '', ']');
        $parent = $parts ? rtrim($parts[count($parts) - 2] ?? '', ']') : '';

        return $parent !== '' && (string) $value === $parent;
    }

    /**
     * True for our own guard fields and for form plumbing.
     *
     * A bare `id` is only dropped inside an envelope (wpforms[id], formData[id]).
     * At the top level it is far likelier to be a field someone actually named
     * "id", and dropping a real answer is the worse mistake.
     */
    private static function isSkippable(string $name, bool $atRoot): bool
    {
        $lower = strtolower($name);

        if (in_array($name, self::SKIP_KEYS, true) || in_array($lower, self::NOISE_KEYS, true)) {
            return true;
        }

        foreach (self::NOISE_PREFIXES as $prefix) {
            if (strpos($lower, $prefix) === 0) {
                return true;
            }
        }

        return $lower === 'id' && ! $atRoot;
    }

    /**
     * A JSON object/array carried inside a string field, or null when the value
     * is just text. Scalars stay scalars: a message that happens to start with a
     * brace is still a message.
     *
     * @param mixed $value
     * @return array<int|string, mixed>|null
     */
    private static function decodeStructure($value): ?array
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '' || ($trimmed[0] !== '{' && $trimmed[0] !== '[')) {
            return null;
        }

        $decoded = json_decode($trimmed, true);

        return is_array($decoded) && $decoded !== [] ? $decoded : null;
    }

    /**
     * The same capture for adapters that never see `$_POST`: the Custom Form API
     * is handed its fields by the developer's own code, and the WooCommerce block
     * checkout reads them off the order object.
     *
     * Takes the adapter's own field list *before* type filtering, so a select or
     * a date reaches the log even though the engine will not scan it.
     *
     * @param array<int, array{name?: string, type?: string, value?: mixed}> $rawFields
     * @return array<string, string>
     */
    public static function fromList(array $rawFields): array
    {
        $out = [];
        foreach ($rawFields as $field) {
            if (! is_array($field)) {
                continue;
            }
            $name = isset($field['name']) ? (string) $field['name'] : '';
            if ($name === '' || in_array($name, self::SKIP_KEYS, true)) {
                continue;
            }
            if (count($out) >= self::MAX_FIELDS) {
                break;
            }
            $value = $field['value'] ?? '';
            $out[$name] = is_array($value)
                ? self::clean($name, implode(' ', self::leaves($value)))
                : self::clean($name, $value);
        }

        return $out;
    }

    /**
     * @param array<int|string, mixed> $value
     * @return array<int, string>
     */
    private static function leaves(array $value): array
    {
        $out = [];
        array_walk_recursive($value, static function ($leaf) use (&$out): void {
            if (is_scalar($leaf)) {
                $out[] = (string) $leaf;
            }
        });

        return $out;
    }

    /**
     * @param mixed $value
     */
    private static function clean(string $path, $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (! is_scalar($value) && $value !== null) {
            return '';
        }

        $text = (string) $value;

        // Never store a password, even though a form that posts one through a
        // spam check is already doing something unusual. Matches v2's rule.
        if (stripos($path, 'pass') !== false) {
            return $text === '' ? '' : '********';
        }

        // Strip control characters that would corrupt the JSON envelope, but
        // keep newlines and tabs — a message field's line breaks are part of
        // what the owner is reading.
        $text = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);

        if (mb_strlen($text) > self::MAX_VALUE_LENGTH) {
            $text = mb_substr($text, 0, self::MAX_VALUE_LENGTH) . '…';
        }

        return $text;
    }
}
