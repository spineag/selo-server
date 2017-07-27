<?php
/**
 * setup memcached
 */

////// ORIGINAL

$memcached = new Memcached();
$memcached->addServer(MEMCACHED_HOST, MEMCACHED_PORT) or die("Could not connect");
