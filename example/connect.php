<?php

require_once __DIR__ . '/../lib/Autoloader.php';

BauerBox_NNTP_Autoloader::register();

$nntp = new BauerBox\NNTP\NNTP('us.news.astraweb.com');

$nntp->connect();

print_r($nntp->getCapabilities());

$nntp->disconnect();

exit(0);