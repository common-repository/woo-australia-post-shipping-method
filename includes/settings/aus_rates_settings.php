<?php
$this->init_settings(); 
global $woocommerce;
$wc_main_settings = array();
$posted_services = array();
if(isset($_POST['wf_aus_rates_save_changes_button']))
{
	$wc_main_settings = get_option('woocommerce_wf_australia_post_settings');
	$wc_main_settings['title'] = (isset($_POST['wf_australia_post_title'])) ? sanitize_text_field($_POST['wf_australia_post_title']) :  __( 'Australia POST', 'wf_australia_post' );
	$wc_main_settings['availability'] = (isset($_POST['wf_australia_post_availability']) && $_POST['wf_australia_post_availability'] ==='all') ? 'all' : 'specific';
	$services         = array();
	$posted_services  = $_POST['australia_post_service'];

	foreach ( $posted_services as $code => $settings ) {

		$services[ $code ] = array(
			'name'                  => wc_clean( $settings['name'] ),
			'order'                 => wc_clean( $settings['order'] ),
			'enabled'               => isset( $settings['enabled'] ) ? true : false,
			'adjustment'            => wc_clean( $settings['adjustment'] ),
			'adjustment_percent'    => str_replace( '%', '', wc_clean( $settings['adjustment_percent'] ) ),
			'extra_cover'           => isset( $settings['extra_cover'] ) ? true : false,
			'delivery_confirmation' => isset( $settings['delivery_confirmation'] ) ? true : false,
		);

	}
		
	$wc_main_settings['services'] = $services;
	$wc_main_settings['offer_rates'] = (isset($_POST['wf_australia_post_offer_rates'])) ? 'cheapest' : 'all';
	$wc_main_settings['disable_alternate_services'] = (isset($_POST['wf_australia_post_disable_alternate_services'])) ? 'yes' : '';
	
	if($wc_main_settings['availability'] === 'specific')
	{
		$wc_main_settings['countries'] = isset($_POST['wf_australia_post_countries']) ? $_POST['wf_australia_post_countries'] : '';
	}
	
	update_option('woocommerce_wf_australia_post_settings',$wc_main_settings);
	
}

$general_settings = get_option('woocommerce_wf_australia_post_settings');
$this->custom_services = isset($general_settings['services']) ? $general_settings['services'] : array();
$auspost_customer_account_error_on_rates_settings = get_option('auspost_customer_account_error');

if(!empty($auspost_customer_account_error_on_rates_settings)){
    echo '<div class="error"><p>' . __($auspost_customer_account_error_on_rates_settings, 'wf-shipping-auspost') . '</p></div>';
    delete_option('auspost_customer_account_error');
}
?>

<table>
	<tr valign="top">
		<tr valign="top" ">
		<td style="width:30%;font-weight:800;">
			<label for="wf_australia_post_delivery_time"><?php _e('Show/Hide','wf_australia_post') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_australia_post_offer_rates" id="wf_australia_post_offer_rates" style="" value="yes" <?php echo (isset($general_settings['offer_rates']) && $general_settings['offer_rates'] ==='cheapest') ? 'checked' : ''; ?> placeholder="">  <?php _e('Show Cheapest Rates Only','wf_australia_post') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('On enabling this, the cheapest rate will be shown in the cart/checkout page.','wf_australia_post') ?>"></span>
			</fieldset>
		
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_australia_post_disable_alternate_services" id="wf_australia_post_disable_alternate_services" style="" value="yes" <?php echo (isset($general_settings['disable_alternate_services']) && $general_settings['disable_alternate_services'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Disable Alternative Services','wf_australia_post') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('On Enabling this, you won’t be able to see the alternative services on hovering over the services mentioned below.','wf_australia_post') ?>"></span>
			</fieldset>
		</td>
	</tr>
		<td style="width:30%;font-weight:800;">
			<label for="wf_australia_post_"><?php _e('Method Config','wf_australia_post') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<label for="wf_australia_post_title"><?php _e('Method Title / Availability','wf_australia_post') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Provide the service name which would be reflected in the Cart/Checkout page if the cheapest rate has been enabled.','wf_australia_post') ?>"></span>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="text" name="wf_australia_post_title" id="wf_australia_post_title" style="" value="<?php echo (isset($general_settings['title'])) ? $general_settings['title'] : __( 'Australia Post', 'wf_australia_post' ); ?>" placeholder=""> 
			</fieldset>
			
			<fieldset style="padding:3px;">
				<?php if(isset($general_settings['availability']) && $general_settings['availability'] ==='specific')
				{ ?>
				<input class="input-text regular-input " type="radio" name="wf_australia_post_availability"  id="wf_australia_post_availability1" value="all" placeholder=""> <?php _e('Supports All Countries','wf_australia_post') ?>
				<input class="input-text regular-input " type="radio"  name="wf_australia_post_availability" checked="true" id="wf_australia_post_availability2"  value="specific" placeholder=""> Supports <?php _e('Specific Countries','wf_australia_post') ?>
				<?php }else{ ?>
				<input class="input-text regular-input " type="radio" name="wf_australia_post_availability" checked="true" id="wf_australia_post_availability1"  value="all" placeholder=""> <?php _e('Supports All Countries','wf_australia_post') ?>
				<input class="input-text regular-input " type="radio" name="wf_australia_post_availability" id="wf_australia_post_availability2"  value="specific" placeholder=""> <?php _e('Supports Specific Countries','wf_australia_post') ?>
				<?php } ?>
			</fieldset>
			<fieldset style="padding:3px;" id="aus_specific">
				<label for="wf_australia_post_countries"><?php _e('Specific Countries','wf_australia_post') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('You can select the shipping method to be available for all countries or selective countries.','wf_australia_post') ?>"></span><br/>

				<select class="chosen_select" multiple="true" name="wf_australia_post_countries[]" >
					<?php 
					$woocommerce_countries = $woocommerce->countries->get_countries();
					$selected_country =  (isset($general_settings['countries']) && !empty($general_settings['countries']) ) ? $general_settings['countries'] : array($woocommerce->countries->get_base_country());

					
					foreach ($woocommerce_countries as $key => $value) {
						if(in_array($key, $selected_country))
						{
							echo '<option value="'.$key.'" selected>'.$value.'</option>';
						}
						echo '<option value="'.$key.'">'.$value.'</option>';
					}
					?>
			</fieldset>
		</td>
		</tr>
		<tr valign="top">
			<td colspan="2">
				<?php
				include_once( 'wf_html_services.php' );
				?>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:right;padding-right: 10%;">
				<br/>
				<input type="submit" value="<?php _e('Save Changes','wf_australia_post') ?>" class="button button-primary" name="wf_aus_rates_save_changes_button">
				
			</td>
		</tr>

	</table>

	<script type="text/javascript">

		
		jQuery(window).load(function(){
			
			jQuery('#wf_australia_post_availability1').change(function(){
				jQuery('#aus_specific').hide();

			}).change();

			jQuery('#wf_australia_post_availability2').change(function(){
				if(jQuery('#wf_australia_post_availability2').is(':checked')) {
					jQuery('#aus_specific').show();
				}else
				{
					jQuery('#aus_specific').hide();
				}
			}).change();

		});

	</script>