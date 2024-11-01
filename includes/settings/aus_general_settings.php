<?php 

global $woocommerce;
if(isset($_POST['wf_aus_genaral_save_changes_button']))
{
	$wc_main_settings = get_option('woocommerce_wf_australia_post_settings');
	$wc_main_settings['api_key'] = (isset($_POST['wf_australia_post_api_key'])) ? sanitize_text_field($_POST['wf_australia_post_api_key']) : '';
	$wc_main_settings['api_pwd'] = (isset($_POST['wf_australia_post_api_pwd'])) ? sanitize_text_field($_POST['wf_australia_post_api_pwd']) : '';
	$wc_main_settings['api_account_no'] = (isset($_POST['wf_australia_post_api_account_no'])) ? sanitize_text_field($_POST['wf_australia_post_api_account_no']) : '';
	$wc_main_settings['conversion_rate'] = (isset($_POST['wf_australia_post_conversion_rate'])) ? sanitize_text_field($_POST['wf_australia_post_conversion_rate']) : '';
	$wc_main_settings['origin_name'] = (isset($_POST['wf_australia_post_origin_name'])) ? sanitize_text_field($_POST['wf_australia_post_origin_name']) : '';
	$wc_main_settings['origin_suburb'] = (isset($_POST['wf_australia_post_origin_suburb'])) ? sanitize_text_field($_POST['wf_australia_post_origin_suburb']) : '';
	$wc_main_settings['origin_line'] = (isset($_POST['wf_australia_post_origin_line'])) ? sanitize_text_field($_POST['wf_australia_post_origin_line']) : '';
	$wc_main_settings['origin_state'] = (isset($_POST['wf_australia_post_origin_state'])) ? sanitize_text_field($_POST['wf_australia_post_origin_state']) : '';
	$wc_main_settings['origin'] = (isset($_POST['wf_australia_post_origin'])) ? sanitize_text_field($_POST['wf_australia_post_origin']) : '';
	$wc_main_settings['shipper_phone_number'] = (isset($_POST['wf_australia_post_shipper_phone_number'])) ? sanitize_text_field($_POST['wf_australia_post_shipper_phone_number']) : '';
	$wc_main_settings['shipper_email'] = (isset($_POST['wf_australia_post_shipper_email'])) ? sanitize_text_field($_POST['wf_australia_post_shipper_email']) : '';
	$wc_main_settings['contracted_rates'] = (isset($_POST['wf_australia_post_contracted_rates'])) ? 'yes' : '';
	$wc_main_settings['enabled'] = (isset($_POST['wf_australia_post_enabled'])) ? 'yes' : '';
	$wc_main_settings['enabled_label'] = (isset($_POST['wf_australia_post_enabled_label'])) ? 'yes' : '';
	$wc_main_settings['debug_mode'] = (isset($_POST['wf_australia_post_debug_mode'])) ? 'yes' : '';
	$wc_main_settings['contracted_api_mode'] = $_POST['wf_australia_post_contracted_api_mode'];
	
	update_option('woocommerce_wf_australia_post_settings',$wc_main_settings);
}

$general_settings = get_option('woocommerce_wf_australia_post_settings');
$general_settings = empty($general_settings) ? array() : $general_settings;
$premium_tag = " <small style='color:green'>Premium</small>";

?>

<img style="float:right;" src="<?php echo WF_AUSTRALIA_POST_URL.'includes/settings/aus.png'; ?>" width="180" height="" />


