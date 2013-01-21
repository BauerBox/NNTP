<?php

use BauerBox\NNTP\Command\AuthinfoCommand;
use BauerBox\NNTP\Command\CapabilitiesCommand;
use BauerBox\NNTP\Command\HelpCommand;
use BauerBox\NNTP\Command\ListCommand;
use BauerBox\NNTP\Command\GroupCommand;
use BauerBox\NNTP\Command\ListgroupCommand;
use BauerBox\NNTP\Command\HeadCommand;
use BauerBox\NNTP\Command\OverCommand;

require_once __DIR__ . '/../lib/Autoloader.php';

BauerBox_NNTP_Autoloader::register();

//$nntp = new BauerBox\NNTP\NNTP('us.news.astraweb.com');
$nntp = new BauerBox\NNTP\NNTP('news.giganews.com'); // 480 - Auth Required on HELP
$nntp->connect();

echo "VERSION: " . $nntp->getVersion() . PHP_EOL;

if ($nntp->executeCommand(new AuthinfoCommand(array('USER' => ''))) === AuthinfoCommand::STATUS_SEND_MORE) {
    if ($nntp->executeCommand(new AuthinfoCommand(array('PASS' => ''))) === AuthinfoCommand::STATUS_OK) {
        echo "Authentication Completed" . PHP_EOL;
    }
}

echo "HELP: " . $nntp->executeCommand(new HelpCommand()) . PHP_EOL;
echo "CAPABILITIES: " . $nntp->executeCommand(new CapabilitiesCommand()) . PHP_EOL;


/*
$groups = $nntp->executeCommand(new ListCommand('@^alt\.bin@', array('ACTIVE')));

foreach ($groups as $group) {
    echo "GROUP: {$group}" . PHP_EOL;
}
*/

$group = $nntp->executeCommand(new GroupCommand('alt.binaries.boneless'));

$nntp->executeCommand(new HeadCommand(array($group->getHighWaterMark())));

$nntp->disconnect();
exit(0);
