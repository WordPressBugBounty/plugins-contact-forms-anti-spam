# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Product context

**Maspik** (product name) is a WordPress anti-spam plugin distributed as **`contact-forms-anti-spam`** on WordPress.org. It blocks spam across form plugins and core WP forms without CAPTCHA. Free tier + **Pro** license (gated via `cfes_is_supporting()`).

There is **no automated test suite** — verify changes manually via the admin Playground form, a real form plugin, and the Spam Log.

## Development Environment

This is a WordPress plugin running under MAMP. There is no build step — PHP files are served directly.

- **WordPress root**: `/Applications/MAMP/htdocs/sites/maspik/`
- **Plugin slug**: `contact-forms-anti-spam`
- **Text domain**: `contact-forms-anti-spam`
- **Current version**: bump `MASPIK_VERSION` in `contact-forms-anti-spam.php` (and the plugin header in the same file). Record releases in `README.txt` (== Changelog ==) and `CHANGELOG.md`.

To update translation `.po`/`.mo` files after string changes, use WP-CLI or PoEdit targeting `languages/`.

The `license/vendor/` directory is managed by Composer (inside `license/`). Run `composer install` there only when updating the license library dependency.

## Legacy naming (read before grepping)

The codebase predates the Maspik brand. Prefixes are mixed:

| Prefix | Examples | Role |
|--------|----------|------|
| `maspik_*` | `maspik_get_settings`, `maspik_get_real_ip` | Newer APIs |
| `efas_*` | `efas_add_to_log`, `efas_get_spam_api`, `efas_makeArray` | Logging, dashboard cache, arrays |
| `cfas_*` | `cfas_get_error_text`, `cfas_refresh_api` | User-facing errors, dashboard sync |
| `cfes_*` | `cfes_is_supporting` | Pro license gate |

`includes/functions.php` (~3500 lines) is the central hub for settings, logging, import/export, Matrix quotas, and admin AJAX — expect most cross-cutting changes there.

## Architecture Overview

### Boot sequence

`contact-forms-anti-spam.php` → (on `init` hook) `maspik_plugins_loaded()` → instantiates `Maspik` (in `includes/class-maspik.php`) → `$plugin->run()`.

`Maspik::load_dependencies()` conditionally requires each form integration file based on whether the target plugin is active and the matching `maspik_support_*` setting is not `"no"`.

### Settings storage

Settings are **not** stored in `wp_options`. They live in a custom database table accessed through:

- `maspik_get_settings($key, $type = '')` — reads a setting. Pass `'select'` as `$type` to get an array of row objects (used for multi-value fields like country/language blacklists).
- `maspik_save_settings($key, $value)` — writes a setting.
- `efas_get_spam_api($key, $type)` — reads the Pro **dashboard cache** stored in `wp_options` as `spamapi` (populated by `cfas_refresh_api()`). Behavior varies by field:
  - **Boolean toggles** (`maspikHoneypot`, `maspikTimeCheck`, `NeedPageurl`, `maspik_ai_enabled`, …): **OR** — active if **either** local or dashboard is on (`$MASPIK_SYNC_BOOL_API_TO_LOCAL` in `consts.php`).
  - **Blacklists / limits** in validators: typically **merged** with local lists (e.g. `array_merge` in `validateTextField()`), not “local overrides dashboard”.
  - Returns `false` when not Pro, no `private_file_id` / `popular_spam`, or key missing from cache.

Default values are defined in `$MASPIK_DEFAULT_SETTINGS` in `includes/consts.php`.

### Spam check pipeline

Each form integration runs checks in **two phases**:

1. **Per-field validators** (inside the integration loop) — blacklist, char limits, links, emoji, phone format, etc.
2. **`GeneralCheck()`** in `includes/spam-block.php` — IP-level and cross-cutting checks, called once per submission with `$content_fields` for Matrix.

`GeneralCheck()` runs (when applicable) in roughly this order:

1. **Honeypot** — hidden field `full-name-maspik-hp` must be empty.
2. **Spam key** (`maspikTimeCheck`) — key from `maspik_get_spam_key()` injected via JS must match `$_POST['maspik_spam_key']`.
3. **Country blacklist** — geolocate IP via `free.freeipapi.com` (Pro: `cfes_is_supporting("country_location")`).
4. **IP blacklist** — exact match and CIDR.
5. **AbuseIPDB** / **Proxycheck.io** / **Numverify** — optional external APIs.
6. **Maspik Matrix** — `maspik_ai_check_submission()` when `maspik_ai_enabled` (see below).

**Direct POST / bot context:** setting `NeedPageurl` (UI: “Direct POST attack”) makes integrations raise `maspik_matrix_raise_plugin_spam_likelihood_floor()` when expected POST markers are missing (e.g. CF7 without `_wpcf7` → sentinel `no_cf7_id`). Documented sentinels live in `includes/ai-spam-check.php` (`maspik_matrix_referrer_for_payload`).

