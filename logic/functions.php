<?php

/// ----------------------------------------------------------------------------
/// Functions

// Process command text and call command's function
function processcommand($command, &$player) {
    global $commandslist, $commandsargs, $gamebook;

    $command = pre_processes_magic($command, $player);

    // Split by whitespace
    // $cmd[0] is the command
    $cmd = preg_split('/\s+/', trim($command));
    $cmd[0] = trim(strtolower($cmd[0]));

    // Special case for quick page lookup
    if (is_numeric($cmd[0])) {
        $cmd[1] = $cmd[0];
        $cmd[0] = 'page';
        _cmd_page($cmd, $player);
        return;
    }

    // look for a command function to call
    if (array_key_exists($cmd[0], $commandslist)) {
        $cmd = advanced_command_split($command, $commandsargs[$cmd[0]]);
        if (!$cmd) {
            sendqmsg("Sorry, I didn't understand that command!", ":interrobang:");
        } else {
            call_user_func_array([$gamebook, $commandslist[$cmd[0]]], array($cmd, &$player));
        }
    }
}


function pre_processes_magic($command, &$player) {
    // magic to allow semi-colons
    $command = str_replace("{sc}", ";", $command);

    // magic to substitute dice rolls
    $command = preg_replace_callback(
        '/{([1-9][0-9]?)d([1-9][0-9]{0,2})?([+|\-][1-9][0-9]{0,2})?}/',
        function ($matches) {
            $roll = 0;
            if (!isset($matches[2]) || !$matches[2]) {
                $matches[2] = 6;
            }
            foreach (range(1, $matches[1]) as $i) {
                $roll += rand(1, $matches[2]);
            }
            if (isset($matches[3])) {
                $roll += $matches[3];
            }
            return $roll;
        },
        $command
    );

    // magic to substitute player vars
    // build substitute array
    $sa = array();
    recursive_flatten_player($player, $sa);
    // perform substitution
    $command = preg_replace_callback(
        '/{(.+?)}/',
        function ($matches) use ($sa) {
            if (array_key_exists($matches[1], $sa)) {
                if (is_bool($sa[$matches[1]])) {
                    return $sa[$matches[1]]?'yes':'no';
                }
                return $sa[$matches[1]];
            }
            return $matches[0];
        },
        $command
    );

    return $command;
}


function recursive_flatten_player(&$player, &$return, $keychain="") {
    foreach ($player as $key => $val) {
        if ($key == 'creationdice' || $key == 'stuff') { // skip these
            continue;
        } elseif (is_array($val)) {
            recursive_flatten_player($player[$key], $return, $keychain.$key.'_');
        } else {
            $return[$keychain.$key] = &$player[$key];
        }
    }
}


function advanced_command_split($command, $def) {
    $regex = "/^\\s*(\\S+)";
    foreach ($def as $d) {
        switch ($d) {
        case 'l':  //whole line
            $regex .= "\s+(.+)";
            break;
        case 'ol':  //optional whole line
            $regex .= "(\s+.+)?";
            break;
        case 'oms':  //optional multi string (hard, doesn't match numbers)
            $regex .= "(\s+(?![0-9]+).+?)?";
            break;
        case 'ms':  //multi string (hard, doesn't match numbers)
            $regex .= "\s+((?![0-9]+).+?)";
            break;
        case 'osl':  //optional string (loose, matches numbers)
            $regex .= "(\s+[^\s]+)?";
            break;
        case 'os':  //optional string (hard, doesn't match numbers)
            $regex .= "(\s+(?![0-9]+)[^\s]+)?";
            break;
        case 's':  //string (loose, matches numbers)
            $regex .= "\s+([^\s]+)";
            break;
        case 'on':  //optional number
            $regex .= "(\s+[0-9]+)?";
            break;
        case 'n':  //number
            $regex .= "\s+([0-9]+)";
            break;
        case 'onm':  //optional number modifier
            $regex .= "(\s+[+\-][0-9]+)?";
            break;
        case 'nm':  //number modifier
            $regex .= "\s+([+\-]?[0-9]+)";
            break;
        default:  //misc
            $regex .= $d;
            break;
        }
    }
    $regex .= '\s*$/i';
    $matches = array();

    if (!preg_match($regex, $command, $matches)) {
        return false;
    }

    array_shift($matches);
    $matches = array_map('trim', $matches);
    $matches = array_pad($matches, sizeof($def)+1, null);
    //print_r($matches);
    return $matches;
}


/// register new command
function register_command($name, $function, $args = []) {
    global $commandslist, $commandsargs;

    if (!is_array($commandslist)) {
        $commandslist = array();
    }

    $commandslist[$name] = $function;
    $commandsargs[$name] = $args;
}


// Figure out what rules we are running
function getbook() {
    return BOOK_TYPE;
}


// Load the player array from a serialized array
// If we can't find the file, generate a new character
function load($file = 'save.txt') {
    $save = file_get_contents($file);
    if (!$save) {
        require_once 'logic/roll_character.php';
        $p = roll_character();
    } else {
        $p = unserialize($save);
    }

    return $p;
}


// Serialize and save player array
function save(&$p, $file="save.txt") {
    file_put_contents($file, serialize($p));
}


