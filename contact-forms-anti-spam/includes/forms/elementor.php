<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Main Elementor validation functions
 *
 */

add_action( 'elementor_pro/forms/validation', 'efas_validation_process' , 10, 2 );
function efas_validation_process ( $record, $ajax_handler ) {
  $spam = false;
  $reason ="";
    
  $NeedPageurl =  maspik_get_settings("NeedPageurl");   
  
  if ( efas_get_spam_api('NeedPageurl') ){
    $NeedPageurl = $NeedPageurl ? $NeedPageurl : efas_get_spam_api('NeedPageurl',"bool");
  }


  if( !array_key_exists('referrer', $_POST ) && $NeedPageurl ){
    $spam = true;
    $reason = "Page source url is empty";
    $message = "block_empty_source";
    $error_message = cfas_get_error_text("block_empty_source");
    $spam_val = $reason;
  }

    
    
    if(!$spam){
      //  $ip = efas_getRealIpAddr();
      $meta = $record->get_form_meta( [ 'page_url', 'page_title', 'user_agent', 'remote_ip' ] );
      $ip =  $meta['remote_ip']['value'] ? $meta['remote_ip']['value'] : efas_getRealIpAddr();

      // Country IP Check 
      $GeneralCheck = GeneralCheck($ip,$spam,$reason,$_POST,"elementor");
      $spam = isset($GeneralCheck['spam']) ? $GeneralCheck['spam'] : false ;
      $reason = isset($GeneralCheck['reason']) ? $GeneralCheck['reason'] : false ;
      $message = isset($GeneralCheck['message']) ? $GeneralCheck['message'] : false ;
      $error_message = cfas_get_error_text($message);
      $spam_val = $GeneralCheck['value'] ? $GeneralCheck['value'] : false ;
    }   
    
    $fields = $record->get_field(0);
    // Get the last element of the array
    $lastKey = end($fields);

    if ($lastKey['type'] === 'hidden') {
        // Move the internal pointer to the second-to-last element
        $secondLastKey = prev($fields);
        $lastKey = $secondLastKey;
    }

    // Retrieve the key of the last (or second-to-last) element
    $lastKeyId = key($fields);

  if ( $spam ) {
    efas_add_to_log($type = "General",$reason, $_POST['form_fields'],"Elementor forms", $message,  $spam_val);
    $ajax_handler->add_error( $lastKeyId, $error_message );
  }
  
}

// Validate the Text fields.
add_action( 'elementor_pro/forms/validation/text', function( $field, $record, $ajax_handler ) {
    $field_value = strtolower($field['value']);
    if(!$field_value){
      return;
    }
	$validateTextField = validateTextField($field_value);
    $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
    $message = isset($validateTextField['message']) ? $validateTextField['message'] : 0;
    $spam_lbl = isset($validateTextField['label']) ? $validateTextField['label'] : 0 ;
    $spam_val = isset($validateTextField['option_value']) ? $validateTextField['option_value'] : 0 ;

    if( $spam ) {
        $error_message = cfas_get_error_text($message);
        efas_add_to_log($type = "text",$spam, $_POST['form_fields'],"Elementor forms", $spam_lbl, $spam_val);          
        $ajax_handler->add_error( $field['id'], $error_message );
    }
}, 10, 3 );

// Validate the Email fields.
add_action('elementor_pro/forms/validation/email', function ($field, $record, $ajax_handler) {
    
    $field_value = strtolower($field['value']);
    if (!$field_value) {
        return;
    }
	// check Email For Spam
	$spam = checkEmailForSpam($field_value);
  $spam_val = $field_value;

    if ($spam) {
       	$error_message = cfas_get_error_text("emails_blacklist");
        efas_add_to_log($type = "email", "Email $field_value is block $spam", $_POST['form_fields'],"Elementor forms", "emails_blacklist", $spam_val);
        $ajax_handler->add_error($field['id'], $error_message);
    }

}, 10, 3);


