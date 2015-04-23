<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */

require "plugins/base_ssh.php";

class LG_Plugin_ssh_cisco extends LG_PluginBase_SSH {

	private $cmds = array(
		'ping' => 'ping __HOST__',
		'traceroute' => 'traceroute __HOST__',
		'bgp_v4' => 'sh ip bgp __HOST__',
		'bgp_v6' => 'sh bgp ipv6 uni __HOST__'
	);

	public function __construct($router, $pluginparams = array(), $async=false, $async_id=null) {
		parent::__construct($router, $pluginparams, $async, $async_id);
	}

	public function LookupPing($host) {
		$host = $this->_HostToIP($host);
		if(false === $host) return false;
		$cmd = str_replace('__HOST__', $host, $this->cmds['ping']);
		return $this->RunCMD($cmd);
	}

	public function LookupTraceroute($host) {
		$host = $this->_HostToIP($host);
		if(false === $host) return false;
		$cmd = str_replace('__HOST__', $host, $this->cmds['traceroute']);
		return $this->RunCMD($cmd);
	}

	public function LookupBgp($host) {
		$host = $this->_HostToIP($host);
		if(false === $host) return false;
		if(false !== strpos($host, ':')) {
			$cmd = $this->cmds['bgp_v6'];
		}
		else {
			$cmd = $this->cmds['bgp_v4'];
		}
		$cmd = str_replace('__HOST__', $host, $cmd);
		return $this->RunCMD($cmd);
	}

};
