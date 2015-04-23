<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */

class LG_PluginBase {

	protected $router;
	protected $pluginparams;
	protected $async;
	protected $async_id;

	public function __construct($router, $pluginparams = array(), $async=false, $async_id=null) {
		$this->router = $router;
		$this->pluginparams = $pluginparams;
		$this->async = $async;
		$this->async_id = $async_id;
	}

	protected function _FollowCNAME($host, $calls=0) {
		if($calls == 10) return false; # We can't follow CNAMEs in eternity
		$records = dns_get_record($host);
		foreach($records as $record) {
			if($record['class'] != 'IN') continue;
			if(in_array($record['type'], array('A', 'AAAA'))) {
				return $record['host'];
			}
			if($record['type'] == 'CNAME') {
				return $this->_FollowCNAME($record['host'], $calls+1);
			}
		}
		return false;
	}

	public function _is_IP($ip) {
		$filter_options = array(
			'options' => array(
				'default' => false
			),
			'flags' => FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
		if(false !== filter_var($ip, FILTER_VALIDATE_IP, $filter_options)) {
			return true;
		}
		return false;
	}

	public function _Ip2Rdns($ip) {
		if(false !== strpos($ip, ':')) {
			$addr = inet_pton($ip);
			$unpack = unpack('H*hex', $addr);
			$hex = $unpack['hex'];
			return implode('.', array_reverse(str_split($hex))) . '.ip6.arpa';
		}
		else {
			$parts = explode('.', $ip);
			$parts = array_reverse($parts);
			return implode('.', $parts) . ".in-addr.arpa";
		}
	}

	protected function _HostToIP($host) {
		if($this->_is_IP($host)) {
			return $host;
		}
		$records = dns_get_record("$host", DNS_ANY);
		foreach($records as $record) {
			if($record['class'] != 'IN') continue;
			if(in_array($record['type'], array('A', 'AAAA'))) {
				return $record['host'];
			}
			if($record['type'] == 'CNAME') {
				$res = $this->_FollowCNAME($record['host']);
				if(false === $res) continue;
				return $res;
			}
		}
		return false;
	}

	public function _AbortAsync() {
		$this->_AsyncSetStatus("error");
		die();
	}

	protected function _AsyncSetChunks($chunks) {
		LG_cache::set("async_{$this->async_id}_nochunks", $chunks);
	}

	protected function _AsyncSetStatus($status) {
		LG_cache::set("async_{$this->async_id}", $status);
	}

	protected function _AsyncWriteData($chunkno, $data) {
		LG_cache::set("async_{$this->async_id}_ch_{$chunkno}", $data);
	}

	/* These functions should be overloaded by sub-classes */

	public function LookupPing($host) {
		return false;
	}

	public function LookupDns($host, $type) {
		return false;
	}

	public function LookupTraceroute($host) {
		return false;
	}

	public function LookupBgp($host) {
		return false;
	}
};
