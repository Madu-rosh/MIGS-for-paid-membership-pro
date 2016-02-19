<?php
/*
Plugin Name: Migs Gateway for Paid Memberships Pro
Description: Migs Gateway for Paid Memberships Pro
Version: .1
*/

define("PMPRO_MIGSGATEWAY_DIR", dirname(__FILE__));
function add_query_vars_filter( $vars ){
		$vars[] = "status";
		return $vars;
	}
add_filter( 'query_vars', 'add_query_vars_filter' );

//load payment gateway class
require_once(PMPRO_MIGSGATEWAY_DIR . "/classes/class.pmprogateway_migs.php");