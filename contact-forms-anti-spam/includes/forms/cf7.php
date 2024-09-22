<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/*
* CF7 Hooks
*/

add_filter( 'wpcf7_validate', 'efas_wpcf7_validate_process' , 10, 2 );
function efas_wpcf7_validate_process ( $result, $tags ) {
  $error_message = cfas_get_error_text();
  $reversed = array_reverse($tags);
  $last = $reversed[1];
  $spam = false;
  $reason ="";
  // ip
  $ip =  efas_getRealIpAddr();

  
  // Country IP Check 
    $GeneralCheck = GeneralCheck($ip,$spam,$reason,$_POST,"cf7");
    $spam = isset($GeneralCheck['spam']) ? $GeneralCheck['spam'] : false ;
    $reason = isset($GeneralCheck['reason']) ? $GeneralCheck['reason'] : false ;
    $message = $GeneralCheck['message'] ? $GeneralCheck['message'] : false ;
    $spam_val = $GeneralCheck['value'] ? $GeneralCheck['value'] : false ;
  
  //If country or ip is in blacklist
  if ( $spam ) {
          $result['valid'] = false;
          $result['reason'] = $reason;
      	  $result->invalidate( $last, cfas_get_error_text($message) );
          $post_entrys = array_filter($_POST, function($key) {
            return strpos($key, '_wpcf7') === false;
            }, ARRAY_FILTER_USE_KEY);

      efas_add_to_log($type = "General", $reason, $post_entrys, "Contact from 7", $message,  $spam_val);
  	}
	return $result;
}


// Add custom validation for CF7 text fields
function efas_cf7_text_validation_filter($result,$tag){
	$type = $tag['type'];
	$name = $tag['name'];
  	$field_value = strtolower($_POST[$name]); 
    if ( empty( $field_value ) ) {
      return $result;
    }
  
	$validateTextField = validateTextField($field_value);
    $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0 ;
    $message = isset($validateTextField['message']) ? $validateTextField['message'] : 0 ;
    $spam_lbl = isset($validateTextField['label']) ? $validateTextField['label'] : 0 ;
    $spam_val = isset($validateTextField['option_value']) ? $validateTextField['option_value'] : 0 ;

    if( $spam ) {
      $error_message = cfas_get_error_text($message);
      $post_entrys = array_filter($_POST, function($key) {
            return strpos($key, '_wpcf7') === false;
            }, ARRAY_FILTER_USE_KEY);
      efas_add_to_log($type = "text","$spam", $post_entrys, "Contact from 7", $spam_lbl, $spam_val);          
      $result['valid'] = false;
      $result->invalidate( $tag, $error_message );
    }
    
	return $result;
}

add_filter('wpcf7_validate_text','efas_cf7_text_validation_filter', 10, 2); // Normal field
add_filter('wpcf7_validate_text*', 'efas_cf7_text_validation_filter', 10, 2); // Req. field

// Add custom validation for CF7 email fields
function efas_cf7_email_validation_filter($result,$tag){
	$type = $tag['type'];
	$name = $tag['name'];
	$the_value = $_POST[$name];
  	$field_value = strtolower($the_value); 
    if ( empty( $field_value ) ) {
      return $result;
    }
	// check Email For Spam
	$spam = checkEmailForSpam($field_value);
  $spam_val = $field_value;

   if( $spam ) {
      $error_message = cfas_get_error_text();
      $post_entrys = array_filter($_POST, function($key) {
            return strpos($key, '_wpcf7') === false;
            }, ARRAY_FILTER_USE_KEY);
       efas_add_to_log($type = "email","Email $field_value is block $spam" , $post_entrys, "Contact from 7", "emails_blacklist", $spam_val);
      $result['valid'] = false;
      $result->invalidate( $tag, $error_message );
   }
   return $result;
}
add_filter('wpcf7_validate_email','efas_cf7_email_validation_filter', 10, 2); // Normal field
add_filter('wpcf7_validate_email*', 'efas_cf7_email_validation_filter', 10, 2); // Req. field


