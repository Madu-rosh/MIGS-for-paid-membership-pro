<?php	
	//include pmprogateway
	//require_once(dirname(__FILE__) . "/class.pmprogateway.php");
	
	//load classes init method
	add_action('init', array('PMProGateway_migs', 'init'));
		
	class PMProGateway_Migs extends PMProGateway
	{
		function PMProGateway_Migs($gateway = NULL)
		{
			// if(!class_exists("Migs"))
				// require_once(dirname(__FILE__) . "/../../includes/lib/Migs/Migs.php");
			
			// //set API connection vars
			// Migs::sellerId(pmpro_getOption('migs_accountnumber'));			
			// Migs::username(pmpro_getOption('migs_apiusername'));
			// Migs::password(pmpro_getOption('migs_apipassword'));
			// Migs::$verifySSL = false;

			$this->gateway = $gateway;
			return $this->gateway;
		}										
		
		/**
		 * Run on WP init
		 *		 
		 * @since 1.8
		 */
		static function init()
		{			
			//make sure PayPal Express is a gateway option
			add_filter('pmpro_gateways', array('PMProGateway_migs', 'pmpro_gateways'));
			
			//add fields to payment settings
			add_filter('pmpro_payment_options', array('PMProGateway_migs', 'pmpro_payment_options'));		
			add_filter('pmpro_payment_option_fields', array('PMProGateway_migs', 'pmpro_payment_option_fields'), 10, 2);

			//code to add at checkout
			$gateway = pmpro_getGateway();
			if($gateway == "migs")
			{				
				//add_filter('pmpro_include_billing_address_fields', '__return_false');
				//add_filter('pmpro_include_payment_information_fields', '__return_false');
				add_filter('pmpro_include_billing_address_fields', array('PMProGateway_migs', 'pmpro_include_billing_address_fields'));
				add_filter('pmpro_required_billing_fields', array('PMProGateway_migs', 'pmpro_required_billing_fields'));
				add_filter('pmpro_include_payment_information_fields', array('PMProGateway_migs', 'pmpro_include_payment_information_fields'));
				//add_filter('pmpro_checkout_default_submit_button', array('PMProGateway_migs', 'pmpro_checkout_default_submit_button'));
				add_filter('pmpro_checkout_before_change_membership_level', array('PMProGateway_migs', 'pmpro_checkout_before_change_membership_level'), 10, 2);
			}
		}
		
		/**
		 * Make sure this gateway is in the gateways list
		 *		 
		 * @since 1.8
		 */
		static function pmpro_gateways($gateways)
		{
			if(empty($gateways['migs']))
				$gateways['migs'] = __('migs', 'pmpro');
		
			return $gateways;
		}
		
		/**
		 * Get a list of payment options that the this gateway needs/supports.
		 *		 
		 * @since 1.8
		 */
		static function getGatewayOptions()
		{			
			$options = array(
				'sslseal',
				'nuclear_HTTPS',
				'gateway_environment',
				'merchantide',
				'accescodee',
				'securehashe',
				'currency',
				'use_ssl',
				'tax_state',
				'tax_rate',
				'accepted_credit_cards'
			);
			
			return $options;
		}
	
		/**
		 * Set payment options for payment settings page.
		 *		 
		 * @since 1.8
		 */
		static function pmpro_payment_options($options)
		{			
			//get stripe options
			$migs_options = PMProGateway_migs::getGatewayOptions();
			
			//merge with others.
			$options = array_merge($migs_options, $options);
			
			return $options;
		}
		
		/**
		 * Display fields for this gateway's options.
		 *		 
		 * @since 1.8
		 */
		static function pmpro_payment_option_fields($values, $gateway)
		{
		?>
			<tr class="pmpro_settings_divider gateway gateway_migs" <?php if($gateway != "migs") { ?>style="display: none;"<?php } ?>>
				<td colspan="2">
					<?php _e('MIGS Settings', 'pmpro'); ?>
				</td>
			</tr>
			<tr class="gateway gateway_migs" <?php if($gateway != "migs") { ?>style="display: none;"<?php } ?>>
				<?php // migs custom pamyment settings here ?>
			</tr>
			<tr class="gateway gateway_migs" <?php if($gateway != "migs") { ?>style="display: none;"<?php } ?>>
				<th scope="row" valign="top">
					<label for="merchantide"><?php _e('Merchant ID', 'pmpro');?>:</label>
				</th>
				<td>
					<input type="text" id="merchantide" name="merchantide" size="60" value="<?php echo esc_attr($values['merchantide'])?>" />
				</td>
			</tr>
			<tr class="gateway gateway_migs" <?php if($gateway != "migs") { ?>style="display: none;"<?php } ?>>
				<th scope="row" valign="top">
					<label for="accescodee"><?php _e('Access Code', 'pmpro');?>:</label>
				</th>
				<td>
					<input type="text" id="accescodee" name="accescodee" size="60" value="<?php echo esc_attr($values['accescodee'])?>" />
				</td>
			</tr>
			<tr class="gateway gateway_migs" <?php if($gateway != "migs") { ?>style="display: none;"<?php } ?>>
				<th scope="row" valign="top">
					<label for="securehash"><?php _e('Secure Hash', 'pmpro');?>:</label>
				</th>
				<td>
					<input type="text" id="securehashe" name="securehashe" size="60" value="<?php echo esc_attr($values['securehashe'])?>" />
				</td>
			</tr>
		
		<?php
		}
		
		static function pmpro_include_billing_address_fields($include)
		{
			//check settings RE showing billing address
			// if(!pmpro_getOption("example_billingaddress"))
				// $include = false;

			return $include;
		}
		
		/**
		 * Remove required billing fields
		 *
		 * @since 1.8
		 */
		static function pmpro_required_billing_fields($fields)
		{
			unset($fields['CardType']);
			unset($fields['AccountNumber']);
			unset($fields['ExpirationMonth']);
			unset($fields['ExpirationYear']);
			unset($fields['CVV']);

			return $fields;
		}
		/*hide the card field*/
		static function pmpro_include_payment_information_fields($include)
		{
			//global vars
			global $pmpro_requirebilling, $pmpro_show_discount_code, $discount_code, $CardType, $AccountNumber, $ExpirationMonth, $ExpirationYear;
			
			//get accepted credit cards
			$pmpro_accepted_credit_cards = pmpro_getOption("accepted_credit_cards");
			$pmpro_accepted_credit_cards = explode(",", $pmpro_accepted_credit_cards);
			$pmpro_accepted_credit_cards_string = pmpro_implodeToEnglish($pmpro_accepted_credit_cards);

			//include ours
			?>
			<table id="pmpro_payment_information_fields" class="pmpro_checkout top1em" width="100%" cellpadding="0" cellspacing="0" border="0" 
			<?php //if(!$pmpro_requirebilling || apply_filters("pmpro_hide_payment_information_fields", false) ) { ?>
			style="display: none;"
			<?php //} ?>>
			<thead>
				<tr>
					<th><span class="pmpro_thead-msg"><?php printf(__('We Accept %s', 'pmpro'), $pmpro_accepted_credit_cards_string);?></span><?php _e('Payment Information', 'pmpro');?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td>
						<?php
							$sslseal = pmpro_getOption("sslseal");
							if($sslseal)
							{
							?>
								<div class="pmpro_sslseal"><?php echo stripslashes($sslseal)?></div>
							<?php
							}
						?>
						<?php 
						/*this section is for if any discount code exists*/
						//if($pmpro_show_discount_code) { 
						?>
						
						<!-- <div class="pmpro_payment-discount-code">
							<label for="discount_code"><?php //_e('Discount Code', 'pmpro');?></label>
							<input class="input <?php //echo pmpro_getClassForField("discount_code");?>" id="discount_code" name="discount_code" type="text" size="20" value="<?php //echo esc_attr($discount_code)?>" />
							<input type="button" id="discount_code_button" name="discount_code_button" value="<?php //_e('Apply', 'pmpro');?>" />
							<p id="discount_code_message" class="pmpro_message" style="display: none;"></p>
						</div> -->
						<?php //} ?>

					</td>
				</tr>
			</tbody>
			</table>
			<?php

			//don't include the default
			return false;
		}
		
		/**
		 * Swap in our submit buttons.
		 *
		 * @since 1.8
		 */
		static function pmpro_checkout_default_submit_button($show)
		{
			global $gateway, $pmpro_requirebilling;
			
			//show our submit buttons
			?>			
			<span id="pmpro_submit_span">
				<input type="hidden" name="submit-checkout" value="1" />		
				<input type="submit" class="pmpro_btn pmpro_btn-submit-checkout" value="<?php if($pmpro_requirebilling) { _e('checkout', 'pmpro'); } else { _e('Proceed', 'pmpro');}?> &raquo;" />		
			</span>
			<?php
		
			//don't show the default
			return false;
		}
		
		/**
		 * Instead of change membership levels, send users to 2Checkout to pay.
		 *
		 * @since 1.8
		 */
		static function pmpro_checkout_before_change_membership_level($user_id, $morder)
		{
			global $wpdb, $discount_code_id;
			
			//if no order, no need to pay
			if(empty($morder))
				return;
			
			$morder->user_id = $user_id;				
			$morder->saveOrder();
			
			//save discount code use
			if(!empty($discount_code_id))
				$wpdb->query("INSERT INTO $wpdb->pmpro_discount_codes_uses (code_id, user_id, order_id, timestamp) VALUES('" . $discount_code_id . "', '" . $user_id . "', '" . $morder->id . "', now())");	
			
			do_action("pmpro_before_send_to_migs", $user_id, $morder);
			
			$morder->Gateway->sendToMigs($morder);
		}
		
		/**
		 * Process checkout.
		 *		
		 */
		function process(&$order)
		{						
			if(empty($order->code))
				$order->code = $order->getRandomCode();			
			
			//clean up a couple values
			$order->payment_type = "migs";
			$order->CardType = "";
			$order->cardtype = "";
			
			//just save, the user will go to 2checkout to pay
			$order->status = "review";	
			$order->payment_transaction_id = $order->code;
			$order->subscription_transaction_id=$order->code.'_'.date("Y-m-d");
			$order->membership_level = apply_filters("pmpro_checkout_level", $order->membership_level);
			$order->saveOrder();
			//sendToMigs($order);
			//if($this->sendToMigs($order))
			return true;//else return false;	
		}
		
		function sendToMigs(&$order)
		{						
			global $pmpro_currency;		
			//$order_id = $order->code;
			//taxes on initial amount
			$initial_payment = $order->InitialPayment;
			$initial_payment_tax = $order->getTaxForPrice($initial_payment);
			$initial_payment = round((float)$initial_payment + (float)$initial_payment_tax, 2);
			$details='';

			//taxes on the amount (NOT CURRENTLY USED)
			$amount = $order->PaymentAmount;
			$amount_tax = $order->getTaxForPrice($amount);			
			$amount = round((float)$amount + (float)$amount_tax, 2);	
			// Recurring membership			
			if( pmpro_isLevelRecurring( $order->membership_level ) ) {
				$amount = number_format($initial_payment - $amount, 2, ".", "");		//negative amount for lower initial payments
				$recurring_payment = number_format($order->membership_level->billing_amount, 2, ".", "");
				$recurring_payment_tax = number_format($order->getTaxForPrice($recurring_payment), 2, ".", "");
				$recurring_payment = number_format(round((float)$recurring_payment + (float)$recurring_payment_tax, 2), 2, ".", "");
				$amount = number_format($recurring_payment, 2, ".", "");

				$details= ( $order->BillingFrequency == 1 ) ? $order->BillingFrequency . ' ' . $order->BillingPeriod : $order->BillingFrequency . ' ' . $order->BillingPeriod . 's';

				if( property_exists( $order, 'TotalBillingCycles' ) )
					$details = ($order->BillingFrequency * $order->TotalBillingCycles ) . ' ' . $order->BillingPeriod;
				else
					$details = 'Forever';
			}
			// Non-recurring membership
			else {
				$amount = number_format($initial_payment, 2, ".", "");
			}
			if(!empty($order->TrialBillingPeriod)) {
				$trial_amount = $order->TrialAmount;
				$trial_tax = $order->getTaxForPrice($trial_amount);
				$trial_amount = pmpro_formatPrice(round((float)$trial_amount + (float)$trial_tax, 2), false, false);
				$amount = $trial_amount; // Negative trial amount
			}
			global $pmpro_level;

			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();

			//what amount to charge?
			$amount = $order->InitialPayment;

			//tax
			$order->subtotal = $amount;
			$tax = $order->getTax(true);
			$amount = round((float)$order->subtotal + (float)$tax, 2);

			//create a customer
			//$result = $this->getCustomer($order);

			/*if(empty($result))
			{
				//failed to create customer
				return false;
			}*/

			//charge
			try
			{
				
				$order_id = $order->code;
				$order_amount = 100 * $amount;
			
				$md5Hash = pmpro_getOption("securehashe");
			
			/* Make sure user entered MIGS url, otherwise use the default one */
			if( trim( $this->service_host ) == "" || $this->service_host == null ) {
				$this->service_host = "https://migs.mastercard.com.au/vpcpay";
			}
			$service_host = $this->service_host."?";
			$rurl = admin_url("admin-ajax.php") . "?action=migspaymenthandler";
			//$rurl='https://nibaya.com/ccc/wp-content/plugins/pmpro-example-gateway/classes/paymenthandler.php?';
			//$rurl = pmpro_url("confirmation", "?level=" . $order->membership_level);
			$user_ID = get_current_user_id();			
			$DigitalOrder = array(
				"vpc_Version" => "1",
				"vpc_Command" => "pay",
				"vpc_AccessCode" => pmpro_getOption("accescodee"),
				"vpc_MerchTxnRef" => $order_id.'_'.date("Y-m-d"),
				"vpc_Merchant" => pmpro_getOption("merchantide"),
				"vpc_OrderInfo" => $order_id.'_'.date("Y-m-d"),
				"vpc_Amount" => $order_amount,
				"vpc_Locale" => "en",
				//"vpc_ReturnURL" => get_home_url().'/membership-account/membership-confirmation/?level='.$level_id
				//"vpc_ReturnURL" => get_home_url().'/membership-account/membership-confirmation/ssl?level='.$level_id
				//"vpc_ReturnURL" => get_home_url().'/mypage'
				"vpc_ReturnURL" => $rurl.'&level=1'
			);
			
			ksort ( $DigitalOrder );
			
			foreach( $DigitalOrder as $key => $value ) {
				if ( strlen( $value ) > 0 ) {
					if ( $appendAmp == 0 ) {
						$service_host .= urlencode( $key ) . '=' . urlencode( $value );
						$appendAmp = 1;
					} else{
						$service_host .= '&' . urlencode( $key ) . "=" . urlencode( $value );
					}
					$md5Hash .= $value;
				}
			}	

			$service_host .= "&vpc_SecureHash=". strtoupper( md5($md5Hash));
			//$order->saveOrder();
			//echo $service_host;
			header("Location:".$service_host);
			exit();
				
			}
			catch (Exception $e)
			{
				//$order->status = "error";
				$order->errorcode = true;
				$order->error = "Error: " . $e->getMessage();
				$order->shorterror = $order->error;
				return false;
			}
			//$order->saveOrder();
			//echo $service_host;
			//header("Location:".$service_host);
			//exit();			
			
			

			// Demo mode?
			// if(empty($order->gateway_environment))
				// $gateway_environment = pmpro_getOption("gateway_environment");
			// else
				// $gateway_environment = $order->gateway_environment;
			// if("sandbox" === $gateway_environment || "beta-sandbox" === $gateway_environment)
			// {
				// Migs::sandbox(true);
				// $tco_args['demo'] = 'Y';
			// }
			// else
				// Migs::sandbox(false);
			
			// Trial?
			//li_#_startup_fee	Any start up fees for the product or service. Can be negative to provide discounted first installment pricing, but cannot equal or surpass the product price.
							
			
			
			//redirect to 2checkout
			
		}

		function cancel(&$order) {
			//no matter what happens below, we're going to cancel the order in our system
			$order->updateStatus("cancelled");

			//require a subscription id
			if(empty($order->subscription_transaction_id))
				return false;

			//build api params
			$params = array();
			$params['sale_id'] = $order->subscription_transaction_id;
			
			// Demo mode?
			if(empty($order->gateway_environment))
				$gateway_environment = pmpro_getOption("gateway_environment");
			else
				$gateway_environment = $order->gateway_environment;
			
			// if("sandbox" === $gateway_environment || "beta-sandbox" === $gateway_environment)
			// {
				// Migs::sandbox(true);
				// $params['demo'] = 'Y';
			// }
			// else
				// Migs::sandbox(false);

			//$result = Migs_Sale::stop( $params ); // Stop the recurring billing

			// Successfully cancelled
			if (isset($result['response_code']) && $result['response_code'] === 'OK') {
				$order->updateStatus("cancelled");	
				return true;
			}
			// Failed
			else {
				$order->status = "error";
				$order->errorcode = $result->getCode();
				$order->error = $result->getMessage();
								
				return false;
			}
			
			return $order;
		}
	}