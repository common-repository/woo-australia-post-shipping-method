<?php

if (!defined('ABSPATH')) {
    exit;
}

/*Making compatible with PHP 7.1 later versions*/
if (version_compare(phpversion(), '7.1', '>=')) {
    ini_set( 'serialize_precision', -1 );// Avoiding adding of unnecessary 17 decimal places resulted from json_encode
}

class wf_australia_post_shipping extends WC_Shipping_Method {

	private $endpoints = array(
		'calculation' => 'https://digitalapi.auspost.com.au/postage/{type}/{doi}/calculate.json',
		'services' => 'https://digitalapi.auspost.com.au/postage/{type}/{doi}/service.json',
		'getAccounts' => 'https://digitalapi.auspost.com.au/shipping/v1/accounts/'
	);

	/** Services called from 'services' API without options */
	private $services = array(
		// Domestic
		'AUS_PARCEL_REGULAR' => array(
			// Name of the service shown to the user
			'name' => 'Regular / Parcel Post',
			// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
			'alternate_services' => array(
				'AUS_PARCEL_REGULAR_SATCHEL_500G',
				'AUS_PARCEL_REGULAR_SATCHEL_3KG',
				'AUS_PARCEL_REGULAR_SATCHEL_5KG',
				'AUS_LETTER_REGULAR_SMALL',
				'AUS_LETTER_REGULAR_MEDIUM',
				'AUS_LETTER_REGULAR_LARGE',
				'AUS_LETTER_REGULAR_LARGE_125',
				'AUS_LETTER_REGULAR_LARGE_250',
				'AUS_LETTER_REGULAR_LARGE_500'
			)
		),
		'AUS_PARCEL_EXPRESS' => array(
			// Name of the service shown to the user
			'name' => 'Express Post',
			// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
			'alternate_services' => array(
				'AUS_PARCEL_EXPRESS_SATCHEL_500G',
				'AUS_PARCEL_EXPRESS_SATCHEL_3KG',
				'AUS_PARCEL_EXPRESS_SATCHEL_5KG',
				'AUS_LETTER_EXPRESS_SMALL',
				'AUS_LETTER_EXPRESS_MEDIUM',
				'AUS_LETTER_EXPRESS_LARGE'
			)
		),
		'AUS_PARCEL_COURIER' => array(
			// Name of the service shown to the user
			'name' => 'Courier Post',
			// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
			'alternate_services' => array(
				'AUS_PARCEL_COURIER_SATCHEL_SMALL',
				'AUS_PARCEL_COURIER_SATCHEL_MEDIUM',
				'AUS_PARCEL_COURIER_SATCHEL_LARGE'
			)
		),
		'INT_PARCEL_COR_OWN_PACKAGING' => array(
			'name' => 'International Post Courier'
		),
		'INT_PARCEL_EXP_OWN_PACKAGING' => array(
			'name' => 'International Post Express'
		),
		'INT_PARCEL_STD_OWN_PACKAGING' => array(
			'name' => 'International Post Standard'
		),
		'INT_PARCEL_AIR_OWN_PACKAGING' => array(
			'name' => 'International Post Economy Air'
		),
		'INT_LETTER_COR_OWN_PACKAGING' => array(
			'name' => 'International Letter Courier'
		),
		'INT_LETTER_EXP_OWN_PACKAGING' => array(
			'name' => 'International Letter Express'
		),
		'INT_LETTER_REG_SMALL_ENVELOPE' => array(
			'name' => 'International Letter Registered Post DL'
		),
		'INT_LETTER_REG_LARGE_ENVELOPE' => array(
			'name' => 'International Letter Registered Post B4'
		),
		'INT_LETTER_AIR_OWN_PACKAGING_LIGHT' => array(
			'name' => 'International Letter Economy Air',
			'alternate_services' => array(
				'INT_LETTER_AIR_OWN_PACKAGING_MEDIUM',
				'INT_LETTER_AIR_OWN_PACKAGING_HEAVY',
				'INT_LETTER_AIR_SMALL_ENVELOPE',
				'INT_LETTER_AIR_LARGE_ENVELOPE'
			),
		),
		'E34' => array(
			'name' => 'EXPRESS POST'
		),
		'T28S' => array(
			'name' => 'PARCEL POST W/SIGNATURE'
		),
		'E34S' => array(
			'name' => 'EXPRESS POST W/SIGNATURE'
		),
		'RPI6' => array(
			'name' => 'REGISTERED POST INTL 6 '
		),
		'PTI8' => array(
			'name' => 'PACK & TRACK INT\'L 8 '
		),
		'7C55' => array(
			'name' => 'PARCEL POST + SIGNATURE'
		),
		'7I55' => array(
			'name' => 'EXPRESS POST + SIGNATURE'
		),
		'RPI8' => array(
			'name' => 'REGISTERED POST INT\'L 8'
		),
		'ECM8' => array(
			'name' => 'EXPRESS COURIER INT\'L MERCH 8Z'
		),
		'AIR8' => array(
			'name' => 'INTERNATIONAL AIRMAIL 8Z'
		),
		'ECD8' => array(
			'name' => 'EXPRESS COURIER INT\'L DOC 8'
		),
//		'X1' => array(
//			  'name' => 'EXPRESS POST EPARCEL'
//			  ),    
		'XS' => array(
			'name' => 'EXPRESS POST SATCHELS'
		),
//		'S1' => array(
//			  'name' => 'EPARCEL 1'
//			  ),     
		'7B05' => array(
			'name' => 'PARCEL POST + SIGNATURE'
		),
		'7H05' => array(
			'name' => 'EXPRESS POST + SIGNATURE'
		),
		'NFR' => array(
			'name' => 'NATIONAL FULL RATE'
		),
		'XNFR' => array(
			'name' => 'EXPRESS NATIONAL FULL RATE'
		),
			/*

			  'INT_PARCEL_SEA_OWN_PACKAGING' => array(
			  'name' => 'Sea Mail'
			  ), */
	);
	private $extra_cover = array(
		'INT_PARCEL_AIR_OWN_PACKAGING' => 500,
		'INT_PARCEL_STD_OWN_PACKAGING' => 5000,
		'INT_PARCEL_COR_OWN_PACKAGING' => 5000,
		'INT_PARCEL_EXP_OWN_PACKAGING' => 5000,
		'AUS_PARCEL_REGULAR' => 5000,
		'AUS_PARCEL_COURIER' => 5000,
		'AUS_PARCEL_EXPRESS' => 5000,
		'AUS_PARCEL_COURIER' => 5000
	);
	private $delivery_confirmation = array(
		'INT_PARCEL_SEA_OWN_PACKAGING',
		'INT_PARCEL_AIR_OWN_PACKAGING',
		'AUS_PARCEL_REGULAR',
		'AUS_PARCEL_EXPRESS',
	);
	private $sod_cost = array('domestic' => 2.95, 'international' => 4.99); // Signature on delivery charges
	private $extra_cover_cost = array('domestic' => 1.5, 'international' => 9.6);// Extra cover costs
	private $additional_extra_cover_cost = array('international' => 2.5);
	private $found_rates;
	private $rate_cache;

