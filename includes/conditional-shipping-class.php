<?php 
if ( ! class_exists( 'Conditional_Shipping_Class' ) ) {
    class Conditional_Shipping_Class extends WC_Shipping_Method {
		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'conditional_shipping_class';
			$this->instance_id        = absint( $instance_id );
			$this->method_title       = __( 'Conditional Shipping' );  

			$this->enabled            = "yes"; 
			$this->title              = "Shipping";
			$this->supports             = array(
				'settings',
				'shipping-zones',
				'instance-settings',
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
		function init() {
			// Load the settings API
			$this->init_form_fields(); 
			$this->init_settings(); 
			
			$this->title            			= $this->get_option( 'title' );
			$this->condition            		= $this->get_option( 'condition' );
			$this->city_name_weight_fallback 	= $this->get_option('city_name_weight_fallback');
			// Save settings in admin if you have any defined
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			
		}
		/**
		 * Get setting form fields for instances of this shipping method within zones.
		 *
		 * @return array
		 */
		public function get_instance_form_fields() {
			return parent::get_instance_form_fields();
			
		}

		/**
		 * Process and redirect if disabled.
		 */
		public function process_admin_options() {
			parent::process_admin_options();
		}
		/**
		 * Output the shipping settings screen.
		 */
		public function admin_options() {
			// $data = get_option( 'woocommerce_' . $this->id . '_settings' ); 
			
			?>
			<style>
				.woocommerce_conditional_shipping_class_container{
					/* display:flex; */
					/* gap: 15px; */
					width: 20%;
				}
				label[for="woocommerce_conditional_shipping_class_cities"],label[for="woocommerce_conditional_shipping_class_enable_cities_select"] {
					width: 50%;
				}
			</style>
			<div class="wrap">
				<h1><?php esc_html_e('Global Plugin Settings', 'text-domain'); ?></h1>
				<div class="woocommerce_conditional_shipping_class_container">
					<?php echo $this->generate_settings_html( $this->get_form_fields(), false ); ?>
				</div>
			
			</div>
			
			<?php

			
			
		}

		/**
		 * Always return shipping method is available
		 *
		 * @param array $package Shipping package.
		 * @return bool
		 */
		public function is_available( $package ) {
			$is_available = true;
			return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this );
		}
		

		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 * @param array $package
		 * @return void
		 */
		public function calculate_shipping( $package = array() ) {
			if('city' === $this->condition){
				$total_weight = 0;
				$cost = 0;
				$address = $package["destination"];
				$city=$address['city'];
				// Get the cart items
				$cart = WC()->cart->get_cart();
				$data_current = get_option( 'woocommerce_' . $this->id .'_'.$this->instance_id. '_settings' );
				
				$city_key = strtolower(str_replace(' ', '_', $city));
	
				$cost = $data_current['city_name_'.$city_key] ?? null;
	
				// Create the shipping rate array
				$rate = array(
					'label' => $this->title,
					'cost'  => $cost, // Set calculated cost
					'calc_tax' => 'per_item'
				);
	
				// Register the shipping rate
				$this->add_rate( $rate );
			}else if('city_weight' === $this->condition){
				$total_weight = 0;
				$cost = 0;
				$address = $package["destination"];
				$city=$address['city'];
				// Get the cart items
				$cart = WC()->cart->get_cart();
				$data_current = get_option( 'woocommerce_' . $this->id .'_'.$this->instance_id. '_settings' );
				// Loop through each item in the cart to calculate total weight
				foreach ( $cart as $cart_item_key => $cart_item ) {
					$product = $cart_item['data']; // Get the product object
					$quantity = $cart_item['quantity']; // Get quantity of the product in cart
					$weight = $product->get_weight(); // Get product weight
	
					// Calculate total weight (weight * quantity)
					$total_weight += floatval( $weight ) * $quantity;
					
				}
				$city_key = strtolower(str_replace(' ', '_', $city));
				$cost_string = $data_current['city_name_weight_'.$city_key] ?? null;
				// var_dump($cost);
				$cost_arr = explode(',',$cost_string);
				$number=0;
				$cost = $this->city_name_weight_fallback ;
				for( $i=0.5;$i<=count($cost_arr)/2; $i +=0.5 ){
					if ($total_weight <= $i) {
						$cost = $cost_arr[$number];
						break;
					}
					$number++;
				}
				// var_dump($total_weight);
				// Create the shipping rate array
				$rate = array(
					'label' => $this->title,
					'cost'  => $cost, // Set calculated cost
					'calc_tax' => 'per_item'
				);
	
				// Register the shipping rate
				$this->add_rate( $rate );
			}
			
		}
	
		 /**
         * Define settings fields for the shipping method
         *
         * @return void
         */
        function init_form_fields() {
			$this->form_fields = array(
				'enable_cities_select' => array(
                    'title'       => __( 'Enable Cities Select', 'shipping-option-conditions-wc' ),
                    'type'        => 'checkbox',
                    'label'       => __( 'Check to change city input into a select', 'shipping-option-conditions-wc' ),
                    'default'     => false,
                    'desc_tip'    => true,
                ),
				'cities' => array(
					'title'       => __( 'Cities', 'shipping-option-conditions-wc' ),
					'type'        => 'textarea',
					'description' => __( 'please write cities will there cost seperaterd by |, for new item put it in next line', 'shipping-option-conditions-wc' ),
					'default'     => '',
					'style'       => 'width:100%',
					'desc_tip'    => true,
				),
			// Add more settings as needed
            );
            $this->instance_form_fields = array(
                'title' => array(
                    'title'       => __( 'Title', 'shipping-option-conditions-wc' ),
                    'type'        => 'text',
                    'description' => __( 'Title of your shipping method.', 'shipping-option-conditions-wc' ),
                    'default'     => $this->method_title,
                    'desc_tip'    => true,
                ),
				'condition' => array(
                    'title'       => __( 'Condition', 'shipping-option-conditions-wc' ),
                    'type'        => 'select',
                    'label'       => __( 'Condition on the shipping option', 'shipping-option-conditions-wc' ),
                    'default'     => 'weight',
                    'desc_tip'    => true,
					'options'	  => array(
						'city' => 'By City',
						'city_weight' => 'By City Weight',
						'comming_soon' => 'Coming Soon'
					)
                ),
                // Add more settings as needed
            );
			$data_current = get_option( 'woocommerce_' . $this->id .'_'.$this->instance_id. '_settings' ); 
			$data = get_option( 'woocommerce_' . $this->id . '_settings' ); 
			$cities = $data['cities'] ?? null;
			$condition = $data_current['condition'] ?? null;
			// var_dump($condition);
			// var_dump($data_current);

			// Split the string into lines
			if ( 'city' === $condition ){
				$lines = explode("\n", $cities);

				foreach ($lines as $line) {
					// Split each line by the ': ' delimiter
					list($key, $value) = explode(' : ', $line);
					$key = strtolower(str_replace(' ', '_', $key));
					// var_dump($key);
					// var_dump($value);

					// Trim whitespace and add to the array
					$this->instance_form_fields['city_name_'.$key]=array(
						'title'       => $value,
						'type'        => 'text',
						'description' => __( 'Enter the price of the provided city', 'shipping-option-conditions-wc' ),
						'desc_tip'    => true,	
					);
				}
			}else if ( 'city_weight' === $condition ){
				$this->instance_form_fields['city_name_weight_fallback']=array(
					'title'       => 'FallBack',
					'type'        => 'text',
					'description' => __( 'FallBack price for all locations  ', 'shipping-option-conditions-wc' ),
					'desc_tip'    => true,	
				);
				$lines = explode("\n", $cities);

				foreach ($lines as $line) {
					// Split each line by the ': ' delimiter
					list($key, $value) = explode(' : ', $line);
					$key = strtolower(str_replace(' ', '_', $key));
					// var_dump($key);
					// var_dump($value);

					// Trim whitespace and add to the array
					$this->instance_form_fields['city_name_weight_'.$key]=array(
						'title'       => $value,
						'type'        => 'text',
						'description' => __( 'Enter the price of the provided city. <br> The price should be coma separated for <strong>0.5kg,1kg,1.5kg,2kg,2.5kg,3kg,3.5kg,4kg,4.5kg,5kg</strong>  ', 'shipping-option-conditions-wc' ),
						'desc_tip'    => true,	
					);
				}
			}
			
        }
		
	}
		
}