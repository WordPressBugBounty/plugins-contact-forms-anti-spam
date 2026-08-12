<?php

declare(strict_types=1);

namespace Maspik\Admin\Rest;

use Maspik\Infrastructure\Settings\DashboardRules;
use Maspik\Infrastructure\Settings\Settings;
use Maspik\Premium\ProGate;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Central-Dashboard connection. A site connects with its Dashboard ID; the
 * Dashboard then pushes shared rules (blacklists) into the local `spamapi`
 * option, which the engine already merges into every check.
 *
 *   GET    /maspik/v1/dashboard          → { connected, id, synced_rules }
 *   POST   /maspik/v1/dashboard  {id}    → connect + pull rules
 *   DELETE /maspik/v1/dashboard          → disconnect
 *
 * refresh() pulls the shared rules from wpmaspik.com (mirrors v2's
 * cfas_refresh_api) and normalises them into the shape the engine reads from
 * `spamapi`. Runs on connect and can be re-fired on a cron.
 */
final class DashboardController
{
    private const API_BASE = 'https://wpmaspik.com/wp-json/acf/v3';

    /** Cron hook that re-pulls Dashboard rules (v2 weekly refresh). */
    public const CRON_HOOK = 'maspik_dashboard_sync';

    /** @var Settings */
    private $settings;

    /** @var ProGate */
    private $pro;

    public function __construct(Settings $settings, ProGate $pro)
    {
        $this->settings = $settings;
        $this->pro = $pro;
    }

    public function registerRoutes(): void
    {
        $can = static function (): bool {
            return current_user_can('manage_options');
        };

        register_rest_route('maspik/v1', '/dashboard', [
            ['methods' => 'GET', 'callback' => [$this, 'status'], 'permission_callback' => $can],
            ['methods' => 'POST', 'callback' => [$this, 'connect'], 'permission_callback' => $can],
            ['methods' => 'DELETE', 'callback' => [$this, 'disconnect'], 'permission_callback' => $can],
        ]);
    }

    public function status(): WP_REST_Response
    {
        $id = $this->settings->raw('maspik_dashboard_id');

        return new WP_REST_Response([
            'connected' => $id !== '',
            'id' => $id,
            'synced_rules' => $this->syncedRuleCount(),
            'last_sync' => get_option('maspik_dashboard_last_sync', ''),
        ]);
    }

    public function connect(WP_REST_Request $request): WP_REST_Response
    {
        if (! $this->pro->supports('pro')) {
            return new WP_REST_Response(['ok' => false, 'reason' => 'requires_pro'], 403);
        }

        $params = (array) $request->get_json_params();
        $id = isset($params['id']) ? sanitize_text_field((string) $params['id']) : '';
        if ($id === '') {
            return new WP_REST_Response(['ok' => false, 'reason' => 'missing_id'], 400);
        }

        $this->settings->save('maspik_dashboard_id', $id);
        $this->refresh();

        if (! wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + DAY_IN_SECONDS, 'daily', self::CRON_HOOK);
        }

        return $this->status();
    }

    public function disconnect(): WP_REST_Response
    {
        $this->settings->save('maspik_dashboard_id', '');
        delete_option('spamapi');
        delete_option('maspik_dashboard_last_sync');
        wp_clear_scheduled_hook(self::CRON_HOOK);

        return $this->status();
    }

    /** Cron callback: re-pull the shared rules. */
    public function sync(): void
    {
        $this->refresh();
    }

    /**
     * Pull shared rules from wpmaspik.com into the `spamapi` option (v2
     * cfas_refresh_api). Pro-only, like v2's cfes_is_supporting() gate.
     */
    private function refresh(): void
    {
        $id = $this->settings->raw('maspik_dashboard_id');
        if ($id === '' || ! $this->pro->supports('pro')) {
            return;
        }

        $domain = (string) wp_parse_url(home_url(), PHP_URL_HOST);

        // Private per-account file (num is v2's opaque cache-buster).
        $acf = $this->fetchAcf(self::API_BASE . '/apis/' . rawurlencode($id) . '?num=2367816&site=' . rawurlencode($domain));

        // Optional shared "popular spam" list, appended to the text/email/url
        // fields (v2 behaviour, gated on a setting; off by default).
        if ($this->settings->bool('maspik_popular_spam')) {
            $popular = $this->fetchAcf(self::API_BASE . '/options/public_api?num=234442&site=' . rawurlencode($domain));
            foreach (['text_field', 'textarea_field', 'email_field', 'url_field', 'contain_links'] as $f) {
                if (isset($popular[$f]) && $popular[$f] !== '') {
                    $acf[$f] = isset($acf[$f]) && $acf[$f] !== '' ? $acf[$f] . "\n" . $popular[$f] : $popular[$f];
                }
            }
        }

        update_option('spamapi', DashboardRules::normalize($acf));
        update_option('maspik_dashboard_last_sync', current_time('mysql'));
        /** Fires after the site pulls rules from the Dashboard. */
        do_action('maspik/dashboard_refresh', $id);
    }

    /** GET an ACF v3 endpoint; returns its `acf` payload or []. */
    private function fetchAcf(string $url): array
    {
        $response = wp_remote_get($url, ['timeout' => 15]);
        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            return [];
        }
        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        return is_array($body) && isset($body['acf']) && is_array($body['acf']) ? $body['acf'] : [];
    }

    private function syncedRuleCount(): int
    {
        $dashboard = get_option('spamapi');
        if (! is_array($dashboard)) {
            return 0;
        }
        $count = 0;
        foreach ($dashboard as $value) {
            if (is_array($value)) {
                $count += count($value);
            }
        }

        return $count;
    }
}
