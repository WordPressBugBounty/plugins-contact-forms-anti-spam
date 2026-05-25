# Maspik Plugin — WordPress Security Audit Report

**Plugin:** Maspik – Ultimate Spam Protection  
**Version:** 2.8.1.1  
**Plugin slug:** `contact-forms-anti-spam`  
**Files audited:** 41 PHP files  
**Audit date:** 2026-05-17  
**Total issues:** 34 (2 Critical, 4 High, 17 Medium, 9 Low, 2 Informational)

---

## Summary Table

| ID   | File | Line(s) | Severity | Issue Type |
|------|------|---------|----------|------------|
| C-01 | admin/class-maspik-admin.php | 12 | Critical | Hardcoded API Key |
| C-02 | includes/statistics-data.php | 91, 164 | Critical | Hardcoded Weak Authentication Secret |
| H-01 | includes/functions.php | 1479–1483 | High | Missing Nonce / CSRF |
| H-02 | admin/maspik-statistics.php | 215–450 | High | SQL Injection Risk (Raw Query Pattern) |
| H-03 | admin/maspik-statistics.php | 155–170 | High | XSS via Unescaped JSON in Script Tag |
| H-04 | includes/dashboard-statistics.php | 488 | High | XSS via Unescaped JSON in Script Tag |
| M-01 | contact-forms-anti-spam.php | 244 | Medium | XSS — Unescaped Nonce in HTML Attribute |
| M-02 | admin/class-maspik-admin.php | 520–554 | Medium | Unsanitized `$_GET` Passed to Action Hook |
| M-03 | admin/class-maspik-admin.php | 617 | Medium | Unsanitized `$_SERVER` in Email Header |
| M-04 | admin/class-maspik-admin.php | 573–686 | Medium | Missing Capability Check on AJAX Handler |
| M-05 | admin/class-maspik-admin.php | 638–653 | Medium | Information Disclosure in AJAX Response |
| M-06 | includes/functions.php | 96 | Medium | XSS — Unescaped URL in HTML Attribute |
| M-07 | includes/functions.php | 595–600 | Medium | SQL — Unparameterized DELETE Query |
| M-08 | includes/functions.php | 1313, 1336 | Medium | SSRF / Improper URL Construction |
| M-09 | includes/functions.php / spam-block.php | 627 / 163 | Medium | SSRF — IP in External API URL |
| M-10 | includes/functions.php | 677 | Medium | Unsanitized `$_SERVER` Stored in DB |
| M-11 | includes/statistics-data.php | 24, 130 | Medium | Unsanitized `$_SERVER` in External Request |
| M-12 | includes/functions.php | 1951–1992 | Medium | Insufficient Capability Check (CSV Download) |
| M-13 | admin/maspik-statistics.php | 47–51 | Medium | Insufficient Date Parameter Validation |
| M-14 | admin/class-maspik-admin.php | 483–489 | Medium | Role Name Used Instead of Capability |
| M-15 | includes/forms/playground.php | 9–15 | Medium | Missing Nonce on Public AJAX Handler |
| M-16 | admin/partials/maspik-log.php | 30 | Medium | `wp_redirect()` Instead of `wp_safe_redirect()` |
| M-17 | includes/statistics-data.php | 181–185 | Medium | SQL `IN` Clause Without `prepare()` |
| L-01 | includes/forms/everest.php | 1–3 | Low | Missing Direct-Access Guard |
| L-02 | includes/forms/jetform.php | 1–4 | Low | Missing Direct-Access Guard |
| L-03 | includes/functions.php | 326, 562 | Low | SQL Raw Query (trusted table name) |
| L-04 | admin/partials/maspik-log.php | 27, 111 | Low | SQL Raw Query (trusted table name) |
| L-05 | includes/functions.php | 1246 | Low | Inline JS Alert in Admin Context |
| L-06 | admin/partials/maspik-log.php | 239 | Low | Inconsistent Escaping Functions |
| L-07 | includes/functions.php | 1921–1946 | Low | `htmlspecialchars()` vs `esc_html()` |
| L-08 | includes/functions.php | 2935 | Low | Missing `isset()` Before Nonce Check |
| L-09 | contact-forms-anti-spam.php | 159–168 | Low | SQL Raw `ALTER`/`SHOW` (trusted table name) |
| I-01 | includes/statistics-data.php | throughout | Info | Privacy — Telemetry Scope |
| I-02 | contact-forms-anti-spam.php | ~574 | Info | Privacy — Deactivation Survey |

