<?php

/// ----------------------------------------------------------------------------
/// Functions
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


// Figure out what rules we are running
function getbook() {
    return BOOK_TYPE;
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


/// ----------------------------------------------------------------------------
/// Send message to slack functions
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
