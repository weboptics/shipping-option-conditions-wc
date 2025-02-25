<?php
/**
 * Custom Shipping Table File
 *
 * @package 1.0.9
 */

if ( ! class_exists( 'HS_WCSH_State_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	/**
	 * Custom Shipping Table class
	 *
	 * @package 1.0.9
	 */
	class HS_WCSH_State_Table extends WP_List_Table {
		/**
		 * Zone ID
		 *
		 * @var $zone_id
		 */
		private $zone_id;

		/**
		 * Constructor for Custom Shipping Table class
		 *
		 * @param id $zone_id zone id.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct( $zone_id ) {
			parent::__construct(); // Call parent constructor.
			$this->zone_id = $zone_id;
		}

		/**
		 * Prepare the data for the table
		 */
		public function prepare_items() {
			// Set up pagination.
			$per_page = 10;
			$this->set_pagination_args(
				array(
					'total_items' => count( $this->get_shipping_zones() ),
					'per_page'    => $per_page,
					'total_pages' => ceil( count( $this->get_shipping_zones() ) / $per_page ),
				)
			);

			// Set the columns.
			$columns = array(
				'zone_name'   => 'Zone Name',
				'zone_code'   => 'Zone Code',
				'zone_amount' => 'Zone Amount',
			);

			// Set the columns and set the data.
			$columns               = $this->get_columns();
			$this->_column_headers = array( $columns, array(), array() );
			// Fetch and slice the data based on pagination.
			$this->items = array_slice(
				$this->get_shipping_zones(),
				( $this->get_pagenum() - 1 ) * $per_page,
				$per_page
			);
		}

		/**
		 * Get the shipping zone data (Replace with your actual data source)
		 */
		private function get_shipping_zones() {

			// Check if zone_id is valid.
			if ( ! $this->zone_id ) {
				return array(); // If no zone_id, return empty array.
			}

			$data              = array();
			$selected_currency = get_woocommerce_currency();
			// Initialize the shipping zone object with the provided zone_id.
			$zone_obj      = new WC_Shipping_Zone( $this->zone_id );
			$regions       = $zone_obj->get_zone_locations();
			$regions_codes = wp_list_pluck( $regions, 'code' );
			$states        = $this->get_states_by_country_codes( $regions_codes );

			$i = 0;
			foreach ( $states as $code => $name ) {
				// Fetch custom option for each state and store it.
				$saved_value               = get_option( 'conditional_shipping_option_' . $this->zone_id . '_' . $code, '' );
				$data[ $i ]['zone_name']   = $code;
				$data[ $i ]['zone_code']   = $name;
				$data[ $i ]['zone_amount'] = '<input type="number" value="' . $saved_value . '" name="zone_data[' . $this->zone_id . '][' . $code . ']"> ' . $selected_currency;
				$i++;
			}

			return $data;
		}

		/**
		 * Override get_columns() to specify table columns
		 */
		public function get_columns() {
			// Define the columns for your table.
			return array(
				'zone_name'   => 'Zone Name',
				'zone_code'   => 'Zone Code',
				'zone_amount' => 'Zone Amount',

			);
		}


		/**
		 * Render the table row
		 *
		 * @param array  $item Item array.
		 * @param strong $column_name Column name.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'zone_name':
				case 'zone_code':
				case 'zone_amount':
					return $item[ $column_name ];
				default:
					return print_r( $item, true );
			}
		}

		/**
		 * Get states by country code.
		 *
		 * @param string $country_codes Country Code.
		 */
		public function get_states_by_country_codes( $country_codes ) {
			// Get the WooCommerce countries instance.
			$wc_countries = new WC_Countries();

			// Initialize an array to store the states for each country code.
			$states_by_country = array();

			// Loop through each country code in the array.
			foreach ( $country_codes as $country_code ) {
				// Get the list of states for the given country code.
				$states     = $wc_countries->get_states( $country_code );
				$new_states = array();
				foreach ( $states as $key => $state ) {
					$new_states[ $key ] = $state . ' (' . $country_code . ')';
				}
				// If the country has states, store them in the result array.
				if ( $states ) {
					$states_by_country = array_merge( $states_by_country, $new_states );
				} else {
					// If no states found for this country, store an empty array.
					$states_by_country[] = array();
				}
			}

			// Return the array of states for all country codes.
			return $states_by_country;
		}

	}

}
