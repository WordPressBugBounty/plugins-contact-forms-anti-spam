=== Maspik - Advanced Spam Protection ===
Contributors: yonifre
Donate link: https://paypal.me/yonifre
Tags: spam, blacklist, anti spam, Honeypot, antispam 
Tested up to: 6.7
Requires at least: 5.0
Requires PHP: 7.0
Stable tag: 2.2.12
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The best spam protection plugin. Block spam using advanced filters, blacklists, and IP verification - works with all major form plugins and comments.

== Description ==

## Say Goodbye to Spam with Maspik! ##
Maspik uses a highly efficient "blacklist" method that surpasses traditional CAPTCHA services like Google's in both efficiency and accuracy, with a success rate of over 95%.

With Maspik, you have the power to define what is considered spam by adding phrases to your blacklist. Fast and precise blocking of spam submissions takes as little as 2 minutes to set up.

## How to block spam in wordpress? ##

Maspik allows you to specify words, email addresses, phone formats, IP addresses, and more. If submissions contain links, originate from certain countries, or are in specified languages, Maspik flags them as spam and keeps them out of your inbox.

## Features ##

* **Blacklisting by Field Type:**
  * Text fields (Name/Subject)
  * Email fields (supports regex/wildcard patterns)
  * Text area fields
  * Phone number verification with regex/wildcard format
* **Character Control:**
  * Maximum number of characters in text fields
  * Maximum number of characters in text area fields
* **Link Limitation:**
  * Limit the number of links allowed in text areas (ideally 0)
* **Blocking:**
  * Specific IP addresses
  * Spam submissions in WordPress comments and subscription forms
* **Spam Log:**
  * Review blocked submissions
* **Advance Blocking:**
  * Honeypot
  * IP verification
  * Block submissions without source URLs (Elementor)
* **API Integrations:**
  * Proxycheck.io
  * AbuseIPDB.com

## Supported Forms ##

Maspik integrates seamlessly with a wide range of popular contact forms and comments:

* Elementor forms
* Contact Form 7
* NinjaForms
* Everest Forms
* Formidable forms
* JetFormBuilder
* Forminator forms
* Fluentforms
* Bricksbuilder forms
* BuddyPress
* WPForms*
* GravityForms*
* WordPress comments
* WordPress registration form
* WooCommerce registration form*
* WooCommerce review* 
(*) Pro license required

## We offer also a Pro version! ##

### Pro Version Features ###

The Pro version offers advanced functionality:

* IP verification - Increases the monthly limit from 100 to 1,000 IP checks.
* Integration with the Maspik Spam API
* Create and use your own SPAM API across multiple websites
* Import/Export Settings
* Blocking based on specific languages (e.g. block Russian/Chinese/Arabic content)
* Country-specific blocking or allowing submissions (e.g. block USA/China/Russia)

##Important Note##

Be cautious when selecting words to blacklist as each website has different needs. For example, if you're a digital marketing agency and blacklist the word "SEO," you may lose some valid leads.
The plugin is provided "as is" and the user assumes full responsibility for configuring and using it appropriately for their specific needs.

The plugin is GDPR compliant.