---

## Critical Severity

---

### C-01 — Hardcoded API Key

- **File:** `admin/class-maspik-admin.php:12`
- **Issue type:** Secret exposure
- **Description:** A live API key is hardcoded directly in PHP source. Anyone with filesystem access or access to the source repository can read it. If this key authenticates against the Maspik dashboard or any external service, it can be abused.

**Vulnerable code:**
```php
```

**Fix:** Remove from source. Store as a WordPress option or a constant in `wp-config.php` defined outside the plugin directory. Rotate the key immediately.

---

### C-02 — Hardcoded Weak Authentication Secret

- **File:** `includes/statistics-data.php:91, 164`
- **Issue type:** Insecure authentication
- **Description:** The static string `plug1n` is appended as `?key=plug1n` to authenticate data uploads to `receiver.wpmaspik.com`. Since this value is in the public plugin source, any third party can craft requests to that endpoint or impersonate any site running the plugin.

**Vulnerable code:**
```php
$url = "https://receiver.wpmaspik.com/wp-json/statistics-maspik/v1/statistics?..." . "&key=plug1n";
$url = "https://receiver.wpmaspik.com/wp-json/statistics-maspik/v1/spamlog?..."    . "&key=plug1n";
```

**Fix:** Replace with a per-site HMAC-signed request (the same pattern already used in `includes/ai-spam-check.php`). Rotate the server-side secret on `receiver.wpmaspik.com` immediately.

---

## High Severity

---

### H-01 — Missing Nonce Verification on AJAX Handler (CSRF)

- **File:** `includes/functions.php:1479–1483`
- **Issue type:** CSRF
- **Description:** `Maspik_dismiss_notice_callback()` is registered as `wp_ajax_Maspik_dismiss_notice` but contains no nonce verification. Any logged-in user at any privilege level can call this action and permanently suppress the data-sharing consent notice for the entire site.

**Vulnerable code:**
```php
function Maspik_dismiss_notice_callback() {
    set_transient('Mapik_dismissed_shereing_notice', true, MONTH_IN_SECONDS);
    wp_die();
}
add_action('wp_ajax_Maspik_dismiss_notice', 'Maspik_dismiss_notice_callback');
```

**Fix:**
```php
function Maspik_dismiss_notice_callback() {
    check_ajax_referer('maspik_dismiss_notice', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_die(-1, 403);
    }
    set_transient('Mapik_dismissed_shereing_notice', true, MONTH_IN_SECONDS);
    wp_die();
}
```
Pass `wp_create_nonce('maspik_dismiss_notice')` from the admin page that renders the notice.

---

### H-02 — SQL Injection Risk (Raw Query Pattern)

- **File:** `admin/maspik-statistics.php:215–450`
- **Issue type:** SQL injection risk
- **Description:** `$where_clause` is built from preset strings for standard date ranges, but for custom date ranges it is constructed with `$wpdb->prepare()`. That prepared fragment is then interpolated into a bare string passed to `$wpdb->get_var()`, `$wpdb->get_results()`, etc. — approximately 15 separate queries. This pattern is inherently fragile: the variable `$where_clause` is never treated as untrusted, and any future code path that allows a user-controlled value into it produces direct SQL injection.

