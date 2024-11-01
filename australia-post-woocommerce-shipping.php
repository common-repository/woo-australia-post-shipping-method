<?php
/*
	Plugin Name: WooCommerce Australia Post Shipping (Basic)
	Plugin URI: https://www.xadapter.com/product/woocommerce-australia-post-shipping-plugin-with-tracking/
	Description: Australia Post API Real-time Shipping Rates. Label Printing and Tracking available for the premium version.
	Version: 2.1.2
    WC requires at least: 2.6.0
    WC tested up to: 3.4.4
	Author: AdaptXY
	Author URI: https://adaptxy.com/
    Text Domain: wf-shipping-auspost
*/

function wf_au_post_shipping_method_pre_activation_check(){
    // Checking if the WooCommerce is active or not
    if ( !is_plugin_active('woocommerce/woocommerce.php') ){
        deactivate_plugins( basename( __FILE__ ) );
        wp_die( __("Oops! WooCommerce plugin must be active for WooCommerce Australia Post Shipping (Basic) to work   ", "wf-shipping-auspost" ), "", array('back_link' => 1 ));
    }
	//check if basic version is there
	if ( is_plugin_active('australia-post-woocommerce-shipping/australia-post-woocommerce-shipping.php') ){
        deactivate_plugins( basename( __FILE__ ) );
		wp_die( __("Oops! You tried installing the basic version without deactivating and deleting the premium version. Kindly deactivate and delete WooCommerce Australia Post Shipping With Tracking and then try again", "wf-shipping-auspost" ), "", array('back_link' => 1 ));
	}
}

register_activation_hook( __FILE__, 'wf_au_post_shipping_method_pre_activation_check' );

if( ! defined('WF_AUSTRALIA_POST_ID') ){
	define("WF_AUSTRALIA_POST_ID", "wf_australia_post");
}

if( ! defined('WF_AUSTRALIA_POST_URL') ){
	define("WF_AUSTRALIA_POST_URL", plugin_dir_url(__FILE__));
}
/**
 * Localisation
 */
