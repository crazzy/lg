<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */

$global_config = array(
	'companyname' => 'MyCompany',
	'companylink' => 'http://example.org',
	'theme' => 'basic',
	'memcache_host' => '127.0.0.1',
	'memcache_port' => '11211',
	'memcache_prefix' => 'lg',
	'ratelimit_global' => 60, # Per minute
	'ratelimit_perip' => 5,
	'ratelimit_whitelist' => array(),
	'checks_enabled' => array('ping', 'traceroute', 'dns', 'bgp')
);
$routers = array(
	'rtr1' => array(
		'address' => '10.0.0.1',
		'plugin' => 'ssh_openbgpd',
		'pluginparams' => array(
			'ssh_username' => 'secret',
			'ssh_password' => 'secret'
		)
	),
	'rtr2' => array(
		'address' => '10.0.0.2',
		'plugin' => 'ssh_openbgpd',
		'pluginparams' => array(
			'ssh_username' => 'secret',
			'ssh_password' => 'secret'
		)
	)
);

