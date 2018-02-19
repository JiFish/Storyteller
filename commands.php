<?php

/// This is messy. But it was quick.
function register_commands()
{
    register_command('look',        '_cmd_look');
    register_command('page',        '_cmd_page');
    register_command('eat',         '_cmd_eat');
    register_command('skill',       '_cmd_stat_adjust');
    register_command('stam',        '_cmd_stat_adjust');
    register_command('stamina',     '_cmd_stat_adjust');
    register_command('luck',        '_cmd_stat_adjust');
    register_command('prov',        '_cmd_stat_adjust');
    register_command('provisons',   '_cmd_stat_adjust');
    register_command('gold',        '_cmd_stat_adjust');
    register_command('weapon',      '_cmd_stat_adjust');
    register_command('weaponbonus', '_cmd_stat_adjust');
    register_command('pay',         '_cmd_pay');
    register_command('spend',       '_cmd_pay');
    register_command('luckyescape', '_cmd_luckyescape');
    register_command('le',          '_cmd_luckyescape');
    register_command('get',         '_cmd_get');
    register_command('take',        '_cmd_get');
    register_command('drop',        '_cmd_drop');
    register_command('lose',        '_cmd_drop');
    register_command('use',         '_cmd_drop');
    register_command('roll',        '_cmd_roll');
    register_command('test',        '_cmd_test');
    register_command('newgame',     '_cmd_newgame');
    register_command('info',        '_cmd_info');
    register_command('status',      '_cmd_info');
    register_command('stats',       '_cmd_stats');
    register_command('s',           '_cmd_stats');
    register_command('stuff',       '_cmd_stuff');
    register_command('i',           '_cmd_stuff');
    register_command('help',        '_cmd_help');
    register_command('?',           '_cmd_help');
    register_command('helpmore',    '_cmd_helpmore');
    register_command('fight',       '_cmd_fight');
    register_command('edgar',       '_cmd_edgar');
}

//// !look
function _cmd_look($cmd, &$player)
{
    require("book.php");
    sendqmsg($book[$player['lastpage']]);
}

//// !page <num> / !<num> (Read page from book)
function _cmd_page($cmd, &$player)
{
    if (!is_numeric($cmd[1])) {
        return;
    }

    require("book.php");
    if (array_key_exists($cmd[1], $book)) {
        $player['lastpage'] = $cmd[1];
        $story = $book[$cmd[1]];

        // Attempt to find pages that give you only one choice
        // and add that page to the command list
        preg_match_all('/turn to ([0-9]+)/i', $story, $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) == 1 &&
            stripos($story,"if ") === false &&
            stripos($story,"you may ") === false) {
                addcommand("!".$matches[0][1]);
            }

        // Look for choices in the text and give them bold formatting
        $story = preg_replace('/\(?turn(ing)? to [0-9]+\)?/i', '*${0}*', $story);
    } else {
        $story = "**PAGE NOT FOUND**";
    }

    sendqmsg($story);
}

//// !eat
function _cmd_eat($cmd, &$player)
{
    if ($player['prov'] < 1) {
        sendqmsg("*No food to eat!*");
    } else {
        $player['prov']--;
        $player['stam']+=4;
        if ($player['stam'] > $player['max']['stam']) {
            $player['stam'] = $player['max']['stam'];
        }
        sendqmsg("*Yum! Stamina now ".$player['stam']." and ".$player['prov']." provisions left.*",":bread:");
    }
}

