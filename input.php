<?php

// This file is useful for testing from the command line e.g.
// php input.txt !echo Hello world!

// You could also put it online by editing .htaccess, allowing
// you to create alternate ways of inputting commands, e.g. a webform

if (isset($argv[1])) {
    $_POST['text'] = implode(" ", array_slice($argv, 1));
} else if (isset($_REQUEST['c'])) {
    $_POST['text'] = $_REQUEST['c'];
} else {
    die("No command!");
}

require_once 'logic/config.php';

$_POST['trigger_word'] = '!';
$_POST['token'] = $config->slack_token;

require 'storyteller.php';
echo "OK";
