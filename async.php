<?php
# Inclusions
require "config.php";
require "util.php";

/* Check valid call and valid input data */
if(!isset($_SERVER['REQUEST_METHOD']) || ($_SERVER['REQUEST_METHOD'] != 'POST')) {
	die();
}
$async_id = isset($_POST['async_id']) ? $_POST['async_id'] : '';
if(!preg_match('/^[a-z0-9]+$/', $async_id)) {
	die();
}
$nextchunk = isset($_POST['nextchunk']) ? $_POST['nextchunk'] : '';
if(!preg_match('/^[0-9]+$/', $nextchunk)) {
	die();
}

/* Check status and if we have more data */
$status = $memcache->get($global_config['memcache_prefix'] . '_async_' . $async_id);
if(false === $status) die("error");
header("X-LG-Async-Status: $status\r\n");
if("error" == $status) die("error");
if("init" == $status) die("init");
if("data" == $status) {
	$data = $memcache->get($global_config['memcache_prefix'] . '_async_' . $async_id . '_ch_' . $nextchunk);
	if(false === $data) {
		header("X-LG-Async-Status: wait\r\n", TRUE);
		die("wait");
	}
	elseif(empty($data)) {
		header("X-LG-Async-Status: wait\r\n", TRUE);
		die("wait");
	}
	else {
		die($data);
	}
}
if("complete" == $status) {
	$tot_chunks = $memcache->get($global_config['memcache_prefix'] . '_async_' . $async_id . '_nochunks');
	if(false === $tot_chunks) {
		header("X-LG-Async-Status: error\r\n", TRUE);
		die("error");
	}
	for($i = $nextchunk; $i <= $tot_chunks; ++$i) {
		echo $memcache->get($global_config['memcache_prefix'] . '_async_' . $async_id . '_ch_' . $i);
	}
	die();
}