//// Various statistic adjustment commands
function _cmd_stat_adjust($cmd, &$player)
{
    // Aliases. Allow people to give long-form stat names if they like
    if ($cmd[0] == 'stamina') $cmd[0] = 'stam';
    if ($cmd[0] == 'provisons') $cmd[0] = 'prov';
    if ($cmd[0] == 'weaponbonus') $cmd[0] = 'weapon';

    // Setup the details of the ajustment
    // $statref is a reference to the stat that will be changed
    // $max is the maximum we will allow it to be set to
    // $statname is what we will send back to slack
    // $val is the adjustment or new value
    if ($cmd[1] == "max" && is_numeric($cmd[2])) {
        $statref = &$player['max'][$cmd[0]];
        $max = 99;
        $statname = 'maximum '.$cmd[0];
        $val = $cmd[2];
    } else if (is_numeric($cmd[1])) {
        $statref = &$player[$cmd[0]];
        $max = $player['max'][$cmd[0]];
        $statname = $cmd[0];
        $val = $cmd[1];
    } else {
        // invalid command
        return;
    }

    // apply adjustment to stat
    if ($val[0] == "+") {
        $val = substr($val,1);
        $statref += (int)$val;
        if ($statref > $max) {
            $statref = $max;
        }
        sendqmsg("*Added $val to $statname, now $statref.*");
    } else if ($val[0] == "-") {
        $val = substr($val,1);
        $statref -= (int)$val;
        if ($statref < 0) {
            $statref = 0;
        }
        sendqmsg("*Subtracted $val from $statname, now $statref.*");
    } else {
        $statref = (int)$val;
        if ($statref > $max) {
            $statref = $max;
        }
        sendqmsg("*Set $statname to $statref.*");
    }
}

//// !pay (alias for losing gold)
function _cmd_pay($cmd, &$player)
{
    if (!is_numeric($cmd[1])) {
        return;
    } else if ($player['gold'] < $cmd[1]) {
        sendqmsg("* You don't have ".$cmd[1]." gold!");
    } else {
        addcommand("!gold -".$cmd[1]);
    }
}

//// !luckyescape (roll for running away)
function _cmd_luckyescape($cmd, &$player)
{
    $d1 = rand(1,6);
    $d2 = rand(1,6);
    $e1 = diceemoji($d1);
    $e2 = diceemoji($d2);
    $out = "_Testing luck to negate escape damage!_\n";
    $target = $player['luck'];
    $player['luck']--;

    if ($d1+$d2 <= $target) {
        $out .= "_*You are lucky*_\n_(_ $e1 $e2 _ vs $target, Remaining luck ".$player['luck'].")_\n";
        $out .= "_No stamina loss!_";
        $icon = ":four_leaf_clover:";
    }
    else {
        $player['stam'] -= 2;
        if ($player['stam'] < 0) $player['stam'] = 0;
        $out .= "_*You are unlucky.*_\n_(_ $e1 $e2 _ vs $target, Remaining luck ".$player['luck'].")_\n";
        $out .= "_*Lost 2 stamina!* Remaining stamina ".$player['stam']."_";
        $icon = ':lightning:';
    }

    sendqmsg($out,$icon);
}

//// !get / !take (add item to inventory/stuff list)
function _cmd_get($cmd, &$player)
{
    if (!$cmd[1]) {
        return;
    }

    // Turn the params back in to one string, since items can have whitespace
    // in their name
    $cmd[1] = implode(" ",array_slice($cmd, 1));

    // Attempt to catch cases where people get or take gold or provisions
    // and turn them in to stat adjustments
    // "x Gold"
    preg_match_all('/^([0-9]+) gold/i', $cmd[1], $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("!gold +".$matches[0][1]);
        return;
    }
    // "provision"
    if (strtolower($cmd[1]) == "provision") {
        addcommand("!prov +1");
        return;
    }
    // "x provisions"
    preg_match_all('/^([0-9]+) provisions/i', $cmd[1], $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("!prov +".$matches[0][1]);
        return;
    }

    // Otherwise just append it to the stuff array
    $player['stuff'][] = $cmd[1];
    sendqmsg("*Got the ".$cmd[1]."!*",":moneybag:");
}