For more information, visit our website: [WpMaspik.com](https://wpmaspik.com/?readme-file)



== Installation ==

Search for "Maspik - Spam Blacklist" in the Wordpress Plugin repository through the 'Plugins' menu in Wordpress.
Install and activate the plugin.
In the Wordpress dashboard menu, find the Maspik - Spam Blacklist setting page.
Add spam words as needed.
== Frequently Asked Questions ==
= Does the plugin work with all Wordpress forms? =
Maspik currently supports:

<ul>
<li>Elementor forms</li>
<li>Contact form 7</li>
<li>NinjaForms</li>
<li>Everest Forms</li>
<li>JetFormBuilder</li>
<li>Formidable forms</li>
<li>Forminator forms</li>
<li>Fluentforms</li>
<li>Bricksbuilder forms</li>
<li>BuddyPress</li>
<li>Wpforms (Maspik Pro license required)</li>
<li>Gravityforms (Maspik Pro license required)</li>
<li>Wordpress comments</li>
<li>Wordpress registration form</li>
<li>Woocommerce registration form (Maspik Pro license required)</li>
<li>Woocommerce review (Maspik Pro license required)</li>
</ul>

More forms will be supported in future releases.
Looking for specific plugin support? Let us know at [WpMaspik.com](https://wpmaspik.com/#contact)

= Where do I set this up? =

In the WordPress dashboard menu, look for the 'Maspik spam" item and click on it.

= Will MASPIK slow down my site? =
No.
I developed this plugin using high-quality server-side code and avoided using CSS/JS to ensure optimal website performance as CSS/JS running in the front-end can slow down websites.

= How can I report security bugs? =
You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/contact-forms-anti-spam)

== Screenshots ==

1. Setting page of block spam in wordpress
2. How to block spam in wordpress
3. Anti spam settings page
4. Stop spam in wordpress comments
5. Best spam solution in wordpress
6. Block spam submissions in wordpress contact form
7. spam filter settings page


== Changelog ==

= 2.2.12 - 16/12/2024 =
* New feature! - Add support in Numverify API for phone number validation.
* Fixed - fix error in Formidable forms.
* Fixed - Fixed an issue where the Dashboard ID was not displaying correctly.
* Fixed - Fixed an issue where spam log entry limit was not working as in some cases.

= 2.2.11 - 05/12/2024 =
* Improvement - Improve text in settings page and translation
* Bug fix - Fix UI glitch in settings page

= 2.2.10 - 03/12/2024 =
* Improvement - Compatibility with WP version 6.7

= 2.2.9 - 22/11/2024 =
* Fixed: Removed autofill attribute from honeypot fields to improve compatibility with AMP pages
* Improvement - Spam log default save entries max number is now 1000 (was 2000)

= 2.2.8 - 08/11/2024 =
* New Feature - Add support in BuddyPress forms.
* Improvement - Improve layout of Playground form.
* Improvement - add page link in spam log.
* Improvement - improve time block check to reduce false positive.
* Improvement - Add spanish translation.


= 2.2.7 - 16/10/2024 =
* Bug fix - Fix error in Contact Form 7 with checkbox field in some cases.

= 2.2.6 - 15/10/2024 =
* Improvement - update license manager library
* Bug fix - Fix spam message validation for phone field.

= 2.2.5 - 06/10/2024 =
* Improvement - Editor can publish comments without validation check.
* Bug fix - Fix phone number limit digit check on Elementor form.

= 2.2.4 - 04/10/2024 =
* Bug fix - Fix error in Country check for some cases.
* Bug fix - Fix error in AbuseAPI check for some cases.
* Remove - shortcode option in text-area field, because can be confuseding.


= 2.2.3 - 01/10/2024 =
* New Feature - IP verification, add IP verification usage activity to Maspik dashboard.
* Improvement - Improve code performance in CF7 & Elementor forms, validate spam up to 50% faster
* Improvement - Improve option to mark "Not a Spam" on Spam log.
* Improvement - Improve form data UI in Spam log.
* Improvement - Change date format in Spam log to Wordpress format.

= 2.2.2 - 14/09/2024 =
* Improvement - Better caching mechanism for IP address verification

= 2.2.1 - 10/09/2024 =
* Bug Fix - Fixed an issue where settings were not being saved correctly in certain server environments
* Improvement - Scheduled deletion of outdated IP check data twice daily for improved performance

= 2.2.0 - 08/09/2024 =
* Improvement - Improve UI/UX
* Improvement - Make main block setting as ON by default. You can deactivate settings in the settings page.
* Improvement - Forms are now supported by default. You can deactivate support for specific forms in the settings page.


== Upgrade Notice ==

= 2.0.0 =
Major Update - New user experience with a fresh & clear design. (note: previous spam log content will be deleted)