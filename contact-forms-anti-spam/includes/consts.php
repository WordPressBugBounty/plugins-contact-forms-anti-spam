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

// Forbidden languages
$MASPIK_FORBIDDEN_LANGUAGES = [
    // Unicode Scripts
    '\p{Arabic}' => esc_html__('Arabic', 'contact-forms-anti-spam'),
    '\p{Armenian}' => esc_html__('Armenian', 'contact-forms-anti-spam'),
    '\p{Bengali}' => esc_html__('Bengali', 'contact-forms-anti-spam'),
    '\p{Braille}' => esc_html__('Braille', 'contact-forms-anti-spam'),
    '\p{Ethiopic}' => esc_html__('Ethiopic', 'contact-forms-anti-spam'),
    '\p{Georgian}' => esc_html__('Georgian', 'contact-forms-anti-spam'),
    '\p{Greek}' => esc_html__('Greek', 'contact-forms-anti-spam'),
    '\p{Han}' => esc_html__('Han (Chinese)', 'contact-forms-anti-spam'),
    '\p{Katakana}' => esc_html__('Katakana', 'contact-forms-anti-spam'),
    '\p{Hiragana}' => esc_html__('Hiragana', 'contact-forms-anti-spam'),
    '\p{Hebrew}' => esc_html__('Hebrew', 'contact-forms-anti-spam'),
    '\p{Syriac}' => esc_html__('Syriac', 'contact-forms-anti-spam'),
    '\p{Latin}' => esc_html__('Latin', 'contact-forms-anti-spam'),
    '\p{Mongolian}' => esc_html__('Mongolian', 'contact-forms-anti-spam'),
    '\p{Thai}' => esc_html__('Thai', 'contact-forms-anti-spam'),
    '\p{Unknown}' => esc_html__('Unknown language', 'contact-forms-anti-spam'),

    // European Languages
    '[À-ÿ]' => esc_html__('French (À-ÿ)', 'contact-forms-anti-spam'),
    '[ÄäÖöÜüß]' => esc_html__('German ([ÄäÖöÜüß])', 'contact-forms-anti-spam'),
    '[ÉÍÓÚáéíóúüÜñÑ]' => esc_html__('Spanish (ÁÉÍÓÚáéíóúüÜñÑ)', 'contact-forms-anti-spam'),
    '[A-Za-z]' => esc_html__('English (A-Za-z)', 'contact-forms-anti-spam'),
    '[ÀàÉéÈèÌìÍíÒòÓóÙùÚú]' => esc_html__('Italian (ÀàÉéÈèÌìÍíÒòÓóÙùÚú)', 'contact-forms-anti-spam'),
    '[À-ÖØ-öø-ÿ]' => esc_html__('Dutch (À-ÖØ-öø-ÿ)', 'contact-forms-anti-spam'),
    '[ÄäÅåÖö]' => esc_html__('Swedish (ÄäÅåÖö)', 'contact-forms-anti-spam'),
    '[ÆæØøÅå]' => esc_html__('Danish (ÆæØøÅå)', 'contact-forms-anti-spam'),
    '[ÆæØøÅå]' => esc_html__('Norwegian (ÆæØøÅå)', 'contact-forms-anti-spam'),

    // Eastern European Languages
    '[А-Яа-яЁё]' => esc_html__('Russian (А-Яа-яЁ)', 'contact-forms-anti-spam'),
    '[ĄąĆćĘęŁłŃńÓóŚśŹźŻż]' => esc_html__('Polish (ĄąĆćĘęŁłŃńÓóŚśŹźŻż)', 'contact-forms-anti-spam'),
    '[ĂăÂâÎîȘșȚț]' => esc_html__('Romanian (ĂăÂâÎîȘșȚț)', 'contact-forms-anti-spam'),
    '[ÁáČčĎďÉéÍíĹĺĽľŇňÓóŔŕŠšŤťÚúÝýŽž]' => esc_html__('Czech (ÁáČčĎďÉéÍíĹĺĽľŇňÓóŔŕŠšŤťÚúÝýŽž)', 'contact-forms-anti-spam'),
    '[А-ЩЬЮЯЇІЄҐа-щьюяїієґЁё]' => esc_html__('Ukrainian (А-ЩЬЮЯЇІЄҐа-щьюяїієґЁё)', 'contact-forms-anti-spam'),
    '[ÁáÉéÍíÓóÚúÜüŐőŰű]' => esc_html__('Hungarian (ÁáÉéÍíÓóÚúÜüŐőŰ)', 'contact-forms-anti-spam'),
    '[ÁáÄäČčĎďÉéÍíĹĺĽľŇňÓóÔôŔŕŠšŤťÚúÝýŽž]' => esc_html__('Slovak (ÁáÄäČčĎďÉéÍíĹĺĽľŇňÓóÔôŔŕŠšŤťÚúÝýŽž)', 'contact-forms-anti-spam'),
    '[А-Яа-яЋћĆć]' => esc_html__('Serbian (А-Яа-яЋћĆć)', 'contact-forms-anti-spam'),

    // Other Languages
    '[ÇçĞğÖöŞşÜü]' => esc_html__('Turkish (ÇçĞğİıÖöŞşÜü)', 'contact-forms-anti-spam'),
];


