<?php

declare(strict_types=1);

namespace Maspik\Frontend;

use Maspik\Domain\Check\VerificationKeyCheck;
use Maspik\Domain\Check\HoneypotCheck;
use Maspik\Infrastructure\Settings\Settings;
use Maspik\Integrations\Forms\ElementorAtomic;

/**
 * Enqueues the front-end guard script (honeypot + advanced key injection).
 * Replaces v2's inline wp_footer echo with a cacheable static file; the DOM
 * behavior (field names, search/GET-form exclusions) is unchanged.
 */
final class ScriptInjector
{
    /** @var Settings */
    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function enqueue(): void
    {
        $honeypot = $this->settings->boolEffective('maspikHoneypot');
        $advancedKey = $this->settings->boolEffective('verification_key');

        if (! $honeypot && ! $advancedKey) {
            return;
        }

        wp_enqueue_script(
            'maspik-guard',
            MASPIK_URL . 'assets/js/maspik-guard.js',
            [],
            MASPIK_VERSION,
            true
        );

        wp_add_inline_script('maspik-guard', 'window.maspikGuardConfig = ' . wp_json_encode([
            'honeypot' => $honeypot,
            'honeypotName' => HoneypotCheck::FIELD_NAME,
            'honeypotLabel' => __('Leave this field empty', 'contact-forms-anti-spam'),
            'advancedKey' => $advancedKey,
            'keyName' => VerificationKeyCheck::FIELD_NAME,
            'keyValue' => $advancedKey ? $this->settings->spamKey() : '',
            // Elementor Atomic (e-form) forms carry the guard fields as
            // data-interaction-id pseudo-fields, under distinct ids.
            'atomicHpId' => ElementorAtomic::HP_ID,
            'atomicKeyId' => ElementorAtomic::KEY_ID,
        ]) . ';', 'before');

        wp_register_style('maspik-guard', false, [], MASPIK_VERSION);
        wp_enqueue_style('maspik-guard');
        wp_add_inline_style(
            'maspik-guard',
            '.maspik-field{display:none!important;pointer-events:none!important;opacity:0!important;position:absolute!important;left:-99999px!important;}'
        );
    }
}