**Representative vulnerable code:**
```php
$where_clause = "date >= DATE_SUB(NOW(), INTERVAL 7 DAY)"; // preset
// or:
$where_clause = $wpdb->prepare("date BETWEEN %s AND %s", $start, $end); // custom

$total_blocked  = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where_clause");
$countries_data = $wpdb->get_results("SELECT country, COUNT(*) ... FROM $table WHERE $where_clause ...");
```

**Fix:** Refactor queries to use `$wpdb->prepare()` for the full query string. Validate `$range` against an explicit allowlist before building `$where_clause`.

---

### H-03 — XSS via Unescaped JSON in Script Tag

- **File:** `admin/maspik-statistics.php:155–170`
- **Issue type:** Stored XSS
- **Description:** Database-sourced spam log data (country names, IP addresses, page URLs, user agents — all of which can contain attacker-controlled content) is inserted into a `<script>` block using plain `json_encode()`. PHP's `json_encode()` does not escape `</script>`, `<!--`, or Unicode line terminators. A spam log entry containing `</script><script>alert(1)</script>` in any of those fields would execute JavaScript in the admin's browser.

**Vulnerable code:**
```php
echo "<script>
    var maspikStats = {
        timelineData: " . json_encode($timeline_data) . ",
        countriesData: " . json_encode($countries_data) . ",
        ...
    };
</script>";
```

**Fix:**
```php
$flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
echo '<script>var maspikStats = ' . wp_json_encode($data, $flags) . ';</script>';
```

---

### H-04 — XSS via Unescaped JSON in Script Tag

- **File:** `includes/dashboard-statistics.php:488`
- **Issue type:** Stored XSS
- **Description:** Same pattern as H-03. `$type_totals` (spam type labels from the log) is echoed into a script block with plain `json_encode()`. A helper `maspik_safe_json_encode()` with correct hex-escaping flags already exists in this file but is not used here.

**Vulnerable code:**
```php
const typeData = <?php echo json_encode($type_totals); ?>;
```

**Fix:** Use the existing helper:
```php
const typeData = <?php echo maspik_safe_json_encode($type_totals); ?>;
```

---

## Medium Severity

---

### M-01 — XSS: Unescaped Nonce in HTML Attribute

- **File:** `contact-forms-anti-spam.php:244`
- **Issue type:** XSS
- **Description:** A nonce value is echoed into an HTML attribute without `esc_attr()`. Nonces are hexadecimal and unlikely to be malicious in practice, but unescaped output in attribute context violates WordPress coding standards and is fragile if nonce format or filters ever change.

**Vulnerable code:** `value="<?php echo $nonce; ?>"`  
**Fix:** `value="<?php echo esc_attr($nonce); ?>"`

---

### M-02 — Unsanitized `$_GET` Passed to Action Hook

- **File:** `admin/class-maspik-admin.php:520–554`
- **Issue type:** Reflected XSS risk
- **Description:** `$_GET['error_message']` is passed unsanitized to `do_action('admin_notices', ...)` in four admin page display methods. Any code hooked to `admin_notices` that echoes the second argument without escaping produces a reflected XSS in the admin panel.

**Vulnerable code:**
```php
if (isset($_GET['error_message'])) {
    do_action('admin_notices', $_GET['error_message']);
}
```

**Fix:**
```php
if (isset($_GET['error_message'])) {
    $error = sanitize_text_field(wp_unslash($_GET['error_message']));
    echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
}
```

---

### M-03 — Unsanitized `$_SERVER` in Email Header

- **File:** `admin/class-maspik-admin.php:617`
- **Issue type:** Email header injection
- **Description:** `$_SERVER['HTTP_HOST']` is used unsanitized to construct the `From:` email address. A spoofed `Host:` header in a misconfigured reverse proxy environment could inject arbitrary content into the email header.

**Vulnerable code:** `$from_email = 'noreply@' . $_SERVER['HTTP_HOST'];`  
**Fix:** `$from_email = 'noreply@' . wp_parse_url(get_site_url(), PHP_URL_HOST);`

