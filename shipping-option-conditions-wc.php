<?php
/**
 * Plugin Name:       Shipping Option Conditions for WooCommerce
 * Plugin URI:        https://github.com/weboptics/shipping-option-conditions-wc
 * Description:       Handle the basics shipping condition with this plugin
 * Version:           1.2.0
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            WebOptics
 * Author URI:        https://weboptics.co/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shipping-option-conditions-wc
 * Requires Plugins:  woocommerce
 *
 * @package 1.2.0
 */

// disallow direct access.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'HS_WCSH_PLUGIN_FILE' ) ) {
    define( 'HS_WCSH_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'HS_WCSH_PLUGIN_URL' ) ) {
    define( 'HS_WCSH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'HS_WCSH_PLUGIN_DIR' ) ) {
    define( 'HS_WCSH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Only load the main initialization class here.
if ( ! class_exists( 'HS_WCSH_Init' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-hs-wcsh-init.php';
}

// Instantiate the main class on the 'plugins_loaded' hook.
function hs_wcsh_run() {
    new HS_WCSH_Init();
}
add_action( 'plugins_loaded', 'hs_wcsh_run' );
