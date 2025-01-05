<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Maspik - Advanced Spam Protection
 * Plugin URI:        https://wpmaspik.com/
 * Description:       The best spam protection plugin. Block spam using advanced filters, blacklists, and IP verification...
 * Version:           2.2.14
 * Author:            WpMaspik
 * Author URI:        https://wpmaspik.com/?readme
 * Text Domain:       contact-forms-anti-spam
 * Domain Path:       /languages
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * 
 * Maspik is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * Any spam blocking action taken by this plugin is solely at the user's own risk and discretion.
 * The plugin developers and contributors cannot be held responsible for any false positives
 * or legitimate messages that may be blocked.
 *
 * You should have received a copy of the GNU General Public License
 * along with Maspik. If not, see <http://www.gnu.org/licenses/>.
 * 
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) exit; 

/**
 * Currently plugin version.
 */
define( 'MASPIK_VERSION', '2.2.14' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-settings-page-activator.php
 */
// For future version
/*
function maspik_on_plugin_activation() {
	maspik_auto_update_db(); // Run the auto create database function
    if ( ! get_option( 'maspik_run_once' ) ) {
		maspik_auto_update_db();
        maspik_make_default_values();
        update_option( 'maspik_run_once', 1 ); // 1 means the function has run
    }
}
// Ensure the function runs on plugin activation
register_activation_hook( __FILE__, 'maspik_on_plugin_activation' );
*/


/**
 * The code that runs during plugin deactivation.
 */
// For future version
function deactivate_maspik() {
	//require_once plugin_dir_path( __FILE__ ) . 'includes/class-maspik-deactivator.php';
	//Settings_Page_Deactivator::deactivate();
}
//register_deactivation_hook( __FILE__, 'deactivate_maspik' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-maspik.php';


if (version_compare(PHP_VERSION, '7.0.0', '>=') && apply_filters( 'maspik_active_license_library', true )) {
  require plugin_dir_path(__FILE__) . 'license/license.php';
}



/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function Run_Maspik() {
	$plugin = new Maspik();
	$plugin->run();
}

Run_Maspik();

add_filter( 'plugin_row_meta', 'maspik_plugin_row_meta', 10, 2 );
function maspik_plugin_row_meta( $links, $file ) {
	if( strpos( $file, basename(__FILE__) ) ) {
		$maspik_links = array(
			'donat_link' => '<a href="https://wordpress.org/support/plugin/contact-forms-anti-spam/reviews/#new-post" target="_blank">'.__( 'Give us 5 stars', 'contact-forms-anti-spam' ).'</a>',
			'settings' => '<a href="'.admin_url().'admin.php?page=maspik" target="_blank">'.__( 'Setting page', 'contact-forms-anti-spam' ).'</a>',
		);
		
		$links = array_merge( $links, $maspik_links );
	}
	
	return $links;
}
