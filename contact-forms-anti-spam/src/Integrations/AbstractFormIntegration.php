<?php

declare(strict_types=1);

namespace Maspik\Integrations;

use Maspik\Domain\Check\VerificationKeyCheck;
use Maspik\Domain\Check\HoneypotCheck;
use Maspik\Domain\Model\Field;
use Maspik\Domain\Model\Submission;
use Maspik\Integrations\Support\RawPayload;
use Maspik\Infrastructure\ClientIp;
use Maspik\Integrations\Support\FieldMapper;

/**
 * Shared plumbing for form adapters. A concrete adapter only has to:
 *   1. declare id / label / toggleKey / isAvailable,
 *   2. bind its plugin's validation hook,
 *   3. extract the plugin's fields into rawFields (name/type/value) and call
 *      submissionFrom(), then map a spam Verdict onto the plugin's error API.
 *
 * The guard-field, client-IP and referrer extraction — identical for every
 * plugin — lives here so it is written and reasoned about once.
 */
abstract class AbstractFormIntegration implements FormIntegration
{
    /** @var ClientIp */
    protected $clientIp;

    public function __construct(ClientIp $clientIp)
    {
        $this->clientIp = $clientIp;
    }

    /** Free by default; Pro-only integrations override this to true. */
    public function pro(): bool
    {
        return false;
    }

    /** Enabled by default; opt-in integrations override this to true. */
    public function optIn(): bool
    {
        return false;
    }

    /**
     * Build a Submission from raw plugin fields + a plugin type map.
     *
     * @param array<int, array{name: string, type: string, value: string|array}> $rawFields
     * @param array<string, string> $typeMap
     */
    protected function submissionFrom(array $rawFields, array $typeMap): Submission
    {
        return $this->submission(FieldMapper::map($rawFields, $typeMap));
    }

    /**
     * Build a Submission from already-normalized Field[] (core WP forms with
     * fixed field sets use this directly).
     *
     * @param Field[] $fields
     */
    protected function submission(array $fields): Submission
    {
        $post = $_POST;

        $hidden = [
            HoneypotCheck::FIELD_NAME => isset($post[HoneypotCheck::FIELD_NAME])
                ? (string) wp_unslash($post[HoneypotCheck::FIELD_NAME]) : '',
            VerificationKeyCheck::FIELD_NAME => isset($post[VerificationKeyCheck::FIELD_NAME])
                ? (string) wp_unslash($post[VerificationKeyCheck::FIELD_NAME]) : '',
        ];

        return $this->submissionWithHidden($fields, $hidden);
    }

    /**
     * Build a Submission with explicit guard-field values, for adapters whose
     * plugin carries the honeypot/key inside its own payload rather than $_POST
     * (e.g. Elementor Atomic Forms). $hidden must be keyed by
     * HoneypotCheck::FIELD_NAME / VerificationKeyCheck::FIELD_NAME.
     *
     * @param Field[]               $fields
     * @param array<string, string> $hidden
     */
    protected function submissionWithHidden(array $fields, array $hidden): Submission
    {
        $referrer = isset($_SERVER['HTTP_REFERER'])
            ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : null;

        // Captured here rather than per adapter: every plugin shapes its data
        // differently, but they all arrive in the same request.
        $raw = RawPayload::capture(isset($_POST) ? (array) wp_unslash($_POST) : []);

        return new Submission(
            $fields,
            $this->id(),
            $this->label(),
            $this->clientIp->get(),
            $hidden,
            $referrer,
            $raw
        );
    }
}
