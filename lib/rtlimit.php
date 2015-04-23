<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */

require_once "Net/IPv6.php";
require_once "Net/IPv4.php";

class LG_rtlimit {

	private static $ip = false;
	private static $min = false;

	private static function init() {
		if(self::$ip === false) {
			if(!empty($_SERVER['REMOTE_ADDR'])) {
				self::$ip = $_SERVER['REMOTE_ADDR'];
			}
			else {
				self::$ip = '127.0.0.0';
			}
			self::$min = date('i');
			self::push();
		}
	}

	private static function cidr_match($ip, $cidr) {
		if(false !== strpos($ip, ':')) {
			return Net_IPv6::isInNetmask("$ip/128", $cidr);
		}
		else {
			return Net_IPv4::ipInNetwork($ip, $cidr);
		}
	}

	private static function is_whitelisted() {
		global $global_config;
		foreach($global_config['ratelimit_whitelist'] as $wl) {
			if(self::cidr_match(self::$ip, $wl)) return true;
		}
		return false;
	}

	private static function getrate($subject) {
		$min = self::$min;
		$rate = LG_cache::get("rlimit_{$subject}_{$min}");
		if(false===$rate) $rate = 0;
		return $rate;
	}

	private static function setrate($subject, $rate) {
		$min = self::$min;
		LG_cache::set("rlimit_{$subject}_{$min}", $rate, 120);
	}

	private static function push() {
		$ip_rate = self::getrate(self::$ip);
		$gl_rate = self::getrate("global");
		self::setrate(self::$ip, $ip_rate+1);
		self::setrate("global", $gl_rate+1);
	}

	public static function check() {
		global $global_config;
		self::init();
		if(self::is_whitelisted()) return true;
		$ip_rate = self::getrate(self::$ip);
		$gl_rate = self::getrate("global");
		if($gl_rate > $global_config['ratelimit_global']) return false;
		if($ip_rate > $global_config['ratelimit_perip']) return false;
		return true;
	}

};
