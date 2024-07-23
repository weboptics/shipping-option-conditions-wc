<?php
/**
 * Plugin Name:       Shipping Option Conditions for WooCommerce
 * Plugin URI:        https://github.com/webzombies/shipping-option-conditions-wc 
 * Description:       Handle the basics shipping condition with this plugin
 * Version:           1.0.7
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            Haseeb Nawaz Awan
 * Author URI:        https://github.com/haseebnawaz298
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shipping-option-conditions-wc 
 */

//disallow direct access,
if (!defined('WPINC')) {
    die;
}

// exit if accessed directly
if (! defined('ABSPATH') ) { 
	exit;
}

/**
 * Hide shipping rates when free shipping is available main classs.
 */
class HS_WCSH_Init{
	public function __construct() {
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_filter( 'woocommerce_package_rates', array($this,'hide_shipping_when_free_is_available'), 100 );
			add_action('woocommerce_init',  array($this,'shipping_instance_form_fields_filters'));
		}
		add_action('admin_notices',  array($this,'custom_plugin_activation_notice'));
	}

	/**
	 * Hide shipping rates when free shipping is available.
	 *
	 * @param array $rates Array of rates found for the package.
	 * @return array $rates
	 */
	function hide_shipping_when_free_is_available( $rates ) {
		$new_rates = array();
		foreach ( $rates as $rate_id => $rate ) {
			$shipping_data = get_option('woocommerce_'.$rate->method_id.'_'.$rate->instance_id.'_settings');
			$woo_show_hide=(isset($shipping_data['woo_show_hide'])) ? $shipping_data['woo_show_hide'] : 'no';
			$woo_show_hide_override=(isset($shipping_data['woo_show_hide_override'])) ? $shipping_data['woo_show_hide_override'] : 'no';
			if ( 'free_shipping' === $rate->method_id && 'yes' === $woo_show_hide ) {
				$new_rates[ $rate_id ] = $rate;
			}elseif('yes' == $woo_show_hide_override){
				$new_rates[ $rate_id ] = $rate;
			}
		}
		
		return ! empty( $new_rates ) ? $new_rates : $rates;
	}

	/**
	 * Show / Hide settings 
	 * 
	 * @param array $settings woo field settings.
	 * 
	 * @return $settings. 
	 */
	function shipping_instance_form_add_extra_fields_free($settings)
	{
		$settings['woo_show_hide'] = [
			'title' => 'Show / Hide',
			'default' => 'Show / Hide',
			'type' => 'checkbox',
			'label' => 'Used to Hide Shipping Method',
			'description' => 'If checked, other shipping would be un-available when this is available.',
			'desc_tip'    => true,
		];

		return $settings;
	}

	/**
	 * Show / Hide Override settings 
	 * 
	 * @param array $settings woo field settings.
	 * 
	 * @return $settings. 
	 */
	function shipping_instance_form_add_extra_fields_others($settings)
	{
		$settings['woo_show_hide_override'] = [
			'title' => 'Show / Hide - Override',
			'default' => 'Show / Hide - Override',
			'type' => 'checkbox',
			'label' => 'Used Override the Hidden Shipping Method',
			'description' => 'If checked, this shipping method will show even if Its hidden by free shipping.',
			'desc_tip'    => true,
		];

		return $settings;
	}
	/**
	 * Add settings in shipping zone.
	 */
	function shipping_instance_form_fields_filters()
	{	
		// Retrieve shipping zones
		$shipping_zones = WC_Shipping_Zones::get_zones();
		$shipping_methods = WC()->shipping->get_shipping_methods();
		
		foreach ($shipping_methods as $shipping_method) {
			// var_dump($shipping_method);
			if('free_shipping'===$shipping_method->id){
				add_filter('woocommerce_shipping_instance_form_fields_' . $shipping_method->id, array($this,'shipping_instance_form_add_extra_fields_free'));
			}else{
				add_filter('woocommerce_shipping_instance_form_fields_' . $shipping_method->id, array($this,'shipping_instance_form_add_extra_fields_others'));
			}
		}
	}

	/**
	 * Admin notice.
	 */
	function custom_plugin_activation_notice() {
		global $pagenow;
		if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) &&  $pagenow == 'plugins.php'  ) {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e('The "Shipping Option Conditions for WooCommerce" requires WooCommerce to be installed and activated. Please install and activate WooCommerce to use this plugin.', 'shipping-option-conditions-wc'); ?></p>
		</div>
		<?php
		}
	}
}
new HS_WCSH_Init();
