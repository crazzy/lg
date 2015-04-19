<?php

class LG_Plugin_ssh_openbgpd extends LG_PluginBase {

	private $cmds = array(
		'ping' => '/sbin/ping -w 1 -c 3 __HOST__',
		'traceroute' => '/usr/sbin/traceroute -w 2 -A __HOST__',
		'bgp' => '/usr/bin/sudo /usr/sbin/bgpctl sh ip bgp __HOST__',
		'dns' => '/usr/sbin/dig -t __TYPE__ __HOST__'
	);

	public function __construct($router, $pluginparams = array(), $async_callback=false) {
		parent::__construct($router, $pluginparams, $async_callback);
	}

	private function CheckParams() {
		if(!isset($this->pluginparams['ssh_username'])) return false;
		if(!isset($this->pluginparams['ssh_password'])) return false;
		return true;
	}

	private function RunCMDAsync($cmd) {
		global $async_id;
		$this->_CheckAsync($async_id);
		$ssh = ssh2_connect($this->router);
		if(!is_resource($ssh)) $this->AbortAsync($async_id);
		if(!ssh2_auth_password($ssh, $this->pluginparams['ssh_username'], $this->pluginparams['ssh_password'])) {
			$ssh = null;
			$this->AbortAsync($async_id);
		}
		if(!($stream = ssh2_exec($ssh, $cmd))) {
			$ssh = null;
			$this->AbortAsync($async_id);
		}
		stream_set_blocking($stream, true);
		$i = 0;
		while($buf = fread($stream, 4096)) {
			call_user_func_array($this->async_callback, array($buf, $i, $async_id));
			if($i == 0) {
				call_user_func_array($this->async_callback, array(NULL, NULL, $async_id, 'data'));
			}
			$i += 1;
		}
		fclose($stream);
		$this->_AsyncSetChunks($async_id, $i-1);
		call_user_func_array($this->async_callback, array(NULL, NULL, $async_id, 'complete'));
		ssh2_exec($ssh, "exit");
		$ssh = null;
		die();
	}

	private function RunCMD($cmd) {
		$ssh = ssh2_connect($this->router);
		if(!is_resource($ssh)) return false;
		if(!ssh2_auth_password($ssh, $this->pluginparams['ssh_username'], $this->pluginparams['ssh_password'])) {
			$ssh = null;
			return false;
		}
		if(!($stream = ssh2_exec($ssh, $cmd))) {
			$ssh = null;
			return false;
		}
		stream_set_blocking($stream, true);
		$result = "";
		while($buf = fread($stream, 4096)) {
			$result .= $buf;
		}
		fclose($stream);
		ssh2_exec($ssh, "exit");
		$ssh = null;
		if($result == "") {
			return false;
		}
		return $result;
	}

	public function LookupPing($host) {
		if(!$this->CheckParams()) return false;
		$host = $this->_HostToIP($host);
		if(false === $host) return false;
		$cmd = str_replace('__HOST__', $host, $this->cmds['ping']);
		if(false === $this->async_callback) {
			return $this->RunCMD($cmd);
		}
		else {
			$this->RunCMDAsync($cmd);
		}
	}

	public function LookupTraceroute($host) {
		if(!$this->CheckParams()) return false;
		$host = $this->_HostToIP($host);
		if(false === $host) return false;
		$cmd = str_replace('__HOST__', $host, $this->cmds['traceroute']);
		if(false === $this->async_callback) {
			return $this->RunCMD($cmd);
		}
		else {
			$this->RunCMDAsync($cmd);
		}
	}

	public function LookupBgp($host) {
		if(!$this->CheckParams()) return false;
		$host = $this->_HostToIP($host);
		if(false === $host) return false;
		$cmd = str_replace('__HOST__', $host, $this->cmds['bgp']);
		if(false === $this->async_callback) {
			return $this->RunCMD($cmd);
		}
		else {
			$this->RunCMDAsync($cmd);
		}
	}

	public function LookupDns($host) {
		if(!$this->CheckParams()) return false;
		if($this->_is_IP($host)) {
			$host = $this->_Ip2Rdns($host);
			$type = "ptr";
		}
		else {
			$type = "any";
		}
		$cmd = str_replace('__HOST__', $host, $this->cmds['dns']);
		$cmd = str_replace('__TYPE__', $type, $cmd);
		if(false === $this->async_callback) {
			return $this->RunCMD($cmd);
		}
		else {
			$this->RunCMDAsync($cmd);
		}
	}

};
