<style>
    .infotips {
      position: relative;

      /* positioning on the page */
      color:#black;
      font-family: helvetica;
      text-decoration: none;
     /* text-shadow: 1px 1px 1px black;*/
    }

    .infotips::before{
        content:attr(data-tip);
        position: absolute;
        font-family: arial, sans-serif;
        font-size: 10px;
        font-weight: bold;
        z-index: 999;
        white-space: normal;;
        word-wrap:normal;
        bottom: 0px;
        left: 25%;
        background: #00a4e4;
        color: black;
        padding: 5px 10px 90px 10px;
        line-height: 100%;
        height: 100px;;
        top: 20%;
        opacity:0;
    }

    .infotips:hover::before{
        opacity: 1;
        color: white;
        width: auto;
        background-color:black;
        height: 100%;
    }

</style>
<tr valign="top" id="service_options" >
    <td class="forminp"  colspan="2" style="padding-left:0px">
        <strong><?php _e( 'Services', 'wf_australia_post' ); ?></strong></br></br>
        <style>
            #tiptip_content
            {
                max-width:unset !important;
            }
        </style>
        <?php 
            $premium_tag = "<br><small style='color:green'>Premium</small>";
        ?>
        <table class="australia_post_services widefat">
            <thead>
                <th class="sort">&nbsp;</th>
                <th style="text-align:center; padding: 10px; width:45%;"><?php _e( 'Service', 'wf_australia_post' ); ?></th>
                <th style="text-align:center; padding: 10px;width:5%;"><?php _e( 'Enable', 'wf_australia_post' ); ?></th>
                <th><?php _e( 'Name'.$premium_tag, 'wf_australia_post' ); ?></th>
                <th style="text-align:center; padding: 10px;width:5%;"><?php _e( 'Extra Cover'.$premium_tag, 'wf_australia_post' ); ?></th>
                <th style="text-align:center; padding: 10px;width:5%;"><?php _e( 'Signature / Registered'.$premium_tag, 'wf_australia_post' ); ?></th>
                <th><?php echo sprintf( __( 'Adjustment (%s)'.$premium_tag, 'wf_australia_post' ), get_woocommerce_currency_symbol() ); ?></th>
                <th><?php _e( 'Adjustment (%)'.$premium_tag, 'wf_australia_post' ); ?></th>
            </thead>
            <tbody>
                <?php
                    $contracted_account_details = '';
                    $extra_cover = array();
                    $signature_on_delivery = array();
                    $postage_products = array();

                    if($this->contracted_rates){
                        $get_accounts_endpoint = 'https://' .self::API_HOST.self::API_BASE_URL.'accounts/';
                        if ($this->contracted_api_mode == 'live') {
                            $get_accounts_endpoint = str_replace('test/', '', $get_accounts_endpoint);
                        }

                        $contracted_account_details = $this->get_services($get_accounts_endpoint, $this->api_account_no);
                        $contracted_account_details = json_decode($contracted_account_details, true);

                        if(!empty($contracted_account_details)){
                            if(isset($contracted_account_details['postage_products']) && !empty($contracted_account_details['postage_products'])){
                                $postage_products = $contracted_account_details['postage_products'];
                                foreach($postage_products as $postage_product){
                                    if(isset($postage_product['features'])){
                                        $extra_cover[$postage_product['product_id']] = $postage_product['features']['TRANSIT_COVER']['attributes']['maximum_cover'];
                                    }else{
                                        $extra_cover[$postage_product['product_id']] = 0;
                                    }

                                    if(isset($postage_product['options'])){
                                        if($postage_product['options']['signature_on_delivery_option'] === true)
                                        $signature_on_delivery[$postage_product['product_id']] = $postage_product['type'];
                                    }
                                }   
                            }else if($contracted_account_details['errors']){
                                $auspost_customer_account_error = $contracted_account_details['errors'];
                                update_option('auspost_customer_account_error', $auspost_customer_account_error['0']['name'].' : '.$auspost_customer_account_error['0']['message']);
                            }
                        }
                    }else {
                        $postage_products = $this->services;
                        $extra_cover = $this->extra_cover;
                        $signature_on_delivery = $this->delivery_confirmation;
                    }
                    
                    $name = '';
                    $product_id = '';
                    $sort = 0;
                    $this->ordered_services = array();
                    if(is_array($postage_products)){
                        foreach ( $postage_products as $code => $values ) {

                            if ( is_array( $values ) ){
                                if(isset($values['name'])){
                                    $name = $values['name'];
                                }else{
                                    $name = $values['type'];
                                    $product_id = $values['product_id'];                                
                                }
                            }
                            else{
                                $name = $values;
                            }

                            if ( isset( $this->custom_services[ $code ] ) && isset( $this->custom_services[ $code ]['order'] ) ) {
                                $sort = $this->custom_services[ $code ]['order'];
                            }

                            while ( isset( $this->ordered_services[ $sort ] ) )
                                $sort++;

                            $other_service_codes = isset( $values['alternate_services'] ) ? $values['alternate_services'] : '';

                            if(isset($values['name'])){
                                $this->ordered_services[ $sort ] = array( $code, $name, $other_service_codes );
                            }else{
                                $this->ordered_services[ $sort ] = array( $product_id, $name, $other_service_codes );
                            }

                            $sort++;
                        }   
                    }

                    ksort( $this->ordered_services );
                    if(empty($this->ordered_services)){
                        ?>
                        <tr style="text-align:left;"><td>No Services</td></tr>
                        <?php
                    }else{
                        foreach ( $this->ordered_services as $value ) {
                        $code = $value[0];
                        $name = $value[1];
                        $other_service_codes = array_filter( (array) $value[2] );
                        foreach ( $other_service_codes as $key => $value ) {
                            $other_service_codes[$key] = str_replace( $code . '_', '', $value );
                        }
                        ?>
                        <tr>
                            <td class="sort"><input type="hidden" class="order" name="australia_post_service[<?php echo $code; ?>][order]" value="<?php echo isset( $this->custom_services[ $code ]['order'] ) ? $this->custom_services[ $code ]['order'] : ''; ?>" /></td>
                            <td style="text-align:left;"><?php
                                echo '<strong class="infotips" data-tip="';

                                echo $name;

                                if ( $other_service_codes )
                                    echo ' , ' . implode( ', ', $other_service_codes ) .' ';

                                echo '" style="width:auto;">';

                                if ( ! empty( $postage_products['image'] ) )
                                    echo '<img src="' . plugins_url( $postage_products[ $code ]['image'], dirname( __FILE__ ) ) . '" alt="' . $postage_products['type'] . '" />';
                                else
                                    echo $name;

                                echo '</strong>';
                                ?></td>
            
                            <td style="text-align:center"><input type="checkbox" name="australia_post_service[<?php echo $code; ?>][enabled]" <?php checked( ( ! isset( $this->custom_services[ $code ]['enabled'] ) || ! empty( $this->custom_services[ $code ]['enabled'] ) ), true ); ?>/></td>
                            <td><input type="text" name="australia_post_service[<?php echo $code; ?>][name]" placeholder="<?php echo $name; ?> (<?php echo $this->title; ?>)" value="<?php echo isset( $this->custom_services[ $code ]['name'] ) ? $this->custom_services[ $code ]['name'] : ''; ?>" size="50" readonly/></td>
                            <td style="text-align:center">
                                <?php if ( in_array( $code, array_keys( $extra_cover ) ) ) : ?>
                                    <input type="checkbox" name="australia_post_service[<?php echo $code; ?>][extra_cover]" onclick="return false;"/>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center">
                                <?php if ( in_array( $code, array_keys($signature_on_delivery )) ) : ?>
                                    <input type="checkbox" name="australia_post_service[<?php echo $code; ?>][delivery_confirmation]" onclick="return false;"/>
                                <?php endif; ?>
                            </td>
                            <td><input type="text" name="australia_post_service[<?php echo $code; ?>][adjustment]" placeholder="N/A" value="<?php echo isset( $this->custom_services[ $code ]['adjustment'] ) ? $this->custom_services[ $code ]['adjustment'] : ''; ?>" size="4" readonly/></td>
                            <td><input type="text" name="australia_post_service[<?php echo $code; ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $this->custom_services[ $code ]['adjustment_percent'] ) ? $this->custom_services[ $code ]['adjustment_percent'] : ''; ?>" size="4" readonly/></td>
                        </tr>
                        <?php
                        }
                    }
                ?>
            </tbody>
        </table>
    </td>
</tr>
        