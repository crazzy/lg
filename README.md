# lg - Looking Glass

This is my looking glass software I created because I thought the existing ones sucked. It has the regular features of a looking glass, ping, traceroute, bgp and dns lookups. What sets this software apart in my opinion is that it's easily configurable to use different router softwares on different routers, and it's very easy to add new plugins for not yet supported router types.

Another very interesting feature is the support for asynchronous lookups, with which you can see the traceroute appearing line by line in front of you, just as if you were running it in a console. Of course if javascript is not available in the browser it'll work just fine as well, you will just have to wait for the traceroute to complete before you see the output.

## Requirements

* PHP 5.3.3+
* Net/IPv4 and Net/IPv6 from Pear
* php5-memcache
* php5-ssh2 (Most probably, but can be avoided with a custom plugin.)
* Memcached
* Jquery (bundled)

## Configuration

See config-sample.php, copy it as config.php and edit it. It's pretty self-explaining.

## Demo

A live example of this software running can be seen at [AS202044](lg.as202044.net)