---

### M-04 — Missing Capability Check on AJAX Handler

- **File:** `admin/class-maspik-admin.php:573–686`
- **Issue type:** Privilege escalation
- **Description:** `maspik_handle_feedback_submission()` verifies a nonce but performs no `current_user_can()` check. Any subscriber-level logged-in user can call it, send email via the site, and receive the debug data described in M-05.

**Fix:** Add after the nonce check:
```php
if (!current_user_can('manage_options')) {
    wp_send_json_error(['message' => 'Unauthorized'], 403);
}
```

---

### M-05 — Information Disclosure in AJAX Response

- **File:** `admin/class-maspik-admin.php:638–653`
- **Issue type:** Information disclosure
- **Description:** The feedback AJAX handler returns detailed server environment data in its JSON success response: `$_SERVER['SERVER_SOFTWARE']`, PHP version, WordPress version, and the full list of active plugins. This is accessible to any logged-in user (see M-04) and enables server fingerprinting.

**Vulnerable code:**
```php
wp_send_json_success([
    'message' => 'Feedback submitted successfully',
    'debug' => [
        'server'         => $_SERVER['SERVER_SOFTWARE'],
        'php_version'    => phpversion(),
        'wp_version'     => get_bloginfo('version'),
        'active_plugins' => get_option('active_plugins'),
    ]
]);
```

**Fix:** Remove the `debug` key from the production response entirely, or gate it behind `WP_DEBUG && current_user_can('manage_options')`.

---

### M-06 — XSS: Unescaped URL in HTML Attribute

- **File:** `includes/functions.php:96`
- **Issue type:** XSS
- **Description:** `get_site_url()` is concatenated into an `href` attribute without `esc_url()`. Unlikely to contain malicious content in practice, but violates WordPress coding standards and is fragile if the site URL option contains special characters.

**Vulnerable code:** `echo "<a href='" . get_site_url() . "/wp-admin/admin.php?page=maspik_activator'>...";`  
**Fix:** `echo '<a href="' . esc_url(admin_url('admin.php?page=maspik_activator')) . '">';`

---

### M-07 — SQL: Unparameterized DELETE Query

- **File:** `includes/functions.php:595–600`
- **Issue type:** SQL
- **Description:** A DELETE query interpolates a computed integer variable directly rather than using `$wpdb->prepare()`. The value is derived from `intval()`, so not directly exploitable, but bypasses WordPress's parameterized query API.

**Vulnerable code:** `$wpdb->query("DELETE FROM $table ORDER BY id ASC LIMIT $entries_to_delete");`  
**Fix:** `$wpdb->query($wpdb->prepare("DELETE FROM {$table} ORDER BY id ASC LIMIT %d", $entries_to_delete));`

---

### M-08 — SSRF: Improper URL Construction for External APIs

- **File:** `includes/functions.php:1313, 1336`
- **Issue type:** SSRF / URL injection
- **Description:** IP addresses are concatenated directly into AbuseIPDB and Proxycheck.io API endpoint URLs without `rawurlencode()`. IP validation upstream mitigates this, but the lack of encoding is fragile if validation is ever weakened.

**Vulnerable code:**
```php
$url = "https://api.abuseipdb.com/api/v2/check?ipAddress=" . $ip . "&maxAgeInDays=90";
$url = "https://proxycheck.io/v2/" . $ip . "?key=" . $apikey . "&risk=1&vpn=1";
```

**Fix:**
```php
$url = "https://api.abuseipdb.com/api/v2/check?ipAddress=" . rawurlencode($ip) . "&maxAgeInDays=90";
$url = "https://proxycheck.io/v2/" . rawurlencode($ip) . "?key=" . rawurlencode($apikey) . "&risk=1&vpn=1";
```

---

### M-09 — SSRF: IP Appended to Geolocation API URL

