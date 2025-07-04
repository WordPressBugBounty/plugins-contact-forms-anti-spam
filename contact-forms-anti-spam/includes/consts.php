<?php
// If this file is called directly, abort.
if (!defined('ABSPATH')) exit;

// Toggle mapping constants
$MASPIK_TOGGLE_MAP = [
    // Text related toggles
    'MaxCharactersInTextField' => 'text_limit_toggle',
    'custom_error_message_MaxCharactersInTextField' => 'text_custom_message_toggle',
    
    // Textarea related toggles
    'MaxCharactersInTextAreaField' => 'textarea_limit_toggle',
    'contain_links' => 'textarea_link_limit_toggle',
    'custom_error_message_MaxCharactersInTextAreaField' => 'textarea_custom_message_toggle',
    'emoji_check' => 'emoji_custom_message_toggle',
    
    // Phone related toggles
    'MaxCharactersInPhoneField' => 'tel_limit_toggle',
    'custom_error_message_MaxCharactersInPhoneField' => 'phone_limit_custom_message_toggle',
    'custom_error_message_tel_formats' => 'phone_custom_message_toggle',
    'tel_formats' => 'phone_custom_message_toggle',
    
    // Language related toggles
    'lang_needed' => 'lang_need_custom_message_toggle',
    'lang_forbidden' => 'lang_forbidden_custom_message_toggle',
    
    // Country related toggles
    'country_blacklist' => 'country_custom_message_toggle'
];

// Value conversion mapping
$MASPIK_FIELD_DISPLAY_NAMES = [
    // Text related fields
    "text_blacklist" => "Text Field",
    "MinCharactersInTextField" => "Text Field Min Character",
    "MaxCharactersInTextField" => "Text Field Max Character",
    
    // Email fields
    "emails_blacklist" => "Email Field",
    
    // Textarea fields
    "textarea_blacklist" => "Textarea Field",
    "MinCharactersInTextAreaField" => "Textarea Field Min Character",
    "MaxCharactersInTextAreaField" => "Textarea Field Max Character",
    
    // Phone fields
    "tel_formats" => "Phone Format Field",
    "MinCharactersInPhoneField" => "Phone Field Min Character",
    "MaxCharactersInPhoneField" => "Phone Field Max Character",
    
    // Language fields
    "lang_needed" => "Language Required",
    "lang_forbidden" => "Language Forbidden",
    
    // Other fields
    "country_blacklist" => "Countries",
    "ip_blacklist" => "IP",
    "maspikHoneypot" => "Honeypot",
    "maspikTimeCheck" => "Time Check",
    "maspikYearCheck" => "Year Check",
    "emoji_check" => "Emoji Check"
]; 



// Plugin mapping array for maspik_if_plugin_is_active()
$MASPIK_PLUGIN_MAP = [
    'Elementor pro' => 'elementor-pro',
    'Buddypress' => 'buddypress',
    'Hello Plus' => 'hello-plus',
    'Contact form 7' => 'contact-form-7',
    'Woocommerce Review' => 'woocommerce',
    'Woocommerce Registration' => 'woocommerce',
    'Wpforms' => 'wpforms',
    'Gravityforms' => 'gravityforms',
    'Formidable' => 'formidable',
    'Fluentforms' => 'fluentforms',
    'Bricks' => 'bricks',
    'Forminator' => 'forminator',
    'Wordpress Registration' => 'Wordpress Registration',
    'Ninjaforms' => 'ninjaforms',
    'Jetforms' => 'jetforms',
    'Everestforms' => 'everestforms',
    'Wordpress Comments' => 'comments',
    'Custom PHP Forms' => 'custom'
];


$MASPIK_IMPORT_OPTIONS = [
    // Text field options
    'text_blacklist',
    'text_limit_toggle',
    'text_custom_message_toggle',
    'MinCharactersInTextField',
    'MaxCharactersInTextField',
    'custom_error_message_MaxCharactersInTextField',
    
    // Email options
    'emails_blacklist',
    
    // Textarea options
    'textarea_blacklist',
    'textarea_limit_toggle',
    'textarea_link_limit_toggle',
    'textarea_custom_message_toggle',
    'MinCharactersInTextAreaField',
    'MaxCharactersInTextAreaField',
    'contain_links',
    'custom_error_message_MaxCharactersInTextAreaField',
    
    // Phone options
    'tel_formats',
    'tel_limit_toggle',
    'MinCharactersInPhoneField',
    'MaxCharactersInPhoneField',
    'phone_custom_message_toggle',
    'custom_error_message_tel_formats',
    'phone_limit_custom_message_toggle',
    
    // Language options
    'lang_needed',
    'lang_need_custom_message_toggle',
    'custom_error_message_lang_needed',
    'lang_forbidden',
    'lang_forbidden_custom_message_toggle',
    'custom_error_message_lang_forbidden',
    
    // Country options
    'country_blacklist',
    'AllowedOrBlockCountries',
    'country_custom_message_toggle',
    'custom_error_message_country_blacklist',
    
    // Other options
    'private_file_id',
    'popular_spam',
    'NeedPageurl',
    'ip_blacklist',
    'error_message',
    
    // API options
    'abuseipdb_api',
    'abuseipdb_score',
    'proxycheck_io_api',
    'proxycheck_io_risk'
];


