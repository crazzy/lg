<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */

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
			# CIDR support
			if(preg_match('/^[a-z0-9:\.]+\/[0-9]{1,3}$/', $input)) {
				$parts = explode('/', $input);
				$cidr=true;
				$input=$parts[0];
			}
			else {
				$cidr=false;
			}
			# IP (both v4/v6)
			$filter_options = array(
				'options' => array(
					'default' => false
				),
				'flags' => FILTER_FLAG_NO_PRIV_RANGE |
						FILTER_FLAG_NO_RES_RANGE
			);
			if(false !== filter_var($input, FILTER_VALIDATE_IP, $filter_options)) {
				if($cidr) {
					return $input . '/' . $parts[1];
				}
				else {
					return $input;
				}
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

