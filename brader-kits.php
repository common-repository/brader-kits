<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://brader.id/#brader-kits
 * @since             21.8.15
 * @package           Brader_Kits
 *
 * @wordpress-plugin
 * Plugin Name:       Brader Kits
 * Plugin URI:        https://brader.id/#brader-kits
 * Description:       Set of tool-kits that can extend your WooCommerce Store
 * Version:           21.8.15
 * Author:            Brader
 * Author URI:        https://github.com/sutanto1010
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       brader-kits
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'BRADER_KITS_VERSION', '21.8.15' );
function activate_brader_kits() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-brader-kits-activator.php';
    Brader_Kits_Activator::activate();

}

function deactivate_brader_kits() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-brader-kits-deactivator.php';
    Brader_Kits_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_brader_kits' );
register_deactivation_hook( __FILE__, 'deactivate_brader_kits' );
require plugin_dir_path( __FILE__ ) . 'includes/class-brader-kits.php';
function run_brader_kits() {
    $plugin = new Brader_Kits();
    $plugin->run();
}
run_brader_kits();