// Required languages
$MASPIK_REQUIRED_LANGUAGES = [
    // Unicode Scripts
    '\p{Arabic}' => esc_html__('Arabic', 'contact-forms-anti-spam'),
    '\p{Armenian}' => esc_html__('Armenian', 'contact-forms-anti-spam'),
    '\p{Bengali}' => esc_html__('Bengali', 'contact-forms-anti-spam'),
    '\p{Braille}' => esc_html__('Braille', 'contact-forms-anti-spam'),
    '\p{Ethiopic}' => esc_html__('Ethiopic', 'contact-forms-anti-spam'),
    '\p{Georgian}' => esc_html__('Georgian', 'contact-forms-anti-spam'),
    '\p{Greek}' => esc_html__('Greek', 'contact-forms-anti-spam'),
    '\p{Han}' => esc_html__('Han (Chinese)', 'contact-forms-anti-spam'),
    '\p{Katakana}' => esc_html__('Katakana', 'contact-forms-anti-spam'),
    '\p{Hiragana}' => esc_html__('Hiragana', 'contact-forms-anti-spam'),
    '\p{Hebrew}' => esc_html__('Hebrew', 'contact-forms-anti-spam'),
    '\p{Syriac}' => esc_html__('Syriac', 'contact-forms-anti-spam'),
    '\p{Latin}' => esc_html__('Latin', 'contact-forms-anti-spam'),
    '\p{Mongolian}' => esc_html__('Mongolian', 'contact-forms-anti-spam'),
    '\p{Thai}' => esc_html__('Thai', 'contact-forms-anti-spam'),
    '\p{Unknown}' => esc_html__('Unknown language', 'contact-forms-anti-spam'),
    
    // European Languages
    '[A-Za-zÀ-ÿ]' => esc_html__('French (A-Za-zÀ-ÿ)', 'contact-forms-anti-spam'),
    '[A-Za-zÄäÖöÜüß]' => esc_html__('German ([A-Za-zÄäÖöÜüß])', 'contact-forms-anti-spam'),
    '[A-Za-zÁÉÍÓÚáéíóúüÜñÑ]' => esc_html__('Spanish (A-Za-zÁÉÍÓÚáéíóúüÜñÑ)', 'contact-forms-anti-spam'),
    '[A-Za-z]' => esc_html__('English (A-Za-z)', 'contact-forms-anti-spam'),
    '[A-Za-zÀàÉéÈèÌìÍíÒòÓóÙùÚú]' => esc_html__('Italian (A-Za-zÀàÉéÈèÌìÍíÒòÓóÙùÚú)', 'contact-forms-anti-spam'),
    '[A-Za-zÀ-ÖØ-öø-ÿ]' => esc_html__('Dutch (A-Za-zÀ-ÖØ-öø-ÿ)', 'contact-forms-anti-spam'),
    '[A-Za-zÄäÅåÖö]' => esc_html__('Swedish (A-Za-zÄäÅåÖö)', 'contact-forms-anti-spam'),
    '[A-Za-zÆæØøÅå]' => esc_html__('Danish (A-Za-zÆæØøÅå)', 'contact-forms-anti-spam'),
    '[A-Za-zÆæØøÅå]' => esc_html__('Norwegian (A-Za-zÆæØøÅå)', 'contact-forms-anti-spam'),
    
    // Eastern European Languages
    '[А-Яа-яЁё]' => esc_html__('Russian (А-Яа-яЁ)', 'contact-forms-anti-spam'),
    '[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]' => esc_html__('Polish (A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż)', 'contact-forms-anti-spam'),
    '[A-Za-zĂăÂâÎîȘșȚț]' => esc_html__('Romanian (A-Za-zĂăÂâÎîȘșȚț)', 'contact-forms-anti-spam'),
    '[A-Za-zÁáČčĎďÉéÍíĹĺĽľŇňÓóŔŕŠšŤťÚúÝýŽž]' => esc_html__('Czech (A-Za-zÁáČčĎďÉéÍíĹĺĽľŇňÓóŔŕŠšŤťÚúÝýŽž)', 'contact-forms-anti-spam'),
    '[A-Za-zА-ЩЬЮЯЇІЄҐа-щьюяїієґЁё]' => esc_html__('Ukrainian (A-Za-zА-ЩЬЮЯЇІЄҐа-щьюяїієґЁё)', 'contact-forms-anti-spam'),
    '[A-Za-zÁáÉéÍíÓóÚúÜüŐőŰű]' => esc_html__('Hungarian (A-Za-zÁáÉéÍíÓóÚúÜüŐőŰ)', 'contact-forms-anti-spam'),
    '[A-Za-zÁáÄäČčĎďÉéÍíĹĺĽľŇňÓóÔôŔŕŠšŤťÚúÝýŽž]' => esc_html__('Slovak (A-Za-zÁáÄäČčĎďÉéÍíĹĺĽľŇňÓóÔôŔŕŠšŤťÚúÝýŽž)', 'contact-forms-anti-spam'),
    '[A-Za-zА-Яа-яЋћĆć]' => esc_html__('Serbian (A-Za-zА-Яа-яЋћĆć)', 'contact-forms-anti-spam')
];