load_plugin_textdomain( 'wf_australia_post', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


/**
 * Check if WooCommerce is active
 */
if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {	

	if (!function_exists('wf_get_settings_url')){
		function wf_get_settings_url(){
			return version_compare(WC()->version, '2.1', '>=') ? "wc-settings" : "woocommerce_settings";
		}
	}
	
	if (!function_exists('wf_plugin_override')){
		add_action( 'plugins_loaded', 'wf_plugin_override' );
		function wf_plugin_override() {
			if (!function_exists('WC')){
				function WC(){
					return $GLOBALS['woocommerce'];
				}
			}
		}
	}
	if (!function_exists('wf_get_shipping_countries')){
		function wf_get_shipping_countries(){
			$woocommerce = WC();
			$shipping_countries = method_exists($woocommerce->countries, 'get_shipping_countries')
					? $woocommerce->countries->get_shipping_countries()
					: $woocommerce->countries->countries;
			return $shipping_countries;
		}
	}
	
	if(!class_exists('wf_au_post_woocommerce_shipping_setup')){
		
		class wf_au_post_woocommerce_shipping_setup {
			public function __construct() {
				$this->wf_init();
				add_action( 'init', array( $this, 'init' ) );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'wf_plugin_action_links' ) );		
				add_action( 'woocommerce_shipping_init', array( $this, 'wf_australia_post_init' ) );
				add_filter( 'woocommerce_shipping_methods', array( $this, 'wf_australia_post_add_method' ) );		
				add_filter( 'admin_enqueue_scripts', array( $this, 'wf_australia_post_scripts' ) );		
				
			}
			public function init() {
				if ( ! class_exists( 'wf_order' ) ) {
			  		include_once 'includes/class-wf-legacy.php';
			  	}
			}
			public function wf_init() {
                                $this->settings = get_option( 'woocommerce_'.WF_AUSTRALIA_POST_ID.'_settings', null );
                                $this->contracted_rates = isset($this->settings['contracted_rates']) && ($this->settings['contracted_rates'] == 'yes') ? true : false;			
			}
			
			public function wf_plugin_action_links( $links ) {
				$plugin_links = array(
					'<a href="' . admin_url( 'admin.php?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_australia_post' ) . '">' . __( 'Settings', 'wf_australia_post' ) . '</a>',
					'<a href="https://www.xadapter.com/category/product/woocommerce-australia-post-shipping-plugin-with-tracking/" target="_blank">' . __('Documentation', 'wf_australia_post') . '</a>',
					'<a href="https://wordpress.org/support/plugin/woo-australia-post-shipping-method" target="_blank">' . __( 'Support', 'wf_australia_post' ) . '</a>',
                    '<a href="https://www.xadapter.com/product/woocommerce-australia-post-shipping-plugin-with-tracking/" target="_blank">'.__('Go Premium','wf_australia_post').'</a>'
				);

				return array_merge( $plugin_links, $links );
			}
			
			public function wf_australia_post_init() {
				include_once( 'includes/class-wf-australia-post-shipping.php' );
			}
			
			public function wf_australia_post_add_method( $methods ) {
				$methods[] = 'wf_australia_post_shipping';
				return $methods;
			}
			
            public function wf_australia_post_scripts() {
				wp_enqueue_script('jquery');
                wp_enqueue_script( 'jquery-ui-sortable' );
                wp_enqueue_script('xadapter-auspost-custom', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/xadapter-auspost-custom.js', basename(__FILE__)), array(), '2.0.0', true);
                wp_enqueue_script('wf_common', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/wf_common.js', basename(__FILE__)), array(), '2.0.0', true);
                wp_localize_script('xadapter-auspost-custom', 'xa_auspost_custom', array('contracted_rates' => ''));                                
            }


		}
		new wf_au_post_woocommerce_shipping_setup();
	}
	
		if(!class_exists('wf_custom_woocommerce_fields')){
		class  wf_custom_woocommerce_fields{
	
			public function __construct() {
				if( is_admin() ){
					add_action( 'woocommerce_product_options_shipping', array($this,'wf_additional_product_shipping_options')  );
					add_action( 'woocommerce_process_product_meta', array( $this, 'wf_save_additional_product_shipping_options' ) );
				}
			}

			function wf_additional_product_shipping_options() {
				//Tariff code field
				woocommerce_wp_text_input( array(
					'id' => '_wf_tariff_code',
					'label' => __('Tariff Code (Australia Post)', 'wf-shipping-auspost'),
					'description' => __('The Harmonized Commodity Description and Coding System, also known as the Harmonized System (HS) of tariff nomenclature is an internationally standardized system of names and numbers to classify traded products.'),
					'desc_tip' => 'true',
					'placeholder' => ''
					) 
				);

				//Country of origin
				woocommerce_wp_text_input( array(
					'id' => '_wf_country_of_origin',
					'label' => __('Country of Origin (Australia Post)', 'wf-shipping-auspost'),
					'description' => __('A note on the country of origin can be updated here. This will be part of the commercial invoice. ', 'wf-shipping-auspost' ),
					'desc_tip' => 'true',
					'placeholder' => ''
					) 
				);
				
				//product shipping description
				woocommerce_wp_text_input( array(
					'id' => '_wf_shipping_description',
					'label' => __('Shipping Description (Australia Post)', 'wf-shipping-auspost'),
					'description' => __('A note on shipping product. This will be part of the commercial invoice. ', 'wf-shipping-auspost' ),
					'desc_tip' => 'true',
					'placeholder' => ''
					) 
				);
				
			}

			function wf_save_additional_product_shipping_options( $post_id ) {
				//Tariff code value
				if ( isset( $_POST['_wf_tariff_code'] ) ) {
					update_post_meta( $post_id, '_wf_tariff_code', esc_attr( $_POST['_wf_tariff_code'] ) );
				}
				//Country of manufacture
				if ( isset( $_POST['_wf_country_of_origin'] ) ) {
					update_post_meta( $post_id, '_wf_country_of_origin', esc_attr( $_POST['_wf_country_of_origin'] ) );
				}
				
				//Country of manufacture
				if ( isset( $_POST['_wf_shipping_description'] ) ) {
					update_post_meta( $post_id, '_wf_shipping_description', esc_attr( $_POST['_wf_shipping_description'] ) );
				}
				
			}
		}
		new wf_custom_woocommerce_fields();
	}
}
