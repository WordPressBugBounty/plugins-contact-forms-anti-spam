<?php

declare(strict_types=1);

namespace Maspik\Infrastructure\Settings;

/**
 * Normalises the shared-rules payload (from wpmaspik.com, or an existing v2
 * `spamapi` option) into the shape the engine reads: local Schema keys, with
 * every multiline rule field expressed as an array.
 *
 * Pure and idempotent — safe to run on the live API response and on an
 * already-normalised option, which is what makes the v2→3.0 migration and the
 * cron refresh share one code path.
 */
final class DashboardRules
{
    /** ACF field names on wpmaspik.com → local Schema keys (renamed in v3). */
    private const KEY_MAP = [
        'text_field' => 'text_blacklist',
        'textarea_field' => 'text_blacklist',
        'email_field' => 'emails_blacklist',
        'url_field' => 'url_blacklist',
    ];

    /**
     * @param array<string, mixed> $raw
     * @return array<string, mixed>
     */
    public static function normalize(array $raw): array
    {
        $out = [];
        foreach ($raw as $field => $value) {
            $key = self::KEY_MAP[$field] ?? $field;
            $definition = Schema::options()[$key] ?? null;
            if ($definition === null) {
                continue;
            }

            if ($definition['type'] === Schema::TYPE_MULTILINE) {
                $list = self::toList($key, $value);
                $out[$key] = isset($out[$key]) && is_array($out[$key])
                    ? array_values(array_unique(array_merge($out[$key], $list)))
                    : $list;
            } else {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /**
     * Split a rule field into a de-duplicated list. Already-array input is just
     * cleaned (idempotent re-runs); country codes are whitespace-separated (v2);
     * every other list is one rule per line so multi-word phrases stay intact.
     *
     * @param mixed $value
     * @return string[]
     */
    private static function toList(string $key, $value): array
    {
        if (is_array($value)) {
            $parts = $value;
        } elseif ($key === 'country_blacklist') {
            $parts = preg_split('/\s+/', trim((string) $value));
        } else {
            $parts = explode("\n", str_replace("\r", '', (string) $value));
        }

        $items = [];
        foreach ((array) $parts as $part) {
            $part = trim((string) $part);
            if ($part !== '' && ! in_array($part, $items, true)) {
                $items[] = $part;
            }
        }

        return $items;
    }
}
