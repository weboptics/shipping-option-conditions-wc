<?php
/**
 * Condition Shipping class
 *
 * @package 1.0.9
 */

if ( ! class_exists( 'HS_WCSH_CS' ) ) {

	/**
	 * Condition Shipping class
	 *
	 * @package 1.0.9
	 */
	class HS_WCSH_CS extends WC_Shipping_Method {
		/**
		 * Constructor for your shipping class
		 *
		 * @param id $instance_id instance id.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id           = 'conditional_shipping_class';
			$this->instance_id  = absint( $instance_id );
			$this->method_title = __( 'Conditional Shipping' );

			$this->enabled            = 'yes';
			$this->title              = 'Conditional Shipping';
			$this->method_description = __( 'Shipping Options Based of Custom Conditions' );
			$this->supports           = array(
				// 'settings',
				'shipping-zones',
				'instance-settings-modal',
			);

			$this->init();
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		public function init() {
			// Load the settings API.
			$this->init_form_fields();
			$this->init_settings();

			$this->title                     = $this->get_option( 'title' );
			$this->description               = $this->get_option( 'description' );
			$this->condition                 = $this->get_option( 'condition' );
			$this->city_name_weight_fallback = $this->get_option( 'city_name_weight_fallback' );
			// Save settings in admin if you have any defined.
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

		}


		/**
		 * Define settings fields for the shipping method
		 */
		public function init_form_fields() {

			$this->instance_form_fields = array(
				'title'     => array(
					'title'       => __( 'Title', 'shipping-option-conditions-wc' ),
					'type'        => 'text',
					'description' => __( 'Title of your shipping method.', 'shipping-option-conditions-wc' ),
					'default'     => $this->method_title,
					'desc_tip'    => true,
				),
				'condition' => array(
					'title'    => __( 'Condition', 'shipping-option-conditions-wc' ),
					'type'     => 'select',
					'label'    => __( 'Condition on the shipping option', 'shipping-option-conditions-wc' ),
					'default'  => 'weight',
					'desc_tip' => true,
					'options'  => array(
						'state_county' => 'By State/County',
						// 'city'         => 'By City',
						// 'city_weight'  => 'By City Weight',
						// 'comming_soon' => 'Coming Soon',
					),
				),
			);

		}

		/**
		 * Calculate_shipping function.
		 *
		 * @access public
		 * @param array $package Package data.
		 * @return void
		 */
		public function calculate_shipping( $package = array() ) {
			if ( 'state_county' === $this->condition ) {
				$code           = 'JK';
				$shipping_zone  = WC_Shipping_Zones::get_zone_matching_package( $package );
				$zone_id        = $shipping_zone->get_zone_id();
				$address        = $package['destination'];
				$checkout_state = $address['state'];
				$cost           = get_option( 'custom_shipping_option_' . $zone_id . '_' . $checkout_state, '' );

				$rate = array(
					'label'    => $this->title,
					'cost'     => $cost, // Set calculated cost.
					'calc_tax' => 'per_item',
				);

				// Register the shipping rate.
				$this->add_rate( $rate );
			}
		}
	}
}
