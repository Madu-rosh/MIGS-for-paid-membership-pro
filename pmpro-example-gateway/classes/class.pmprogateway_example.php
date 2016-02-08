<?php
	//load classes init method
	add_action('init', array('PMProGateway_example', 'init'));
	/**
	 * PMProGateway_gatewayname Class
	 *
	 * Handles example integration.
	 *
	 */
	class PMProGateway_example extends PMProGateway
	{
		function PMProGateway($gateway = NULL)
		{
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
			//make sure example is a gateway option
			add_filter('pmpro_gateways', array('PMProGateway_example', 'pmpro_gateways'));

			//add fields to payment settings
			add_filter('pmpro_payment_options', array('PMProGateway_example', 'pmpro_payment_options'));
			add_filter('pmpro_payment_option_fields', array('PMProGateway_example', 'pmpro_payment_option_fields'), 10, 2);

			//add some fields to edit user page (Updates)
			add_action('pmpro_after_membership_level_profile_fields', array('PMProGateway_example', 'user_profile_fields'));
			add_action('profile_update', array('PMProGateway_example', 'user_profile_fields_save'));

			//updates cron
			add_action('pmpro_activation', array('PMProGateway_example', 'pmpro_activation'));
			add_action('pmpro_deactivation', array('PMProGateway_example', 'pmpro_deactivation'));
			add_action('pmpro_cron_example_subscription_updates', array('PMProGateway_example', 'pmpro_cron_example_subscription_updates'));

			//code to add at checkout if example is the current gateway
			$gateway = pmpro_getOption("gateway");
			if($gateway == "example")
			{
				//add_action('wp_print_scripts', 'enqueue_scripts');
				add_action('pmpro_checkout_preheader', array('PMProGateway_example', 'pmpro_checkout_preheader'));
				add_filter('pmpro_checkout_order', array('PMProGateway_example', 'pmpro_checkout_order'));
				add_filter('pmpro_include_billing_address_fields', array('PMProGateway_example', 'pmpro_include_billing_address_fields'));
				add_filter('pmpro_required_billing_fields', array('PMProGateway_example', 'pmpro_required_billing_fields'));
				add_filter('pmpro_include_cardtype_field', array('PMProGateway_example', 'pmpro_include_billing_address_fields'));
				add_filter('pmpro_include_payment_information_fields', array('PMProGateway_example', 'pmpro_include_payment_information_fields'));
				add_filter('pmpro_after_checkout', array('PMProGateway_example', 'pmpro_after_checkout'));
			}
			if(isset($_REQUEST['status']))
			{
				pmpro_after_checkout($user_id, $morder);	
		
			}		
			
		}
		/* this is for enqueeu script i am tring only*/
		/*function enqueue_scripts() {
    // Your actual AJAX script
    //wp_enqueue_script( 'my-script', '/js/script.js', array( 'jquery' ) );
    // This will localize the link for the ajax url to your 'my-script' js file (above). You can retreive it in 'script.js' with 'myAjax.ajaxurl'
			wp_localize_script( 'my-script', 'myAjax', array( 'ajaxurl' => $_SERVER['DOCUMENT_ROOT'].'/ccc/wp-content/plugins/pmpro-example-gateway/classes/paymenthandler.php'));
		}*/
			

		/**
		 * Make sure example is in the gateways list
		 *
		 * @since 1.8
		 */
		static function pmpro_gateways($gateways)
		{
			if(empty($gateways['example']))
				$gateways['example'] = __('example', 'pmpro');

			return $gateways;
		}

		/**
		 * Get a list of payment options that the example gateway needs/supports.
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
		 * Check settings if billing address should be shown.
		 * @since 1.8
		 */
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

		/**
		 * Set payment options for payment settings page.
		 *
		 * @since 1.8
		 */
		static function pmpro_payment_options($options)
		{
			//get example options
			$example_options = PMProGateway_example::getGatewayOptions();

			//merge with others.
			$options = array_merge($example_options, $options);

			return $options;
		}

		/**
		 * Display fields for example options.
		 *
		 * @since 1.8
		 */
		static function pmpro_payment_option_fields($values, $gateway)
		{
		?>
		<tr class="pmpro_settings_divider gateway gateway_example" <?php if($gateway != "example") { ?>style="display: none;"<?php } ?>>
			<td colspan="2">
				<?php _e('example Settings', 'pmpro'); ?>
			</td>
		</tr>
		<tr class="gateway gateway_example" <?php if($gateway != "example") { ?>style="display: none;"<?php } ?>>
			<?php // example custom pamyment settings here ?>
		</tr>
		<tr class="gateway gateway_example" <?php if($gateway != "example") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="merchantide"><?php _e('Merchant ID', 'pmpro');?>:</label>
			</th>
			<td>
				<input type="text" id="merchantide" name="merchantide" size="60" value="<?php echo esc_attr($values['merchantide'])?>" />
			</td>
		</tr>
		<tr class="gateway gateway_example" <?php if($gateway != "example") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="accescodee"><?php _e('Access Code', 'pmpro');?>:</label>
			</th>
			<td>
				<input type="text" id="accescodee" name="accescodee" size="60" value="<?php echo esc_attr($values['accescodee'])?>" />
			</td>
		</tr>
		<tr class="gateway gateway_example" <?php if($gateway != "example") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="securehash"><?php _e('Secure Hash', 'pmpro');?>:</label>
			</th>
			<td>
				<input type="text" id="securehashe" name="securehashe" size="60" value="<?php echo esc_attr($values['securehashe'])?>" />
			</td>
		</tr>
		
		<?php
		}
        /**
		 * Code added to checkout preheader.
		 *
		 * @since 1.8
		 */
		static function pmpro_checkout_preheader()
		{
		}
		/**
		 * Filtering orders at checkout.
		 *
		 * @since 1.8
		 */
		static function pmpro_checkout_order($morder)
		{
			//example lite code to get name from other sources if available
			global $pmpro_example_lite, $current_user;
			if(!empty($pmpro_example_lite) && empty($morder->FirstName) && empty($morder->LastName))
			{
				if(!empty($current_user->ID))
				{
					$morder->FirstName = get_user_meta($current_user->ID, "first_name", true);
					$morder->LastName = get_user_meta($current_user->ID, "last_name", true);
				}
				elseif(!empty($_REQUEST['first_name']) && !empty($_REQUEST['last_name']))
				{
					$morder->FirstName = $_REQUEST['first_name'];
					$morder->LastName = $_REQUEST['last_name'];
				}
			}
			return $morder;
		}
		

		/**
		 * Code to run after checkout
		 *
		 * @since 1.8
		 */
		static function pmpro_after_checkout($user_id, $morder)
		{
			global $gateway,$pmpro_msg, $pmpro_msgt, $pmpro_level, $current_user, $pmpro_review,$discount_code, $bemail;

			//PayPal Express Call Backs
			
				if(!empty($_REQUEST['paymentAmount']))
					$_SESSION['paymentAmount'] = $_REQUEST['paymentAmount'];
				if(!empty($_REQUEST['currencyCodeType']))
					$_SESSION['currCodeType'] = $_REQUEST['currencyCodeType'];
				if(!empty($_REQUEST['paymentType']))
					$_SESSION['paymentType'] = $_REQUEST['paymentType'];

				$morder = new MemberOrder();
				
				
			
			if($_REQUEST['status']=='sucess')
			{
				$morder = new MemberOrder();
				//set up level var
				$morder->getMembershipLevel();
				$morder->membership_level = apply_filters("pmpro_checkout_level", $morder->membership_level);

				//tax
				$morder->subtotal = $morder->InitialPayment;
				$morder->getTax();
				if($pmpro_level->billing_limit)
					$morder->TotalBillingCycles = $pmpro_level->billing_limit;

				if(pmpro_isLevelTrial($pmpro_level))
				{
					$morder->TrialBillingPeriod = $pmpro_level->cycle_period;
					$morder->TrialBillingFrequency = $pmpro_level->cycle_number;
					$morder->TrialBillingCycles = $pmpro_level->trial_limit;
					$morder->TrialAmount = $pmpro_level->trial_amount;
				}

				if($morder->confirm())
				{
					$pmpro_confirmed = true;
				}
				else
				{
					$pmpro_msg = $morder->error;
					$pmpro_msgt = "pmpro_error";
				}
			}
				
			

			if(!empty($morder))
				return array("pmpro_confirmed"=>$pmpro_confirmed, "morder"=>$morder);
			else
				return $pmpro_confirmed;
			if($gateway == "example")
			{
				if(!empty($morder) && !empty($morder->Gateway) && !empty($morder->Gateway->customer) && !empty($morder->Gateway->customer->id))
				{
					update_user_meta($user_id, "pmpro_example_customerid", $morder->Gateway->customer->id);
				}
			}
			
		}
		
		/**
		 * Use our own payment fields at checkout. (Remove the name attributes.)		
		 * @since 1.8
		 */
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
		 * Fields shown on edit user page
		 *
		 * @since 1.8
		 */
		static function user_profile_fields($user)
		{
			global $wpdb, $current_user, $pmpro_currency_symbol;

			$cycles = array( __('Day(s)', 'pmpro') => 'Day', __('Week(s)', 'pmpro') => 'Week', __('Month(s)', 'pmpro') => 'Month', __('Year(s)', 'pmpro') => 'Year' );
			$current_year = date("Y");
			$current_month = date("m");

			//make sure the current user has privileges
			$membership_level_capability = apply_filters("pmpro_edit_member_capability", "manage_options");
			if(!current_user_can($membership_level_capability))
				return false;

			//more privelges they should have
			$show_membership_level = apply_filters("pmpro_profile_show_membership_level", true, $user);
			if(!$show_membership_level)
				return false;

			//check that user has a current subscription at Us
			$last_order = new MemberOrder();
			$last_order->getLastMemberOrder($user->ID);

			//assume no sub to start
			$sub = false;

			//check that gateway is WE
			if($last_order->gateway == "example")
			{
				//is there a customer?
				$sub = $last_order->Gateway->getSubscription($last_order);
			}

			$customer_id = $user->pmpro_example_customerid;

			if(empty($sub))
			{
				//make sure we delete example updates
				update_user_meta($user->ID, "pmpro_example_updates", array());

				//if the last order has a sub id, let the admin know there is no sub at example
				if(!empty($last_order) && $last_order->gateway == "example" && !empty($last_order->subscription_transaction_id) && strpos($last_order->subscription_transaction_id, "sub_") !== false)
				{
				?>
				<p><strong>Note:</strong> Subscription <strong><?php echo $last_order->subscription_transaction_id;?></strong> could not be found at example. It might have been deleted.</p>
				<?php
				}
			}
			else
			{
			?>
			<h3><?php _e("Subscription Updates", "pmpro"); ?></h3>
			<p>
				<?php
					if(empty($_REQUEST['user_id']))
						_e("Subscription updates, allow you to change the member's subscription values at predefined times. Be sure to click Update Profile after making changes.", 'pmpro');
					else
						_e("Subscription updates, allow you to change the member's subscription values at predefined times. Be sure to click Update User after making changes.", 'pmpro');
				?>
			</p>
			<table class="form-table">
				<tr>
					<th><label for="membership_level"><?php _e("Update", "pmpro"); ?></label></th>
					<td id="updates_td">
						<?php
							$old_updates = $user->pmpro_example_updates;
							if(is_array($old_updates))
							{
								$updates = array_merge(
									array(array('template'=>true, 'when'=>'now', 'date_month'=>'', 'date_day'=>'', 'date_year'=>'', 'billing_amount'=>'', 'cycle_number'=>'', 'cycle_period'=>'Month')),
									$old_updates
								);
							}
							else
								$updates = array(array('template'=>true, 'when'=>'now', 'date_month'=>'', 'date_day'=>'', 'date_year'=>'', 'billing_amount'=>'', 'cycle_number'=>'', 'cycle_period'=>'Month'));

							foreach($updates as $update)
							{
							?>
							<div class="updates_update" <?php if(!empty($update['template'])) { ?>style="display: none;"<?php } ?>>
								<select class="updates_when" name="updates_when[]">
									<option value="now" <?php selected($update['when'], "now");?>>Now</option>
									<option value="payment" <?php selected($update['when'], "payment");?>>After Next Payment</option>
									<option value="date" <?php selected($update['when'], "date");?>>On Date</option>
								</select>
								<span class="updates_date" <?php if($uwhen != "date") { ?>style="display: none;"<?php } ?>>
									<select name="updates_date_month[]">
										<?php
											for($i = 1; $i < 13; $i++)
											{
											?>
											<option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?>" <?php if(!empty($update['date_month']) && $update['date_month'] == $i) { ?>selected="selected"<?php } ?>>
												<?php echo date("M", strtotime($i . "/1/" . $current_year));?>
											</option>
											<?php
											}
										?>
									</select>
									<input name="updates_date_day[]" type="text" size="2" value="<?php if(!empty($update['date_day'])) echo esc_attr($update['date_day']);?>" />
									<input name="updates_date_year[]" type="text" size="4" value="<?php if(!empty($update['date_year'])) echo esc_attr($update['date_year']);?>" />
								</span>
								<span class="updates_billing" <?php if($uwhen == "no") { ?>style="display: none;"<?php } ?>>
									<?php echo $pmpro_currency_symbol?><input name="updates_billing_amount[]" type="text" size="10" value="<?php echo esc_attr($update['billing_amount']);?>" />
									<small><?php _e('per', 'pmpro');?></small>
									<input name="updates_cycle_number[]" type="text" size="5" value="<?php echo esc_attr($update['cycle_number']);?>" />
									<select name="updates_cycle_period[]">
									  <?php
										foreach ( $cycles as $name => $value ) {
										  echo "<option value='$value'";
										  if(!empty($update['cycle_period']) && $update['cycle_period'] == $value) echo " selected='selected'";
										  echo ">$name</option>";
										}
									  ?>
									</select>
								</span>
								<span>
									<a class="updates_remove" href="javascript:void(0);">Remove</a>
								</span>
							</div>
							<?php
							}
							?>
						<p><a id="updates_new_update" href="javascript:void(0);">+ New Update</a></p>
					</td>
				</tr>
			</table>
			<script>
				<!--
				jQuery(document).ready(function() {
					//function to update dropdowns/etc based on when field
					function updateSubscriptionUpdateFields(when)
					{
						if(jQuery(when).val() == 'date')
							jQuery(when).parent().children('.updates_date').show();
						else
							jQuery(when).parent().children('.updates_date').hide();

						if(jQuery(when).val() == 'no')
							jQuery(when).parent().children('.updates_billing').hide();
						else
							jQuery(when).parent().children('.updates_billing').show();
					}

					//and update on page load
					jQuery('.updates_when').each(function() { if(jQuery(this).parent().css('display') != 'none') updateSubscriptionUpdateFields(this); });

					//add a new update when clicking to
					var num_updates_divs = <?php echo count($updates);?>;
					jQuery('#updates_new_update').click(function() {
						//get updates
						updates = jQuery('.updates_update').toArray();

						//clone the first one
						new_div = jQuery(updates[0]).clone();

						//append
						new_div.insertBefore('#updates_new_update');

						//update events
						addUpdateEvents()

						//unhide it
						new_div.show();
						updateSubscriptionUpdateFields(new_div.children('.updates_when'));
					});

					function addUpdateEvents()
					{
						//update when when changes
						jQuery('.updates_when').change(function() {
							updateSubscriptionUpdateFields(this);
						});

						//remove updates when clicking
						jQuery('.updates_remove').click(function() {
							jQuery(this).parent().parent().remove();
						});
					}
					addUpdateEvents();
				});
			-->
			</script>
			<?php
			}
		}

		/**
		 * Process fields from the edit user page
		 *
		 * @since 1.8
		 */
		static function user_profile_fields_save($user_id)
		{
			global $wpdb;

			//check capabilities
			$membership_level_capability = apply_filters("pmpro_edit_member_capability", "manage_options");
			if(!current_user_can($membership_level_capability))
				return false;

			//make sure some value was passed
			if(!isset($_POST['updates_when']) || !is_array($_POST['updates_when']))
				return;

			//vars
			$updates = array();
			$next_on_date_update = "";

			//build array of updates (we skip the first because it's the template field for the JavaScript
			for($i = 1; $i < count($_POST['updates_when']); $i++)
			{
				$update = array();

				//all updates have these values
				$update['when'] = $_POST['updates_when'][$i];
				$update['billing_amount'] = $_POST['updates_billing_amount'][$i];
				$update['cycle_number'] = $_POST['updates_cycle_number'][$i];
				$update['cycle_period'] = $_POST['updates_cycle_period'][$i];

				//these values only for on date updates
				if($_POST['updates_when'][$i] == "date")
				{
					$update['date_month'] = str_pad($_POST['updates_date_month'][$i], 2, "0", STR_PAD_LEFT);
					$update['date_day'] = str_pad($_POST['updates_date_day'][$i], 2, "0", STR_PAD_LEFT);
					$update['date_year'] = $_POST['updates_date_year'][$i];
				}

				//make sure the update is valid
				if(empty($update['cycle_number']))
					continue;

				//if when is now, update the subscription
				if($update['when'] == "now")
				{
					//get level for user
					$user_level = pmpro_getMembershipLevelForUser($user_id);

					//get current plan at Example to get payment date
					$last_order = new MemberOrder();
					$last_order->getLastMemberOrder($user_id);
					$last_order->setGateway('example');
					$last_order->Gateway->getCustomer($last_order);

					$subscription = $last_order->Gateway->getSubscription($last_order);

					if(!empty($subscription))
					{
						$end_timestamp = $subscription->current_period_end;

						//cancel the old subscription
						if(!$last_order->Gateway->cancelSubscriptionAtGateway($subscription))
						{
							//throw error and halt save
							function pmpro_example_user_profile_fields_save_error($errors, $update, $user)
							{
								$errors->add('pmpro_example_updates',__('Could not cancel the old subscription. Updates have not been processed.', 'pmpro'));
							}
							add_filter('user_profile_update_errors', 'pmpro_example_user_profile_fields_save_error', 10, 3);

							//stop processing updates
							return;
						}
					}

					//if we didn't get an end date, let's set one one cycle out
					if(empty($end_timestamp))
						$end_timestamp = strtotime("+" . $update['cycle_number'] . " " . $update['cycle_period'], current_time('timestamp'));

					//build order object
					$update_order = new MemberOrder();
					$update_order->setGateway('example');
					$update_order->user_id = $user_id;
					$update_order->membership_id = $user_level->id;
					$update_order->membership_name = $user_level->name;
					$update_order->InitialPayment = 0;
					$update_order->PaymentAmount = $update['billing_amount'];
					$update_order->ProfileStartDate = date("Y-m-d", $end_timestamp);
					$update_order->BillingPeriod = $update['cycle_period'];
					$update_order->BillingFrequency = $update['cycle_number'];

					//need filter to reset ProfileStartDate
					add_filter('pmpro_profile_start_date', create_function('$startdate, $order', 'return "' . $update_order->ProfileStartDate . 'T0:0:0";'), 10, 2);

					//update subscription
					$update_order->Gateway->subscribe($update_order, false);

					//update membership
					$sqlQuery = "UPDATE $wpdb->pmpro_memberships_users
									SET billing_amount = '" . esc_sql($update['billing_amount']) . "',
										cycle_number = '" . esc_sql($update['cycle_number']) . "',
										cycle_period = '" . esc_sql($update['cycle_period']) . "',
										trial_amount = '',
										trial_limit = ''
									WHERE user_id = '" . esc_sql($user_id) . "'
										AND membership_id = '" . esc_sql($last_order->membership_id) . "'
										AND status = 'active'
									LIMIT 1";

					$wpdb->query($sqlQuery);

					//save order so we know which plan to look for at example (order code = plan id)
					$update_order->status = "success";
					$update_order->saveOrder();

					continue;
				}
				elseif($update['when'] == 'date')
				{
					if(!empty($next_on_date_update))
						$next_on_date_update = min($next_on_date_update, $update['date_year'] . "-" . $update['date_month'] . "-" . $update['date_day']);
					else
						$next_on_date_update = $update['date_year'] . "-" . $update['date_month'] . "-" . $update['date_day'];
				}

				//add to array
				$updates[] = $update;
			}

			//save in user meta
			update_user_meta($user_id, "pmpro_example_updates", $updates);

			//save date of next on-date update to make it easier to query for these in cron job
			update_user_meta($user_id, "pmpro_example_next_on_date_update", $next_on_date_update);
		}

		/**
		 * Cron activation for subscription updates.
		 *
		 * @since 1.8
		 */
		static function pmpro_activation()
		{
			wp_schedule_event(time(), 'daily', 'pmpro_cron_example_subscription_updates');
		}

		/**
		 * Cron deactivation for subscription updates.
		 *
		 * @since 1.8
		 */
		static function pmpro_deactivation()
		{
			wp_clear_scheduled_hook('pmpro_cron_example_subscription_updates');
		}

		/**
		 * Cron job for subscription updates.
		 *
		 * @since 1.8
		 */
		static function pmpro_cron_example_subscription_updates()
		{
			global $wpdb;

			//get all updates for today (or before today)
			$sqlQuery = "SELECT *
						 FROM $wpdb->usermeta
						 WHERE meta_key = 'pmpro_example_next_on_date_update'
							AND meta_value IS NOT NULL
							AND meta_value <> ''
							AND meta_value < '" . date("Y-m-d", strtotime("+1 day")) . "'";
			$updates = $wpdb->get_results($sqlQuery);
			
			if(!empty($updates))
			{
				//loop through
				foreach($updates as $update)
				{
					//pull values from update
					$user_id = $update->user_id;

					$user = get_userdata($user_id);
					
					//if user is missing, delete the update info and continue
					if(empty($user) || empty($user->ID))
					{						
						delete_user_meta($user_id, "pmpro_example_updates");
						delete_user_meta($user_id, "pmpro_example_next_on_date_update");
					
						continue;
					}
					
					$user_updates = $user->pmpro_example_updates;
					$next_on_date_update = "";					
					
					//loop through updates looking for updates happening today or earlier
					if(!empty($user_updates))
					{
						foreach($user_updates as $key => $update)
						{
							if($update['when'] == 'date' &&
								$update['date_year'] . "-" . $update['date_month'] . "-" . $update['date_day'] <= date("Y-m-d")
							)
							{
								//get level for user
								$user_level = pmpro_getMembershipLevelForUser($user_id);

								//get current plan at Example to get payment date
								$last_order = new MemberOrder();
								$last_order->getLastMemberOrder($user_id);
								$last_order->setGateway('example');
								$last_order->Gateway->getCustomer($last_order);

								if(!empty($last_order->Gateway->customer))
								{
									//find the first subscription
									if(!empty($last_order->Gateway->customer->subscriptions['data'][0]))
									{
										$first_sub = $last_order->Gateway->customer->subscriptions['data'][0]->__toArray();
										$end_timestamp = $first_sub['current_period_end'];
									}
								}

								//if we didn't get an end date, let's set one one cycle out
								$end_timestamp = strtotime("+" . $update['cycle_number'] . " " . $update['cycle_period']);

								//build order object
								$update_order = new MemberOrder();
								$update_order->setGateway('example');
								$update_order->user_id = $user_id;
								$update_order->membership_id = $user_level->id;
								$update_order->membership_name = $user_level->name;
								$update_order->InitialPayment = 0;
								$update_order->PaymentAmount = $update['billing_amount'];
								$update_order->ProfileStartDate = date("Y-m-d", $end_timestamp);
								$update_order->BillingPeriod = $update['cycle_period'];
								$update_order->BillingFrequency = $update['cycle_number'];

								//update subscription
								$update_order->Gateway->subscribe($update_order, false);

								//update membership
								$sqlQuery = "UPDATE $wpdb->pmpro_memberships_users
												SET billing_amount = '" . esc_sql($update['billing_amount']) . "',
													cycle_number = '" . esc_sql($update['cycle_number']) . "',
													cycle_period = '" . esc_sql($update['cycle_period']) . "'
												WHERE user_id = '" . esc_sql($user_id) . "'
													AND membership_id = '" . esc_sql($last_order->membership_id) . "'
													AND status = 'active'
												LIMIT 1";

								$wpdb->query($sqlQuery);

								//save order
								$update_order->status = "success";
								$update_order->save();

								//remove update from list
								unset($user_updates[$key]);
							}
							elseif($update['when'] == 'date')
							{
								//this is an on date update for the future, update the next on date update
								if(!empty($next_on_date_update))
									$next_on_date_update = min($next_on_date_update, $update['date_year'] . "-" . $update['date_month'] . "-" . $update['date_day']);
								else
									$next_on_date_update = $update['date_year'] . "-" . $update['date_month'] . "-" . $update['date_day'];
							}
						}
					}

					//save updates in case we removed some
					update_user_meta($user_id, "pmpro_example_updates", $user_updates);

					//save date of next on-date update to make it easier to query for these in cron job
					update_user_meta($user_id, "pmpro_example_next_on_date_update", $next_on_date_update);
				}
			}
		}

		
		/**
		 * Process checkout and decide if a charge and or subscribe is needed
		 *
		 * @since 1.4
		 */
		function process(&$order)
		{
			//check for initial payment
			if(floatval($order->InitialPayment) == 0)
			{
				//just subscribe
				return $this->subscribe($order);
			}
			else
			{
				//charge then subscribe
				if($this->charge($order))
				{
					if(pmpro_isLevelRecurring($order->membership_level))
					{
						if($this->subscribe($order))
						{
							//yay!
							return true;
						}
						else
						{
							//try to refund initial charge
							return false;
						}
					}
					else
					{
						//only a one time charge
						$order->status = "success";	//saved on checkout page
						return true;
					}
				}
				else
				{
					if(empty($order->error))
						$order->error = __("Unknown error: Initial payment failed.", "pmpro");
					return false;
				}
			}
		}

		/**
		 * Make a one-time charge with example
		 *
		 * @since 1.4
		 */
		function charge(&$order)
		{
			global $pmpro_currency,$pmpro_level;

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
			$rurl = admin_url("admin-ajax.php") . "?action=paymenthandler";
			
			$user_ID = get_current_user_id();			
			$DigitalOrder = array(
				"vpc_Version" => "1",
				"vpc_Command" => "pay",
				"vpc_AccessCode" => pmpro_getOption("accescodee"),
				"vpc_MerchTxnRef" => $order_id,
				"vpc_Merchant" => pmpro_getOption("merchantide"),
				"vpc_OrderInfo" => "VPC".$order->getRandomCode(),
				"vpc_Amount" => $order_amount,
				"vpc_Locale" => "en",
				"vpc_ReturnURL" => $rurl
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

			if(empty($response["failure_message"]))
			{
				//successful charge
				$order->payment_transaction_id = $response["id"];
				$order->updateStatus("success");
				$order->saveOrder();
				return true;
			}
			else
			{
				//$order->status = "error";
				$order->errorcode = true;
				$order->error = $response['failure_message'];
				$order->shorterror = $response['failure_message'];
				return false;
			}
		}

		/**
		 * Get a example subscription from a PMPro order
		 *
		 * @since 1.8
		 */
		function getSubscription(&$order)
		{
			global $wpdb;

			//no order?
			if(empty($order) || empty($order->code))
				return false;

			$result = $this->getCustomer($order, true);	//force so we don't get a cached sub for someone else

			//no customer?
			if(empty($result))
				return false;

			//is there a subscription transaction id pointing to a sub?
			if(!empty($order->subscription_transaction_id) && strpos($order->subscription_transaction_id, "sub_") !== false)
			{
				try
				{
					$sub = $this->customer->subscriptions->retrieve($order->subscription_transaction_id);
				}
				catch (Exception $e)
				{
					$order->error = __("Error getting subscription with Example:", "pmpro") . $e->getMessage();
					$order->shorterror = $order->error;
					return false;
				}

				return $sub;
			}
			
			//no subscriptions object in customer
			if(empty($this->customer->subscriptions))
				return false;

			//find subscription based on customer id and order/plan id
			$subscriptions = $this->customer->subscriptions->all();

			//no subscriptions
			if(empty($subscriptions) || empty($subscriptions->data))
				return false;

			//we really want to test against the order codes of all orders with the same subscription_transaction_id (customer id)
			$codes = $wpdb->get_col("SELECT code FROM $wpdb->pmpro_membership_orders WHERE user_id = '" . $order->user_id . "' AND subscription_transaction_id = '" . $order->subscription_transaction_id . "' AND status NOT IN('refunded', 'review', 'token', 'error')");

			//find the one for this order
			foreach($subscriptions->data as $sub)
			{
				if(in_array($sub->plan->id, $codes))
				{
					return $sub;
				}
			}

			//didn't find anything yet
			return false;
		}

		/**
		 * Create a new subscription with example
		 *
		 * @since 1.4
		 */
		function subscribe(&$order, $checkout = true)
		{
			global $pmpro_currency;

			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();

			//filter order before subscription. use with care.
			$order = apply_filters("pmpro_subscribe_order", $order, $this);

			//figure out the user
			if(!empty($order->user_id))
				$user_id = $order->user_id;
			else
			{
				global $current_user;
				$user_id = $current_user->ID;
			}

			//set up customer
			$result = $this->getCustomer($order);
			if(empty($result))
				return false;	//error retrieving customer

			//set subscription id to custom id
			$order->subscription_transaction_id = $this->customer['id'];	//transaction id is the customer id, we save it in user meta later too

			//figure out the amounts
			$amount = $order->PaymentAmount;
			$amount_tax = $order->getTaxForPrice($amount);
			$amount = round((float)$amount + (float)$amount_tax, 2);

			/*
				There are two parts to the trial. Part 1 is simply the delay until the first payment
				since we are doing the first payment as a separate transaction.
				The second part is the actual "trial" set by the admin.

				example only supports Year or Month for billing periods, but we account for Days and Weeks just in case.
			*/
			//figure out the trial length (first payment handled by initial charge)
			if($order->BillingPeriod == "Year")
				$trial_period_days = $order->BillingFrequency * 365;	//annual
			elseif($order->BillingPeriod == "Day")
				$trial_period_days = $order->BillingFrequency * 1;		//daily
			elseif($order->BillingPeriod == "Week")
				$trial_period_days = $order->BillingFrequency * 7;		//weekly
			else
				$trial_period_days = $order->BillingFrequency * 30;	//assume monthly

			//convert to a profile start date
			$order->ProfileStartDate = date("Y-m-d", strtotime("+ " . $trial_period_days . " Day", current_time("timestamp"))) . "T0:0:0";

			//filter the start date
			$order->ProfileStartDate = apply_filters("pmpro_profile_start_date", $order->ProfileStartDate, $order);

			//convert back to days
			$trial_period_days = ceil(abs(strtotime(date("Y-m-d"), current_time("timestamp")) - strtotime($order->ProfileStartDate, current_time("timestamp"))) / 86400);

			//for free trials, just push the start date of the subscription back
			if(!empty($order->TrialBillingCycles) && $order->TrialAmount == 0)
			{
				$trialOccurrences = (int)$order->TrialBillingCycles;
				if($order->BillingPeriod == "Year")
					$trial_period_days = $trial_period_days + (365 * $order->BillingFrequency * $trialOccurrences);	//annual
				elseif($order->BillingPeriod == "Day")
					$trial_period_days = $trial_period_days + (1 * $order->BillingFrequency * $trialOccurrences);		//daily
				elseif($order->BillingPeriod == "Week")
					$trial_period_days = $trial_period_days + (7 * $order->BillingFrequency * $trialOccurrences);	//weekly
				else
					$trial_period_days = $trial_period_days + (30 * $order->BillingFrequency * $trialOccurrences);	//assume monthly
			}
			elseif(!empty($order->TrialBillingCycles))
			{
				/*
					Let's set the subscription to the trial and give the user an "update" to change the sub later to full price (since v2.0)

					This will force TrialBillingCycles > 1 to act as if they were 1
				*/
				$new_user_updates = array();
				$new_user_updates[] = array(
					'when' => 'payment',
					'billing_amount' => $order->PaymentAmount,
					'cycle_period' => $order->BillingPeriod,
					'cycle_number' => $order->BillingFrequency
				);

				//now amount to equal the trial #s
				$amount = $order->TrialAmount;
				$amount_tax = $order->getTaxForPrice($amount);
				$amount = round((float)$amount + (float)$amount_tax, 2);
			}

			//create a plan
			try
			{
                $plan = array(
                    "amount" => $amount * 100,
                    "interval_count" => $order->BillingFrequency,
                    "interval" => strtolower($order->BillingPeriod),
                    "trial_period_days" => $trial_period_days,
                    "name" => $order->membership_name . " for order " . $order->code,
                    "currency" => strtolower($pmpro_currency),
                    "id" => $order->code
                );

				$plan = example_Plan::create(apply_filters('pmpro_example_create_plan_array', $plan));
			}
			catch (Exception $e)
			{
				$order->error = __("Error creating plan with example:", "pmpro") . $e->getMessage();
				$order->shorterror = $order->error;
				return false;
			}

			//before subscribing, let's clear out the updates so we don't trigger any during sub
			if(!empty($user_id))
			{
				$old_user_updates = get_user_meta($user_id, "pmpro_example_updates", true);
				update_user_meta($user_id, "pmpro_example_updates", array());
			}

			if(empty($order->subscription_transaction_id) && !empty($this->customer['id']))
				$order->subscription_transaction_id = $this->customer['id'];

			//subscribe to the plan
			try
			{
				$subscription = array("plan" => $order->code);
				$result = $this->customer->subscriptions->create(apply_filters('pmpro_example_create_subscription_array', $subscription));
			}
			catch (Exception $e)
			{
				//try to delete the plan
				$plan->delete();

				//give the user any old updates back
				if(!empty($user_id))
					update_user_meta($user_id, "pmpro_updates", $old_user_updates);

				//return error
				$order->error = __("Error subscribing customer to plan with example:", "pmpro") . $e->getMessage();
				$order->shorterror = $order->error;
				return false;
			}

			//delete the plan
			$plan = example_Plan::retrieve($order->code);
			$plan->delete();

			//if we got this far, we're all good
			$order->status = "success";
			$order->subscription_transaction_id = $result['id'];

			//save new updates if this is at checkout
			if($checkout)
			{
				//empty out updates unless set above
				if(empty($new_user_updates))
					$new_user_updates = array();

				//update user meta
				if(!empty($user_id))
					update_user_meta($user_id, "pmpro_example_updates", $new_user_updates);
				else
				{
					//need to remember the user updates to save later
					global $pmpro_example_updates;
					$pmpro_example_updates = $new_user_updates;
					function pmpro_user_register_example_updates($user_id)
					{
						global $pmpro_example_updates;
						update_user_meta($user_id, "pmpro_example_updates", $pmpro_example_updates);
					}
					add_action("user_register", "pmpro_user_register_example_updates");
				}
			}
			else
			{
				//give them their old updates back
				update_user_meta($user_id, "pmpro_example_updates", $old_user_updates);
			}

			return true;
		}

		/**
		 * Helper method to update the customer info via getCustomer
		 *
		 * @since 1.4
		 */
		function update(&$order)
		{
			//we just have to run getCustomer which will look for the customer and update it with the new token
			$result = $this->getCustomer($order);

			if(!empty($result))
			{
				return true;
			}
			else
			{
				return false;	//couldn't find the customer
			}
		}
		
		/*
			Cancel a subscription at the gateway.

			Required if supporting recurring subscriptions.
		*/
		function cancel(&$order)
		{
			//no matter what happens below, we're going to cancel the order in our system
			if($update_status)
				$order->updateStatus("cancelled");

			//require a subscription id
			if(empty($order->subscription_transaction_id))
				return false;

			//find the customer
			$result = $this->getCustomer($order);

			if(!empty($result))
			{
				//find subscription with this order code
				$subscription = $this->getSubscription($order);

				if(!empty($subscription))
				{
					if($this->cancelSubscriptionAtGateway($subscription))
					{
						//we're okay, going to return true later
					}
					else
					{
						$order->error = __("Could not cancel old subscription.", "pmpro");
						$order->shorterror = $order->error;

						return false;
					}
				}

				/*
					Clear updates for this user. (But not if checking out, we would have already done that.)
				*/
				if(empty($_REQUEST['submit-checkout']))
					update_user_meta($order->user_id, "pmpro_example_updates", array());

				return true;
			}
			else
			{
				$order->error = __("Could not find the customer.", "pmpro");
				$order->shorterror = $order->error;
				return false;	//no customer found
			}
		}	
		
		/*
			Get subscription status at the gateway.

			Optional if you have code that needs this or
			want to support addons that use this.
		*/
		function getSubscriptionStatus(&$order)
		{
			//require a subscription id
			if(empty($order->subscription_transaction_id))
				return false;
			
			//code to get subscription status at the gateway and test results would go here

			//this looks different for each gateway, but generally an array of some sort
			return array();
		}

		/*
			Get transaction status at the gateway.

			Optional if you have code that needs this or
			want to support addons that use this.
		*/
		function getTransactionStatus(&$order)
		{			
			//code to get transaction status at the gateway and test results would go here
			switch ( $order->status ) {
				case "success":$result="Transaction Successful"; break;
				case "0" : $result = "Transaction Successful"; break;
				case "?" : $result = "Transaction status is unknown"; break;
				case "1" : $result = "Unknown Error"; break;
				case "2" : $result = "Bank Declined Transaction"; break;
				case "3" : $result = "No Reply from Bank"; break;
				case "4" : $result = "Expired Card"; break;
				case "5" : $result = "Insufficient funds"; break;
				case "6" : $result = "Error Communicating with Bank"; break;
				case "7" : $result = "Payment Server System Error"; break;
				case "8" : $result = "Transaction Type Not Supported"; break;
				case "9" : $result = "Bank declined transaction (Do not contact Bank)"; break;
				case "A" : $result = "Transaction Aborted"; break;
				case "C" : $result = "Transaction Cancelled"; break;
				case "D" : $result = "Deferred transaction has been received and is awaiting processing"; break;
				case "F" : $result = "3D Secure Authentication failed"; break;
				case "I" : $result = "Card Security Code verification failed"; break;
				case "L" : $result = "Shopping Transaction Locked (Please try the transaction again later)"; break;
				case "N" : $result = "Cardholder is not enrolled in Authentication scheme"; break;
				case "P" : $result = "Transaction has been received by the Payment Adaptor and is being processed"; break;
				case "R" : $result = "Transaction was not processed - Reached limit of retry attempts allowed"; break;
				case "S" : $result = "Duplicate SessionID (OrderInfo)"; break;
				case "T" : $result = "Address Verification Failed"; break;
				case "U" : $result = "Card Security Code Failed"; break;
				case "V" : $result = "Address Verification and Card Security Code Failed"; break;
				default  : $result = "Unable to be determined";
			}
			return $result;
			//this looks different for each gateway, but generally an array of some sort
			//return array();
		}
		/**
		 * Filter pmpro_next_payment to get date via API if possible
		 *
		 * @since 1.8.6
		*/
		static function pmpro_next_payment($timestamp, $user_id, $order_status)
		{
			//find the last order for this user
			if(!empty($user_id))
			{
				//get last order
				$order = new MemberOrder();
				$order->getLastMemberOrder($user_id, $order_status);
				
				//check if this is a paypal express order with a subscription transaction id
				if(!empty($order->id) && !empty($order->subscription_transaction_id) && $order->gateway == "example")
				{
					//get the subscription and return the current_period end or false
					$subscription = $order->Gateway->getSubscription($order);					
					
					if(!empty($subscription->current_period_end))
						return $subscription->current_period_end;
					else
						return false;
				}
			}
						
			return $timestamp;
		}
	}