- **File:** `includes/functions.php:627` and `includes/spam-block.php:163`
- **Issue type:** SSRF / URL injection
- **Description:** Same pattern as M-08 for `free.freeipapi.com`.

**Vulnerable code:** `wp_remote_get("https://free.freeipapi.com/api/json/" . $ip)`  
**Fix:** `wp_remote_get("https://free.freeipapi.com/api/json/" . rawurlencode($ip))`

---

### M-10 — Unsanitized `$_SERVER['HTTP_REFERER']` Stored in Database

- **File:** `includes/functions.php:677`
- **Issue type:** Stored data integrity / latent XSS
- **Description:** The HTTP `Referer` header is stored in the spam log without sanitization. The log display uses `esc_html()` for output (XSS mitigated at render time), but the raw unsanitized value in the database is a risk for any other code path that reads and outputs it without escaping.

**Vulnerable code:** `$url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';`  
**Fix:** `$url = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';`

---

### M-11 — Unsanitized `$_SERVER['SERVER_NAME']` in External Request

- **File:** `includes/statistics-data.php:24, 130`
- **Issue type:** SSRF / data integrity
- **Description:** `$_SERVER['SERVER_NAME']` is used without sanitization as a URL parameter in requests to `receiver.wpmaspik.com`. A spoofed `Host:` header could send incorrect or malicious domain data to the external service.

**Vulnerable code:** `$domain = $_SERVER['SERVER_NAME'];`  
**Fix:** `$domain = wp_parse_url(get_site_url(), PHP_URL_HOST);`

---

### M-12 — Insufficient Capability Check for CSV Download

- **File:** `includes/functions.php:1951–1992`
- **Issue type:** Privilege escalation / privacy
- **Description:** `Maspik_spamlog_download_csv()` checks `edit_pages` rather than `manage_options`. Editors (a default WordPress role) can therefore download the full spam log, which contains visitor IPs, email addresses, phone numbers, form content, and referer URLs.

**Vulnerable code:** `if (!current_user_can('edit_pages')) {`  
**Fix:** `if (!current_user_can('manage_options')) {`

---

### M-13 — Insufficient Date Parameter Validation

- **File:** `admin/maspik-statistics.php:47–51`
- **Issue type:** Input validation
- **Description:** `$_GET['range']`, `$_GET['start_date']`, and `$_GET['end_date']` are sanitized with `sanitize_text_field()` but not validated against an allowlist or confirmed to be valid dates. This could produce unexpected SQL behavior for malformed date strings.

**Fix:**
```php
$allowed_ranges = ['7days', '30days', '90days', 'custom'];
$range = (isset($_GET['range']) && in_array($_GET['range'], $allowed_ranges, true))
    ? $_GET['range'] : '7days';
$start_date = (isset($_GET['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['start_date']))
    ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = (isset($_GET['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['end_date']))
    ? $_GET['end_date'] : date('Y-m-d');
```

---

### M-14 — Role Name Used Instead of Capability

- **File:** `admin/class-maspik-admin.php:483–489`
- **Issue type:** Access control
- **Description:** Admin menu pages are registered with the string `'administrator'` (a role name) instead of the capability string `'manage_options'`. In standard single-site WordPress these are equivalent, but on Multisite `'administrator'` does not behave as expected and can produce incorrect access control.

**Fix:** Replace all occurrences of `'administrator'` in `add_menu_page()` / `add_submenu_page()` with `'manage_options'`.

---

### M-15 — Missing Nonce on Public AJAX Handler

- **File:** `includes/forms/playground.php:9–15`
- **Issue type:** CSRF / resource abuse
- **Description:** The playground form handler is publicly accessible (`wp_ajax_nopriv_`) and its nonce check is commented out. This allows unauthenticated users to trigger spam-check computations — including potentially rate-limited external IP API calls — without any CSRF protection or rate limiting.