// Convert number to html entity of dice emoji
function diceemoji($r) {
    if ($r >= 1 && $r <= 6) {
        return mb_convert_encoding('&#'.(9855+$r).';', 'UTF-8', 'HTML-ENTITIES');
    } elseif ($r >= 7 && $r <= 9) {
        return mb_convert_encoding('&#'.(127000+$r).';', 'UTF-8', 'HTML-ENTITIES');
    }

    return "[$r]";
}


// Adds a new command to the command list
function addcommand($cmd) {
    global $commandlist;
    return array_unshift($commandlist, $cmd);
}


/// ----------------------------------------------------------------------------
/// Send message to slack functions

function format_story($page, $text, &$player) {
    require "book.php";

    // Book specific specials
    $gamebook = getbook();
    if ($gamebook == 'sob') {
        $text = str_ireplace('The Banshee', $player['shipname'], $text);
    }
    if ($gamebook == 'sst') {
        $text = str_ireplace('The Traveller', $player['shipname'], $text);
        $text = str_ireplace('Starship Traveller', 'Starship '.substr($player['shipname'], 4), $text);
    }

    // Look for choices in the text and give them bold formatting
    $story = preg_replace('/\(?turn(ing)?( back)? to (section )?[0-9]+\)?/i', '*${0}*', $text);
    $story = preg_replace('/Your (adventure|quest) (is over|ends here|is at an end)\.?/i', '*${0}*', $story);
    $story = preg_replace('/((Add|Subject|Deduct|Regain|Gain|Lose) )?([1-9] (points? )?from your (SKILL|LUCK|STAMINA)|([1-9] )?(SKILL|LUCK|STAMINA) points?|your (SKILL|LUCK|STAMINA))/', '*${0}*', $story);

    // Wrapping and formatting
    $story = str_replace("\n", "\n\n", $story);
    $story = wordwrap($story, 100);
    $story = explode("\n", $story);
    for ($l = 0; $l < sizeof($story); $l++) {
        if (trim($story[$l]) == "") {
            $story[$l] = "> ";
        } else {
            // Prevent code blocks from linebreaking
            if (substr_count($story[$l], '`') % 2 != 0) {
                if (array_key_exists($l+1, $story)) {
                    $story[$l+1] = $story[$l].' '.$story[$l+1];
                    $story[$l] = '';
                    continue;
                }
            }

            // Deal with bold blocks across lines
            if (substr_count($story[$l], '*') % 2 != 0) {
                $story[$l] .= '*';
                if (array_key_exists($l+1, $story)) {
                    $story[$l+1] = "*".$story[$l+1];
                }
            }

            // Italic and quote
            $story[$l] = "> _".$story[$l].'_';
        }
    }
    $story = "> — *$page* —\n".implode("\n", $story);

    return $story;
}


function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (int)($sec + $usec * 1000000);
}


function apply_temp_stats(&$player) {
    foreach ($player['temp'] as $k => $v) {
        if (array_key_exists($k, $player)) {
            $player[$k] += $v;
        }
    }
}


function unapply_temp_stats(&$player) {
    foreach ($player['temp'] as $k => $v) {
        if (array_key_exists($k, $player)) {
            $player[$k] -= $v;
        }
    }
    $player['temp'] = array();
}


function backup_player(&$p) {
    save($p, 'save_backup.txt');
}


function backup_remove() {
    if (file_exists('save_backup.txt')) {
        unlink('save_backup.txt');
    }
}


function restore_player(&$p) {
    if (file_exists('save_backup.txt')) {
        unlink('save.txt');
        copy('save_backup.txt', 'save.txt');
        $p = load();
        return true;
    }
    return false;
}


function basic_num_to_word($num) {
    switch ($num) {
    case 0:
        return 'Zero';
    case 1:
        return 'One';
    case 2:
        return 'Two';
    case 3:
        return 'Three';
    case 4:
        return 'Four';
    case 5:
        return 'Five';
    case 6:
        return 'Six';
    case 7:
        return 'Seven';
    case 8:
        return 'Eight';
    case 9:
        return 'Nine';
    case 10:
        return 'Ten';
    default:
        return $num;
    }
}


// Filter an array of commands by a disabled list and remove entries
// that are on the disabled list
function filter_command_list(&$disabledcommands, &$commandlist) {
    if (isset($disabledcommands) && is_array($disabledcommands)) {
        foreach ($commandlist as $key => $cmd) {
            // Determine end of command word.
            // Either the 1st space of the end of the string
            $cmdend = stripos($cmd, ' ');
            if ($cmdend === false) {
                $cmdend = strlen($cmd);
            }
            foreach ($disabledcommands as $dc) {
                // Look for match
                if (strtolower(substr($cmd, 0, $cmdend)) == strtolower($dc)) {
                    unset($commandlist[$key]);
                    break;
                }
            }
        }
    }
}


function get_stat_from_alias($input, $stats) {
    foreach ($stats as $s => $val) {
        if ($s == $input) {
            $thisstat = $s;
            break;
        }
        if (isset($val['alias'])) {
            foreach ($val['alias'] as $a) {
                if ($a == $input) {
                    $thisstat = $s;
                    break;
                }
            }
        }
    }

    return $thisstat;
}
