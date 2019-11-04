<?php

if (!file_exists('madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
}
include 'madeline.php';
include 'useful.php'; // Useful classes and functions
include 'traits/load.php';
    \AppName\traits\load();

include 'cluster.php'; // Commands cluster
include 'EventHandler.php'; // Main event handler

$settings = json_decode("settings.json", true);
include 'login_helper.php'; // may not start without this
$MadelineProto = new \danog\MadelineProto\API('sessions/bot.session', $settings);
$MadelineProto->async(true);
$MadelineProto->loop(function () use ($MadelineProto) {
    yield $MadelineProto->start();
    yield $MadelineProto->setEventHandler('\EventHandler');
});
$MadelineProto->loop();