**Vulnerable code:**
```php
add_action('wp_ajax_nopriv_maspik_handle_playground_form', 'maspik_handle_playground_form');
function maspik_handle_playground_form() {
    //check_ajax_referer('contact_form_nonce', 'nonce'); // COMMENTED OUT
```

**Fix:** Re-enable the nonce check. If the playground is intended only for admins, remove the `nopriv` hook entirely.

---

### M-16 — `wp_redirect()` Instead of `wp_safe_redirect()`

- **File:** `admin/partials/maspik-log.php:30`
- **Issue type:** Open redirect risk
- **Description:** `wp_redirect()` is used instead of `wp_safe_redirect()`. The URL is `admin_url()` (trusted here), but WordPress coding standards require `wp_safe_redirect()` to prevent open redirect vulnerabilities if the URL ever includes a user-influenced component.

**Vulnerable code:** `wp_redirect(admin_url('admin.php?page=maspik-log.php'));`  
**Fix:** `wp_safe_redirect(admin_url('admin.php?page=maspik-log.php'));`

---

### M-17 — SQL `IN` Clause Without `$wpdb->prepare()`

- **File:** `includes/statistics-data.php:181–185`
- **Issue type:** SQL
- **Description:** An array of IDs is joined with `implode(',', ...)` and inserted directly into an `IN()` SQL clause. Values are `intval()`'d so not directly exploitable, but this bypasses WordPress's parameterized query API.

**Vulnerable code:**
```php
$ids_string = implode(',', $ids_to_update);
$wpdb->query("UPDATE $table_name SET synced = 1 WHERE id IN ($ids_string)");
```

**Fix:**
```php
$placeholders = implode(',', array_fill(0, count($ids_to_update), '%d'));
$wpdb->query($wpdb->prepare(
    "UPDATE {$table_name} SET synced = 1 WHERE id IN ($placeholders)",
    ...$ids_to_update
));
```

---

## Low Severity

---

### L-01 — Missing Direct-Access Guard

- **File:** `includes/forms/everest.php:1–3`
- **Description:** The file has no `ABSPATH`/`WPINC` check at the top. It is always loaded via `require_once` inside a guarded class method, so exploitation is unlikely, but every plugin file should be independently guarded.
- **Fix:** Add `if (!defined('WPINC')) { die; }` as the first line after `<?php`.

---

### L-02 — Missing Direct-Access Guard

- **File:** `includes/forms/jetform.php:1–4`
- **Description:** Same as L-01.
- **Fix:** Add `if (!defined('WPINC')) { die; }`.

---

### L-03 — SQL Raw `SHOW TABLES LIKE` Queries

- **File:** `includes/functions.php:326, 562`
- **Description:** `SHOW TABLES LIKE '...'` queries use string interpolation instead of `$wpdb->prepare()`. Table name is derived from `$wpdb->prefix` (trusted), so not exploitable, but violates coding standards.
- **Fix:** `$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));`

---

### L-04 — SQL Raw Queries in Log Admin

- **File:** `admin/partials/maspik-log.php:27, 111`
- **Description:** `DELETE FROM $table` and `SELECT * FROM $table` use string interpolation on a trusted table name. Not exploitable, but inconsistent with the codebase style.
- **Fix:** Apply `esc_sql()` to the table name, or note explicitly that it is always derived from `$wpdb->prefix`.

---

### L-05 — Inline JavaScript `alert()` in Admin

- **File:** `includes/functions.php:1246`
- **Description:** `echo "<script>alert('...');</script>"` is used for admin notifications. Inline scripts bypass Content Security Policy headers. The content is hardcoded so no XSS risk, but admin notices via the `admin_notices` hook should be used instead.

---

### L-06 — Inconsistent Escaping Functions

- **File:** `admin/partials/maspik-log.php:239`
- **Description:** `htmlspecialchars($url)` is used on line 239 and `esc_url($url)` on line 242. The `htmlspecialchars()` call is redundant and the inconsistency makes the code harder to audit.
- **Fix:** Remove the `htmlspecialchars()` call; use `esc_url()` throughout.

