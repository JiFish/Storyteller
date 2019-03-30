<?php

require_once 'config.php';
require_once 'logic/functions.php';
require_once 'logic/slack.php';
require_once 'logic/fight_logic.php';

// Check the incoming data for the secret slack token
if ($_POST['token'] != SLACK_TOKEN) {
    header('HTTP/1.0 403 Forbidden');
    die('Access Denied. Token does not match');
}

// Gamebook Object
require_once 'logic/gamebooks/'.getbook().'.php';
$bookclass = 'Book_'.getbook();
$gamebook = new $bookclass($player);

// Split the command list by semi-colons. Allows multiple commands to be queued
// Note, some commands will queue other commands
// Note $commandlist is referenced as a global variable
$commandlist = explode(";", html_entity_decode($_POST['text']));

// Trim and Filter Trigger word from commands
// From this point onwards all commands are expected to NOT have the trigger word
foreach ($commandlist as $key => $command) {
    $command = trim($command);
    if (stripos($command, $_POST['trigger_word']) === 0) {
        $commandlist[$key] = substr($command, strlen($_POST['trigger_word']));
    }
}

$gamebook->processCommandList($commandlist);
$gamebook->savePlayer();
