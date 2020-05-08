<?php

function dice($min = 1, $max = 6) {
    global $config;

    switch ($config->character_rolls) {
    case 'normal':
        $r = rand($min, $max);
        break;
    case 'reroll':
        $r = rand($min+1, $max);
        break;
    case 'min3':
        $r = max(3, rand($min, $max));
        break;
    case 'min4':
        $r = max(4, rand($min, $max));
        break;
    case 'roll2':
        $r = max(rand($min, $max), rand($min, $max));
        break;
    case 'roll3':
        $r = max(rand($min, $max), rand($min, $max), rand($min, $max));
        break;
    case 'average':
        $r = ceil(($max-$min)/2);
        break;
    case 'max':
        $r = $max;
        break;
    case 'heaven':
        $r = 7;
        break;
    default:
        die(CHARACTER_ROLLS.' is not a valid roll type.');
    }
    return $r;
}


function roll_stats(&$player, $stats) {
    foreach ($stats as $statname => $v) {
        // Current value
        if (!isset($v['roll'])) {
            $player[$statname] = 0;
        } elseif (is_numeric($v['roll']) || is_bool($v['roll'])) {
            $player[$statname] = $v['roll'];
        } else {
            list($r, $s) = roll_dice_string($v['roll'], true);
            $player[$statname] = $r;
            $player['creationdice'] .= ' '.$s;
        }
        // Maximum value
        if (!isset($v['max'])) {
            $player['max'][$statname] = 99999;
        } elseif (is_numeric($v['max'])) {
            $player['max'][$statname] = $v['max'];
        } elseif ($v['max'] == 'roll') {
            $player['max'][$statname] = $player[$statname];
        } else {
            list($r, $s) = roll_dice_string($v['max'], true);
            $player['max'][$statname] = $r;
            $player['creationdice'] .= ' '.$s;
        }
    }
    $player['creationdice'] = trim($player['creationdice']);
}


function roll_dice_string($dice, $rolling_character = false) {
    $roll = 0;
    $str = "";

    if (is_numeric($dice)) {
        $numdice = $dice;
        $sides = 6;
        $bonus = false;
    } else {
        preg_match('/^([1-9]?[0-9])?d((?:[1-9][0-9]{0,2}|%))?([+|\-][1-9][0-9]{0,2})?/', $dice, $matches);
        if (!$matches) {
            return ['roll' => 0, 'str' => "Dice string not understood"];
        }
        $matches = array_pad($matches, 4, null);
        $sides = ($matches[2]?$matches[2]:6);
        $numdice = ($matches[1]?$matches[1]:1);
        $numdice = min($numdice, 100);
        $bonus = ($matches[3]?$matches[3]:false);
    }
    // Roll Dice
    foreach (range(1, $numdice) as $i) {
        // Determine if we're using the fixed dice function
        if ($rolling_character) {
            $rollfunc = 'dice';
        } else {
            $rollfunc = 'rand';
        }
        // Percentile (0-9) dice. Also used for Lone Wolf
        if ($sides == '%') {
            $r = $rollfunc(0, 9);
        }
        // All other dice
        else {
            $r = $rollfunc(1, $sides);
        }
        $roll += $r;
        if ($sides == 6) {
            $str .= diceemoji($r)." ";
        } elseif ($sides <= 20) {
            $str .= genericemoji($r)." ";
        } else {
            $str .= "[$r] ";
        }
    }
    if ($bonus && !$rolling_character) {
        $roll += $bonus;
        $str .= $bonus;
    }
    return [$roll, rtrim($str)];
}
