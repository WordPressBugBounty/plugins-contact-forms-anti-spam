<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * AI-based spam check functionality for Maspik plugin
 * 
 * This file contains the main AI spam check function that communicates
 * with an external AI API to detect spam submissions.
 * 
 * @package Maspik
 * @since 2.5.5
 */

/**
 * Check submission using AI-based spam detection
 * 
 * @param array $fields Array of form fields and their values
 * @return array Array containing spam check results
 */
function maspik_ai_check_submission( array $fields ): array {
    // Get AI settings from options/DB with fallbacks to constants
    $endpoint = MASPIK_AI_ENDPOINT;

    // Pull license & token from the DLM option first
    $dlm = get_option('maspik_dlm_license'); // array with keys: key, token, expires_at, etc.
    $license = 'try_free_as_beta';
    $token   = 'try_free_as_beta';
    if ( is_array( $dlm ) && cfes_is_supporting() ) {
        $license = isset($dlm['key'])   ? trim((string)$dlm['key'])   : '';
        $token   = isset($dlm['token']) ? trim((string)$dlm['token']) : '';
    }
    
    //error_log('Maspik AI: Endpoint=' . $endpoint . ', Mode=' . (maspik_is_ai_beta_mode() ? 'beta' : 'live'));
    

    $threshold = (int) maspik_get_settings('maspik_ai_threshold', 60 ) ;
    $threshold = $threshold < 3 ? 50 : $threshold;
    $context   = (string) maspik_get_settings('maspik_ai_context', '' );
    
    // Limit context to 170 characters
    if ( strlen($context) > 170 ) {
        $context = substr($context, 0, 170);
    }
    // In the new flow, the HMAC secret is the site token from DLM
    $secret    = $token;
    
    // Remove verbose config logging

    // If AI is disabled or missing configuration, allow submission
    if ( empty($endpoint) ) {
            return ['allow' => true, 'reason' => 'AI disabled: missing endpoint'];
    }
    
    if ( empty($license) ) {
        return ['allow' => true, 'reason' => 'AI disabled: missing license key'];
    }
    
    if ( empty($token) ) {
        return ['allow' => true, 'reason' => 'AI disabled: missing site token'];
    }

    // Build request payload
    $payload = [
        'fields'        => $fields,
        'context'       => [
            'business_info' => $context,
            'site_url'      => home_url(),
            'plugin_version'=> defined('MASPIK_VERSION') ? MASPIK_VERSION : 'dev',
            'site_title_and_tagline' => get_bloginfo('name') . ' ' . get_bloginfo('description'),
            'site_language' => get_locale(),
        ],
    ];
    
    // JSON string (not array!) - important for Lambda to receive proper structure
    $body = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    
    // Verify JSON is valid
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        //error_log('Maspik AI: JSON encode error: ' . json_last_error_msg());
        return ['allow' => true, 'reason' => 'AI error: JSON encoding failed'];
    }
    
    //error_log('Maspik AI: Sending fields: ' . print_r($fields, true));
    //error_log('Maspik AI: Context: ' . $context. ' License: ' . $license. ' Token: ' . $token);

    // Set up headers with authentication (License + site token + HMAC)
    $headers = [
        'Content-Type'      => 'application/json',
        'X-Maspik-Token'    => $token, // send token so Lambda can validate & cache on first request
    ];

    
    $headers['Authorization'] = 'Bearer ' . $license;
    

    // Add signature (HMAC over raw body using the per-site token)
    if ( ! empty($secret) ) {
        $raw_sig = hash_hmac('sha256', $body, $secret, true);
        $headers['X-Maspik-Signature'] = base64_encode($raw_sig);
    }

    // Send request to AI API
    $resp = wp_remote_post( $endpoint, [
        'timeout'   => 7,
        'headers'   => $headers,
        'body'      => $body,     // keep as string, not array
        'sslverify' => true,
    ]);

    // Handle request errors gracefully
    if ( is_wp_error($resp) ) {
        // On failure, don't block - allow submission and log error
        $error_msg = $resp->get_error_message();
        //error_log('Maspik AI Error: ' . $error_msg);
        return ['allow' => true, 'reason' => 'AI unavailable: ' . $error_msg];
    }

    $code = wp_remote_retrieve_response_code($resp);
    $body = wp_remote_retrieve_body($resp);
    
    // Log only important response info
    //error_log('Maspik AI Response: ' . $code . ' - ' .$body);
    // error_log('Maspik AI Response Headers: ' . print_r(wp_remote_retrieve_headers($resp), true));
    
    // Try to decode JSON response
    $json = null;
    if ( !empty($body) ) {
        $json = json_decode( $body, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            //error_log('Maspik AI: JSON decode failed - ' . json_last_error_msg());
        }
    }

    // Process successful response
    if ( $code === 200 && is_array($json) && !empty($json) ) {
        // Validate required fields in response
        if ( !isset($json['response']['spam_score']) ) {
                //error_log('Maspik AI: Missing spam_score in response');
            return ['allow' => true, 'reason' => 'AI response invalid: missing spam_score'];
        }
        
        $score  = (int)$json['response']['spam_score'];
        
        // Validate score is within valid range
        if ( $score < 0 || $score > 100 ) {
            //error_log('Maspik AI: Invalid score ' . $score . ' (0-100 expected)');
            return ['allow' => true, 'reason' => 'AI response invalid: spam_score out of range (0-100)'];
        }
        
        $reason = isset($json['response']['reason']) ? (string)$json['response']['reason'] : '';
        $errors = !empty($json['response']['field_errors']) && is_array($json['response']['field_errors']) ? $json['response']['field_errors'] : [];

        $block = ( $score >= $threshold );
        
        //error_log('Maspik AI Result: Score=' . $score . ', Threshold=' . $threshold . ', Block=' . ($block ? 'yes' : 'no') . ', Provider=' . ($json['provider_used'] ?? 'unknown'));
        
        $result = [
            'allow'        => !$block,
            'score'        => $score,
            'reason'       => $reason,
            'field_errors' => $errors,
            'provider'     => $json['provider_used'] ?? 'ai',
            "prompt"      => $json['aiprompt'] ?? [],
            "business_info_preview" => $json['business_info_preview'] ?? [],
        ];
        
        // Save AI log to database (only if we have valid data)
        if ( is_array($fields) && is_array($json) && is_array($result) ) {
            try {
                maspik_save_ai_log($fields, $json, $result);
            } catch ( Exception $e ) {
                //error_log('Maspik AI: Failed to save log: ' . $e->getMessage());
            }
        }
        
        return $result;
    }

    // Handle specific error codes with better error messages
    if ( $code === 401 ) {
        if ( maspik_is_ai_beta_mode() ) {
            $error_result = ['allow' => true, 'reason' => 'AI beta mode: unauthorized - check if endpoint supports beta mode'];
        } else {
            $error_result = ['allow' => true, 'reason' => 'AI live mode: unauthorized - check license key and signature'];
        }
        
        // Save 401 error log
        $error_response = ['error' => true, 'http_code' => 401, 'error_detail' => 'Unauthorized', 'response_body' => $body];
        if ( is_array($fields) && is_array($error_response) && is_array($error_result) ) {
            try {
                maspik_save_ai_log($fields, $error_response, $error_result);
            } catch ( Exception $e ) {
                if ( defined('WP_DEBUG') && WP_DEBUG ) {
                    error_log('Maspik AI: Failed to save 401 error log: ' . $e->getMessage());
                }
            }
        }
        return $error_result;
    }
    
    if ( $code === 403 ) {
        $error_result = ['allow' => true, 'reason' => 'AI live mode: license invalid or expired'];
        
        // Save 403 error log
        $error_response = ['error' => true, 'http_code' => 403, 'error_detail' => 'License invalid or expired', 'response_body' => $body];
        if ( is_array($fields) && is_array($error_response) && is_array($error_result) ) {
            try {
                maspik_save_ai_log($fields, $error_response, $error_result);
            } catch ( Exception $e ) {
            }
        }
        return $error_result;
    }
    
    if ( $code === 429 ) {
        $error_result = ['allow' => true, 'reason' => 'AI: rate limit exceeded'];
        
        // Save 429 error log
        $error_response = ['error' => true, 'http_code' => 429, 'error_detail' => 'Rate limit exceeded', 'response_body' => $body];
        if ( is_array($fields) && is_array($error_response) && is_array($error_result) ) {
            try {
                maspik_save_ai_log($fields, $error_response, $error_result);
            } catch ( Exception $e ) {
                if ( defined('WP_DEBUG') && WP_DEBUG ) {
                    error_log('Maspik AI: Failed to save 429 error log: ' . $e->getMessage());
                }
            }
        }
        return $error_result;
    }
    
    if ( $code === 500 ) {
        $error_result = ['allow' => true, 'reason' => 'AI server error - check server logs'];
        
        // Enhanced 500 error logging with more details
        $error_detail = 'Server error';
        $response_headers = wp_remote_retrieve_headers($resp);
        
        // Try to extract more info from response body
        if ( !empty($body) ) {
            $json_error = json_decode($body, true);
            if ( $json_error && isset($json_error['error']) ) {
                $error_detail = $json_error['error'];
            } else {
                $error_detail = 'Server error: ' . substr($body, 0, 200); // First 200 chars
            }
        }
        
        // Save enhanced 500 error log
        $error_response = [
            'error' => true, 
            'http_code' => 500, 
            'error_detail' => $error_detail, 
            'response_body' => $body,
            'response_headers' => $response_headers ? $response_headers->getAll() : [],
            'timestamp' => current_time('mysql')
        ];
        
        if ( is_array($fields) && is_array($error_response) && is_array($error_result) ) {
            try {
                maspik_save_ai_log($fields, $error_response, $error_result);
            } catch ( Exception $e ) {
                if ( defined('WP_DEBUG') && WP_DEBUG ) {
                    error_log('Maspik AI: Failed to save 500 error log: ' . $e->getMessage());
                }
            }
        }
        
        // Also log to WordPress error log for immediate visibility
        if ( defined('WP_DEBUG') && WP_DEBUG ) {
            error_log('Maspik AI 500 Error: ' . $error_detail . ' | Response: ' . substr($body, 0, 500));
        }
        
        return $error_result;
    }

    // Handle unknown error codes
    $error_body = wp_remote_retrieve_body($resp);
    $error_detail = '';
    
    if ( !empty($error_body) ) {
        $error_json = json_decode($error_body, true);
        if ( is_array($error_json) && isset($error_json['error']) ) {
            $error_detail = ' - ' . $error_json['error'];
        }
    }
        
    $error_result = ['allow' => true, 'reason' => 'AI unknown error ' . $code . $error_detail];
    
    // Save error log to database
    $error_response = [
        'error' => true,
        'http_code' => $code,
        'error_detail' => $error_detail,
        'response_body' => $error_body
    ];
    
    if ( is_array($fields) && is_array($error_response) && is_array($error_result) ) {
        try {
            maspik_save_ai_log($fields, $error_response, $error_result);
        } catch ( Exception $e ) {
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                error_log('Maspik AI: Failed to save error log: ' . $e->getMessage());
            }
        }
    }
    
    return $error_result;
}

