<?php
/**
 * Plugin Name: WooCommerce Shipping Option Conditions
 * 
 */



/**
 * Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function my_hide_shipping_when_free_is_available( $rates ) {
	$free = array();
	foreach ( $rates as $rate_id => $rate ) {
		$shipping_data = get_option('woocommerce_free_shipping_'.$rate->instance_id.'_settings');
		$woo_show_hide=(isset($shipping_data['woo_show_hide'])) ? $shipping_data['woo_show_hide'] : 'no';
		if ( 'free_shipping' === $rate->method_id && 'yes' === $woo_show_hide ) {
			$free[ $rate_id ] = $rate;
			// break;
		}
	}
	return ! empty( $free ) ? $free : $rates;
}
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );


function shipping_instance_form_add_extra_fields($settings)
{
    $settings['woo_show_hide'] = [
        'title' => 'Show / Hide',
		'default' => 'Show / Hide',
        'type' => 'checkbox',
        'label' => 'If checked, other shipping would be un-available when this is available.',
		'description' => 'If checked, other shipping would be un-available when this is available.',
		'desc_tip'    => true,
    ];

    return $settings;
}

function shipping_instance_form_fields_filters()
{	
    $shipping_methods = WC()->shipping->get_shipping_methods();
    foreach ($shipping_methods as $shipping_method) {
		if('free_shipping'===$shipping_method->id){
			add_filter('woocommerce_shipping_instance_form_fields_' . $shipping_method->id, 'shipping_instance_form_add_extra_fields');
		}
    }
}

add_action('woocommerce_init', 'shipping_instance_form_fields_filters');
