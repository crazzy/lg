<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */

require "plugins/base_ssh.php";

class LG_Plugin_ssh_quagga extends LG_PluginBase_SSH {

	private $cmds = array(
		'ping' => '/bin/ping -w 1 -c 3 __HOST__',
		'traceroute' => '/usr/sbin/traceroute -w 2 -A __HOST__',
		'bgp' => "/usr/bin/sudo /usr/bin/vtysh -c 'sh __IPPROTO__ bgp __HOST__'",
		'dns' => '/usr/bin/dig -t __TYPE__ __HOST__'
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
		if(false!==strpos($host, ':')) {
			$proto = "ipv6";
		}
		else {
			$proto = "ip";
		}
		$cmd = str_replace('__HOST__', $host, $this->cmds['bgp']);
		$cmd = str_replace('__IPPROTO__', $proto, $cmd);
		return $this->RunCMD($cmd);
	}

	public function LookupDns($host, $type) {
		$cmd = str_replace('__HOST__', $host, $this->cmds['dns']);
		$cmd = str_replace('__TYPE__', $type, $cmd);
		return $this->RunCMD($cmd);
	}

};