<table style="font-size:13px;">
	<tr valign="top">
		<td style="width:40%;font-weight:700;">
			<label for="wf_australia_post_contracted_api_mode"><?php _e('Account Details','wf_australia_post') ?> </label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " size="40" type="text" name="wf_australia_post_api_key" id="wf_australia_post_api_key"  value="<?php echo (isset($general_settings['api_key'])) ? $general_settings['api_key'] : ''; ?>" placeholder="8fd6e23e-15fd-4e87-b7a2-ba557b0ff0dd"> <label for="wf_australia_post_ac_num"><?php _e('API Key','wf_australia_post') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('On Registering with Australia Post, you would get an  API key which you are required to fill in here. Applicable for both Contracted and Non-contracted accounts.','wf_australia_post') ?>"></span>
			</fieldset>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_australia_post_contracted_rates" id="wf_australia_post_contracted_rates"  <?php echo (isset($general_settings['contracted_rates']) && $general_settings['contracted_rates'] =="yes") ? 'checked' : ''; ?> > <label for="wf_australia_post_ac_num"><?php _e('Contracted Account','wf_australia_post') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('If you have an Australia Post account, enable Contracted.','wf_australia_post') ?>"></span>
			</fieldset>
            <p style="font-size: 80%; padding: 0% !important; margin: 0% !important;"><?php _e('(Go to Rates & Services tab and save the settings while switching from contracted to non-contracted account and Vice versa.)', 'wf_australia_post')?></p>
			<fieldset style="padding:3px;" id="aus_live_test">
				<?php if(isset($general_settings['contracted_api_mode']) && $general_settings['contracted_api_mode'] ==='live')
				{ ?>
				<input class="input-text regular-input " type="radio" name="wf_australia_post_contracted_api_mode"  id="wf_australia_post_contracted_api_mode"  value="test" placeholder=""> <?php _e('Test Mode','wf_australia_post') ?>
				<input class="input-text regular-input " type="radio"  name="wf_australia_post_contracted_api_mode" checked="true" id="wf_australia_post_contracted_api_mode"  value="live" placeholder=""> <?php _e('Live Mode','wf_australia_post') ?>
				<?php }else{ ?>
				<input class="input-text regular-input " type="radio" name="wf_australia_post_contracted_api_mode" checked="true" id="wf_australia_post_contracted_api_mode"  value="test" placeholder=""> <?php _e('Test Mode','wf_australia_post') ?>
				<input class="input-text regular-input " type="radio" name="wf_australia_post_contracted_api_mode" id="wf_australia_post_contracted_api_mode"  value="live" placeholder=""> <?php _e('Live Mode','wf_australia_post') ?>
				<?php } ?>
				<br>
			</fieldset>
	
			<fieldset style="padding:3px;" id="aus_pass">
				<input class="input-text regular-input " type="password" name="wf_australia_post_api_pwd" id="wf_australia_post_api_pwd"  value="<?php echo (isset($general_settings['api_pwd'])) ? $general_settings['api_pwd'] : ''; ?>" placeholder=""> <label for="wf_australia_post_"><?php _e('API password','wf_australia_post') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('If you have enabled Contracted, then enter the API password here.','wf_australia_post') ?>"></span>	
			</fieldset>
			<fieldset style="padding:3px;" id="aus_acc_num">
				<input class="input-text regular-input " type="Password" name="wf_australia_post_api_account_no" id="wf_australia_post_api_account_no"  value="<?php echo (isset($general_settings['api_account_no'])) ? $general_settings['api_account_no'] : ''; ?>" placeholder="**************"/>
				<label for="wf_australia_post_api_account_no"><?php _e('Account Number','wf_australia_post') ?><font style="color:red;">*</font></label>
				<span class="woocommerce-help-tip" data-tip="<?php _e('Account Number of your Australia Post Account','wf_australia_post') ?>"></span>				

			</fieldset>
			
		</td>
	</tr>
	<tr valign="top">
		<td style="width:40%;font-weight:700;">
			<label for="wf_australia_post_rates"><?php _e('Enable/Disable','wf_australia_post') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_australia_post_enabled" id="wf_australia_post_enabled" style="" value="yes" <?php echo (!isset($general_settings['enabled']) || isset($general_settings['enabled']) && $general_settings['enabled'] ==='yes') ? 'checked' : ''; ?> placeholder=""> <?php _e('Enable Real time Rates','wf_australia_post') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enable real time rates, if you want to fetch rates from Australia Post API in Cart/Checkout page.','wf_australia_post') ?>"></span>
			</fieldset>
			<fieldset style="padding:3px;" id="aus_enable_shiping_label">
				<input class="input-text regular-input " type="checkbox" name="wf_australia_post_enabled_label" id="wf_australia_post_enabled_label" style="" placeholder="" onclick="return false;"> <?php _e('Enable Shipping Label (Contracted)'.$premium_tag,'wf_australia_post') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enable Shipping label is available only if you have enabled Contracted. This option enables label creation from the order admin page. Disabling this will hide the label creating button.','wf_australia_post') ?>"></span>
			</fieldset>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_australia_post_debug_mode" id="wf_australia_post_debug_mode" style="" value="yes" <?php echo (isset($general_settings['debug_mode']) && $general_settings['debug_mode'] ==='yes') ? 'checked' : ''; ?> placeholder=""> <?php _e('Enable Developer Mode','wf_australia_post') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enabling Developer’s mode will let you troubleshoot the plugin. Request/Response information would be available in the Cart/Checkout page.','wf_australia_post') ?>"></span>
			</fieldset>
			<?php
			if(class_exists('wf_vendor_addon_setup'))
			{
				?>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_australia_post_vendor_check" id="wf_australia_post_vendor_check" style="" value="yes" <?php echo (isset($general_settings['vendor_check']) && $general_settings['vendor_check'] ==='yes') ? 'checked' : ''; ?> placeholder=""> <?php _e('Enable Multi-Vendor Shipper Address','wf_australia_post') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('You are seeing this option becuase XA Multi-Vendor Shipping Add-On is installed. By enabling this option, Shipper Adress set in multi-vendor plugin settings will be overriden by the below Shipper Address settings..','wf_australia_post') ?>"></span>
			</fieldset>
			<?php
			}
			?>
		</td>
	</tr>
	<tr valign="top">
		<td style="width:40%;font-weight:700;">
			<label for="wf_australia_post_"><?php _e('Default Currency','wf_australia_post') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
				<?php $selected_currency = 'AUD';
				$default_currency = get_woocommerce_currency();
				?>
				<label for="wf_australia_post_"><?php echo '<b>'.$selected_currency.' ('. get_woocommerce_currency_symbol($selected_currency).')</b>'; ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Australian Dollars in the default currency.','wf_australia_post') ?>"></span><br/>
			</fieldset>