/**
 * Save AI log entry to settings
 * 
 * @param array $fields Form fields that were submitted
 * @param array $ai_response Full AI response
 * @param array $result Final result (allow/block)
 * @return bool Success status
 */
function maspik_save_ai_log( array $fields, array $ai_response, array $result ): bool {
    // Get current logs and ensure it's an array
    $current_logs = maspik_get_settings('maspik_ai_logs', []);
    
    // If current_logs is null or not an array, initialize as empty array
    if ( !is_array($current_logs) ) {
        $current_logs = [];
    }
    
    // Prepare new log entry
    $log_entry = [
        'timestamp' => current_time('mysql'),
        'fields' => $fields,
        'ai_response' => $ai_response,
        'result' => $result,
        'ip_address' => maspik_get_real_ip(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    ];
    
    // Add new entry to beginning of array
    array_unshift($current_logs, $log_entry);
    
    // Keep only last 10 entries
    $current_logs = array_slice($current_logs, 0, 10);
    
    // Save back to settings
    $saved = maspik_save_settings('maspik_ai_logs', $current_logs);
        
    return $saved;
}

/**
 * Get AI logs from settings (last 10 entries)
 * 
 * @return array Array of log entries
 */
function maspik_get_ai_logs(): array {
    $logs = maspik_get_settings('maspik_ai_logs', []);
    
    // Ensure we return an array
    if ( !is_array($logs) ) {
        return [];
    }
    
    return $logs;
}


/**
 * Check if AI endpoint is in beta mode
 * 
 * @return bool True if beta mode is enabled
 */
function maspik_is_ai_beta_mode(): bool {
    // Check if there's a specific beta mode setting
   
    return 1;
}

/**
 * Helper function to prepare fields for AI analysis
 * 
 * @param array $form_data Raw form data
 * @param string $form_type Type of form (e.g., 'elementor', 'contact-form-7') - now optional, uniform processing
 * @return array Processed fields ready for AI analysis - flat key-value array
 */
function maspik_prepare_fields_for_ai( array $form_data, string $form_type = '' ): array {
    $processed_fields = [];
    
    // Handle special form structures first, then flatten to uniform structure
    $raw_fields = [];
    
    // Extract fields based on known form structures
    if ( $form_type === 'elementor' && isset($form_data['form_fields']) ) {
        // Elementor stores fields in form_fields sub-array
        $raw_fields = $form_data['form_fields'];
    } else {
        // For all other forms, use the data as-is
        $raw_fields = $form_data;
    }
    
    // Process all fields uniformly
    foreach ( $raw_fields as $key => $value ) {
        // Convert key to string for consistent processing
        $key = (string) $key;
        
        // Skip fields that start with underscore
        if ( strpos($key, '_') === 0 ) {
            continue;
        }
        
        // Skip specific unwanted keys
        if ( in_array($key, ['maspik_spam_key', 'full-name-maspik-hp'], true) ) {
            continue;
        }
        
        // Skip keys that contain unwanted terms
        $unwanted_terms = ['action', 'nonce', 'submit', 'referrer','captcha','time'];
        $skip_field = false;
        foreach ( $unwanted_terms as $term ) {
            if ( strpos($key, $term) !== false ) {
                $skip_field = true;
                break;
            }
        }
        if ( $skip_field ) {
            continue;
        }
        
        // Extract value from nested structures
        $processed_value = $value;
        
        // Handle array values (extract 'value' key if exists)
        if ( is_array($processed_value) ) {
            if ( isset($processed_value['value']) ) {
                $processed_value = $processed_value['value'];
            } else {
                // For other array structures, convert to string representation
                $processed_value = implode(', ', array_filter(array_map('strval', $processed_value)));
            }
        }
        
        // Convert to string and sanitize
        $processed_value = sanitize_text_field((string) $processed_value);
        
        // Skip empty values after processing
        if ( empty($processed_value) ) {
            continue;
        }
        
        // Limit field value to 500 characters
        if ( strlen($processed_value) > 500 ) {
            $processed_value = substr($processed_value, 0, 500);
        }
        
        // Add to final processed fields array
        $processed_fields[$key] = $processed_value;
    }
    
    return $processed_fields;
} 