// Add custom validation for CF7 tel fields
function efas_cf7_tel_validation_filter($result,$tag){
	$type = $tag['type'];
	$name = $tag['name'];
	$field_value = $_POST[$name];
    if ( empty( $field_value ) ) {
		return $result;
    }
  
  	$checkTelForSpam = checkTelForSpam($field_value);
 	$reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
 	$valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
  $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;
   // $condition = isset($checkTelForSpam['condition']) ? $checkTelForSpam['condition'] : 0 ;
  $spam_lbl = isset($checkTelForSpam['label']) ? $checkTelForSpam['label'] : 0 ;
  $spam_val = isset($checkTelForSpam['option_value']) ? $checkTelForSpam['option_value'] : 0 ;

  

  	if(!$valid){
        $post_entrys = array_filter($_POST, function($key) {
            return strpos($key, '_wpcf7') === false;
            }, ARRAY_FILTER_USE_KEY);
        $error_message = cfas_get_error_text($message); 
        efas_add_to_log($type = "tel", $reason , $post_entrys, "Contact from 7", $spam_lbl, $spam_val);
        $result['valid'] = false;
        $result->invalidate( $tag, $error_message );
    } 

	return $result;
}
add_filter('wpcf7_validate_tel','efas_cf7_tel_validation_filter', 10, 2); // Normal field
add_filter('wpcf7_validate_tel*', 'efas_cf7_tel_validation_filter', 10, 2); // Req. field

// Add custom validation for CF7 textarea fields
function efas_cf7_textarea_validation_filter($result,$tag){
	$type = $tag['type'];
	$name = $tag['name'];
  	$field_value = strtolower( $_POST[$name] ) ; 
    if($field_value == "" || !$field_value ){
		return $result;
    }

    $checkTextareaForSpam = checkTextareaForSpam($field_value);
    $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : 0;
    $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : 0;
  	$error_message = cfas_get_error_text($message);
    $spam_lbl = isset($checkTextareaForSpam['label']) ? $checkTextareaForSpam['label'] : 0 ;
    $spam_val = isset($checkTextareaForSpam['option_value']) ? $checkTextareaForSpam['option_value'] : 0 ;
    
    if ( $spam ) {
        $post_entrys = array_filter($_POST, function($key) {
            return strpos($key, '_wpcf7') === false;
            }, ARRAY_FILTER_USE_KEY);
        efas_add_to_log($type = "textarea",$spam, $post_entrys, "Contact from 7", $spam_lbl, $spam_val);
        $result['valid'] = false;
        $result->invalidate( $tag, $error_message );
        return $result;	
	}
  
	return $result;
}
add_filter('wpcf7_validate_textarea','efas_cf7_textarea_validation_filter', 10, 2); // Normal field
add_filter('wpcf7_validate_textarea*', 'efas_cf7_textarea_validation_filter', 10, 2); // Req. field



// maspik_add_text_to_mail_components
function maspik_add_text_to_mail_components( $components, $number ) {
  $add_country_to_emails = maspik_get_settings("add_country_to_emails", '', 'old')  == "yes";
  if($components && $add_country_to_emails){
     $countryName = maspik_add_country_to_submissions($linebreak = "");
	 $body = $components['body'];
     $components[ 'body' ] = $body.$countryName;
  }
	return $components;
}
add_filter( 'wpcf7_mail_components', 'maspik_add_text_to_mail_components', 10, 2 );


function add_custom_html_to_cf7_form( $form_content ) {

    if ( maspik_get_settings('maspikHoneypot') || maspik_get_settings('maspikTimeCheck') || maspik_get_settings('maspikYearCheck') ) {
        $custom_html = "";

        if (maspik_get_settings('maspikHoneypot')) {
            $custom_html .= '<div class="wpcf7-form-control-wrap maspik-field">
                <label for="full-name-maspik-hp" class="wpcf7-form-control-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="full-name-maspik-hp" id="full-name-maspik-hp" class="wpcf7-form-control wpcf7-text" placeholder="Leave this field empty">
            </div>';
        }

        if (maspik_get_settings('maspikYearCheck')) {
            $custom_html .= '<div class="wpcf7-form-control-wrap maspik-field">
                <label for="Maspik-currentYear" class="wpcf7-form-control-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="wpcf7-form-control wpcf7-text" placeholder="">
            </div>';
        }

        if (maspik_get_settings('maspikTimeCheck')) {
            $custom_html .= '<div class="wpcf7-form-control-wrap maspik-field">
                <label for="Maspik-exactTime" class="wpcf7-form-control-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="wpcf7-form-control wpcf7-text" placeholder="">
            </div>';
        }

        $form_content .= $custom_html;
    }

    return $form_content;
}
add_filter( 'wpcf7_form_elements', 'add_custom_html_to_cf7_form' );