	const API_HOST = 'digitalapi.auspost.com.au';
	const API_BASE_URL = '/test/shipping/v1/';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
        if( !function_exists('is_plugin_active') ) {        
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        
		$this->id = WF_AUSTRALIA_POST_ID;
		$this->method_title = __('Australia Post', 'wf_australia_post');
		$this->method_description = __('', 'wf_australia_post');
		$this->init();
	}

	/**
	 * init function.
	 *
	 * @access public
	 * @return void
	 */
	private function init() {
		include_once('data-wf-default-values.php');
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title = $this->get_option('title');
		$this->availability = $this->get_option('availability');
		$this->countries = $this->get_option('countries');
		$this->origin = $this->get_option('origin');
		$option_api_key = $this->get_option('api_key');
		$this->api_key = empty($option_api_key) ? '8fd6e23e-15fd-4e87-b7a2-ba557b0ff0dd' : $option_api_key;

		$this->contracted_rates = $this->get_option('contracted_rates') == 'yes' ? true : false;
		wp_localize_script('xadapter-auspost-custom', 'xa_auspost_custom', array('contracted_rates' => $this->contracted_rates));
		$option_contracted_api_mode = $this->get_option('contracted_api_mode');
		$this->contracted_api_mode = isset($option_contracted_api_mode) ? $option_contracted_api_mode : 'test';

		$option_api_pwd = $this->get_option('api_pwd');
		$this->api_pwd = empty($option_api_pwd) ? 'xe2b62f280f0d074428a' : $option_api_pwd;
		$option_api_account_no = $this->get_option('api_account_no');
		$this->api_account_no = empty($option_api_account_no) ? '1012131403' : $option_api_account_no;

		$this->packing_method = $this->get_option('packing_method');
		$this->boxes = $this->get_option('boxes');
		$this->custom_services = $this->get_option('services');
		$this->offer_rates = $this->get_option('offer_rates');
		$this->debug = $this->get_option('debug_mode') == 'yes' ? true : false;
		$this->alternate_services = $this->get_option('disable_alternate_services') == 'yes' ? false : true;
		$this->max_weight = $this->get_option('max_weight');
		$this->weight_unit = get_option('woocommerce_weight_unit');
		$this->dimension_unit = get_option('woocommerce_dimension_unit');
		$this->weight_packing_process = !empty($this->settings['weight_packing_process']) ? $this->settings['weight_packing_process'] : 'pack_descending'; // This feature will be implementing in next version
		$this->general_settings = get_option('woocommerce_wf_australia_post_settings');

		add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'clear_transients'));
	}

	/*
	 * function to get eligible postage products for a given contracted account number
	 * @access private
	 */

	private function get_services($endpoint, $account_number) {
		$header = '';
		$responseBody = '';

		$args = array(
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
				'Account-Number' => $this->api_account_no,
				'Authorization' => 'Basic ' . base64_encode($this->api_key . ":" . $this->api_pwd)
			),
		);

		$service_base_url = $endpoint . $account_number;

		$response = wp_remote_get($service_base_url, $args);
		if (is_array($response)) {
			$header = $response['headers']; // array of http header lines
			$responseBody = $response['body']; // use the content
		}
		return $responseBody;
	}

	/**
	 * Output a message
	 */
	public function debug($message, $type = 'notice') {
		if ($this->debug) {
			if (version_compare(WOOCOMMERCE_VERSION, '2.1', '>=')) {
				wc_add_notice($message, $type);
			} else {
				global $woocommerce;

				$woocommerce->add_message($message);
			}
		}
	}

	/**
	 * environment_check function.
	 *
	 * @access public
	 * @return void
	 */
	private function environment_check() {
		global $woocommerce;

		if (get_woocommerce_currency() != "AUD") {
			echo '<div class="error">
				<p>' . __('Australia Post requires that the currency is set to Australian Dollars.', 'wf_australia_post') . '</p>
			</div>';
		} elseif ($woocommerce->countries->get_base_country() != "AU") {
			echo '<div class="error">
				<p>' . __('Australia Post requires that the base country/region is set to Australia.', 'wf_australia_post') . '</p>
			</div>';
		}
	}

	/**
	 * admin_options function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options() {
		// Check users environment supports this method
		$this->environment_check();

		// Show settings
		parent::admin_options();
	}

	/**
	 * validate_box_packing_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_box_packing_field($key) {
		if (!isset($_POST['boxes_outer_length']))
			return;

		$boxes_outer_length = $_POST['boxes_outer_length'];
		$boxes_outer_width = $_POST['boxes_outer_width'];
		$boxes_outer_height = $_POST['boxes_outer_height'];
		$boxes_inner_length = $_POST['boxes_inner_length'];
		$boxes_inner_width = $_POST['boxes_inner_width'];
		$boxes_inner_height = $_POST['boxes_inner_height'];
		$boxes_box_weight = $_POST['boxes_box_weight'];
		$boxes_max_weight = $_POST['boxes_max_weight'];
		$boxes_is_letter = isset($_POST['boxes_is_letter']) ? $_POST['boxes_is_letter'] : array();

		$boxes = array();

		for ($i = 0; $i < sizeof($boxes_outer_length); $i ++) {

			if ($boxes_outer_length[$i] && $boxes_outer_width[$i] && $boxes_outer_height[$i]) {

				$outer_dimensions = array_map('floatval', array($boxes_outer_length[$i], $boxes_outer_height[$i], $boxes_outer_width[$i]));
				$inner_dimensions = array_map('floatval', array($boxes_inner_length[$i], $boxes_inner_height[$i], $boxes_inner_width[$i]));

				sort($outer_dimensions);
				sort($inner_dimensions);

				// Min sizes - girth min is 16
				$outer_girth = $outer_dimensions[0] + $outer_dimensions[0] + $outer_dimensions[1] + $outer_dimensions[1];
				$inner_girth = $inner_dimensions[0] + $inner_dimensions[0] + $inner_dimensions[1] + $inner_dimensions[1];

				if ($outer_girth < 16) {
					if ($outer_dimensions[0] < 4)
						$outer_dimensions[0] = 4;

					if ($outer_dimensions[1] < 5)
						$outer_dimensions[1] = 5;
				}

				if ($inner_girth < 16) {
					if ($inner_dimensions[0] < 4)
						$inner_dimensions[0] = 4;

					if ($inner_dimensions[1] < 5)
						$inner_dimensions[1] = 5;
				}

				if ($outer_dimensions[2] > 105)
					$outer_dimensions[2] = 105;

				if ($inner_dimensions[2] > 105)
					$inner_dimensions[2] = 105;

				$outer_length = $outer_dimensions[2];
				$outer_height = $outer_dimensions[0];
				$outer_width = $outer_dimensions[1];

				$inner_length = $inner_dimensions[2];
				$inner_height = $inner_dimensions[0];
				$inner_width = $inner_dimensions[1];

				if (empty($inner_length) || $inner_length > $outer_length)
					$inner_length = $outer_length;

				if (empty($inner_height) || $inner_height > $outer_height)
					$inner_height = $outer_height;

				if (empty($inner_width) || $inner_width > $outer_width)
					$inner_width = $outer_width;

				$weight = floatval($boxes_max_weight[$i]);

				if ($weight > 22 || empty($weight))
					$weight = 22;

				$boxes[] = array(
					'outer_length' => $outer_length,
					'outer_width' => $outer_width,
					'outer_height' => $outer_height,
					'inner_length' => $inner_length,
					'inner_width' => $inner_width,
					'inner_height' => $inner_height,
					'box_weight' => floatval($boxes_box_weight[$i]),
					'max_weight' => $weight,
					'is_letter' => isset($boxes_is_letter[$i]) ? true : false
				);
			}
		}

		return $boxes;
	}

	/**
	 * clear_transients function.
	 *
	 * @access public
	 * @return void
	 */
	public function clear_transients() {
		delete_transient('wf_australia_post_quotes');
	}

	public function generate_activate_box_html() {
		ob_start();
		$plugin_name = 'australiapost';
		include( 'wf_api_manager/html/html-wf-activation-window.php' );
		return ob_get_clean();
	}

	public function generate_wf_aus_tab_box_html() {

		$tab = (!empty($_GET['subtab'])) ? esc_attr($_GET['subtab']) : 'general';

		echo '
                <div class="wrap">
                    <style>
                        .woocommerce-help-tip{color:darkgray !important;}
                        .woocommerce-save-button{display:none !important;}
                        <style>
                        .woocommerce-help-tip {
                            position: relative;
                            display: inline-block;
                            border-bottom: 1px dotted black;
                        }

                        .woocommerce-help-tip .tooltiptext {
                            visibility: hidden;
                            width: 120px;
                            background-color: black;
                            color: #fff;
                            text-align: center;
                            border-radius: 6px;
                            padding: 5px 0;

                            /* Position the tooltip */
                            position: absolute;
                            z-index: 1;
                        }

                        .woocommerce-help-tip:hover .tooltiptext {
                            visibility: visible;
                        }
                        </style>
                    </style>
                    <hr class="wp-header-end">';
		$this->wf_aus_shipping_page_tabs($tab);
		switch ($tab) {
			case "general":
				echo '<div class="table-box table-box-main" id="general_section" style="margin-top: 0px;
						border: 1px solid #ccc;border-top: unset !important;padding: 5px;">';
				require_once('settings/aus_general_settings.php');
				echo '</div>';
				break;
			case "rates":
				echo '<div class="table-box table-box-main" id="rates_section" style="margin-top: 0px;
						border: 1px solid #ccc;border-top: unset !important;padding: 5px;">';
				require_once('settings/aus_rates_settings.php');

				echo '</div>';
				break;
			case "labels":
				echo '<div class="table-box table-box-main" id="labels_section" style="margin-top: 0px;
						border: 1px solid #ccc;border-top: unset !important;padding: 5px;">';
				require_once('market.php');
				echo '</div>';
				break;
			case "packing":
				echo '<div class="table-box table-box-main" id="packing_section" style="margin-top: 0px;
						border: 1px solid #ccc;border-top: unset !important;padding: 5px;">';
				require_once('settings/aus_packingl_settings.php');
				echo '</div>';
				break;
			case "premium":
				echo '<div class="table-box table-box-main" id="licence_section" style="margin-top: 0px;
						border: 1px solid #ccc;border-top: unset !important;padding: 5px;"><br>';
				$plugin_name = 'australiapost';
				include_once( 'market.php' );

				echo '</div>';
				break;
		}
		echo '
                </div>';
	}

	public function wf_aus_shipping_page_tabs($current = 'general') {
		$activation_check = get_option('australiapost_activation_status');
		if (!empty($activation_check) && $activation_check === 'active') {
			$acivated_tab_html = "<small style='color:green;font-size:xx-small;'>(Activated)</small>";
		} else {
			$acivated_tab_html = "<small style='color:red;font-size:xx-small;'>(Activate)</small>";
		}
        
        $tag_premium = "<small style='color:green;font-size:xx-small;'>Premium</small>";
        $tab_premium = "<navtab style='color:red'>Go Premium</navtab>";
        
		$tabs = array(
			'general' => __("General", 'wf_australia_post'),
			'rates' => __("Rates & Services", 'wf_australia_post'),
			'labels' => __("Label & Tracking ".$tag_premium, 'wf_australia_post'),
			'packing' => __("Packaging ".$tag_premium, 'wf_australia_post'),
			'premium' => __($tab_premium, 'wf_australia_post')
		);
        
		$html = '<h2 class="nav-tab-wrapper">';
		foreach ($tabs as $tab => $name) {
            if($tab == 'labels' || $tab == 'packing'){
                $class = ($tab == $current) ? 'nav-tab-active' : '';
                $style = ($tab == $current) ? 'border-bottom: 1px solid transparent !important;' : '';
                $html .= '<a style="text-decoration:none !important;' . $style . '" class="nav-tab ' . $class . '" href="?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_australia_post&subtab=' . 'premium' . '">' . $name . '</a>';   
            }else{
                $class = ($tab == $current) ? 'nav-tab-active' : '';
                $style = ($tab == $current) ? 'border-bottom: 1px solid transparent !important;' : '';
                $html .= '<a style="text-decoration:none !important;' . $style . '" class="nav-tab ' . $class . '" href="?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_australia_post&subtab=' . $tab . '">' . $name . '</a>';
            }
		}
		$html .= '</h2>';
		echo $html;
	}

	/**
	 * init_form_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields() {
		global $woocommerce;
		if (isset($_GET['page']) && $_GET['page'] === 'wc-settings') {
			$this->form_fields = array(
				'wf_aus_tab_box_key' => array(
					'type' => 'wf_aus_tab_box'
				),
			);
		}
	}

	/**
	 * Will girth fit
	 *
	 * @param  [type] $item_w
	 * @param  [type] $item_h
	 * @param  [type] $package_w
	 * @param  [type] $package_h
	 * @return bool
	 */
	public function girth_fits_in_satchel($item_l, $item_w, $item_h, $package_l, $package_w) {

		// Check max height
		if ($item_h > ( $package_w / 2 ))
			return false;

		// Girth = around the item
		$item_girth = $item_w + $item_h;

		if ($item_girth > $package_w)
			return false;

		// Girth 2 = around the item
		$item_girth = $item_l + $item_h;

		if ($item_girth > $package_l)
			return false;

		return true;
	}

	/**
	 * See if rate is satchel
	 *
	 * @return boolean
	 */
	public function is_satchel($code) {
		return strpos($code, '_SATCHEL_') !== false;
	}

	/*
	 * function to get highest dimension among all the packed products
	 * @access public
	 */

	public function return_highest($dimension_array) {
		$dimension = 0;
		$dimension = round(max($dimension_array));
		return $dimension;
	}

	/**
	 * calculate_shipping function.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping($package = array()) {
		global $woocommerce;
		$this->is_international = ( $package['destination']['country'] == 'AU' ) ? false : true;
		$this->found_rates = array();
		$this->rate_cache = get_transient('wf_australia_post_quotes');
		$headers = $this->get_request_header();
		$package_requests = $this->get_package_requests($package);
		$settings_services = !empty($this->general_settings['services'])? $this->general_settings['services']: $this->settings['services'];
		$custom_services = array();

		try{
            if ($this->contracted_rates) {
                $from_and_to = $this->get_request($package);

                $from_post_cod = str_replace(' ', '', strtoupper($this->origin));
                $to_post_cod = isset($from_and_to['to_postcode']) ? array('postcode' => $from_and_to['to_postcode']) : array('country' => $from_and_to['country_code']);
                if (is_array($package_requests)) {
                    foreach ($package_requests as $key => $package_request) {
                        $from_weight_unit = '';
                        if ($this->weight_unit != 'kg') {
                            $from_weight_unit = $this->weight_unit;
                        }

                        $from_dimension_unit = '';
                        if ($this->dimension_unit != 'cm') {
                            $from_dimension_unit = $this->dimension_unit;
                        }
                        
                        $package_extra_cover = $package_request['InsuredValue']['Amount']? $package_request['InsuredValue']['Amount']: 0;
                        
                        $package_req = array(
                            'weight' => round(wc_get_weight($package_request['Weight']['Value'], 'kg', $from_weight_unit), 2),
                            'length' => round(wc_get_dimension($package_request['Dimensions']['Length'], 'cm', $from_dimension_unit)),
                            'width' => round(wc_get_dimension($package_request['Dimensions']['Width'], 'cm', $from_dimension_unit)),
                            'height' => round(wc_get_dimension($package_request['Dimensions']['Height'], 'cm', $from_dimension_unit))
                        );

                        $request_params = array(
                            'from' => array('postcode' => $from_post_cod),
                            'to' => $to_post_cod,
                            'items' => $package_req
                        );

                        $headers = $this->buildHttpHeaders($request_params);

                        $endpoint = 'https://' . self::API_HOST . self::API_BASE_URL . 'prices/items/';
                        if ($this->contracted_api_mode == 'live') {
                            $endpoint = str_replace('test/', '', $endpoint);
                        }

                        $this->debug(__('AusPost debug is ON - to hide these messages, disable <i>debug mode</i> in settings.', 'wf_australia_post'));
                        $this->debug('AusPost Contracted rate REQUEST: <pre>' . print_r($request_params, true) . '</pre>');

                        $rates = $this->get_contracted_rates($endpoint, $request_params, $headers);


                        if (!empty($rates)) {
                            foreach ($rates as $rate) {
                                if ($rate->calculated_price) {
                                    $this->prepare_rate($rate->product_id, $rate->product_id, $rate->product_type, $rate->calculated_price, $request_params);
                                }
                            }
                        }

                        $get_account_endpoint = 'https://' .self::API_HOST.self::API_BASE_URL.'accounts/';
                        if ($this->contracted_api_mode == 'live') {
                            $get_account_endpoint = str_replace('test/', '', $get_account_endpoint);
                        }
                        $acc_num = $this->api_account_no;

                        $contracted_account_details = $this->get_services($get_account_endpoint, $acc_num);
                        $contracted_account_details = json_decode($contracted_account_details, true);
                        $postage_products = array();
                        $postage_products = $contracted_account_details['postage_products'];
                        $cheapest = '';

                        if (!empty($this->found_rates)) {
                            if (is_array($postage_products)) {
                                foreach ($postage_products as $postage_product) {
                                    foreach ($this->found_rates as $rate) {
                                        $rate['enabled'] = false;
                                        if ($postage_product['product_id'] === $rate['id']) {
                                            foreach ($settings_services as $key => $settings_service) {
                                                if ($settings_service['enabled']) {
                                                    if ($postage_product['product_id'] === $key) {
                                                        $adjustment = 0;
                                                        $adjustment_percentage = 0;
                                                        if (!empty($settings_service['name'])) {
                                                            $rate['label'] = $settings_service['name'];
                                                        } else {
                                                            $rate['label'] = (isset($postage_product['type']) && $postage_product['type']) ? $postage_product['type'] : $rate['label'];
                                                        }
                                                        if ($settings_service['extra_cover'] == true) {
                                                            if ($this->is_international) {
                                                                if ($package_extra_cover <= 100) {
                                                                    $rate['cost'] += $this->extra_cover_cost['international']; // extra cover fee for less than 100
                                                                } else {
                                                                    $sub_extra_cover = ($package_extra_cover - 100) / 100;

                                                                    if (is_float($sub_extra_cover)) {
                                                                        if (round($sub_extra_cover) > $sub_extra_cover) {
                                                                            $sub_extra_cover = round($sub_extra_cover);
                                                                        }
                                                                        if (round($sub_extra_cover) < $sub_extra_cover) {
                                                                            $sub_extra_cover = round($sub_extra_cover) + 1;
                                                                        }
                                                                    }

                                                                    $rate['cost'] += $this->extra_cover_cost['international'] + ( 2.5 * $sub_extra_cover); // extra cover fee for greater than 100
                                                                }
                                                            }else{
                                                                $sub_extra_cover = $package_extra_cover / 100;
                                                                if (is_float($sub_extra_cover)) {
                                                                    if (round($sub_extra_cover) > $sub_extra_cover) {
                                                                        $sub_extra_cover = round($sub_extra_cover);
                                                                    }
                                                                    if (round($sub_extra_cover) < $sub_extra_cover) {
                                                                        $sub_extra_cover = round($sub_extra_cover) + 1;
                                                                    }
                                                                }
                                                                $rate['cost'] += $this->extra_cover_cost['domestic'] * $sub_extra_cover;
                                                            }
                                                        }

                                                        if($this->is_international){
                                                            if($settings_service['delivery_confirmation'] == true){
                                                                $rate['cost'] += $this->sod_cost['international'];
                                                            }
                                                        }else{
                                                            if ($package_extra_cover >= 300 || ($settings_service['delivery_confirmation'] == true)) {
                                                                $rate['cost'] += $this->sod_cost['domestic'];
                                                            }
                                                        }

                                                        if (!empty($settings_service['adjustment'])) {
                                                            $adjustment = $settings_service['adjustment'];
                                                        }

                                                        if (!empty($settings_service['adjustment_percent'])) {
                                                            $adjustment_percentage = $rate['cost'] * ($settings_service['adjustment_percent'] / 100);
                                                        }
                                                        $rate['cost'] += ($adjustment == 0) ? 0 : $adjustment;
                                                        $rate['cost'] += ($adjustment_percentage == 0) ? 0 : $adjustment_percentage;
                                                        $rate['enabled'] = true;
                                                        $this->found_rates[$postage_product['product_id']]['cost'] = $rate['cost'];
                                                    }
                                                }
                                            }
                                            if ($this->offer_rates != 'all') {
                                                if (!$cheapest || $cheapest['cost'] > $rate['cost']) {
                                                    $cheapest = $rate;
                                                }
                                                $cheapest['label'] = $this->title;
                                            } else {
                                                if (($this->general_settings['enabled'] == 'yes') && $rate['enabled'] == true) {
                                                    $this->add_rate($rate);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if ($this->general_settings['enabled'] && $this->offer_rates != 'all') {
                                $this->add_rate($cheapest);
                            }
                        }
                        $this->debug('AusPost Contracted rate RESPONSE: <pre>' . print_r($rates, true) . '</pre>');
                    }
                }
                return;
            } else {

                $custom_services = $this->custom_services;

                $this->debug(__('Australia Post debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf_australia_post'));

                // Prepare endpoints
                $letter_services_endpoint = str_replace(array('{type}', '{doi}'), array('letter', (!$this->is_international ? 'domestic' : 'international' )), $this->endpoints['services']);
                $letter_calculation_endpoint = str_replace(array('{type}', '{doi}'), array('letter', (!$this->is_international ? 'domestic' : 'international' )), $this->endpoints['calculation']);
                $services_endpoint = str_replace(array('{type}', '{doi}'), array('parcel', (!$this->is_international ? 'domestic' : 'international' )), $this->endpoints['services']);
                $calculation_endpoint = str_replace(array('{type}', '{doi}'), array('parcel', (!$this->is_international ? 'domestic' : 'international' )), $this->endpoints['calculation']);

                if ($package_requests) {
                    foreach ($package_requests as $key => $package_request) {
                        $from_weight_unit = '';
                        if ($this->weight_unit != 'kg') {
                            $from_weight_unit = $this->weight_unit;
                        }

                        $from_dimension_unit = '';
                        if ($this->dimension_unit != 'cm') {
                            $from_dimension_unit = $this->dimension_unit;
                        }
                        
                        $package_extra_cover = $package_request['InsuredValue']['Amount']?$package_request['InsuredValue']['Amount']: 0;

                        $package_req = array(
                            'weight' => round(wc_get_weight($package_request['Weight']['Value'], 'kg', $from_weight_unit), 2),
                            'length' => round(wc_get_dimension($package_request['Dimensions']['Length'], 'cm', $from_dimension_unit)),
                            'width' => round(wc_get_dimension($package_request['Dimensions']['Width'], 'cm', $from_dimension_unit)),
                            'height' => round(wc_get_dimension($package_request['Dimensions']['Height'], 'cm', $from_dimension_unit))
                        );

                        $request = http_build_query(array_merge($package_req, $this->get_request($package)), '', '&');

                        if (isset($package_request['thickness'])) {
                            $response = $this->get_response($letter_services_endpoint, $request, $headers);
                        } else {
                            $response = $this->get_response($services_endpoint, $request, $headers);
                        }

                        if (isset($response->services->service) && is_array($response->services->service)) {
                            // Loop our known services
                            foreach ($this->services as $service => $values) {

                                $rate_code = (string) $service;
                                $rate_id = $this->id . ':' . $rate_code;
                                $rate_name = (string) $values['name'];
                                $rate_cost = 0;
                                $adjustment = 0;
                                $adjustment_percentage = 0;

                                // Main service code
                                foreach ($response->services->service as $quote) {
                                    if (( $this->alternate_services && isset($values['alternate_services']) && in_array($quote->code, $values['alternate_services']) ) || $service == $quote->code) {
                                        $delivery_confirmation = false;

                                        if ($this->is_satchel($quote->code)) {
                                            switch ($quote->code) {
                                                case 'AUS_PARCEL_REGULAR_SATCHEL_500G' :
                                                case 'AUS_PARCEL_EXPRESS_SATCHEL_500G' :
                                                    if ($package_request['Dimensions']['Length'] > 35 || $package_request['Dimensions']['Width'] > 22 || !$this->girth_fits_in_satchel($package_request['Dimensions']['Length'], $package_request['Dimensions']['Width'], $package_request['Dimensions']['Height'], 35, 22)) {
                                                        continue;
                                                    }
                                                    break;
                                                case 'AUS_PARCEL_REGULAR_SATCHEL_3KG' :
                                                case 'AUS_PARCEL_EXPRESS_SATCHEL_3KG' :
                                                    if ($package_request['Dimensions']['Length'] > 40 || $package_request['Dimensions']['Width'] > 31 || !$this->girth_fits_in_satchel($package_request['Dimensions']['Length'], $package_request['Dimensions']['Width'], $package_request['Dimensions']['Height'], 40, 31)) {
                                                        continue;
                                                    }
                                                    break;
                                                case 'AUS_PARCEL_REGULAR_SATCHEL_5KG' :
                                                case 'AUS_PARCEL_EXPRESS_SATCHEL_5KG' :
                                                    if ($package_request['Dimensions']['Length'] > 51 || $package_request['Dimensions']['Width'] > 43 || !$this->girth_fits_in_satchel($package_request['Dimensions']['Length'], $package_request['Dimensions']['Width'], $package_request['Dimensions']['Height'], 51, 43)) {
                                                        continue;
                                                    }
                                                    break;
                                            }
                                            if(isset($this->custom_services[$rate_code]['delivery_confirmation'])){
                                            	if ($this->custom_services[$rate_code]['delivery_confirmation'] == true) {
		                                                $delivery_confirmation = true;
		                                            }
                                            }
                                        } else {
                                        	if(isset($this->custom_services[$rate_code]['delivery_confirmation'])){
                                        		if ($this->custom_services[$rate_code]['delivery_confirmation'] == true) {
		                                                $delivery_confirmation = true;
		                                            }
                                        	}
                                        }

                                        if ($rate_cost == 0) {
                                            $rate_cost = $quote->price;
                                        } elseif ($quote->price < $rate_cost) {
                                            $rate_cost = $quote->price;
                                        }
                                    }
                                }


                                if ($rate_cost) {
                                    if (!$this->is_international) {
                                        // User wants extra cover
                                        if(isset($custom_services[$rate_code])){
                                            if ($custom_services[$rate_code]['extra_cover'] == true) {
                                                $sub_extra_cover = $package_extra_cover / 100;
                                                if (is_float($sub_extra_cover)) {
                                                    if (round($sub_extra_cover) > $sub_extra_cover) {
                                                        $sub_extra_cover = round($sub_extra_cover);
                                                    }
                                                    if (round($sub_extra_cover) < $sub_extra_cover) {
                                                        $sub_extra_cover = round($sub_extra_cover) + 1;
                                                    }
                                                }
                                                $rate_cost += $this->extra_cover_cost['domestic'] * $sub_extra_cover;
                                                if ($package_extra_cover > 300) {
                                                    $delivery_confirmation = true;
                                                }
                                            }

                                            // User wants SOD
                                            if ($custom_services[$rate_code]['delivery_confirmation'] == true) {//($delivery_confirmation == true) || 
                                                $rate_cost += $this->sod_cost['domestic'];
                                            }

                                            if (!empty($custom_services[$rate_code]['adjustment'])) {
                                                $adjustment = $custom_services[$rate_code]['adjustment'];
                                            }

                                            if (!empty($custom_services[$rate_code]['adjustment_percent'])) {
                                                $adjustment_percentage = $rate_cost * ($custom_services[$rate_code]['adjustment_percent'] / 100);
                                            }
                                            $rate_cost += $adjustment + $adjustment_percentage;
                                        }
                                    } else {
                                        if(isset($custom_services[$rate_code])){
                                            if ($custom_services[$rate_code]['extra_cover'] == true) {
                                                if ($package_extra_cover <= 100) {
                                                    $rate_cost += $this->extra_cover_cost['international']; // extra cover fee for less than 100
                                                } else {
                                                    $sub_extra_cover = ($package_extra_cover - 100) / 100;

                                                    if (is_float($sub_extra_cover)) {
                                                        if (round($sub_extra_cover) > $sub_extra_cover) {
                                                            $sub_extra_cover = round($sub_extra_cover);
                                                        }
                                                        if (round($sub_extra_cover) < $sub_extra_cover) {
                                                            $sub_extra_cover = round($sub_extra_cover) + 1;
                                                        }
                                                    }

                                                    $rate_cost += $this->extra_cover_cost['international'] + ( 2.5 * $sub_extra_cover); // extra cover fee for greater than 100
                                                }
                                            }

                                            if ($custom_services[$rate_code]['delivery_confirmation'] == true) {
                                                $rate_cost += $this->sod_cost['international'];
                                            }

                                            if (!empty($custom_services[$rate_code]['adjustment'])) {
                                                $adjustment = $this->custom_services[$rate_code]['adjustment'];
                                            }

                                            if (!empty($custom_services[$rate_code]['adjustment_percent'])) {
                                                $adjustment_percentage = $rate_cost * ($custom_services[$rate_code]['adjustment_percent'] / 100);
                                            }
                                            $rate_cost += $adjustment + $adjustment_percentage;
                                        }
                                    }
                                    $this->prepare_rate($rate_code, $rate_id, $rate_name, $rate_cost, $package_req);
                                }
                            }
                        }
                    }

                    // Now do the calculation API
                    $additional_package_requests = $this->get_additional_package_requests($package, $package_requests);
                    if ($additional_package_requests) {
                        // Clear old
                        foreach ($additional_package_requests as $key => $package_request) {
                            $rate_code = $package_request['service_code'];

                            unset($this->found_rates[$this->id . ':' . $rate_code]);
                        }

                        // Request new
                        foreach ($additional_package_requests as $key => $additional_package_request) {

                            $request = str_replace('suboption_code_2', 'suboption_code', http_build_query(array_merge($additional_package_request, $this->get_request($package)), '', '&'));

                            if (isset($additional_package_request['thickness'])) {
                                $response = $this->get_response($letter_calculation_endpoint, $request, $headers);
                            } else {
                                $response = $this->get_response($calculation_endpoint, $request, $headers);
                            }

                            if (isset($response->postage_result) && is_object($response->postage_result)) {

                                $service = $response->postage_result;

                                $rate_code = $additional_package_request['service_code'];

                                if ($additional_package_request['option_code'] && ( strstr($rate_code, 'AUS_LETTER_REGULAR_SMALL') || strstr($rate_code, 'AUS_LETTER_REGULAR_LARGE') ))
                                    $rate_code = 'AUS_PARCEL_REGULAR';

                                if ($additional_package_request['option_code'] && ( strstr($rate_code, 'AUS_LETTER_EXPRESS_SMALL') || strstr($rate_code, 'AUS_LETTER_EXPRESS_LARGE') ))
                                    $rate_code = 'AUS_PARCEL_EXPRESS';

                                $rate_id = $this->id . ':' . $rate_code;

                                if (isset($this->services[$rate_code]['name']))
                                    $rate_name = (string) $this->services[$rate_code]['name'];

                                $rate_cost = (float) $service->total_cost;

                                //Delivery confirmation and Extra cover option together not support by API. The solution is to add a static value $2.95 for domestic and $4.99 for international with rates.
                                if (!$this->is_international) {
                                    if ($delivery_confirmation) {
                                        $rate_cost += $this->sod_cost['domestic'];
                                    }
                                } else {
                                    if ($delivery_confirmation) {
                                        $rate_cost += $this->sod_cost['international'];
                                    }
                                }

                                $this->prepare_rate($rate_code, $rate_id, $rate_name, $rate_cost, $additional_package_request);
                            }
                        }
                    }
                }
            }
            // Set transient
            set_transient('wf_australia_post_quotes', $this->rate_cache, YEAR_IN_SECONDS);

    //		 Ensure rates were found for all packages
            if ($this->found_rates) {
                foreach ($this->found_rates as $key => $value) {
                    if(isset($value['packages'])){
                        if ($value['packages'] < sizeof($package_requests))
                        unset($this->found_rates[$key]);
                    }
                }
            }

            // Add rates
            if ($this->found_rates) {
                $all_services = $this->general_settings['services'];
                if ($this->offer_rates == 'all') {
                    uasort($this->found_rates, array($this, 'sort_rates'));
                    foreach ($this->found_rates as $key => $rate) {
                        $service_name = str_replace("wf_australia_post:", "", $key);
                        foreach ($all_services as $service_key => $service) {
                            if ($service_key === $service_name) {
                                if (isset($service['name']) && !empty($service['name'])) {
                                    $rate['label'] = $service['name'];
                                }
                            }
                            if(isset($custom_services[$service_name])){
                                if ($this->general_settings['enabled'] && $custom_services[$service_name]['enabled']) {
                                    $this->add_rate($rate);
                                    $this->found_rates[$key]['cost'] = $rate['cost'];
                                }
                            }
                        }
                    }
                } else {

                    $cheapest_rate = '';

                    foreach ($this->found_rates as $key => $rate) {
                        if (!$cheapest_rate || $cheapest_rate['cost'] > $rate['cost'])
                            $cheapest_rate = $rate;
                    }

                    $cheapest_rate['label'] = $this->title;

                    if ($this->general_settings['enabled']) {
                        $this->add_rate($cheapest_rate);
                    }
                }
            }
        }catch(Exception $e){
            $this->debug("Save the rates in settings (Australia Post)");
        }
	}
    
	/**
	 * prepare_rate function.
	 *
	 * @access private
	 * @param mixed $rate_code
	 * @param mixed $rate_id
	 * @param mixed $rate_name
	 * @param mixed $rate_cost
	 * @return void
	 */
	private function prepare_rate($rate_code, $rate_id, $rate_name, $rate_cost, $package_request = '') {
		if (!empty($this->custom_services[$rate_code])) {
			$this->custom_services[$rate_code] = apply_filters('wf_australia_post_rate_services', $this->custom_services, $this->custom_services[$rate_code], $rate_code, $package_request);
		}

		// Name adjustment
		if (!empty($this->custom_services[$rate_code]['name']))
			$rate_name = $this->custom_services[$rate_code]['name'];

		// Cost adjustment %
		if (!empty($this->custom_services[$rate_code]['adjustment_percent']))
			$rate_cost = $rate_cost + ( $rate_cost * ( floatval($this->custom_services[$rate_code]['adjustment_percent']) / 100 ) );
		// Cost adjustment
		if (!empty($this->custom_services[$rate_code]['adjustment']))
			$rate_cost = $rate_cost + floatval($this->custom_services[$rate_code]['adjustment']);
		// Enabled check
		if (isset($this->custom_services[$rate_code]) && isset($this->custom_services[$rate_code]['enabled']) && empty($this->custom_services[$rate_code]['enabled']))
			return;

		// Merging
		if (isset($this->found_rates[$rate_id])) {
			$rate_cost = $rate_cost + $this->found_rates[$rate_id]['cost'];
			$packages = 1 + $this->found_rates[$rate_id]['packages'];
		} else {
			$packages = 1;
		}

		// Sort
		if (isset($this->custom_services[$rate_code]['order'])) {
			$sort = $this->custom_services[$rate_code]['order'];
		} else {
			$sort = 999;
		}

		$this->found_rates[$rate_id] = array(
			'id' => $rate_id,
			'label' => $rate_name . ' (' . $this->title . ')',
			'cost' => $rate_cost,
			'sort' => $sort,
			'packages' => $packages
		);
	}

	/**
	 * get_response function.
	 *
	 * @access private
	 * @param mixed $endpoint
	 * @param mixed $request
	 * @return void
	 */
	private function get_response($endpoint, $request, $headers) {
		global $woocommerce;
		if (isset($this->rate_cache[md5($request)])) {

			$response = $this->rate_cache[md5($request)];
		} else {

			$response = wp_remote_get($endpoint . '?' . $request, array(
				'timeout' => 70,
				'sslverify' => 0,
				'headers' => $headers
					)
			);


			$response = json_decode($response['body']);

			// Store result in case the request is made again
			$this->rate_cache[md5($request)] = $response;
		}

		$this->debug('Australia Post REQUEST: <pre>' . print_r(htmlspecialchars($request), true) . '</pre>');
		$this->debug('Australia Post RESPONSE: <pre>' . print_r($response, true) . '</pre>');

		return $response;
	}

	/**
	 * sort_rates function.
	 *
	 * @access public
	 * @param mixed $a
	 * @param mixed $b
	 * @return void
	 */
	public function sort_rates($a, $b) {
		if ($a['sort'] == $b['sort'])
			return 0;
		return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
	}

	/**
	 * get_request_header for JSON function.
	 *
	 */
	private function buildHttpHeaders($request) {
		$a_headers = array(
			'Authorization' => 'Basic ' . base64_encode($this->api_key . ':' . $this->api_pwd),
			'content-length ' => strlen(json_encode($request)),
			'content-type' => 'application/json',
			'Account-Number' => $this->api_account_no
		);
		return $a_headers;
	}

	/**
	 * get_request_header function.
	 *
	 * @access private
	 * @return array
	 */
	private function get_request_header() {
		return array(
			'AUTH-KEY' => $this->api_key
		);
	}

	/**
	 * get_request function.
	 *
	 * @access private
	 * @param mixed $package
	 * @return void
	 */
	private function get_request($package) {

		$request = array();

		$request['from_postcode'] = str_replace(' ', '', strtoupper($this->origin));

		switch ($package['destination']['country']) {
			case "AU" :
				$request['to_postcode'] = str_replace(' ', '', strtoupper($package['destination']['postcode']));
				break;
			default :
				$request['country_code'] = $package['destination']['country'];
				break;
		}

		return $request;
	}

	/**
	 * get_request function.
	 *
	 * @access private
	 * @return void
	 */
	private function get_package_requests($package) {
		$requests = array();
		$requests = $this->per_item_shipping($package);
		return $requests;
	}

	/**
	 * Get additonal requests.
	 *
	 * @param array $package
	 * @param array $regular_requests
	 * @return array
	 */
	private function get_additional_package_requests($package, $regular_requests) {
		$requests = array();
		if ($package['destination']['country'] == 'AU' || isset($request['thickness']))
			return array();

		// Special requests + extra cover + registered post
		if ($regular_requests) {
			foreach ($regular_requests as $request) {

				// loop requests
				foreach ($this->services as $code => $service) {

					if (empty($this->custom_services[$code]['enabled']))
						continue;

					$extra_cover = false;
					$delivery_confirmation = false;
					$create_request = false;

					if (!empty($this->custom_services[$code]['extra_cover']) && isset($request['extra_cover']))
						$extra_cover = true;

					/* if ( ! empty( $this->custom_services[ $code ]['delivery_confirmation'] ) )
					  $delivery_confirmation = true; */

					switch ($code) {

						case "INT_PARCEL_SEA_OWN_PACKAGING" :
						case "INT_PARCEL_AIR_OWN_PACKAGING" :

							if ($package['destination']['country'] == 'AU' || isset($request['thickness']))
								continue;

							$request['service_code'] = $code;

							if ($extra_cover && $request['extra_cover'] < $this->extra_cover[$code]) {
								$request['option_code'] = 'INTL_SERVICE_OPTION_EXTRA_COVER';
								$create_request = true;
							}


							//Delivery confirmation and Extra cover option together not support by API. The solution is to add a static value $2.95 for domestic and $4.99 for international with rates.

							/* if ( $delivery_confirmation ) {
							  $request['option_code']    = 'INTL_SERVICE_OPTION_EXTRA_COVER';
							  $request['suboption_code'] = 'INTL_SERVICE_OPTION_CONFIRM_DELIVERY';
							  $create_request            = true;
							  } */

							break;
					}

					if ($create_request)
						$requests[] = $request;
				}
			}
		}

		return $requests;
	}

	/**
	 * per_item_shipping function.
	 *
	 * @access private
	 * @param mixed $package
	 * @return void
	 */
	private function per_item_shipping($package) {
		global $woocommerce;

		$requests = array();
		// Get weight of order
		foreach ($package['contents'] as $item_id => $values) {			
			
			$values['data'] = $this->wf_load_product($values['data']);
			if (!$values['data']->needs_shipping()) {
				$this->debug(sprintf(__('Product #%d is virtual. Skipping.', 'wf_australia_post'), $item_id));
				continue;
			}

			if (!$values['data']->get_weight() || !$values['data']->length || !$values['data']->height || !$values['data']->width) {
				$this->debug(sprintf(__('Product #%d is missing weight/dimensions. Aborting.', 'wf_australia_post'), $item_id));
				return;
			}
			
			$product_ordered_quantity = $values['quantity'];
			
			$parcel = array();

			$parcel['weight'] = $values['data']->get_weight();

			$dimensions = array($values['data']->length, $values['data']->height, $values['data']->width);

			sort($dimensions);

			$from_dimension_unit = '';
			if ($this->dimension_unit != 'cm') {
				$from_dimension_unit = $this->dimension_unit;
			}

			// Min sizes - girth minimum is 16cm
			$girth = (round(wc_get_dimension($dimensions[0], 'cm', $from_dimension_unit)) + round(wc_get_dimension($dimensions[1], 'cm', $from_dimension_unit))) * 2;

			if ($parcel['weight'] > 22 || $dimensions[2] > 105) {
				$this->debug(sprintf(__('Product %d has invalid weight/dimensions. Aborting. See <a href="http://auspost.com.au/personal/parcel-dimensions.html">http://auspost.com.au/personal/parcel-dimensions.html</a>', 'wf_australia_post'), $item_id), 'error');
				return;
			}

			if ($girth < 16 || $girth > 140) {
				$this->debug(sprintf(__('Girth of the product should lie in between 16cm and 140cm. See <a href="http://ausporthst.com.au/personal/parcel-dimensions.html">http://auspost.com.au/personal/parcel-dimensions.html</a>', 'wf_australia_post'), $item_id), 'error');
				return;
			}

			$insurance_array = array(
				'Amount' => ceil($values['data']->get_price()),
				'Currency' => get_woocommerce_currency()
			);
			
			$group = array(
				'Weight' => array(
					'Value' => round($parcel['weight'], 3),
					'Units' => $this->weight_unit
				),
				'Dimensions' => array(
					'Length' => round($dimensions[2]),
					'Width' => round($dimensions[1]),
					'Height' => round($dimensions[0]),
					'Units' => $this->dimension_unit
				),
				'InsuredValue' => $insurance_array,
			);

			for($quantity = 0; $quantity < $product_ordered_quantity; $quantity++){
				$to_ship[] = $group;
			}
		}

		return $to_ship;
	}

	private function wf_load_product($product) {
		if (!$product) {
			return false;
		}
		return ( WC()->version < '2.7.0' ) ? $product : new wf_product($product);
	}

	private function get_contracted_rates($endpoint, $request, $headers) {
		global $woocommerce;

		$args = array(
			'method' => 'POST',
			'httpversion' => '1.1',
			'headers' => $headers, 'body' => json_encode($request)
		);
		$res = wp_remote_post($endpoint, $args);

		if (is_wp_error($res)) {
			$error_string = $res->get_error_message();
			$this->debug($error_string, 'error');
			return array();
		}

		$response_array = isset($res['body']) ? json_decode($res['body']) : array();

		if (!empty($response_array->errors)) {
			$this->debug($response_array->errors[0]->message, 'error');
			return array();
		}

		if (!empty($response_array)) {

			if (isset($response_array->items[0]->errors)) {
				$this->debug($response_array->items[0]->errors[0]->message, 'error');
			}

			return $response_array->items[0]->prices;
		} else {
			return array();
		}
	}

}
