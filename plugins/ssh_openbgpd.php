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

	private function RunCMD($cmd) {
		global $async;
		if(false !== $async) {
			global $async_id;
			$this->_CheckAsync($async_id);
		}
		$ssh = ssh2_connect($this->router);
		if(!is_resource($ssh)) {
			if(false !== $async) {
				$this->AbortAsync($async_id);
			}
			else {
				return false;
			}
		}
		if(!ssh2_auth_password($ssh, $this->pluginparams['ssh_username'], $this->pluginparams['ssh_password'])) {
			$ssh = null;
			if(false !== $async) {
				$this->AbortAsync($async_id);
			}
			else {
				return false;
			}
		}
		if(!($stream = ssh2_exec($ssh, $cmd))) {
			$ssh = null;
			if(false !== $async) {
				$this->AbortAsync($async_id);
			}
			else {
				return false;
			}
		}
		stream_set_blocking($stream, true);
		$result = "";
		$i = 0;
		while($buf = fread($stream, 4096)) {
			if(false !== $async) {
				call_user_func_array($this->async_callback, array($buf, $i, $async_id));
				if($i == 0) {
					call_user_func_array($this->async_callback, array(NULL, NULL, $async_id, 'data'));
				}
				$i += 1;
			}
			else {
				$result .= $buf;
			}
		}
		fclose($stream);
		ssh2_exec($ssh, "exit");
		$ssh = null;
		if(false !== $async) {
			$this->_AsyncSetChunks($async_id, $i-1);
			call_user_func_array($this->async_callback, array(NULL, NULL, $async_id, 'complete'));
			die();
		}
		else {
			if($result == "") {
				return false;
			}
			return $result;
		}
	}

	public function LookupPing($host) {
		if(!$this->CheckParams()) return false;
		$host = $this->_HostToIP($host);
		if(false === $host) return false;
		$cmd = str_replace('__HOST__', $host, $this->cmds['ping']);
		return $this->RunCMD($cmd);
	}

	public function LookupTraceroute($host) {
		if(!$this->CheckParams()) return false;
		$host = $this->_HostToIP($host);
		if(false === $host) return false;
		$cmd = str_replace('__HOST__', $host, $this->cmds['traceroute']);
		return $this->RunCMD($cmd);
	}

	public function LookupBgp($host) {
		if(!$this->CheckParams()) return false;
		$host = $this->_HostToIP($host);
		if(false === $host) return false;
		$cmd = str_replace('__HOST__', $host, $this->cmds['bgp']);
		return $this->RunCMD($cmd);
	}

	public function LookupDns($host, $type) {
		if(!$this->CheckParams()) return false;
		$cmd = str_replace('__HOST__', $host, $this->cmds['dns']);
		$cmd = str_replace('__TYPE__', $type, $cmd);
		return $this->RunCMD($cmd);
	}

};
