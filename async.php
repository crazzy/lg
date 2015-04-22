<?php
/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */
# Inclusions
require "config.php";
require "lib/cache.php";
require "lib/util.php";

function lg_async_error() {
	header("X-LG-Async-Status: error\r\n", TRUE);
	die("error");
}

/* Check valid call and valid input data */
if(!isset($_SERVER['REQUEST_METHOD']) || ($_SERVER['REQUEST_METHOD'] != 'POST')) {
	lg_async_error();
}
$async_id = isset($_POST['async_id']) ? $_POST['async_id'] : '';
if(!preg_match('/^[a-z0-9]+$/', $async_id)) {
	lg_async_error();
}
$nextchunk = isset($_POST['nextchunk']) ? $_POST['nextchunk'] : '';
if(!preg_match('/^[0-9]+$/', $nextchunk)) {
	lg_async_error();
}

/* Check status and if we have more data */
$status = LG_cache::get("async_$async_id");
if(false === $status) die("error");
header("X-LG-Async-Status: $status\r\n");
switch($status) {
	case 'init':
		die("init");
		break;
	case 'error':
		lg_async_error();
		break;
	case 'data':
		$data = LG_cache::get("async_{$async_id}_ch_{$nextchunk}");
		if((false === $data) || empty($data)) {
			header("X-LG-Async-Status: wait\r\n", TRUE);
			die("wait");
		}
		else {
			die($data);
		}
		break;
	case 'complete':
		$tot_chunks = LG_cache::get("async_{$async_id}_nochunks");
		if(false === $tot_chunks) {
			lg_async_error();
		}
		for($i = $nextchunk; $i <= $tot_chunks; ++$i) {
			echo LG_cache::get("async_{$async_id}_ch_{$i}");
		}
		break;
	default:
		die("error");
		break;
}


