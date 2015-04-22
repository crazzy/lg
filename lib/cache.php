<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */

/* Connecting to memcache */

class LG_cache {

	private static $memcache = false;

	private static function init() {
		global $global_config;
		if(self::$memcache === false) {
			self::$memcache = new Memcache();
			self::$memcache->connect($global_config['memcache_host'], $global_config['memcache_port']);
		}
	}

	private static function prefix($key) {
		global $global_config;
		return $global_config['memcache_prefix'] . '_' . $key;
	}

	public static function get($key) {
		self::init();
		$key = self::prefix($key);
		return self::$memcache->get($key);
	}

	public static function set($key, $value, $expire=600) {
		self::init();
		$key = self::prefix($key);
		self::$memcache->set($key, $value, 0, $expire);
	}

};