$MASPIK_COUNTRIES_LIST = [
    'Continent:AS' => 'Continent: Asia',
    'Continent:AF' => 'Continent: Africa', 
    'Continent:AN' => 'Continent: Antarctica',
    'Continent:EU' => 'Continent: Europe',
    'Continent:NA' => 'Continent: North America',
    'Continent:OC' => 'Continent: Oceania',
    'AL' => 'Albania',
    'DZ' => 'Algeria',
    'AS' => 'American Samoa',
    'AD' => 'Andorra',
    'AO' => 'Angola',
    'AI' => 'Anguilla',
    'AG' => 'Antigua And Barbuda',
    'AR' => 'Argentina',
    'AM' => 'Armenia',
    'AW' => 'Aruba',
    'AU' => 'Australia',
    'AT' => 'Austria',
    'AZ' => 'Azerbaijan',
    'BS' => 'Bahamas',
    'BH' => 'Bahrain',
    'BD' => 'Bangladesh',
    'BB' => 'Barbados',
    'BY' => 'Belarus',
    'BE' => 'Belgium',
    'BZ' => 'Belize',
    'BJ' => 'Benin',
    'BM' => 'Bermuda',
    'BT' => 'Bhutan',
    'BO' => 'Bolivia',
    'BA' => 'Bosnia And Herzegovina',
    'BW' => 'Botswana',
    'BR' => 'Brazil',
    'IO' => 'British Indian Ocean Territory',
    'BN' => 'Brunei',
    'BG' => 'Bulgaria',
    'BF' => 'Burkina Faso',
    'BI' => 'Burundi',
    'KH' => 'Cambodia',
    'CM' => 'Cameroon',
    'CA' => 'Canada',
    'CV' => 'Cape Verde',
    'KY' => 'Cayman Islands',
    'CF' => 'Central African Republic',
    'TD' => 'Chad',
    'CL' => 'Chile',
    'CN' => 'China',
    'CO' => 'Colombia',
    'CG' => 'Congo',
    'CK' => 'Cook Islands',
    'CR' => 'Costa Rica',
    'CI' => 'Cote D\'ivoire',
    'HR' => 'Croatia',
    'CU' => 'Cuba',
    'CY' => 'Cyprus',
    'CZ' => 'Czech Republic',
    'CD' => 'Democratic Republic of the Congo',
    'DK' => 'Denmark',
    'DJ' => 'Djibouti',
    'DM' => 'Dominica',
    'DO' => 'Dominican Republic',
    'EC' => 'Ecuador',
    'EG' => 'Egypt',
    'SV' => 'El Salvador',
    'GQ' => 'Equatorial Guinea',
    'ER' => 'Eritrea',
    'EE' => 'Estonia',
    'ET' => 'Ethiopia',
    'FO' => 'Faroe Islands',
    'FM' => 'Federated States Of Micronesia',
    'FJ' => 'Fiji',
    'FI' => 'Finland',
    'FR' => 'France',
    'GF' => 'French Guiana',
    'PF' => 'French Polynesia',
    'GA' => 'Gabon',
    'GM' => 'Gambia',
    'GE' => 'Georgia',
    'DE' => 'Germany',
    'GH' => 'Ghana',
    'GI' => 'Gibraltar',
    'GR' => 'Greece',
    'GL' => 'Greenland',
    'GD' => 'Grenada',
    'GP' => 'Guadeloupe',
    'GU' => 'Guam',
    'GT' => 'Guatemala',
    'GN' => 'Guinea',
    'GW' => 'Guinea Bissau',
    'GY' => 'Guyana',
    'HT' => 'Haiti',
    'HN' => 'Honduras',
    'HK' => 'Hong Kong',
    'HU' => 'Hungary',
    'IS' => 'Iceland',
    'IN' => 'India',
    'ID' => 'Indonesia',
    'IR' => 'Iran',
    'IE' => 'Ireland',
    'IL' => 'Israel',
    'IM' => 'Isle of Man',
    'IT' => 'Italy',
    'JM' => 'Jamaica',
    'JP' => 'Japan',
    'JO' => 'Jordan',
    'KZ' => 'Kazakhstan',
    'KE' => 'Kenya',
    'KW' => 'Kuwait',
    'KG' => 'Kyrgyzstan',
    'LA' => 'Laos',
    'LV' => 'Latvia',
    'LB' => 'Lebanon',
    'LS' => 'Lesotho',
    'LY' => 'Libyan Arab Jamahiriya',
    'LI' => 'Liechtenstein',
    'LT' => 'Lithuania',
    'LU' => 'Luxembourg',
    'MK' => 'Macedonia',
    'MG' => 'Madagascar',
    'MW' => 'Malawi',
    'MY' => 'Malaysia',
    'MV' => 'Maldives',
    'ML' => 'Mali',
    'MT' => 'Malta',
    'MQ' => 'Martinique',
    'MR' => 'Mauritania',
    'MU' => 'Mauritius',
    'MX' => 'Mexico',
    'MC' => 'Monaco',
    'MN' => 'Mongolia',
    'ME' => 'Montenegro',
    'MA' => 'Morocco',
    'MZ' => 'Mozambique',
    'MM' => 'Myanmar',
    'NA' => 'Namibia',
    'NP' => 'Nepal',
    'NL' => 'Netherlands',
    'AN' => 'Netherlands Antilles',
    'NC' => 'New Caledonia',
    'NZ' => 'New Zealand',
    'NI' => 'Nicaragua',
    'NE' => 'Niger',
    'NG' => 'Nigeria',
    'NF' => 'Norfolk Island',
    'MP' => 'Northern Mariana Islands',
    'NO' => 'Norway',
    'OM' => 'Oman',
    'PK' => 'Pakistan',
    'PW' => 'Palau',
    'PA' => 'Panama',
    'PG' => 'Papua New Guinea',
    'PY' => 'Paraguay',
    'PE' => 'Peru',
    'PH' => 'Philippines',
    'PL' => 'Poland',
    'PT' => 'Portugal',
    'PR' => 'Puerto Rico',
    'QA' => 'Qatar',
    'MD' => 'Republic Of Moldova',
    'RE' => 'Reunion',
    'RO' => 'Romania',
    'RU' => 'Russia',
    'RW' => 'Rwanda',
    'KN' => 'Saint Kitts And Nevis',
    'LC' => 'Saint Lucia',
    'VC' => 'Saint Vincent And The Grenadines',
    'WS' => 'Samoa',
    'SM' => 'San Marino',
    'ST' => 'Sao Tome And Principe',
    'SA' => 'Saudi Arabia',
    'SN' => 'Senegal',
    'RS' => 'Serbia',
    'SC' => 'Seychelles',
    'SG' => 'Singapore',
    'SK' => 'Slovakia',
    'SI' => 'Slovenia',
    'SB' => 'Solomon Islands',
    'ZA' => 'South Africa',
    'KR' => 'South Korea',
    'ES' => 'Spain',
    'LK' => 'Sri Lanka',
    'SD' => 'Sudan',
    'SR' => 'Suriname',
    'SZ' => 'Swaziland',
    'SE' => 'Sweden',
    'CH' => 'Switzerland',
    'SY' => 'Syrian Arab Republic',
    'TW' => 'Taiwan',
    'TJ' => 'Tajikistan',
    'TZ' => 'Tanzania',
    'TH' => 'Thailand',
    'TG' => 'Togo',
    'TO' => 'Tonga',
    'TT' => 'Trinidad And Tobago',
    'TN' => 'Tunisia',
    'TR' => 'Turkey',
    'TM' => 'Turkmenistan',
    'UG' => 'Uganda',
    'UA' => 'Ukraine',
    'AE' => 'United Arab Emirates',
    'GB' => 'United Kingdom',
    'US' => 'United States',
    'UY' => 'Uruguay',
    'UZ' => 'Uzbekistan',
    'VU' => 'Vanuatu',
    'VE' => 'Venezuela',
    'VN' => 'Vietnam',
    'VG' => 'Virgin Islands British',
    'VI' => 'Virgin Islands U.S.',
    'YE' => 'Yemen',
    'ZM' => 'Zambia',
    'ZW' => 'Zimbabwe'
];
        
