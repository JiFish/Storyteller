<?php

/// ----------------------------------------------------------------------------
/// Functions
function recursive_flatten_player(&$player, &$return, $keychain="") {
    foreach ($player as $key => $val) {
        if (is_array($val)) {
            recursive_flatten_player($player[$key], $return, $keychain.$key.'_');
        } else {
            $return[$keychain.$key] = &$player[$key];
        }
    }
}


// Figure out what rules we are running
function getbook() {
    global $config;
    return $config->book_rules;
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


// Convert number to html entity of playing card
function cardemoji($r) {
    if ($r == 0) {
        return mb_convert_encoding("&#127183;", 'UTF-8', 'HTML-ENTITIES');
    } elseif ($r <= 13) {
        $start = [127136, 127152, 127168, 127184][rand(0, 3)];
        return mb_convert_encoding('&#'.($start+$r).';', 'UTF-8', 'HTML-ENTITIES');
    }

    return "[$r]";
}


// Convert number to html entity of black circled number
function genericemoji($r) {
    if ($r == 0) {
        return mb_convert_encoding("&#9471;", 'UTF-8', 'HTML-ENTITIES');
    } elseif ($r <= 10) {
        return mb_convert_encoding('&#'.(10101+$r).';', 'UTF-8', 'HTML-ENTITIES');
    } elseif ($r <= 20) {
        return mb_convert_encoding('&#'.(9440+$r).';', 'UTF-8', 'HTML-ENTITIES');
    }

    return "[$r]";
}


// Convert number to html entity of white circled number
function genericemojiwhite($r) {
    if ($r == 0) {
        return mb_convert_encoding("&#9450;", 'UTF-8', 'HTML-ENTITIES');
    } elseif ($r <= 20) {
        return mb_convert_encoding('&#'.(9311+$r).';', 'UTF-8', 'HTML-ENTITIES');
    } elseif ($r <= 50) {
        return mb_convert_encoding('&#'.(12860+$r).';', 'UTF-8', 'HTML-ENTITIES');
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


// Returns FALSE if the item doesn't exist
// Returns an array if there are multiple matches
// Returns a string with the removed item if successful
function smart_remove_from_list(&$list, $item) {
    $item = strtolower($item);
    // lazy item search
    $foundkey = null;
    $foundlist = array();
    foreach ($list as $k => $i) {
        // An exact match always drops
        if ($item == strtolower($i)) {
            $foundkey = $k;
            $foundlist = array($i);
            break;
        }
        // otherwise look for partial matches
        elseif (strpos(strtolower($i), $item) !== false) {
            $foundkey = $k;
            $foundlist[] = $i;
        }
    }

    // Failed to remove
    if (sizeof($foundlist) < 1) {
        return false;
    } elseif (sizeof($foundlist) > 1 && items_are_different($foundlist)) {
        return $foundlist;
    }

    // Successful remove
    $removeditem = $list[$foundkey];
    unset($list[$foundkey]);
    // Re-index the array elements
    $list = array_values($list);
    return $removeditem;
}


function items_are_different($testarray) {
    $match = strtolower(current($testarray));
    foreach ($testarray as $val) {
        if ($match !== strtolower($val)) {
            return true;
        }
    }
    return false;
}
