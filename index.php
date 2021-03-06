<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */
# Constants
define('LG_FORMACTION', '/');
define('LG_FORM_LOOKUP', 'lg_lookup');
define('LG_FORM_ROUTER', 'lg_router');
define('LG_FORM_LOOKUPTYPE', 'lg_lookuptype');
define('LG_FORM_TYPE_PING', 'lg_type_ping');
define('LG_FORM_TYPE_TRACEROUTE', 'lg_type_traceroute');
define('LG_FORM_TYPE_BGP', 'lg_type_bgp');
define('LG_FORM_TYPE_DNS', 'lg_type_dns');

# Inclusions
require "config.php";
require "lib/cache.php";
require "lib/rtlimit.php";
require "lib/util.php";
require "plugins/base.php";

if(!isset($_SERVER['REQUEST_METHOD']) || ($_SERVER['REQUEST_METHOD'] != 'POST')) {
	require "themes/{$global_config['theme']}/front.php";
	die();
}
else {
	/* Checking of input data */
	$required = array(LG_FORM_LOOKUP, LG_FORM_ROUTER, LG_FORM_LOOKUPTYPE);
	$error = false;
	foreach($required as $req) {
		if(!isset($_POST[$req])) {
			$error = true;
			break;
		}
	}
	if($error) {
		require "themes/{$global_config['theme']}/error_input.php";
		die();
	}
	$router = lg_validate_input($_POST[LG_FORM_ROUTER], 'router');
	$lookup = lg_validate_input($_POST[LG_FORM_LOOKUP], 'lookup');
	$lookuptype = lg_validate_input($_POST[LG_FORM_LOOKUPTYPE], 'lookuptype');

	/* Check whether we're running async */
	if(isset($_POST['async'])) {
		$async = true;
	}
	else {
		$async = false;
	}

	/* Ratelimiting */
	if(!LG_rtlimit::check()) {
		if($async) {
			die("ratelimit");
		}
		else {
			require "themes/{$global_config['theme']}/error_ratelimit.php";
			die();
		}
	}

	/* Async? Prepare for that! */
	if($async) {
		$async_id = uniqid();
		LG_cache::set("async_{$async_id}", 'init');
		set_time_limit(0);
		ignore_user_abort(true);
		header("Connection: close\r\n");
		header("Content-Encoding: text/html\r\n");
		ob_start();
		echo $async_id . "\n";
		$size = ob_get_length();
		header("Content-Length: $size\r\n", TRUE);
		ob_end_flush();
		ob_flush();
		flush();
	}
	else {
		$async_id = null;
	}

	/* Loading of plugin */
	$pluginname = $routers[$router]['plugin'];
	require "plugins/{$pluginname}.php";
	$pluginname = "LG_Plugin_{$pluginname}";
	if(isset($routers[$router]['pluginparams'])) {
		$pluginparams = $routers[$router]['pluginparams'];
	}
	else {
		$pluginparams = array();
	}
	$plugin = new $pluginname($routers[$router]['address'], $pluginparams, $async, $async_id);

	/* Call plugin */
	$result = "";
	switch($lookuptype) {
		case LG_FORM_TYPE_PING:
			$result = $plugin->LookupPing($lookup);
			break;
		case LG_FORM_TYPE_TRACEROUTE:
			$result = $plugin->LookupTraceroute($lookup);
			break;
		case LG_FORM_TYPE_BGP:
			$result = $plugin->LookupBgp($lookup);
			break;
		case LG_FORM_TYPE_DNS:
			if($plugin->_is_IP($lookup)) {
				$lookup_host = $plugin->_Ip2Rdns($lookup);
				$dns_type = 'ptr';
			}
			else {
				$lookup_host = $lookup;
				$dns_type = 'any';
			}
			$result = $plugin->LookupDns($lookup_host, $dns_type);
			break;
	}
	if(false === $async) {
		if(false === $result) {
			require "themes/{$global_config['theme']}/error_plugin.php";
		}
		else {
			require "themes/{$global_config['theme']}/result.php";
		}
	}
	else {
		$plugin->_AbortAsync();
	}
}