// Countries list for phone
$MASPIK_COUNTRIES_LIST_FOR_PHONE = [
    'none' => 'None (User will enter the phone number with the country code)',
    'AL' => 'Albania (355)',
    'DZ' => 'Algeria (213)',
    'AS' => 'American Samoa (1684)',
    'AD' => 'Andorra (376)',
    'AO' => 'Angola (244)',
    'AI' => 'Anguilla (1264)',
    'AG' => 'Antigua And Barbuda (1268)',
    'AR' => 'Argentina (54)',
    'AM' => 'Armenia (374)',
    'AW' => 'Aruba (297)',
    'AU' => 'Australia (61)',
    'AT' => 'Austria (43)',
    'AZ' => 'Azerbaijan (994)',
    'BS' => 'Bahamas (1242)',
    'BH' => 'Bahrain (973)',
    'BD' => 'Bangladesh (880)',
    'BB' => 'Barbados (1246)',
    'BY' => 'Belarus (375)',
    'BE' => 'Belgium (32)',
    'BZ' => 'Belize (501)',
    'BJ' => 'Benin (229)',
    'BM' => 'Bermuda (1441)',
    'BT' => 'Bhutan (975)',
    'BO' => 'Bolivia (591)',
    'BA' => 'Bosnia And Herzegovina (387)',
    'BW' => 'Botswana (267)',
    'BR' => 'Brazil (55)',
    'IO' => 'British Indian Ocean Territory (246)',
    'BN' => 'Brunei (673)',
    'BG' => 'Bulgaria (359)',
    'BF' => 'Burkina Faso (226)',
    'BI' => 'Burundi (257)',
    'KH' => 'Cambodia (855)',
    'CM' => 'Cameroon (237)',
    'CA' => 'Canada (1)',
    'CV' => 'Cape Verde (238)',
    'KY' => 'Cayman Islands (1345)',
    'CF' => 'Central African Republic (236)',
    'TD' => 'Chad (235)',
    'CL' => 'Chile (56)',
    'CN' => 'China (86)',
    'CO' => 'Colombia (57)',
    'CG' => 'Congo (242)',
    'CK' => 'Cook Islands (682)',
    'CR' => 'Costa Rica (506)',
    'CI' => 'Cote D\'ivoire (225)',
    'HR' => 'Croatia (385)',
    'CU' => 'Cuba (53)',
    'CY' => 'Cyprus (357)',
    'CZ' => 'Czech Republic (420)',
    'CD' => 'Democratic Republic of the Congo (243)',
    'DK' => 'Denmark (45)',
    'DJ' => 'Djibouti (253)',
    'DM' => 'Dominica (1767)',
    'DO' => 'Dominican Republic (1809)',
    'EC' => 'Ecuador (593)',
    'EG' => 'Egypt (20)',
    'SV' => 'El Salvador (503)',
    'GQ' => 'Equatorial Guinea (240)',
    'ER' => 'Eritrea (291)',
    'EE' => 'Estonia (372)',
    'ET' => 'Ethiopia (251)',
    'FO' => 'Faroe Islands (298)',
    'FM' => 'Federated States Of Micronesia (691)',
    'FJ' => 'Fiji (679)',
    'FI' => 'Finland (358)',
    'FR' => 'France (33)',
    'GF' => 'French Guiana (594)',
    'PF' => 'French Polynesia (689)',
    'GA' => 'Gabon (241)',
    'GM' => 'Gambia (220)',
    'GE' => 'Georgia (995)',
    'DE' => 'Germany (49)',
    'GH' => 'Ghana (233)',
    'GI' => 'Gibraltar (350)',
    'GR' => 'Greece (30)',
    'GL' => 'Greenland (299)',
    'GD' => 'Grenada (1473)',
    'GP' => 'Guadeloupe (590)',
    'GU' => 'Guam (1671)',
    'GT' => 'Guatemala (502)',
    'GN' => 'Guinea (224)',
    'GW' => 'Guinea Bissau (245)',
    'GY' => 'Guyana (592)',
    'HT' => 'Haiti (509)',
    'HN' => 'Honduras (504)',
    'HK' => 'Hong Kong (852)',
    'HU' => 'Hungary (36)',
    'IS' => 'Iceland (354)',
    'IN' => 'India (91)',
    'ID' => 'Indonesia (62)',
    'IR' => 'Iran (98)',
    'IE' => 'Ireland (353)',
    'IL' => 'Israel (972)',
    'IM' => 'Isle of Man (44)',
    'IT' => 'Italy (39)',
    'JM' => 'Jamaica (1876)',
    'JP' => 'Japan (81)',
    'JO' => 'Jordan (962)',
    'KZ' => 'Kazakhstan (7)',
    'KE' => 'Kenya (254)',
    'KW' => 'Kuwait (965)',
    'KG' => 'Kyrgyzstan (996)',
    'LA' => 'Laos (856)',
    'LV' => 'Latvia (371)',
    'LB' => 'Lebanon (961)',
    'LS' => 'Lesotho (266)',
    'LY' => 'Libyan Arab Jamahiriya (218)',
    'LI' => 'Liechtenstein (423)',
    'LT' => 'Lithuania (370)',
    'LU' => 'Luxembourg (352)',
    'MK' => 'Macedonia (389)',
    'MG' => 'Madagascar (261)',
    'MW' => 'Malawi (265)',
    'MY' => 'Malaysia (60)',
    'MV' => 'Maldives (960)',
    'ML' => 'Mali (223)',
    'MT' => 'Malta (356)',
    'MQ' => 'Martinique (596)',
    'MR' => 'Mauritania (222)',
    'MU' => 'Mauritius (230)',
    'MX' => 'Mexico (52)',
    'MC' => 'Monaco (377)',
    'MN' => 'Mongolia (976)',
    'ME' => 'Montenegro (382)',
    'MA' => 'Morocco (212)',
    'MZ' => 'Mozambique (258)',
    'MM' => 'Myanmar (95)',
    'NA' => 'Namibia (264)',
    'NP' => 'Nepal (977)',
    'NL' => 'Netherlands (31)',
    'AN' => 'Netherlands Antilles (599)',
    'NC' => 'New Caledonia (687)',
    'NZ' => 'New Zealand (64)',
    'NI' => 'Nicaragua (505)',
    'NE' => 'Niger (227)',
    'NG' => 'Nigeria (234)',
    'NF' => 'Norfolk Island (672)',
    'MP' => 'Northern Mariana Islands (1670)',
    'NO' => 'Norway (47)',
    'OM' => 'Oman (968)',
    'PK' => 'Pakistan (92)',
    'PW' => 'Palau (680)',
    'PA' => 'Panama (507)',
    'PG' => 'Papua New Guinea (675)',
    'PY' => 'Paraguay (595)',
    'PE' => 'Peru (51)',
    'PH' => 'Philippines (63)',
    'PL' => 'Poland (48)',
    'PT' => 'Portugal (351)',
    'PR' => 'Puerto Rico (1787)',
    'QA' => 'Qatar (974)',
    'MD' => 'Republic Of Moldova (373)',
    'RE' => 'Reunion (262)',
    'RO' => 'Romania (40)',
    'RU' => 'Russia (7)',
    'RW' => 'Rwanda (250)',
    'KN' => 'Saint Kitts And Nevis (1869)',
    'LC' => 'Saint Lucia (1758)',
    'VC' => 'Saint Vincent And The Grenadines (1784)',
    'WS' => 'Samoa (685)',
    'SM' => 'San Marino (378)',
    'ST' => 'Sao Tome And Principe (239)',
    'SA' => 'Saudi Arabia (966)',
    'SN' => 'Senegal (221)',
    'RS' => 'Serbia (381)',
    'SC' => 'Seychelles (248)',
    'SG' => 'Singapore (65)',
    'SK' => 'Slovakia (421)',
    'SI' => 'Slovenia (386)',
    'SB' => 'Solomon Islands (677)',
    'ZA' => 'South Africa (27)',
    'KR' => 'South Korea (82)',
    'ES' => 'Spain (34)',
    'LK' => 'Sri Lanka (94)',
    'SD' => 'Sudan (249)',
    'SR' => 'Suriname (597)',
    'SZ' => 'Swaziland (268)',
    'SE' => 'Sweden (46)',
    'CH' => 'Switzerland (41)',
    'SY' => 'Syrian Arab Republic (963)',
    'TW' => 'Taiwan (886)',
    'TJ' => 'Tajikistan (992)',
    'TZ' => 'Tanzania (255)',
    'TH' => 'Thailand (66)',
    'TG' => 'Togo (228)',
    'TO' => 'Tonga (676)',
    'TT' => 'Trinidad And Tobago (1868)',
    'TN' => 'Tunisia (216)',
    'TR' => 'Turkey (90)',
    'TM' => 'Turkmenistan (993)',
    'UG' => 'Uganda (256)',
    'UA' => 'Ukraine (380)',
    'AE' => 'United Arab Emirates (971)',
    'GB' => 'United Kingdom (44)',
    'US' => 'United States (1)',
    'UY' => 'Uruguay (598)',
    'UZ' => 'Uzbekistan (998)',
    'VU' => 'Vanuatu (678)',
    'VE' => 'Venezuela (58)',
    'VN' => 'Vietnam (84)',
    'VG' => 'Virgin Islands British (1284)',
    'VI' => 'Virgin Islands U.S. (1340)',
    'YE' => 'Yemen (967)',
    'ZM' => 'Zambia (260)',
    'ZW' => 'Zimbabwe (263)'
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
    'Wordpress Comments' => 'comments'
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
    'maspik_Store_log' => 'yes',
    
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