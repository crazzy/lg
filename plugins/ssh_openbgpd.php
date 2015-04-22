<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */

class LG_Plugin_ssh_openbgpd extends LG_PluginBase {

	private $cmds = array(
		'ping' => '/sbin/ping -w 1 -c 3 __HOST__',
		'traceroute' => '/usr/sbin/traceroute -w 2 -A __HOST__',
		'bgp' => '/usr/bin/sudo /usr/sbin/bgpctl sh ip bgp __HOST__',
		'dns' => '/usr/sbin/dig -t __TYPE__ __HOST__'
	);

	public function __construct($router, $pluginparams = array(), $async=false, $async_id=null) {
		parent::__construct($router, $pluginparams, $async, $async_id);
	}

	private function CheckParams() {
		if(!isset($this->pluginparams['ssh_username'])) return false;
		if(!isset($this->pluginparams['ssh_password'])) return false;
		return true;
	}

	private function RunCMD($cmd) {
		$ssh = ssh2_connect($this->router);
		if(!is_resource($ssh)) {
			if($this->async) {
				$this->AbortAsync();
			}
			else {
				return false;
			}
		}
		if(!ssh2_auth_password($ssh, $this->pluginparams['ssh_username'], $this->pluginparams['ssh_password'])) {
			$ssh = null;
			if($this->async) {
				$this->AbortAsync();
			}
			else {
				return false;
			}
		}
		if(!($stream = ssh2_exec($ssh, $cmd))) {
			$ssh = null;
			if($this->async) {
				$this->AbortAsync();
			}
			else {
				return false;
			}
		}
		stream_set_blocking($stream, true);
		$result = "";
		$i = 0;
		while($buf = fread($stream, 4096)) {
			if($this->async) {
				$this->_AsyncWriteData($i, $buf);
				if($i == 0) {
					$this->_AsyncSetStatus('data');
				}
				$i += 1;
			}
			else {
				$result .= $buf;
			}
		}
		fclose($stream);
		ssh2_exec($ssh, "exit");
		unset($ssh);
		if($this->async) {
			$this->_AsyncSetChunks($i-1);
			$this->_AsyncSetStatus('complete');
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
