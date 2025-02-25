<?php
/**
 * Plugin Name:       Shipping Option Conditions for WooCommerce
 * Plugin URI:        https://github.com/weboptics/shipping-option-conditions-wc
 * Description:       Handle the basics shipping condition with this plugin
 * Version:           1.0.9
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            WebOptics
 * Author URI:        https://weboptics.co/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shipping-option-conditions-wc
 *
 * @package 1.0.9
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

if ( ! defined( 'HS_WCSH_PLUGIN_DIR' ) ) {
	define( 'HS_WCSH_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
}
require_once 'includes/tables/class-hs-wcsh-state-table.php';
require_once 'includes/class-hs-wcsh-init.php';

new HS_WCSH_Init();