---

### L-07 — `htmlspecialchars()` Instead of `esc_html()`

- **File:** `includes/functions.php:1921–1946`
- **Description:** `maspik_array_to_html_table()` uses PHP's `htmlspecialchars()` rather than WordPress's `esc_html()`. Functionally equivalent in most cases, but `esc_html()` respects WordPress escaping filters and is the standard.
- **Fix:** Replace `htmlspecialchars($value)` with `esc_html($value)`.

---

### L-08 — Missing `isset()` Before Nonce Verification

- **File:** `includes/functions.php:2935`
- **Description:** `$_POST['nonce']` is accessed directly without an `isset()` check, generating a PHP notice in PHP 7 and a deprecation warning in PHP 8.

**Vulnerable code:** `if (!wp_verify_nonce($_POST['nonce'], 'maspik_ajax_nonce')) {`  
**Fix:**
```php
if (empty($_POST['nonce']) || !wp_verify_nonce(
    sanitize_text_field(wp_unslash($_POST['nonce'])), 'maspik_ajax_nonce'
)) {
```

---

### L-09 — SQL Raw `ALTER TABLE` / `SHOW COLUMNS`

- **File:** `contact-forms-anti-spam.php:159–168`
- **Description:** `SHOW COLUMNS FROM $table_name` and `ALTER TABLE $table_name ADD COLUMN ...` use string interpolation. Table name is always from `$wpdb->prefix`, so not exploitable, but violates coding standards.
- **Fix:** Apply `esc_sql()` to the table name in these queries or add an inline comment documenting it is trusted.

---

## Informational / Privacy

---

### I-01 — Privacy: Telemetry Scope

- **File:** `includes/statistics-data.php`
- **Description:** When users opt into data sharing, the plugin sends to `receiver.wpmaspik.com` weekly: all plugin settings (including the AbuseIPDB, ProxyCheck, and NumVerify API keys stored in plugin settings) plus full spam log data (visitor IPs, email addresses, phone numbers, form content, referer URLs). Verify whether the privacy disclosure fully discloses that third-party API keys are included in the exported settings, and whether GDPR requires more granular consent for sending visitor data to a third-party server.

---

### I-02 — Privacy: Deactivation Survey

- **File:** `contact-forms-anti-spam.php` (deactivation survey section)
- **Description:** On plugin deactivation a survey sends the site URL and admin feedback to `receiver.wpmaspik.com`. The request uses the weak static key identified in C-02. No disclosure of this behavior appears in the plugin's in-code privacy notice.

---

## Files With No Issues Found

The following files were audited and found clean:

- `includes/class-maspik-activator.php`
- `includes/class-maspik-deactivator.php`
- `includes/class-maspik-loader.php`
- `includes/class-maspik-client-ip.php`
- `includes/consts.php`
- `includes/class-maspik.php`
- `includes/ai-spam-check.php` (HMAC-authenticated API calls)
- `includes/disable-comments.php`
- `includes/templates.php`
- `includes/forms/cf7.php`
- `includes/forms/elementor.php`
- `includes/forms/elementor-atomic-forms.php` (uses `wp_json_encode` with hex flags correctly)
- `includes/forms/wpforms.php`
- `includes/forms/ninjaforms.php`
- `includes/forms/fluentforms.php`
- `includes/forms/formidable.php`
- `includes/forms/forminator.php`
- `includes/forms/woocommerce-orders.php`
- `includes/forms/bricks.php`
- `includes/forms/breakdance.php`
- `includes/forms/buddypress.php`
- `includes/forms/divi.php`
- `includes/forms/metform.php`
- `includes/forms/bitform.php`
- `includes/forms/helloplus.php`
- `includes/forms/custom.php`
- `includes/forms/gravityforms.php`
- `admin/partials/maspik-import-export.php`
- `admin/views/statistics-page.php`
- `public/class-efas-public.php`