**IP resolution:** use `maspik_get_real_ip()` (`includes/class-maspik-client-ip.php`) — only trusts forwarded headers from known proxies (Cloudflare, Sucuri).

On block: `efas_add_to_log()` → `{prefix}maspik_spam_logs`. User message: `cfas_get_error_text($message_key)`.

Per-field validators (called from integrations):

| Function | Field type |
|---|---|
| `validateTextField()` | `<input type="text">` |
| `checkTextareaForSpam()` | `<textarea>` |
| `checkEmailForSpam()` | Email fields |
| `checkUrlForSpam()` | URL fields |
| `checkTelForSpam()` | Phone fields |

All check functions return an array with `spam`, `reason`, `message`, `value` keys. `message` is the option key that caused the block (used for logging and filter deletion from the UI).

### Form integrations (`includes/forms/`)

Each file hooks into its form plugin's validation action/filter. **Reference implementation:** `includes/forms/cf7.php`.

Typical pattern:

1. Respect `maspik_disable_*` filter (documented at top of each integration file).
2. Loop fields → run per-field validators; on hit → `efas_add_to_log()` + return plugin-specific error.
3. Build `$content_fields` (text, email, tel, textarea only — no hidden/select/checkbox).
4. Optional: raise Matrix floor for missing form markers when `NeedPageurl` is on.
5. `GeneralCheck(maspik_get_real_ip(), …, $_POST, $form_slug, $content_fields)`.
6. On block → log + invalidate.

**Always loaded:** `wp-general.php` (comments, registration, Woo reviews), `playground.php` (admin test form).

**Conditionally loaded** in `includes/class-maspik.php`: CF7, Elementor (+ `elementor-atomic-forms.php`), Gravity, WPForms (Pro), WooCommerce orders (`woocommerce-orders.php`, Pro), Divi, Breakdance, Bricks, etc. — each requires the target plugin active and `maspik_support_*` ≠ `"no"`.

Developer hooks: https://wpmaspik.com/documentation/developers/

### Capabilities (admin access)

- `maspik_user_can_manage_settings()` → `manage_options` — settings, import/export, clear log, statistics AJAX actions (block IP/country).
- `maspik_user_can_view_spam_log()` → `edit_pages` — spam log, CSV export, statistics page (read-only). Editors see the Maspik menu; opening the parent slug redirects them to the log.

### Pro features

`cfes_is_supporting($feature)` gates Pro-only functionality. Key feature strings: `"country_location"`, `"plugin"`. The license system lives in `license/` and uses the `ideologix/dlm-wp-simple-checker` Composer package.

### Maspik Matrix (AI layer)

`includes/ai-spam-check.php` — endpoint `MASPIK_AI_ENDPOINT` (`https://ipapi.wpmaspik.com/check`).

- **API mode** `maspik_matrix_api_mode`: `2` = IP only, `3` = IP + banned words, `4` = full pipeline (default when invalid/unset).
- **Quota:** Free = 100 Matrix API calls/month; Pro = unlimited. **Only remote API calls count** — local rules do not decrement the quota (`maspik_matrix_monthly_limit()`, `maspik_is_reach_limit_and_increment_api_requests()`).
- **Floor:** `maspik_matrix_raise_plugin_spam_likelihood_floor(1–9, $referrer)` before `GeneralCheck()` so local signals influence Matrix without blocking alone.
- Server `429` → cached locally via transient `maspik_matrix_limit_reached`.

### Database

- `{prefix}maspik_options` — all plugin settings (`option_name` / `option_value` rows).
- `{prefix}maspik_spam_logs` — spam event log (`spam_type`, `spam_value`, `spam_detail`, `spam_ip`, `spam_country`, `spam_agent`, `spam_date`, `spam_source`, `spamsrc_label`, `spamsrc_val`, `spam_tag`).

Table creation/migration: `create_maspik_table()`, `create_maspik_log_table()`, `maspik_check_and_update_log_table()` in `contact-forms-anti-spam.php`.

### Admin UI

`admin/class-maspik-admin.php` registers the settings page at `admin.php?page=maspik`. Views live in `admin/partials/`. Statistics are handled by `admin/maspik-statistics.php` and displayed via `admin/views/statistics-page.php`.

### Key constants and globals (`includes/consts.php`)

- `$MASPIK_TOGGLE_MAP` — maps setting keys to their corresponding toggle option keys.
- `$MASPIK_FIELD_DISPLAY_NAMES` — human-readable labels for each setting key (used in spam log UI).
- `$MASPIK_IMPORT_OPTIONS` — exhaustive list of all exportable/importable option keys.
- `MASPIK_TEMPLATES` — preset configuration bundles (ecommerce, blog, seo, general, etc.).
- `MASPIK_AI_ENDPOINT` — Matrix API URL (`https://ipapi.wpmaspik.com/check`).
