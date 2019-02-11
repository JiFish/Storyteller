<?php

require_once('config.php');
require_once('logic/commands.php');
require_once('logic/functions.php');
require_once('logic/slack.php');
require_once('logic/fight_logic.php');

// Check the incoming data for the secret slack token
if ($_POST['token'] != SLACK_TOKEN) {
    header('HTTP/1.0 403 Forbidden');
    die('Access Denied. Token does not match');
}

$player = load();
register_commands($player);

// Split the command list by semi-colons. Allows multiple commands to be queued
// Note, some commands will queue other commands
// Note $commandlist is referenced as a global variable
$commandlist = explode(";",html_entity_decode($_POST['text']));
$limittime = false;

// Trim and Filter Trigger word from commands
// From this point onwards all commands are expects to NOT have the trigger word
foreach ($commandlist as $key => $command) {
    $command = trim($command);
    if (stripos($command,$_POST['trigger_word']) === 0) {
        $commandlist[$key] = substr($command,strlen($_POST['trigger_word']));
    }
}

// Filter commands using the disabled list. Do this here so macros and $autorun
// can still use disabled commands
filter_command_list($disabledcommands, $commandlist);

$executions = 0;
while (sizeof($commandlist) > 0)
{
    // Process the next command in the list
    processcommand(array_shift($commandlist),$player);

    // If stamina ever drops to less than 1, the player if dead
    // Stop processing any queued commands and tell the player they are dead
    if ($player['stam'] < 1) {
        sendqmsg("_*You are dead.*_ :skull:",":skull:");
        break;
    }

    // Stop processing the queue after MAX_EXECUTIONS
    if ($executions++ > MAX_EXECUTIONS) {
        break;
    }
}

save($player);