//// !drop / !lose / !use
function _cmd_drop($cmd, &$player)
{
    if (!$cmd[1]) {
        return;
    }

    $cmd[1] = implode(" ",array_slice($cmd, 1));

    // TODO: This is code repetition
    // Attempt to catch cases where people get or take gold or provisions
    // and turn them in to stat adjustments
    // "x Gold"
    preg_match_all('/^([0-9]+) gold/i', $cmd[1], $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("!gold -".$matches[0][1]);
        return;
    }
    // "provision"
    if (strtolower($cmd[1]) == "provision") {
        addcommand("!prov -1");
        return;
    }
    // "x provisions"
    preg_match_all('/^([0-9]+) provisions/i', $cmd[1], $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("!prov -".$matches[0][1]);
        return;
    }

    $dropped = false;
    // search stuff list from item and remove it
    foreach($player['stuff'] as $k => $i)
    {
        if (strtolower($i) == strtolower($cmd[1])) {
            unset($player['stuff'][$k]);
            switch ($cmd[0]) {
                case 'lose':
                    sendqmsg("*Lost the ".$cmd[1]."!*");
                    break;
                case 'drop':
                    sendqmsg("*Dropped the ".$cmd[1]."!*",":put_litter_in_its_place:");
                    break;
                case 'use':
                    sendqmsg("*Used the ".$cmd[1]."!*");
                    break;
            }
            $dropped = true;
            break;
        }
    }
    // Nothing was dropped
    if (!$dropped) {
        sendqmsg("*No ".$cmd[1]." to loose!*");
    }
}

//// !roll [x] (roll xd6)
function _cmd_roll($cmd, &$player)
{
    if (!is_numeric($cmd[1]) || $cmd[1] < 1 || $cmd[1] > 100) {
        $cmd[1] = 1;
    }
    $out = "Result:";

    $t = 0;
    for ($a = 0; $a < $cmd[1]; $a++) {
        $r = rand(1,6);
        $emoji = diceemoji($r);
        $out .= " $emoji ($r)";
        $t += $r;
    }
    if ($cmd[1] > 1) {
        $out .= "\n*Total: $t*";
    }
    sendqmsg($out,":game_die:");
}

//// !test <luck/skill/stam> (run a skill test)
function _cmd_test($cmd, &$player)
{
    // Alias for stam
    if ($cmd[1] == "stamina") $cmd[1] = "stam";

    // Check for valid test types
    if ($cmd[1] != "luck" && $cmd[1] != "skill" && $cmd[1] != "stam") {
        return;
    }

    // Setup outcome pages to read if provided
    if (is_numeric($cmd[2])) {
        $success_page = "!".$cmd[2];
    }
    if (is_numeric($cmd[3])) {
        $fail_page = "!".$cmd[3];
    }

    // roll 2d6 and set target from stat name ($cmd[1])
    $d1 = rand(1,6);
    $d2 = rand(1,6);
    $e1 = diceemoji($d1);
    $e2 = diceemoji($d2);
    $target = $player[$cmd[1]];

    // Check roll versus target number
    if ($d1+$d2 <= $target) {
        if ($cmd[1] == "luck") {
            $player['luck']--;
            sendqmsg("_*You are lucky*_\n_(_ $e1 $e2 _ vs $target, Remaining luck ".$player['luck'].")_",':four_leaf_clover:');
        } else if ($cmd[1] == "skill") {
            sendqmsg("_*You are skillful*_\n_(_ $e1 $e2 _ vs $target)_",':runner:');
        } else {
            sendqmsg("_*You are strong enough*_\n_(_ $e1 $e2 _ vs $target)_",':muscle:');
        }
        // Show follow up page
        if (isset($success_page)) {
            addcommand($success_page);
        }
    }
    else {
        if ($cmd[1] == "luck") {
            $player['luck']--;
            sendqmsg("_*You are unlucky.*_\n_(_ $e1 $e2 _ vs $target, Remaining luck ".$player['luck'].")_",':lightning:');
        } else if ($cmd[1] == "skill") {
            sendqmsg("_*You are not skillful*_\n_(_ $e1 $e2 _ vs $target)_",':warning:');
        } else {
            sendqmsg("_*You are not strong enough*_\n_(_ $e1 $e2 _ vs $target)_",':warning:');
        }
        // Show follow up page
        if (isset($fail_page)) {
            addcommand($fail_page);
        }
    }
}