// Default settings array
$MASPIK_DEFAULT_SETTINGS = [
    // General settings
    'maspikDbCheck' => '1',
    'maspikHoneypot' => '1',
    'NeedPageurl' => '1',
    'maspikTimeCheck' => '1', // Spam key check, default on from 2.4.5
    'maspik_Store_log' => 'yes',
    'spam_log_limit' => '1000',
    'contain_links' => '',
    
    // Form support settings
    'maspik_support_cf7' => 'yes',
    'maspik_support_wp_comment' => 'yes',
    'maspik_support_formidable_forms' => 'yes',
    'maspik_support_forminator_forms' => 'yes',
    'maspik_support_fluentforms_forms' => 'yes',
    'maspik_support_bricks_forms' => 'yes',
    'maspik_support_Elementor_forms' => 'yes',
    'maspik_support_hello_plus_forms' => 'yes',
    'maspik_support_registration' => 'yes',
    'maspik_support_ninjaforms' => 'yes',
    'maspik_support_jetforms' => 'yes',
    'maspik_support_everestforms' => 'yes',
    'maspik_support_gravity_forms' => 'yes',
    'maspik_support_Wpforms' => 'yes',
    'maspik_support_woocommerce_review' => 'yes',
    'maspik_support_Woocommerce_registration' => 'yes',
    'maspik_support_buddypress_forms' => 'yes',
    'maspik_support_helloplus_forms' => 'yes'
];

// load templates settings array
const MASPIK_TEMPLATES = array(
    'ecommerce' => array(
        'description' => 'Optimized for online stores with enhanced protection against spam queries, fake reviews, and automated form submissions. Includes specific filters for common e-commerce spam patterns.',
        'text_blacklist' => "\nseo\ncripto\ndiscount\nfree shipping\norder now\npromo code",
        'emails_blacklist' => "*@spam.com\n*@temp-mail.*\n/^sales.*@/",
        'textarea_blacklist' => "buy now\ncheck out\norder today\nspecial offer\ndiscount code",
    ),
    'blog' => array(
        'description' => 'Perfect for blogs, portfolios and service websites. Balanced protection that keeps your forms accessible while blocking common spam patterns and automated submissions.',
        'text_blacklist' => "seo\nbacklink\nrank\nranking\ntraffic\nvisitor\nclick here\nlink exchange\nlink building\nbitcoin\nbtc",
        'emails_blacklist' => "*@spam.com\n*@temp.*\n/^marketing.*@/",
        'textarea_blacklist' => "check my blog\nnice article\ngreat post\nbacklink exchange",
    ),
    'seo' => array(
        'description' => 'Specially configured for SEO, marketing and web agencies. Enhanced protection And allow SEO professionals to get more traffic and backlinks.',
        'text_blacklist' => "seo\nranking\ntraffic\nbacklinks\nmarketing",
        'emails_blacklist' => "*@spam.com\n*@temp.*\n/^seo.*@/",
        'textarea_blacklist' => "increase traffic\nimprove ranking\nseo services",
    ),
    'onlyusa' => array(
        'description' => 'Restricts form submissions to US-based users only. Perfect for businesses that exclusively serve the United States market. (Pro Feature)',
        'AllowedOrBlockCountries' => 'allow',
        'country_blacklist' => 'US'
    ),
    'onlyeu' => array(
        'description' => 'Limits form submissions to European Union countries only. Ideal for EU-focused businesses and GDPR compliance. (Pro Feature)',
        'AllowedOrBlockCountries' => 'allow',
        'country_blacklist' => 'Continent:EU'
    ),
    'onlychina' => array(
        'description' => 'Configures forms to accept submissions only from China. Perfect for businesses targeting the Chinese market exclusively. (Pro Feature)',
        'AllowedOrBlockCountries' => 'allow',
        'country_blacklist' => 'CN'
    ),
    'latinlangneeded' => array(
        'description' => 'Requires submissions to contain Latin-based languages only. Ideal for Western-focused businesses wanting to ensure communication clarity. (Pro Feature)',
        'lang_needed' => '\p{Latin}',
        'lang_forbidden' => '\p{Han}\p{Arabic}\p{Cyrillic}'
    ),
    'general' => array(
        'description' => 'A balanced configuration suitable for most websites. Provides good overall protection against common spam patterns while maintaining form accessibility, not for SEO agencies. link wont be allowed in text area fields',
        'text_blacklist' => "\nbinance\nspam\nscam\nfree\nurgent\nmoney\nSEO\nRanking\nGoogle Ads\nFiverr\nCrypto\nviagra\npoker\ncasino\nxxx\nsex\nporn\nfuck\ndating\npenis\nbitch\nhacker\npussy",
        'emails_blacklist' => "ericjones\n*@temp.*\n*t.me*",
        'textarea_blacklist' => "\nnbinance\nleads\nInvestment\nINSTANTLY\nInstagram growth\nyour website\nYouTube channel\nSubscribe\nunsubcribe\nbit.ly\nbitly\nClick here\nscotsindallas\nwork from home\nmake money fast\nget rich quick\nno experience\ntoo good to be true\nwinner\nbonus\ncongratulations\nrb.gy/\n✅\n⮕\n→\nUnlock the\n(SMS)\n365/24\nLearn More\nGet 50% off\nthem today!\nhigh-quality content\nAI Tools\ncontent generation\nAI tools\ninstant access\nAI power\nno limits\nNo subscriptions\n$100+\nMidJourney\nDALL·E 3\nChatGPT-4",
    )
);
