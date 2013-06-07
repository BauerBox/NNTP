<?php

use BauerBox\NNTP\Command\GroupCommand;
use BauerBox\NNTP\Command\HeadCommand;
use BauerBox\NNTP\Command\ListCommand;
use BauerBox\NNTP\Group\Group;
use BauerBox\NNTP\NNTP;

require_once __DIR__ . '/../lib/Autoloader.php';

BauerBox_NNTP_Autoloader::register();

//$nntp = new BauerBox\NNTP\NNTP('us.news.astraweb.com');
$nntp = new NNTP(__DIR__ . '/parameters.ini', true);
$nntp->connect();

$authenticated = $nntp->authenticate();

if (false === $authenticated) {
    $nntp->disconnect();
    die('Auth failed' . PHP_EOL);
}

// Get list of groups
$groups = $nntp->getActiveGroups();

echo "Found " . count($groups) . " groups" . PHP_EOL;

$sort = array();

foreach ($groups as $group) {
    $sort["{$group}"] = $group->getMaxPosts();
}

arsort($sort);

foreach ($sort as $group => $posts) {
    //echo "GROUP {$group} has {$posts} posts" . PHP_EOL;
    if (false === $nntp->isConnected()) {
        $nntp->connect();
    }

    $group = $nntp->executeCommand(new GroupCommand(array($group)));

    if ($group instanceof Group && $group->isActive()) {
        //echo "  - ACTIVE GROUP" . PHP_EOL;
        $meta = $nntp->executeCommand(new HeadCommand(array($group->getHighWaterMark())));

        if (null === $meta || $meta === HeadCommand::STATUS_FAILED || false === array_key_exists('Date', $meta)) {
            //echo "  - Invalid Last Post" . PHP_EOL;
            echo "{$group}\t{$posts}\tNULL" . PHP_EOL;
            continue;
        }

        try {
            $lastPost = new \DateTime($meta['Date']);
            echo "{$group}\t{$posts}\t{$lastPost->format('Y-m-d H:i:s')}" . PHP_EOL;
        } catch (\Exception $e) {
            $lastPost = 'NULL';
            echo "{$group}\t{$posts}\t{$lastPost}" . PHP_EOL;
        }

        //echo "  - Last Post: " . $lastPost->format('Y-m-d H:i:s') . PHP_EOL;
        //echo "  - Subject  : " . $meta['Subject'] . PHP_EOL;
        //echo "  - Bytes    : " . $meta['Bytes'] . PHP_EOL;
        //echo "  - Lines    : " . $meta['Lines'] . PHP_EOL;
        //print_r($meta);
    } else {
        //echo "  - INACTIVE GROUP :: SKIPPING" . PHP_EOL;
    }
}

$nntp->disconnect();
exit(0);
