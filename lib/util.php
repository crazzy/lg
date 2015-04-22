<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */

/* Connecting to memcache */
global $global_config;
$memcache = new Memcache();
$memcache->connect($global_config['memcache_host'], $global_config['memcache_port']);

/* Input validation stuff */
function lg_validate_input($input, $type) {
	global $routers;
	global $global_config;
	switch($type) {
		case 'router':
			if(!in_array($input, array_keys($routers))) {
				return false;
			}
			return $input;
			break;
		case 'lookup':
			# IP (both v4/v6)
			$filter_options = array(
				'options' => array(
					'default' => false
				),
				'flags' => FILTER_FLAG_NO_PRIV_RANGE |
						FILTER_FLAG_NO_RES_RANGE
			);
			if(false !== filter_var($input, FILTER_VALIDATE_IP, $filter_options)) {
				return $input;
			}
			# Host name
			if(!mb_check_encoding($input, 'ASCII')) {
				$enc = mb_detect_encoding($input);
				if($enc != 'UTF-8') {
					$input = mb_convert_encoding($input, 'UTF-8', $enc);
				}
				$input = idna_to_ascii($input);
				if(substr($input, 0, 4) != 'xn--') return false;
			}
			if(preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $input) &&
				preg_match("/^.{1,253}$/", $input) &&
				preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $input)
			) {
				return $input;
			}
			return false;
			break;
		case 'lookuptype':
			if(!in_array($input, array(
				LG_FORM_TYPE_PING,
				LG_FORM_TYPE_TRACEROUTE,
				LG_FORM_TYPE_BGP,
				LG_FORM_TYPE_DNS
			))) {
				return false;
			}
			return $input;
			break;
		default:
			return false;
			break;
	}
}

/* Used by themes for adding LG javascripts */
function theme_add_js() {
	echo '<script type="text/javascript" src="/js/jquery-2.1.3.min.js"></script>';
	echo '<script type="text/javascript" src="/js/async.js"></script>';
}

/* Used by themes for showing/hiding check options */
function theme_type_enabled($type) {
	global $global_config;
	return in_array($type, $global_config['checks_enabled']);
}

/* Ratelimit functions */
function rlimit_cidrmatcher($ip, $cidr) {
	if(false!==strpos($ip, ':')) {
		require_once "Net/IPv6.php";
		return Net_IPv6::isInNetmask($ip, $cidr);
	}
	else {
		require_once "Net/IPv4.php";
		return Net_IPv4::ipInNetwork($ip, $cidr);
	}
}
function rlimit_whitelisted($ip) {
	global $global_config;
	foreach($global_config['ratelimit_whitelist'] as $wl) {
		if(rlimit_cidrmatcher($ip, $wl)) return true;
	}
	return false;
}
function rlimit_push() {
	global $memcache;
	global $global_config;
	if(empty($_SERVER['REMOTE_ADDR'])) return false;
	$min = date('i');
	$cur_rate = $memcache->get($global_config['memcache_prefix'] . '_rlimit_' . $_SERVER['REMOTE_ADDR'] . '_' . $min);
	if(false===$cur_rate) $cur_rate = 0;
	$memcache->set($global_config['memcache_prefix'] . '_rlimit_' . $_SERVER['REMOTE_ADDR'] . '_' . $min, $cur_rate+1, 0, 120);
}
function rlimit_check() {
	global $memcache;
	global $global_config;
	if(empty($_SERVER['REMOTE_ADDR'])) return true;
	if(rlimit_whitelisted($_SERVER['REMOTE_ADDR'])) return true;
	$min = date('i');
	$cur_rate = $memcache->get($global_config['memcache_prefix'] . '_rlimit_' . $_SERVER['REMOTE_ADDR'] . '_' . $min);
	if(false===$cur_rate) $cur_rate = 0;
	if($cur_rate >= $global_config['ratelimit_perip']) return false;
	return true;
}
function rlimit_gl_push() {
	global $memcache;
	global $global_config;
	$min = date('i');
	$cur_rate = $memcache->get($global_config['memcache_prefix'] . '_rlimit_global_' . $min);
	if(false===$cur_rate) $cur_rate = 0;
	$memcache->set($global_config['memcache_prefix'] . '_rlimit_global_' . $min, $cur_rate, 0, 120);
}
function rlimit_gl_check() {
	global $memcache;
	global $global_config;
	if(rlimit_whitelisted($_SERVER['REMOTE_ADDR'])) return true;
	$min = date('i');
	$cur_rate = $memcache->get($global_config['memcache_prefix'] . '_rlimit_global_' . $min);
	if(false===$cur_rate) $cur_rate = 0;
	if($cur_rate >= $global_config['ratelimit_global']) return false;
	return true;
}
