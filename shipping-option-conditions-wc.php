<?php
/**
 * Plugin Name:       Shipping Option Conditions for WooCommerce
 * Plugin URI:        https://github.com/weboptics/shipping-option-conditions-wc 
 * Description:       Handle the basics shipping condition with this plugin
 * Version:           1.0.8
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            WebOptics
 * Author URI:        https://weboptics.co/
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

if (! defined('HS_WCSH_PLUGIN_FILE') ) { 
	define('HS_WCSH_PLUGIN_FILE',__FILE__);
}

if (! defined('HS_WCSH_PLUGIN_DIR') ) { 
	define('HS_WCSH_PLUGIN_DIR',plugin_dir_url( __FILE__ ));
}


/**
 * Hide shipping rates when free shipping is available main classs.
 */
class HS_WCSH_Init{
	public function __construct() {

		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_filter( 'woocommerce_package_rates', array($this,'hide_shipping_when_free_is_available'), 100 );
			add_action('woocommerce_init',  array($this,'shipping_instance_form_fields_filters'));
			// Change text box to select and set cities options
			$data = get_option( 'woocommerce_conditional_shipping_class_settings' );
			$enable_cities_select = $data['enable_cities_select'] ?? null;
			if ( 'yes' === $enable_cities_select ){
				add_filter( 'woocommerce_checkout_fields', array( $this, 'custom_city_options' ) );
			} 
			add_filter( 'woocommerce_shipping_init', array($this, 'shipping_method') );
			add_filter( 'woocommerce_shipping_methods', array($this, 'add_shipping_method') );
			// add_action('admin_footer', array($this,  'custom_admin_footer_styles'));
			add_filter( 'wp_footer', array( $this, 'city_wp_footer' ) );

		}
		add_action('admin_notices',  array($this,'custom_plugin_activation_notice'));
	}

	function custom_admin_footer_styles() {
		echo '<style>
			#woocommerce_conditional_shipping_class_cities{
				width:100%;
			}
		</style>';
	}


	function city_wp_footer(){
		// var_dump(is_checkout());
		if(is_checkout()){
			?>
			<script>
				jQuery(document).ready(function(){
					jQuery('#billing_city').change(function(){
						jQuery('body').trigger('update_checkout');
					});
					jQuery('#shipping_city').change(function(){
						jQuery('body').trigger('update_checkout');
					});
				})
			</script>
			<?php
		}
	}



	
	function shipping_method() {
		include_once "includes/conditional-shipping-class.php";
	}
	function add_shipping_method( $methods ) {
		$methods['conditional_shipping_class'] = 'Conditional_Shipping_Class';
		return $methods;
	}
	function custom_city_options( $fields ) {
        // global $wpdb;
        // $table = $wpdb->prefix . "shiprate_cities";
        // $cities = $wpdb->get_results("SELECT city_name FROM $table ");
        // $options = array('Select city');
        // foreach($cities as $city) {
        //     $options[$city->city_name] = $city->city_name;
        // }
		$data = get_option( 'woocommerce_conditional_shipping_class_settings' );
		$cities = $data['cities'] ?? null;
		$options = array();

		// Split the string into lines
		$lines = explode("\n", $cities);

		foreach ($lines as $line) {
			// Split each line by the ': ' delimiter
			list($key, $value) = explode(' : ', $line);
			// Trim whitespace and add to the array
			$options[trim($key)] = trim($value);
		}
		
    
        // Get the current city value
        $city_value = isset($fields['shipping']['shipping_city']['default']) ? $fields['shipping']['shipping_city']['default'] : '';
    
     
        // Use the default select box
        $city_args = wp_parse_args(array(
            'type'    => 'select',
            'options' => $options,
            'autocomplete' => true,
        ), $fields['shipping']['shipping_city']);
        $city_args_text = array();
        
    
        // Set the shipping and billing city fields
        $fields['shipping']['shipping_city'] = $city_args;
        $fields['shipping']['shipping_city_text'] = $city_args_text;
    
        $fields['billing']['billing_city'] = $city_args;
        $fields['billing']['billing_city_text'] = $city_args_text;
    
        
    
        return $fields;
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
