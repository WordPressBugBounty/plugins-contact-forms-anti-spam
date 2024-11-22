<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
// Buddypress

/**
 * Check BuddyPress registration form for spam
 */
function maspik_check_bp_registration_form() {
    $error_message   = cfas_get_error_text();
    $spam            = false;
    $reason          = '';
    $message         = '';
    $spam_lbl        = '';
    $spam_val        = '';
  //  $user_email      = isset($_POST['signup_email']) ? sanitize_email($_POST['signup_email']) : '';
    $ip              = efas_getRealIpAddr();
    global $bp;

    $user_email = sanitize_email($bp->signup->email);

    // General Check
    if (!$spam) {
        $GeneralCheck = GeneralCheck($ip, $spam, $reason, $_POST, "buddypress_registration");
        $spam         = $GeneralCheck['spam'] ?? false;
        $reason       = $GeneralCheck['reason'] ?? '';
        $message      = $GeneralCheck['message'] ?? '';
        $spam_val     = $GeneralCheck['value'] ?? '';
        $spam_lbl     = $GeneralCheck['reason'] ?? '';
    }

    // Email check
    if ($user_email && !$spam) {
        $spam = checkEmailForSpam($user_email);
        if ($spam && !$reason) {
            $reason     = "Email $user_email is blocked";
            $spam_lbl   = 'emails_blacklist';
            $spam_val   = $user_email;
        }
    }

    // Log and display error if spam is detected
    if ($spam) {
        efas_add_to_log("Registration", $reason, $_POST, 'BuddyPress registration', $spam_lbl, $spam_val);
        $error_message = cfas_get_error_text($message);
//        bp_core_add_message($error_message, 'error');
        $bp->signup->errors['signup_email'] = $error_message;
        return;
    }
}
add_action('bp_signup_validate', 'maspik_check_bp_registration_form');

/**
 * Add honeypot field to the BuddyPress registration form
 */
function maspik_add_honeypot_to_bp_registration_form() {
    
    if (maspik_get_settings('maspikHoneypot')) {

        echo '<div class="register-section maspik-field" id="maspik-honeypot-section">
        <label for="full-name-maspik-hp">Leave this field empty</label>
            <input type="text" name="full-name-maspik-hp" id="full-name-maspik-hp" value="" tabindex="-1" autocomplete="off" />
        </div>';
    }

    if ( maspik_get_settings( 'maspikYearCheck' ) ) {
        echo '<div class="register-section maspik-field">
            <label for="Maspik-currentYear" class="wpcf7-form-control-label"></label>
            <input size="1" type="text" autocomplete="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="buddypress-form-control" placeholder="">
        </div>';
    }

    if ( maspik_get_settings( 'maspikTimeCheck' ) ) {
        echo '<div class="register-section maspik-field">
            <label for="Maspik-exactTime" class="wpcf7-form-control-label"></label>
            <input size="1" type="text" autocomplete="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="buddypress-form-control" placeholder="">
        </div>';
    }


}
add_action('bp_before_registration_submit_buttons', 'maspik_add_honeypot_to_bp_registration_form', 9999);