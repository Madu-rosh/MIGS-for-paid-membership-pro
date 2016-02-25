<?php	
	//set this in your wp-config.php for debugging
	//define('PMPRO_INS_DEBUG', true);
	//in case the file is loaded directly	
	if(!defined("WP_USE_THEMES"))
	{
		global $isapage;
		$isapage = true;
		
		define('WP_USE_THEMES', false);
		require_once(dirname(__FILE__) . '/../../../../wp-load.php');
	}

	//some globals
	global $wpdb, $gateway_environment, $logstr,$pmpro_currency,$pmpro_level;
	$logstr = "";	//will put debug info here and write to inslog.txt
	$authorised = false;
	
	$md5Hash = pmpro_getOption("securehashe");
	$txnSecureHash = $_REQUEST['vpc_SecureHash'];
	$txnref = $_REQUEST['vpc_MerchTxnRef'];
	$order_id = explode( '_', $txnref );
	$txn_responce=$_REQUEST['vpc_TxnResponseCode'];
	$order_id = $order_id[0];
	$DR = parseDigitalReceipt();
	$msg['class']   = 'error';
	$msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";
	$level_id=$_REQUEST['level'];
	$amount=$_REQUEST['vpc_Amount'];
	
	$ThreeDSecureData = parse3DSecureData();		

	//validate?
	if( !pmpro_migsValidate($md5Hash,$DR["txnResponseCode"]) ) {
		
		inslog("(!!FAILED VALIDATION!!)");		
		//validation failed
		pmpro_migsExit();
	 }
	 //if validation success
	if( pmpro_migsValidate($md5Hash,$DR["txnResponseCode"])) {
		//initial payment, get the order
		$last_subscr_order = new MemberOrder($order_id);
		global $current_user;
		$user_id = $current_user->ID;
		$morder->user_id = $user_id; 
		$morder = new MemberOrder( $order_id );
		$morder->payment_transaction_id = $txnref; 
		$morder->code = $order_id;
		$morder->id=$wpdb->get_var("SELECT id FROM $wpdb->pmpro_membership_orders WHERE code = '" . $order_id . "' LIMIT 1");
		
		/*standard code generation*/
		/*-----------------------------my new test code--------------------------------------*/
		if( ! empty ( $morder ) && ! empty ( $morder->status ) && $morder->status === 'success' ) {
			inslog( "Checkout was already processed (" . $morder->code . "). Ignoring this request." );
		}
		elseif (pmpro_insChangeMembershipLevel( $order_id, $morder ) ) {
				
			inslog( "Checkout processed (" . $morder->code . ") success!" );
		}
		elseif( $last_subscr_order->getLastMemberOrderBySubscriptionTransactionID( $order_id ) == false) {
			//first payment, get order	
			$morder->subscription_transaction_id = $txnref; 
			$morder->InitialPayment = $amount/100;  
			$morder->PaymentAmount = $amount/100;	
			$morder->getMembershipLevel();
			$morder->getUser();

			//update membership
			if( pmpro_insChangeMembershipLevel( $order_id,$morder ) ) {									
				inslog( "Checkout processed (" . $morder->code . ") success!" );			
			}
			else {
				inslog( "ERROR: Couldn't change level for order (" . $morder->code . ")." );	
			}
		}
		else {
			pmpro_insSaveOrder( $order_id, $last_subscr_order );
			
		}	
		
	
		/*------------------------------------------------------------------------------------------------------------*/
		
		// /*-----------------------------my cfrazy try-------------------------------------*/
		// $morder->payment_transaction_id = $txnref; 
		// $morder->code = $order_id;
		// $morder->id=$wpdb->get_var("SELECT id FROM $wpdb->pmpro_membership_orders WHERE code = '" . $order_id . "' LIMIT 1");
        // $morder->subscription_transaction_id = $txnref; 
        // $morder->InitialPayment = $amount/100;  
		// $morder->PaymentAmount = $amount/100;		
		// $morder->status = "success";
		// $morder->getUser();
		// $morder->saveOrder();
		// if(!empty($morder))
				// $invoice = new MemberOrder($morder->id);
			// else
				// $invoice = NULL;

			// $user = get_userdata($morder->user_id);
			// $user->membership_level = $morder->membership_level;		

			
		
			// //send email to member
			// $pmproemail = new PMProEmail();
			// $pmproemail->sendCheckoutEmail($user_id, $morder);

			// //send email to admin
			// $pmproemail = new PMProEmail();
			// $pmproemail->sendCheckoutAdminEmail($user_id, $morder);
			// //email the user their invoice
			// $pmproemail = new PMProEmail();
			// $pmproemail->sendInvoiceEmail($user_id, $morder);
		// /*--------------------------------------------------------------------------------*/
		
		
		inslog("NO MESSAGE: ORDER: " . var_export($morder, true) . "\n---\n");		
		// $service_host=get_home_url().'/membership-account/membership-confirmation/?level='.$order->membership_level->id;
		// header("Location:".$service_host);
		pmpro_migsExit(pmpro_url("confirmation", "?level=" . $morder->membership_level->id));
	 }
	else{
		pmpro_migsExit();
	 }
	 inslog("The PMPro INS handler does not process this type of message. message_type = " . $message_type);
	 pmpro_migsExit();	
	/* my billing functions*/
	function parse3DSecureData() {
			$threeDSecure = array(
				"verType"         	=> array_key_exists( "vpc_VerType", $_REQUEST )          ? $_REQUEST['vpc_VerType']          : "No Value Returned",
				"verStatus"       	=> array_key_exists( "vpc_VerStatus", $_REQUEST )        ? $_REQUEST['vpc_VerStatus']        : "No Value Returned",
				"token"           	=> array_key_exists( "vpc_VerToken", $_REQUEST )         ? $_REQUEST['vpc_VerToken']         : "No Value Returned",
				"verSecurLevel"   	=> array_key_exists( "vpc_VerSecurityLevel", $_REQUEST ) ? $_REQUEST['vpc_VerSecurityLevel'] : "No Value Returned",
				"enrolled"        	=> array_key_exists( "vpc_3DSenrolled", $_REQUEST )      ? $_REQUEST['vpc_3DSenrolled']      : "No Value Returned",
				"xid"             	=> array_key_exists( "vpc_3DSXID", $_REQUEST )           ? $_REQUEST['vpc_3DSXID']           : "No Value Returned",
				"acqECI"          	=> array_key_exists( "vpc_3DSECI", $_REQUEST )           ? $_REQUEST['vpc_3DSECI']           : "No Value Returned",
				"authStatus"      	=> array_key_exists( "vpc_3DSstatus", $_REQUEST )        ? $_REQUEST['vpc_3DSstatus']        : "No Value Returned"
			);
			
			return $threeDSecure;
		}
	
	function parseDigitalReceipt() {		
		
			$dReceipt = array(
				"amount" 			=> null2unknown( $_REQUEST['vpc_Amount']),
				"locale"          	=> null2unknown( $_REQUEST['vpc_Locale']),
				"batchNo"         	=> null2unknown( $_REQUEST['vpc_BatchNo']),
				"command"         	=> null2unknown( $_REQUEST['vpc_Command']),
				"message"         	=> null2unknown( $_REQUEST['vpc_Message']),
				"version"         	=> null2unknown( $_REQUEST['vpc_Version']),
				"cardType"        	=> null2unknown( $_REQUEST['vpc_Card']),
				"orderInfo"       	=> null2unknown( $_REQUEST['vpc_OrderInfo']),
				"receiptNo"       	=> null2unknown( $_REQUEST['vpc_ReceiptNo']),
				"merchantID"      	=> null2unknown( $_REQUEST['vpc_Merchant']),
				"authorizeID"     	=> null2unknown( $_REQUEST['vpc_AuthorizeId']),
				"merchTxnRef"     	=> null2unknown( $_REQUEST['vpc_MerchTxnRef']),
				"transactionNo"   	=> null2unknown( $_REQUEST['vpc_TransactionNo']),
				"acqResponseCode" 	=> null2unknown( $_REQUEST['vpc_AcqResponseCode']),
				"txnResponseCode" 	=> null2unknown( $_REQUEST['vpc_TxnResponseCode'])
			);
			
			return $dReceipt;
		}
		/**
		* Handle null values
		*/
	function null2unknown($data) {
			if ($data == "") {
				return "No Value Returned";
			} else {
				return $data;
			}
		}
	
	/*my functions for the payment gateway*/
	function getTransactionStatus($status)
		{			
			//code to get transaction status at the gateway and test results would go here
			switch ( $status ) {
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
	
	/*
		Add message to inslog string
	*/
	function inslog( $s )
	{		
		global $logstr;		
		$logstr .= "\t" . $s . "\n";
	}
	
	/*
		Output inslog and exit;
	*/
	function pmpro_migsExit($redirect = false)
	{
		global $logstr;
		//echo $logstr;
		
		$logstr = var_export($_REQUEST, true) . "Logged On: " . date("m/d/Y H:i:s") . "\n" . $logstr . "\n-------------\n";		
		echo $logstr;
					
		//log in file or email?
		if(defined('PMPRO_INS_DEBUG') && PMPRO_INS_DEBUG === "log")
		{			
			//file
			$loghandle = fopen(dirname(__FILE__) . "/../logs/ipn.txt", "a+");	
			fwrite($loghandle, $logstr);
			fclose($loghandle);
		}
		elseif(defined('PMPRO_INS_DEBUG'))
		{			
			//email
			if(strpos(PMPRO_INS_DEBUG, "@"))
				$log_email = PMPRO_INS_DEBUG;	//constant defines a specific email address
			else
				$log_email = get_option("admin_email");
			
			wp_mail($log_email, get_option("blogname") . " migs INS Log", nl2br($logstr));
		}
			
		
		if(!empty($redirect))
			wp_redirect($redirect);
			header("Location:".$service_host);
		
		exit;
	}

	/*
		Validate the $_POST with TwoCheckout
	*/
	function pmpro_migsValidate($md5Hash,$code) {
		
		if ( strlen($md5Hash) > 0 && $txn_responce != "7" && $txn_responce != "No Value Returned") {			
			// foreach( $_REQUEST as $key => $value ) {
				// if ( $key!="em_ajax" && $key!="action" && $key!="level" && $key != "vpc_SecureHash" && strlen( $value ) > 0) {
					// $md5Hash .= $value;
				// }
			// }
			// echo strtoupper( md5( $md5Hash ));
			// if ( strtoupper( $txnSecureHash ) != strtoupper( md5( $md5Hash )) ) {
				// $authorised = false;
			// } else {		
			
		
				if( $code == "0" ) {									
					$authorised = true;
					
					
				} else {
					$authorised = false;
				}
			//}
		
		} else {
			  $authorised = false;
		}

		return $authorised;
	}
	/*
		Change the membership level. We also update the membership order to include filtered valus.
	*/
	function pmpro_insChangeMembershipLevel($txnref, &$morder)
	{
		//$recurring = pmpro_getParam( 'recurring', 'POST' );
		
		//filter for level
		$morder->membership_level = apply_filters("pmpro_inshandler_level", $morder->membership_level, $morder->user_id);
					
		//fix expiration date		
		if(!empty($morder->membership_level->expiration_number))
		{
			$enddate = "'" . date("Y-m-d", strtotime("+ " . $morder->membership_level->expiration_number . " " . $morder->membership_level->expiration_period, current_time("timestamp"))) . "'";
		}
		else
		{
			$enddate = "NULL";
		}
		
		//get discount code
		$morder->getDiscountCode();
		if(!empty($morder->discount_code))
		{		
			//update membership level
			$morder->getMembershipLevel(true);
			$discount_code_id = $morder->discount_code->id;
		}
		else
			$discount_code_id = "";
		
		//set the start date to current_time('mysql') but allow filters
		$startdate = apply_filters("pmpro_checkout_start_date", "'" . current_time('mysql') . "'", $morder->user_id, $morder->membership_level);
		
		//custom level to change user to
		$custom_level = array(
			'user_id' => $morder->user_id,
			'membership_id' => $morder->membership_level->id,
			'code_id' => $discount_code_id,
			'initial_payment' => $morder->membership_level->initial_payment,
			'billing_amount' => $morder->membership_level->billing_amount,
			'cycle_number' => $morder->membership_level->cycle_number,
			'cycle_period' => $morder->membership_level->cycle_period,
			'billing_limit' => $morder->membership_level->billing_limit,
			'trial_amount' => $morder->membership_level->trial_amount,
			'trial_limit' => $morder->membership_level->trial_limit,
			'startdate' => $startdate,
			'enddate' => $enddate);

		global $pmpro_error;
		if(!empty($pmpro_error))
		{
			echo $pmpro_error;
			inslog($pmpro_error);				
		}
		
		if( pmpro_changeMembershipLevel($custom_level, $morder->user_id) !== false ) {
			//update order status and transaction ids					
			$morder->status = "success";
			$morder->payment_transaction_id = $txnref;
			//if( $recurring )
				$morder->subscription_transaction_id = $txnref;
			//else
				//$morder->subscription_transaction_id = '';*/
			$morder->saveOrder();
			
			//add discount code use
			if(!empty($discount_code) && !empty($use_discount_code))
			{
				$wpdb->query("INSERT INTO $wpdb->pmpro_discount_codes_uses (code_id, user_id, order_id, timestamp) VALUES('" . $discount_code_id . "', '" . $morder->user_id . "', '" . $morder->id . "', '" . current_time('mysql') . "')");
			}									
		
			//save first and last name fields
			if(!empty($_POST['first_name']))
			{
				$old_firstname = get_user_meta($morder->user_id, "first_name", true);
				if(!empty($old_firstname))
					update_user_meta($morder->user_id, "first_name", $_POST['first_name']);
			}
			if(!empty($_POST['last_name']))
			{
				$old_lastname = get_user_meta($morder->user_id, "last_name", true);
				if(!empty($old_lastname))
					update_user_meta($morder->user_id, "last_name", $_POST['last_name']);
			}
												
			//hook
			do_action("pmpro_after_checkout", $morder->user_id);						
		
			//setup some values for the emails
			if(!empty($morder))
				$invoice = new MemberOrder($morder->id);						
			else
				$invoice = NULL;
		
			inslog("CHANGEMEMBERSHIPLEVEL: ORDER: " . var_export($morder, true) . "\n---\n");
		
			$user = get_userdata($morder->user_id);					
			if(empty($user))
				return false;
				
			$user->membership_level = $morder->membership_level;		//make sure they have the right level info
		
			//send email to member
			$pmproemail = new PMProEmail();				
			$pmproemail->sendCheckoutEmail($user, $invoice);
										
			//send email to admin
			$pmproemail = new PMProEmail();
			$pmproemail->sendCheckoutAdminEmail($user, $invoice);
			
			
			return true;
		}
		else
			return false;
	}
	/*failed payment trigger*/
	function pmpro_insFailedPayment( $last_order ) {		
		//hook to do other stuff when payments fail		
		do_action("pmpro_subscription_payment_failed", $last_order);							
	
		//create a blank order for the email			
		$morder = new MemberOrder();
		$morder->user_id = $last_order->user_id;
				
		// Email the user and ask them to update their credit card information			
		$pmproemail = new PMProEmail();				
		$pmproemail->sendBillingFailureEmail($user, $morder);
	
		// Email admin so they are aware of the failure
		$pmproemail = new PMProEmail();				
		$pmproemail->sendBillingFailureAdminEmail(get_bloginfo("admin_email"), $morder);	
	
		inslog("Payment failed. Emails sent to " . $user->user_email . " and " . get_bloginfo("admin_email") . ".");	
		
		return true;
	}
	/*save order function*/
	function pmpro_insSaveOrder( $txnref, $last_order ) {
		global $wpdb;
		//check that txn_id has not been previously processed
		$old_txn = $wpdb->get_var("SELECT payment_transaction_id FROM $wpdb->pmpro_membership_orders WHERE payment_transaction_id = '" . $txnref . "' LIMIT 1");
		
		if( empty( $old_txn ) ) {	
			//hook for successful subscription payments
			do_action("pmpro_subscription_payment_completed");
			//save order
			$morder = new MemberOrder();
			$morder->user_id = $last_order->user_id;
			$morder->membership_id = $last_order->membership_id;			
			$morder->payment_transaction_id = $txnref;
			$morder->subscription_transaction_id = $last_order->subscription_transaction_id;
			$morder->InitialPayment = $last_order->InitialPayment;//$_POST['item_list_amount_1'];	//not the initial payment, but the class is expecting that
			$morder->PaymentAmount = $last_order->PaymentAmount;//$_POST['item_list_amount_1'];
			
			
			$morder->gateway = $last_order->gateway;
			$morder->gateway_environment = $last_order->gateway_environment;
			
			//save
			$morder->saveOrder();
			
			$pmproemail = new PMProEmail();
			$pmproemail->sendInvoiceEmail($user_id, $morder);
			
			$user = get_userdata($morder->user_id);
			$user->membership_level = $morder->membership_level;		//make sure they have the right level info

			//send email to member
			$pmproemail = new PMProEmail();
			$pmproemail->sendCheckoutEmail($user_id, $morder);

			//send email to admin
			$pmproemail = new PMProEmail();
			$pmproemail->sendCheckoutAdminEmail($user_id, $morder);
			$morder->getMemberOrderByID( $morder->id );
			
			
				
			// //email the user their invoice				
			$pmproemail = new PMProEmail();				
			$pmproemail->sendInvoiceEmail( get_userdata( $last_order->user_id ), $morder );	
			if(strpos(PMPRO_INS_DEBUG, "@"))
				$log_email = PMPRO_INS_DEBUG;	//constant defines a specific email address
			else
				$log_email = get_option("admin_email");	
			
			
			inslog( "New order (" . $morder->code . ") created." );
			return true;
		}
		else {
			inslog( "Duplicate Transaction ID: " . $txnref );
			
			return false;
		}
	}
