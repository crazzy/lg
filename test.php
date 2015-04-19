<?php
$ip = '2001:db8::567:89ab';
$addr = inet_pton($ip);
$unpack = unpack('H*hex', $addr);
$hex = $unpack['hex'];
$arpa = implode('.', array_reverse(str_split($hex))) . '.ip6.arpa';
echo $arpa . "\n";
