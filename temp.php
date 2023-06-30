<?php
function add_custom_option_to_free_shipping_settings( $settings ) {
    // Add a custom field to the Free Shipping settings page
    $settings[] = array(
        'title'    => 'Custom Option',
        'desc'     => 'Enter custom option details.',
        'id'       => 'custom_option',
        'type'     => 'text',
        'desc_tip' => true,
    );

    return $settings;
}
add_filter( 'woocommerce_shipping_settings', 'add_custom_option_to_free_shipping_settings' );
function add_extension_register_page() {
    if ( ! function_exists( 'wc_admin_register_page' ) ) {
        return;
    }
 
    wc_admin_register_page( array(
        'id'       => 'my-example-page',
        'title'    => 'My Example Page',
        'parent'   => 'woocommerce',
        'path'     => '/example',
        'nav_args' => array(
            'order'  => 10,
            'parent' => 'woocommerce',
        ),
    ) );
}
add_action( 'admin_menu', 'add_extension_register_page' );
function enqueue_admin_js(){
	wc_enqueue_js(
		"jQuery( document.body ).on( 'wc_backbone_modal_loaded', function( evt, target ) { 
			jQuery('.'+target).find('tbody').append(`<tr>
			<th>
				<label>Hide / Show</label>
				<span class='woocommerce-help-tip' tabindex='0' aria-label='If checked, other shipping would be un-available when this is available.'></span>
			</th>
			<td>
				<fieldset>
					<label>
					<input type='checkbox' name='woocommerce_free_shipping_hide_show' id='woocommerce_free_shipping_hide_show'> if checked, other shipping would be un-available when this is available</label><br>
					</fieldset>
			</td>
		</tr>`);
			console.log(target);
			console.log(evt);
		})"
	);
}
add_action( 'admin_footer', 'enqueue_admin_js'); // Priority needs to be higher than wc_print_js (25).
