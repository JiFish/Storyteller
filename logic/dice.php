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
        $r = max(3, $max);
        break;
    case 'min4':
        $r = max(4, $max);
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


function baseroll(&$player, $statname, $val = 0) {
    $player[$statname] = $val;
    $player['max'][$statname] = 99999;
}


function ff1die(&$player, $statname) {
    $d = dice();
    $player[$statname] = $d+6;
    $player['max'][$statname] = $player[$statname];
    $player['creationdice'] .= ' '.diceemoji($d);
}


function ff2die(&$player, $statname) {
    $d1 = dice(); $d2 = dice();
    $player[$statname] = $d1+$d2+6;
    $player['max'][$statname] = $player[$statname];
    $player['creationdice'] .= ' '.diceemoji($d1).' '.diceemoji($d2);
}


function ffstam(&$player, $statname) {
    $d1 = dice(); $d2 = dice();
    $player[$statname] = $d1+$d2+12;
    $player['max'][$statname] = $player[$statname];
    $player['creationdice'] .= ' '.diceemoji($d1).' '.diceemoji($d2);
}


function loz3die(&$player, $statname) {
    $d1 = dice(); $d2 = dice(); $d3 = dice();
    $player[$statname] = $d1+$d2+$d3+2;
    $player['max'][$statname] = 99999;
    $player['creationdice'] .= ' '.diceemoji($d1).' '.diceemoji($d2).' '.diceemoji($d3);
}


function twodieplus12(&$player, $statname) {
    $d1 = dice(); $d2 = dice();
    $player[$statname] = $d1+$d2+12;
    $player['max'][$statname] = 99999;
    $player['creationdice'] .= ' '.diceemoji($d1).' '.diceemoji($d2);
}


function fffeardie(&$player, $statname) {
    $d = dice();
    $player[$statname] = 0;
    $player['max'][$statname] = $d+6;
    $player['creationdice'] .= ' '.diceemoji($d);
}


function lonewolfendurance(&$player, $statname) {
    $d = dice(0, 9);
    $player[$statname] = $d+20;
    $player['max'][$statname] = $d+20;
    $player['creationdice'] .= ' '.genericemoji($d);
}


function lonewolfcombat(&$player, $statname) {
    $d = dice(0, 9);
    $player[$statname] = $d+10;
    $player['max'][$statname] = 9999;
    $player['creationdice'] .= ' '.genericemoji($d);
}


function lonewolfgold(&$player, $statname) {
    $d = dice(0, 9);
    $player[$statname] = $d;
    $player['max'][$statname] = 50;
    $player['creationdice'] .= ' '.genericemoji($d);
}


function roll_stats(&$player, $stats) {
    foreach ($stats as $statname => $v) {
        if (!isset($v['roll'])) {
            baseroll($player, $statname);
        } elseif (is_numeric($v['roll'])) {
            baseroll($player, $statname, $v['roll']);
        } else {
            $v['roll']($player, $statname);
        }
    }
    $player['creationdice'] = trim($player['creationdice']);
}
