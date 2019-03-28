<?php

function dice() {
    switch (CHARACTER_ROLLS) {
    case 'normal':
        $r = rand(1, 6);
        break;
    case 'd5+1':
        $r = rand(2, 6);
        break;
    case 'min3':
        $r = max(3, rand(1, 6));
        break;
    case 'min4':
        $r = max(4, rand(1, 6));
        break;
    case 'roll2':
        $r = max(rand(1, 6), rand(1, 6));
        break;
    case 'roll3':
        $r = max(rand(1, 6), rand(1, 6), rand(1, 6));
        break;
    case 'weighted':
        $r = min(6, rand(1, 8));
        break;
    case 'average':
        global $averageflip;
        if (!isset($averageflip)) {
            $averageflip = false;
        }
        $r = $averageflip?3:4;
        $averageflip = !$averageflip;
        break;
    case 'all1':
        $r = 1;
        break;
    case 'all2':
        $r = 2;
        break;
    case 'all3':
        $r = 3;
        break;
    case 'all4':
        $r = 4;
        break;
    case 'all5':
        $r = 5;
        break;
    case 'all6':
        $r = 6;
        break;
    case 'all7':
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
    $player['temp'][$statname] = 0;
    $player['max'][$statname] = 99999;
}


function ff1die(&$player, $statname) {
    $d = dice();
    $player[$statname] = $d+6;
    $player['temp'][$statname] = 0;
    $player['max'][$statname] = $player[$statname];
    $player['creationdice'][] = $d;
}


function ff2die(&$player, $statname) {
    $d1 = dice(); $d2 = dice();
    $player[$statname] = $d1+$d2+6;
    $player['temp'][$statname] = 0;
    $player['max'][$statname] = $player[$statname];
    $player['creationdice'][] = $d1;
    $player['creationdice'][] = $d2;
}


function ffstam(&$player, $statname) {
    $d1 = dice(); $d2 = dice();
    $player[$statname] = $d1+$d2+12;
    $player['temp'][$statname] = 0;
    $player['max'][$statname] = $player[$statname];
    $player['creationdice'][] = $d1;
    $player['creationdice'][] = $d2;
}


function loz3die(&$player, $statname) {
    $d1 = dice(); $d2 = dice(); $d3 = dice();
    $player[$statname] = $d1+$d2+$d3+2;
    $player['temp'][$statname] = 0;
    $player['max'][$statname] = 99999;
    $player['creationdice'][] = $d1;
    $player['creationdice'][] = $d2;
    $player['creationdice'][] = $d3;
}


function twodieplus12(&$player, $statname) {
    $d1 = dice(); $d2 = dice();
    $player[$statname] = $d1+$d2+12;
    $player['temp'][$statname] = 0;
    $player['max'][$statname] = 99999;
    $player['creationdice'][] = $d1;
    $player['creationdice'][] = $d2;
}


function fffeardie(&$player, $statname) {
    $d = dice();
    $player[$statname] = 0;
    $player['temp'][$statname] = 0;
    $player['max'][$statname] = $d+6;
    $player['creationdice'][] = $d;
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
}
