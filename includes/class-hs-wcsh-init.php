<?php
/**
 * Main init class.
 *
 * @package 1.2.0
 */

/**
 * Hide shipping rates when free shipping is available main classes.
 */
class HS_WCSH_Init {

	/**
	 * Constructor function.
	 */
	public function __construct() {
			// @codingStandardsIgnoreStart
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			// @codingStandardsIgnoreEnd
			add_filter( 'woocommerce_package_rates', array( $this, 'hide_shipping_when_free_is_available' ), 100 );
			add_action( 'woocommerce_init', array( $this, 'shipping_instance_form_fields_filters' ) );
		}
		add_action( 'admin_notices', array( $this, 'custom_plugin_activation_notice' ) );
		add_filter( 'admin_footer', array( $this, 'custom_admin_footer' ) );
		add_filter( 'wp_footer', array( $this, 'custom_wp_footer' ) );

		add_filter( 'woocommerce_shipping_init', array( $this, 'shipping_methods' ) );
		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
		add_action( 'admin_menu', array( $this, 'plugin_woocommerce_tab' ) );
		add_action( 'admin_init', array( $this, 'handle_save_for_custom_shipping_tab' ) );

	}

	/**
	 * Hide shipping rates when free shipping is available.
	 *
	 * @param array $rates Array of rates found for the package.
	 * @return array $rates
	 */
	public function hide_shipping_when_free_is_available( $rates ) {
		$new_rates = array();
		foreach ( $rates as $rate_id => $rate ) {
			$shipping_data          = get_option( 'woocommerce_' . $rate->method_id . '_' . $rate->instance_id . '_settings' );
			$woo_show_hide          = ( isset( $shipping_data['woo_show_hide'] ) ) ? $shipping_data['woo_show_hide'] : 'no';
			$woo_show_hide_override = ( isset( $shipping_data['woo_show_hide_override'] ) ) ? $shipping_data['woo_show_hide_override'] : 'no';
			if ( 'free_shipping' === $rate->method_id && 'yes' === $woo_show_hide ) {
				$new_rates[ $rate_id ] = $rate;
			} elseif ( 'yes' === $woo_show_hide_override ) {
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
	public function shipping_instance_form_add_extra_fields_free( $settings ) {
		$settings['woo_show_hide'] = array(
			'title'       => 'Show / Hide',
			'default'     => 'Show / Hide',
			'type'        => 'checkbox',
			'class'       => 'woocommerce-free-shipping-woo-show-hide',
			'label'       => 'Used to Hide Shipping Method',
			'description' => 'If checked, other shipping would be un-available when this is available.',
			'desc_tip'    => true,
		);

		return $settings;
	}

	/**
	 * Show / Hide Override settings
	 *
	 * @param array $settings woo field settings.
	 *
	 * @return $settings.
	 */
	public function shipping_instance_form_add_extra_fields_others( $settings ) {
		$settings['woo_show_hide_override'] = array(
			'title'       => 'Show / Hide - Override',
			'default'     => 'Show / Hide - Override',
			'type'        => 'checkbox',
			'label'       => 'Used Override the Hidden Shipping Method',
			'description' => 'If checked, this shipping method will show even if Its hidden by free shipping.',
			'desc_tip'    => true,
		);

		return $settings;
	}
	/**
	 * Add settings in shipping zone.
	 */
	public function shipping_instance_form_fields_filters() {
		// Retrieve shipping zones.
		$shipping_zones   = WC_Shipping_Zones::get_zones();
		$shipping_methods = WC()->shipping->get_shipping_methods();

		foreach ( $shipping_methods as $shipping_method ) {
			if ( 'free_shipping' === $shipping_method->id ) {
				add_filter( 'woocommerce_shipping_instance_form_fields_' . $shipping_method->id, array( $this, 'shipping_instance_form_add_extra_fields_free' ) );
			} else {
				add_filter( 'woocommerce_shipping_instance_form_fields_' . $shipping_method->id, array( $this, 'shipping_instance_form_add_extra_fields_others' ) );
			}
		}
	}

	/**
	 * Admin notice.
	 */
	public function custom_plugin_activation_notice() {
		global $pagenow;
		// @codingStandardsIgnoreStart
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && 'plugins.php' === $pagenow ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'The Shipping Option Conditions for WooCommerce" requires WooCommerce to be installed and activated. Please install and activate WooCommerce to use this plugin.', 'shipping-option-conditions-wc' ); ?></p>
			</div>
			<?php
		}
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Include Custom Shipping Methods files.
	 */
	public function shipping_methods() {
		require_once 'class-hs-wcsh-cs.php';

	}
	/**
	 * Add Custom Shipping Methods.
	 *
	 * @param array $methods All the shipping methods.
	 *
	 * @return array
	 */
	public function add_shipping_method( $methods ) {
		$methods['conditional_shipping_class'] = 'HS_WCSH_CS';
		return $methods;
	}
	/**
	 * Function to add small css and js to footer.
	 */
	public function custom_wp_footer() {
		?>
		<style></style>
		<?php
	}

	/**
	 * Plugin option tab
	 */
	public function plugin_woocommerce_tab() {
		add_submenu_page(
			'woocommerce',
			'Conditional Shipping',
			'Conditional Shipping',
			'manage_woocommerce',
			'conditional-shipping-options',
			array( $this, 'plugin_woocommerce_tab_output' )
		);
	}
	/**
	 * Plugin option tab output
	 */
	public function plugin_woocommerce_tab_output() {

		$data_store    = WC_Data_Store::load( 'shipping-zone' );
		$all_zones     = $data_store->get_zones();
		$first_zone_id = 0;
		if ( isset( $all_zones[0]->zone_id ) ) {
			$first_zone_id = $all_zones[0]->zone_id;
		}
		$current_tab       = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'zone-' . $first_zone_id; //  phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selected_currency = get_woocommerce_currency();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Conditional Shipping', 'shipping-option-conditions-wc' ); ?></h1>
			<p><?php esc_html_e( 'Here you can add prices by shipping states/city.', 'shipping-option-conditions-wc' ); ?></p>
			<p><strong><?php esc_html_e( 'Any Empty field will be considered free', 'shipping-option-conditions-wc' ); ?></strong></p>
			<!-- Add your custom content or forms here -->
			<h2 class="nav-tab-wrapper">
				<?php
				if ( $all_zones ) {
					foreach ( $all_zones as $zone ) {
						?>
						<a href="?page=custom-shipping-options-tab&tab=zone-<?php echo esc_html( $zone->zone_id ); ?>" class="nav-tab <?php echo 'zone-' . $zone->zone_id === $current_tab ? 'nav-tab-active' : ''; ?>">
							<?php echo esc_html( $zone->zone_name ); ?>
						</a>
						<?php
					}
				}
				?>
			</h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'save_conditional_shipping_options', 'conditional_shipping_options_nonce' ); ?>

				<div class="tab-content">
					<?php
					if ( $all_zones ) {
						foreach ( $all_zones as $zone ) {
							if ( 'zone-' . $zone->zone_id === $current_tab ) {
								$table = new HS_WCSH_State_Table( $zone->zone_id );
								$table->prepare_items();
								$table->display();
							}
						}
					}
					?>
					<p>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'shipping-option-conditions-wc' ); ?></button>
					</p>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Saves the plugin tab data.
	 */
	public function save_plugin_woocommerce_tab_data() {
		if (
			! isset( $_POST['conditional_shipping_options_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['conditional_shipping_options_nonce'] ) ), 'save_conditional_shipping_options' )
		) {
			echo 'error';
			return; // Security check failed.
		}

		if ( isset( $_POST['zone_data'] ) && is_array( $_POST['zone_data'] ) ) {
			$zone_data = array_map( 'sanitize_text_field', wp_unslash( $_POST['zone_data'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			foreach ( $zone_data as $zone_id => $zone_values ) {
				foreach ( $zone_values as $region_code => $value ) {
					// Update the value in the database, using a unique key for the zone and region.
					update_option( 'conditional_shipping_option_' . $zone_id . '_' . $region_code, sanitize_text_field( $value ) );
				}
			}
		}

		// Optionally, add an admin notice for confirmation.
		add_action(
			'admin_notices',
			function () {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Shipping options saved successfully.', 'shipping-option-conditions-wc' ); ?></p>
				</div>
				<?php
			}
		);
	}
	/**
	 * Handle Saves the plugin tab data.
	 */
	public function handle_save_for_custom_shipping_tab() {
		if ( isset( $_GET['page'] ) && 'conditional-shipping-options' === $_GET['page'] ) { //  phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->save_plugin_woocommerce_tab_data();
		}
	}
	/**
	 * Recursive sanitation for an array
	 *
	 * @param array $array array data.
	 *
	 * @return mixed
	 */
	public function recursive_sanitize_text_field( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) || is_object( $value ) ) {
				$value = recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}

		return $array;
	}



	/**
	 * Function to add small css and js to footer.
	 */
	public function custom_admin_footer() {
		?>
		<style>
			label[for="woocommerce_free_shipping_woo_show_hide"]{
				display:block !important;
			}
			.wc-modal-shipping-method-settings fieldset:has(.woocommerce-free-shipping-woo-show-hide){
				display:block !important;
			}
		</style>
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