<!--	
	<?php 
			if($selected_currency != $default_currency)
			{
				?>
				<fieldset style="padding:3px;">
				<label for="wf_australia_post_conversion_rate"><?php _e('Converstion Rate','wf_australia_post') ?></label> <span class="woocommerce-help-tip" data-tip="Enter the conversion rate from your Australia Post (AUD) currency to your store <?php echo '('.$default_currency.')'; ?> currency. "></span> <br/>	 
				<input class="input-text regular-input " type="number" min="0" step="0.00001" name="wf_australia_post_conversion_rate" id="wf_australia_post_conversion_rate" style="" value="<?php echo (isset($general_settings['conversion_rate'])) ? $general_settings['conversion_rate'] : ''; ?>" placeholder=""><b> <?php echo $default_currency; ?></b>
				</fieldset>
				<?php
			}
			 ?>
	-->		
			
		</td>
	</tr>
	<tr valign="top">
		<td style="width:40%;font-weight:700;">
			<label for="wf_australia_post_"><?php _e('Shipper Address','wf_australia_post') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">

			<table>
				<tr>
					<td>
						<fieldset style="padding-left:3px;">
							<label for="wf_australia_post_"><?php _e('Shipper Name','wf_australia_post') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Name of the person responsible for shipping.','wf_australia_post') ?>"></span>	<br/>
							<input class="input-text regular-input " type="text" name="wf_australia_post_origin_name" id="wf_australia_post_origin_name" style="" value="<?php echo (isset($general_settings['origin_name'])) ? $general_settings['origin_name'] : ''; ?>" placeholder=""> 	
						</fieldset>
				</td>
				<td>

						<fieldset style="padding-left:3px;">

							<label for="wf_australia_post_origin_suburb"><?php _e('Suburb','wf_australia_post') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('City of the shipper.','wf_australia_post') ?>"></span>	<br/>
							<input class="input-text regular-input " type="text" name="wf_australia_post_origin_suburb" id="wf_australia_post_origin_suburb" style="" value="<?php echo (isset($general_settings['origin_suburb'])) ? $general_settings['origin_suburb'] : ''; ?>" placeholder="">
						</fieldset>
				
					</td>
					
				</tr>
				<tr>
				<td>

						<fieldset style="padding-left:3px;">

							<label for="wf_australia_post_origin_state"><?php _e('State Code','wf_australia_post') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('State Code of the shipper.','wf_australia_post') ?>"></span>	<br/>
							<input class="input-text regular-input " type="text" name="wf_australia_post_origin_state" id="wf_australia_post_origin_state" style="" value="<?php echo (isset($general_settings['origin_state'])) ? $general_settings['origin_state'] : ''; ?>" placeholder="">
						</fieldset>
				
					</td>
								
				<td>

						<fieldset style="padding-left:3px;">
							<label for="wf_australia_post_"><?php _e('Address','wf_australia_post') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Official address of the shipper.','wf_australia_post') ?>"></span>	<br> 
							<input class="input-text regular-input " type="text" name="wf_australia_post_origin_line" id="wf_australia_post_origin_line" style="" value="<?php echo (isset($general_settings['origin_line'])) ? $general_settings['origin_line'] : ''; ?>" placeholder=""> 	
						</fieldset>

					</td>
				</tr>
				<tr>
				
					<td>

						<fieldset style="padding-left:3px;">
							<label for="wf_australia_post_"><?php _e('Phone Number','wf_australia_post') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Phone number of the shipper.','wf_australia_post') ?>"></span>	<br/>
							<input class="input-text regular-input " type="text" name="wf_australia_post_shipper_phone_number" id="wf_australia_post_shipper_phone_number" style="" value="<?php echo (isset($general_settings['shipper_phone_number'])) ? $general_settings['shipper_phone_number'] : ''; ?>" placeholder=""> 	
						</fieldset>
					</td>
					<td>

						<fieldset style="padding-left:3px;">
							<label for="wf_australia_post_"><?php _e('Email Address','wf_australia_post') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Email address of the shipper.','wf_australia_post') ?>"></span>	<br/>
							<input class="input-text regular-input " type="text" name="wf_australia_post_shipper_email" id="wf_australia_post_shipper_email" style="" value="<?php echo (isset($general_settings['shipper_email'])) ? $general_settings['shipper_email'] : ''; ?>" placeholder=""> 	
						</fieldset>

					</td>
				</tr>
				<tr>
					<td>
	
					<fieldset style="padding-left:3px;">

						<label for="wf_australia_post_origin"><?php _e('Postal Code','wf_australia_post') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Postal code of the shipper(Used for fetching rates and label generation).','wf_australia_post') ?>"></span><br/>
						<input class="input-text regular-input " type="text" name="wf_australia_post_origin" id="wf_australia_post_origin" style="" value="<?php echo (isset($general_settings['origin'])) ? $general_settings['origin'] : ''; ?>" placeholder="">
					</fieldset>
				
					</td>

				</tr>
			</tr>
		</table>

	</td>
</tr>
<tr>
	<td colspan="2" style="text-align:right;">
		<button type="submit" class="button button-primary" name="wf_aus_genaral_save_changes_button"> <?php _e('Save Changes','wf_australia_post') ?> </button>
		
	</td>
</tr>
</table>
<script type="text/javascript">

		
		jQuery(window).load(function(){
			
			jQuery('#wf_australia_post_contracted_rates').change(function(){
				if(jQuery('#wf_australia_post_contracted_rates').is(':checked')) {
					jQuery('#aus_live_test').show();
					jQuery('#aus_pass').show();
					jQuery('#aus_acc_num').show();
					jQuery('#aus_enable_shiping_label').show();
				}else
				{
					jQuery('#aus_live_test').hide();
					jQuery('#aus_pass').hide();
					jQuery('#aus_acc_num').hide();
					jQuery('#aus_enable_shiping_label').hide();
				}
			}).change();

		});

</script>
