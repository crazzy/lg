<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */

class LG_PluginBase_SSH extends LG_PluginBase {

	public function __construct($router, $pluginparams = array(), $async=false, $async_id=null) {
		parent::__construct($router, $pluginparams, $async, $async_id);
	}

	private function CheckParams() {
		if(!isset($this->pluginparams['ssh_username'])) return false;
		if(!isset($this->pluginparams['ssh_password'])) return false;
		return true;
	}

	protected function RunCMD($cmd) {
		if(!$this->CheckParams()) {
			if($this->async) {
				$this->AbortAsync();
			}
			else {
				return false;
			}
		}
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

};