// preg_match the Tel field to the given format.
add_action( 'elementor_pro/forms/validation/tel', function( $field, $record, $ajax_handler ) {
  	$field_value = $field['value']; 
    if ( empty( $field_value ) ) {
        return false; // Not spam if the field is empty or no formats are provided.
    }
  
  	$checkTelForSpam = checkTelForSpam($field_value);
 	  $reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
 	  $valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
    $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;  
    $spam_lbl = isset($checkTelForSpam['label']) ? $checkTelForSpam['label'] : 0 ;
    $spam_val = isset($checkTelForSpam['option_value']) ? $checkTelForSpam['option_value'] : 0 ;
    
    if(!$valid){
      efas_add_to_log($type = "tel",$reason ,$_POST['form_fields'],"Elementor forms", $spam_lbl, $spam_val);
      $ajax_handler->add_error( $field['id'], cfas_get_error_text( $message) );
    }
    
}, 10, 3 );
           
// Validate the Textarea field.
add_action( 'elementor_pro/forms/validation/textarea', function( $field, $record, $ajax_handler ) {
  	$field_value = strtolower($field['value']); 

    if(!$field_value){
      return;
    }

    $checkTextareaForSpam = checkTextareaForSpam($field_value);
    $spam = isset($checkTextareaForSpam['spam'])? $checkTextareaForSpam['spam'] : 0;
    $message = isset($checkTextareaForSpam['message'])? $checkTextareaForSpam['message'] : 0;
  	$error_message = cfas_get_error_text($message);
    $spam_lbl = isset($checkTextareaForSpam['label']) ? $checkTextareaForSpam['label'] : 0 ;
    $spam_val = isset($checkTextareaForSpam['option_value']) ? $checkTextareaForSpam['option_value'] : 0 ;

    if ( $spam ) {
          efas_add_to_log($type = "textarea",$spam, $_POST['form_fields'],"Elementor forms", $spam_lbl, $spam_val);
          $ajax_handler->add_error( $field['id'], $error_message );
    }

}, 10, 3 );

add_filter( 'elementor_pro/forms/wp_mail_message', function( $content ) {
  $add_country_to_emails = maspik_get_settings("add_country_to_emails", '', 'old')  == "yes";
  if( $content && $add_country_to_emails ){
    $countryName = maspik_add_country_to_submissions($linebreak = "<br>");
     return $content.$countryName;
  }
  return $content;
}, 10, 1 );

// Add HP fields to form 
function add_maspik_hp_html_to_elementor_form(  ) {

    if ( maspik_get_settings('maspikHoneypot') || maspik_get_settings('maspikTimeCheck') || maspik_get_settings('maspikYearCheck') ) {
        $addhtml = "";

        $addhtml .= maspik_get_settings('maspikHoneypot') ? '<div class="elementor-field-type-text elementor-field-group maspik-field">
            <label for="full-name-maspik-hp" class="elementor-field-label">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="'.maspik_HP_name().'" id="'.maspik_HP_name().'" class="elementor-field elementor-size-sm elementor-field-textual" placeholder="Leave this field empty">
        </div>' : '';
        
        $addhtml .= maspik_get_settings('maspikYearCheck') ? '<div class="elementor-field-type-text elementor-field-group maspik-field">
            <label for="Maspik-currentYear" class="elementor-field-label">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="elementor-field elementor-size-sm elementor-field-textual" placeholder="">
        </div>' : '';
        
        $addhtml .= maspik_get_settings('maspikTimeCheck') ? '<div class="elementor-field-type-text elementor-field-group maspik-field">
            <label for="Maspik-exactTime" class="elementor-field-label">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="elementor-field elementor-size-sm elementor-field-textual" placeholder="">
        </div>' : '';

        echo $addhtml;
    }

}
// Only add HP fields if its a phone field, if it does not have a phone field, it will add throw a JS. (Couldn't find another way of adding)
// TODO: find php way to hook the elementor <form>
add_action( 'elementor_pro/forms/render_field/tel', 'add_maspik_hp_html_to_elementor_form'  );