//// !newgame (roll new character)
function _cmd_newgame($cmd, &$player)
{
    $player = roll_character($cmd[1],$cmd[2]);
    send_charsheet("*NEW CHARACTER!*\nType `".$_POST['trigger_word']."0` to begin, or `".$_POST['trigger_word']."newgame` to roll again.");
    send_stuff();
}

//// !info / !status (send character sheet and inventory)
function _cmd_info($cmd, &$player)
{
    send_charsheet();
    send_stuff();
}

//// !stats / !s (send character sheet)
function _cmd_stats($cmd, &$player)
{
    send_charsheet();
}

//// !stuff / !i (send inventory)
function _cmd_stuff($cmd, &$player)
{
    send_stuff();
}

//// !help (send basic help)
function _cmd_help($cmd, &$player)
{
    $help = file_get_contents('resources/help.txt');
    // Replace "!" with whatever the trigger word is
    $help = str_replace("!",$_POST['trigger_word'],$help);
    sendqmsg($help);
}

//// !helpmore (send advanced help)
function _cmd_helpmore($cmd, &$player)
{
    $help = file_get_contents('resources/helpmore.txt');
    // Replace "!" with whatever the trigger word is
    $help = str_replace("!",$_POST['trigger_word'],$help);
    senddirmsg($help);
}

//// !fight [name] <skill> <stamina> [maxrounds] (run fight logic)
function _cmd_fight($cmd, &$player)
{
    // No opponent name given
    if (is_numeric($cmd[1]) && is_numeric($cmd[2])) {
        $m = "opponent";
        $mskill = $cmd[1];
        $mstam = $cmd[2];
        $maxrounds = $cmd[3];
    }
    // Opponent name given
    else if ($cmd[1] && is_numeric($cmd[2]) && is_numeric($cmd[3])) {
        $m = $cmd[1];
        $mskill = $cmd[2];
        $mstam = $cmd[3];
        $maxrounds = $cmd[4];
    }
    // Invalid inputs
    else {
        return;
    }

    if (!is_numeric($maxrounds)) {
        $maxrounds = 50;
    }

    $out = "";
    $round = 1;
    while ($player['stam'] > 0 && $mstam > 0) {
        $mroll = rand(1,6);
        $proll = rand(1,6);
        $mattack = $mskill+$mroll;
        $pattack = $player['skill']+$player['weapon']+$proll;

        $memoji = diceemoji($mroll);
        $pemoji = diceemoji($proll);

        if ($pattack > $mattack) {
            $out .= "_You hit the $m. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            $mstam -= 2;
        }
        else if ($pattack < $mattack) {
            $out .= "_The $m hits you! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            $player['stam'] -= 2;
        }
        else {
            $out .= "_You avoid each others blows. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
        }

        if ($round++ == $maxrounds) {
            break;
        }
    }
    if ($mstam < 1) {
        $out .= "_*You have defeated the $m!*_\n";
        $out .= "_(Remaining stamina: ".$player['stam'].")_";
    }
    else if ($player['stam'] < 1) {
        $out .= "_*The $m has defeated you!*_\n";
    }
    else {
        if ($maxrounds > 1) {
            $out .= "_*Combat stopped after $maxrounds rounds.*_\n";
        }
        $out .= "_($m's remianing stamina: $mstam. Your remaining stamina: ".$player['stam'].")_";
    }
    sendqmsg($out,":crossed_swords:");
}

//// !edgar (Easter Egg)
function _cmd_edgar($cmd, &$player)
{
    $player = array('name' => 'Edgar the Sorcerer',
                    'icon' => ':male_mage:',
                    'skill' => 10, 'stam' => 14, 'luck' => 7,
                    'prov' => 5, 'gold' => 20, 'weapon' => 1,
                    'max' => array ('skill' => 10, 'stam' => 18, 'luck' => 8,
                                    'prov' => 99999, 'gold' => 99999,
                                    'weapon' => 99999),
                    'stuff' => array('Magic Staff (+1)','Cotten Robes',
                                     'Lantern','Potion of Skill'),
                    'lastpage' => 1);
    send_charsheet("You have found the secret character!");
    send_stuff();
}
