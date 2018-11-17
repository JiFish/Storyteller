<?php

require('fight_logic.php');

/// This is messy. But it was quick.
function register_commands($gamebook)
{
    register_command('look',        '_cmd_look');
    register_command('page',        '_cmd_page',['n']);
    register_command('background',  '_cmd_background');
    register_command('eat',         '_cmd_eat');
    register_command('pay',         '_cmd_pay',['n']);
    register_command('spend',       '_cmd_pay',['n']);
    register_command('luckyescape', '_cmd_luckyescape');
    register_command('le',          '_cmd_luckyescape');
    register_command('get',         '_cmd_get',['l']);
    register_command('take',        '_cmd_get',['l']);
    register_command('drop',        '_cmd_drop',['l']);
    register_command('lose',        '_cmd_drop',['l']);
    register_command('use',         '_cmd_drop',['l']);
    register_command('roll',        '_cmd_roll',['on']);
    register_command('test',        '_cmd_test',['s','on','on']);
    register_command('ng',          '_cmd_newgame',['osl','osl','osl','osl','osl']);
    register_command('newgame',     '_cmd_newgame',['osl','osl','osl','osl','osl']);
    register_command('info',        '_cmd_info');
    register_command('status',      '_cmd_info');
    register_command('stats',       '_cmd_stats');
    register_command('s',           '_cmd_stats');
    register_command('stuff',       '_cmd_stuff');
    register_command('i',           '_cmd_stuff');
    register_command('help',        '_cmd_help');
    register_command('?',           '_cmd_help');
    register_command('helpmore',    '_cmd_helpmore');
    register_command('helpall',     '_cmd_helpall');
    register_command('fight',       '_cmd_fight',['oms','n','n','osl']);
    register_command('critfight',   '_cmd_critfight',['oms','n','os','on']);
    register_command('bonusfight',  '_cmd_bonusfight',['oms','n','n','n']);
    register_command('vs',          '_cmd_vs',['ms','n','n','ms','n','n']);
    register_command('fighttwo',    '_cmd_fighttwo',['ms','n','n','oms','on','on']);
    register_command('attack',      '_cmd_attack',['n','on']);
    register_command('a',           '_cmd_attack',['n','on']);
    register_command('echo',        '_cmd_echo',['l']);
    register_command('randpage',    '_cmd_randpage',['n','on','on','on','on','on','on','on']);
    
    // Stats commands
    $stats = array('skill', 'stam', 'stamina', 'luck', 'prov',
                   'provisons', 'gold', 'weapon', 'weaponbonus', 'bonus');
    if ($gamebook == 'rtfm') {
        $stats = array_merge($stats,['goldzagors','gz']);
    }
    foreach($stats as $s) {
        register_command($s, '_cmd_stat_adjust',['os','nm']);
    }
}

//// !look
function _cmd_look($cmd, &$player)
{
    require("book.php");
    $story = format_story($player['lastpage'],$book[$player['lastpage']]);
    sendqmsg($story);
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

        // Exclude pages using 'if ', 'you may' or 'otherwise'
        // This isn't perfect, but will prevent many false matches
        if (stripos($story,"if ") === false && stripos($story,"you may ") === false
            && stripos($story,"otherwise") === false) {
            // Attempt to find pages that give you only one choice
            // Find pages with only one turn to and add that page to the command list
            preg_match_all('/turn to ([0-9]+)/i', $story, $matches, PREG_SET_ORDER, 0);
            if (sizeof($matches) == 1) {
                    addcommand("!".$matches[0][1]);
                }
            // Attempt to find pages that end the story, kill the player if found
            elseif (sizeof($matches) < 1 &&
                    preg_match('/Your (adventure|quest) (is over|ends here)\./i', $story, $matches)) {
                $player['stam'] = 0;
            }
        }

        $story = format_story($player['lastpage'],$story);
    } else {
        sendqmsg("*".$cmd[1].": PAGE NOT FOUND*",":interrobang:");
        return;
    }

    if (file_exists('images'.DIRECTORY_SEPARATOR.$player['lastpage'].'.jpg')) {
        sendimgmsg($story,'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'images/'.$player['lastpage'].'.jpg');
    } else {
        sendqmsg($story);
    }
}

//// !background
function _cmd_background($cmd, &$player)
{
    require("book.php");
    $story = format_story(0,$book[0]);
    senddirmsg($story);
}

//// !eat
function _cmd_eat($cmd, &$player)
{
    if ($player['prov'] < 1) {
        sendqmsg("*No food to eat!*",':interrobang:');
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
    if ($cmd[0] == 'bonus') $cmd[0] = 'weapon';
    if ($cmd[0] == 'gz') $cmd[0] = 'goldzagors';

    // Setup the details of the ajustment
    // $statref is a reference to the stat that will be changed
    // $max is the maximum we will allow it to be set to
    // $statname is what we will send back to slack
    // $val is the adjustment or new value
    if (strtolower($cmd[1]) == "max") {
        $statref = &$player['max'][$cmd[0]];
        $max = 999;
        $statname = 'maximum '.$cmd[0];
    } elseif (!$cmd[1]) {
        $statref = &$player[$cmd[0]];
        $max = $player['max'][$cmd[0]];
        $statname = $cmd[0];
    }
    $val = $cmd[2];

    // apply adjustment to stat
    $oldval = $statref;
    if ($val[0] == "+") {
        $val = substr($val,1);
        $statref += (int)$val;
        if ($statref > $max) {
            $statref = $max;
        }
        $msg = "*Added $val to $statname, now $statref.*";
    } else if ($val[0] == "-") {
        $val = substr($val,1);
        $statref -= (int)$val;
        // Allow negative weapon bonuses, but others have a min 0.
        if ($statref < 0 && $cmd[0] != 'weapon') {
            $statref = 0;
        }
        $msg = "*Subtracted $val from $statname, now $statref.*";
    } else {
        $statref = (int)$val;
        if ($statref > $max) {
            $statref = $max;
        }
        $msg = "*Set $statname to $statref.*";
    }
    if ($oldval < $statref && $statname == 'stam') {
        $icon = ':medical_symbol:';
    } elseif ($oldval >= $statref && $statname == 'stam') {
        $icon = ':face_with_head_bandage:';
    } elseif ($oldval < $statref && $statname == 'luck') {
        $icon = ':four_leaf_clover:';
    } elseif ($oldval >= $statref && $statname == 'luck') {
        $icon = ':lightning:';
    } elseif ($statname == 'gold' || $statname == 'goldzagors') {
        $icon = ':moneybag:';
    } elseif ($statname == 'weapon') {
        $icon = ':dagger_knife:';
    } elseif ($statname == 'prov') {
        $icon = ':bread:';
    } else {
        $icon = ':green_book:';
    }
    sendqmsg($msg,$icon);
}

//// !pay (alias for losing gold)
function _cmd_pay($cmd, &$player)
{
    if (!is_numeric($cmd[1])) {
        return;
    } else if ($player['gold'] < $cmd[1]) {
        sendqmsg("* You don't have ".$cmd[1]." gold! *",':interrobang');
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
        $player['stam'] -= 1;
        if ($player['stam'] < 0) $player['stam'] = 0;
        $out .= "_*You are lucky*_\n_(_ $e1 $e2 _ vs $target, Remaining luck ".$player['luck'].")_\n";
        $out .= "_*Lost 1 stamina!* Remaining stamina ".$player['stam']."_";
        $icon = ":four_leaf_clover:";
    }
    else {
        $player['stam'] -= 3;
        if ($player['stam'] < 0) $player['stam'] = 0;
        $out .= "_*You are unlucky.*_\n_(_ $e1 $e2 _ vs $target, Remaining luck ".$player['luck'].")_\n";
        $out .= "_*Lost 3 stamina!* Remaining stamina ".$player['stam']."_";
        $icon = ':lightning:';
    }

    sendqmsg($out,$icon);
}

//// !get / !take (add item to inventory/stuff list)
function _cmd_get($cmd, &$player)
{
    $item = $cmd[1];
    // Attempt to catch cases where people get or take gold or provisions
    // and turn them in to stat adjustments
    // "x Gold"
    preg_match_all('/^([0-9]+) gold/i', $item, $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("!gold +".$matches[0][1]);
        return;
    }
    // "provision"
    if (strtolower($item) == "provision") {
        addcommand("!prov +1");
        return;
    }
    // "x provisions"
    preg_match_all('/^([0-9]+) provisions/i', $item, $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("!prov +".$matches[0][1]);
        return;
    }

    // Prevent duplicate entries
    if (array_search(strtolower($item), array_map('strtolower', $player['stuff'])) !== false) {
        sendqmsg("*You already have '".$item."'. Try giving this item a different name.*",':interrobang:');
        return;
    }

    // Otherwise just append it to the stuff array
    $player['stuff'][] = $item;
    sendqmsg("*Got the ".$item."!*",":school_satchel:");
}

//// !drop / !lose / !use
function _cmd_drop($cmd, &$player)
{
    $drop = $cmd[1];
    // TODO: This is code repetition
    // Attempt to catch cases where people get or take gold or provisions
    // and turn them in to stat adjustments
    // "x Gold"
    preg_match_all('/^([0-9]+) gold/i', $drop, $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("!gold -".$matches[0][1]);
        return;
    }
    // "provision"
    if (strtolower($drop) == "provision") {
        addcommand("!prov -1");
        return;
    }
    // "x provisions"
    preg_match_all('/^([0-9]+) provisions/i', $drop, $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("!prov -".$matches[0][1]);
        return;
    }

    // lazy item search
    $foundkey = null;
    $foundlist = array();
    foreach($player['stuff'] as $k => $i)
    {
        // An exact match always drops
        if ($drop == strtolower($i)) {
            $foundkey = $k;
            $foundlist = array($i);
            break;
        }
        // otherwise look for partial matches
        elseif (strpos(strtolower($i),$drop) !== false) {
            $foundkey = $k;
            $foundlist[] = $i;
        }
    }

    if (sizeof($foundlist) < 1) {
        sendqmsg("*'".$drop."' didn't match anything. Check your !stuff.*",':interrobang:');
    } elseif (sizeof($foundlist) > 1) {
        sendqmsg("*Which did you want to ".$cmd[0]."? ".implode(", ",$foundlist)."*",':interrobang:');
    } else {
        $i = $player['stuff'][$foundkey];
        unset($player['stuff'][$foundkey]);
        switch ($cmd[0]) {
            case 'lose':
                sendqmsg("*Lost the ".$i."!*");
                break;
            case 'drop':
                sendqmsg("*Dropped the ".$i."!*",":put_litter_in_its_place:");
                break;
            case 'use':
                sendqmsg("*Used the ".$i."!*");
                break;
        }
    }
}

//// !roll [x] (roll xd6)
function _cmd_roll($cmd, &$player)
{
    if (!isset($cmd[1]) || $cmd[1] > 100) {
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
        $out .= " *Total: $t*";
    }
    sendqmsg($out,":game_die:");
}

//// !test <luck/skill/stam> (run a skill test)
function _cmd_test($cmd, &$player)
{
    $cmd[1] = strtolower($cmd[1]);
    // Alias for stam
    if ($cmd[1] == "stamina") $cmd[1] = "stam";

    // Check for valid test types
    if ($cmd[1] != "luck" && $cmd[1] != "skill" && $cmd[1] != "stam") {
        sendqmsg("_*Don't know how to test ".$cmd[1]."_",':interrobang:');
        return;
    }

    // Setup outcome pages to read if provided
    if (isset($cmd[2])) {
        $success_page = "!".$cmd[2];
    }
    if (isset($cmd[3])) {
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
            sendqmsg("_*You are not skillful*_\n_(_ $e1 $e2 _ vs $target)_",':tired_face:');
        } else {
            sendqmsg("_*You are not strong enough*_\n_(_ $e1 $e2 _ vs $target)_",':sweat:');
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
    $cmd = array_pad($cmd, 6, '?');
    $player = roll_character($cmd[1],$cmd[2],$cmd[3],$cmd[4],$cmd[5]);
    send_charsheet($player, "_*NEW CHARACTER!*_");
    send_stuff($player);
}

//// !info / !status (send character sheet and inventory)
function _cmd_info($cmd, &$player)
{
    send_charsheet($player);
    send_stuff($player);
}

//// !stats / !s (send character sheet)
function _cmd_stats($cmd, &$player)
{
    send_charsheet($player);
}

//// !stuff / !i (send inventory)
function _cmd_stuff($cmd, &$player)
{
    send_stuff($player);
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

//// !helpall (send complete help)
function _cmd_helpall($cmd, &$player)
{
    $help = file_get_contents('resources/helpall.txt');
    // Replace "!" with whatever the trigger word is
    $help = str_replace("!",$_POST['trigger_word'],$help);
    senddirmsg($help);
}

//// !fight [name] <skill> <stamina> [maxrounds] (run fight logic)
function _cmd_fight($cmd, &$player)
{
    if ($cmd[1]) {
        $m = $cmd[1];
    } else {
        $m = "Opponent";
    }
    $mskill = $cmd[2];
    $mstam = $cmd[3];
    if (isset($cmd[4])) {
        $maxrounds = $cmd[4];
    } else {
        $maxrounds = 50;
    }

    $out = run_fight($player,$m,$mskill,$mstam,$maxrounds);
    sendqmsg($out,":crossed_swords:");
}

//// !critfight [name] <skill> [who] [critchance] (run crit fight logic)
function _cmd_critfight($cmd, &$player)
{
    $m = ($cmd[1]?$cmd[1]:"Opponent");
    $mskill = $cmd[2];
    $critsfor = ($cmd[3]?$cmd[3]:'me');
    $critchance = ($cmd[4]?$cmd[4]:2);
    
    if (!in_array($critsfor,['both','me'])) {
        $critsfor = 'me';
    }
    if (!is_numeric($critchance) || $critchance < 1 || $critchance > 6) {
        $critchance = 2;
    }

    $out = "_*You".($critsfor == 'both'?' both':'')." have to hit critical strikes!* ($critchance in 6 chance)_\n";
    $out .= run_fight($player,$m,$mskill,999,50,$critsfor,$critchance);
    sendqmsg($out,":crossed_swords:");
}

//// !bonusfight [name] <skill> <stamina> <bonusdamage> (run bonus attack fight logic)
function _cmd_bonusfight($cmd, &$player)
{
    $m = ($cmd[1]?$cmd[1]:"Opponent");
    $mskill = $cmd[2];
    $mstam = $cmd[3];
    $bonusdmg = $cmd[4];

    $out .= run_fight($player,$m,$mskill,$mstam,50,'nobody',null,null,null,$bonusdmg);
    sendqmsg($out,":crossed_swords:");
}

//// !vs <name 1> <skill 1> <stamina 1> <name 2> <skill 2> <stamina 2> 
function _cmd_vs($cmd, &$player)
{
    // Invalid inputs
    if (!is_numeric($cmd[2]) || !is_numeric($cmd[3]) || !is_numeric($cmd[5]) || !is_numeric($cmd[6])) {
        return;
    }
    
    $m = $cmd[1];
    $mskill = $cmd[2];
    $mstam = $cmd[3];
    $m2 = $cmd[4];
    $m2skill = $cmd[5];
    $m2stam = $cmd[6];

    $maxrounds = 50;

    $out = "";
    $round = 1;
    while ($mstam > 0 && $m2stam > 0) {
        $mroll = rand(1,6);
        $m2roll = rand(1,6);
        $mattack = $mskill+$mroll;
        $m2attack = $m2skill+$m2roll;

        $memoji = diceemoji($mroll);
        $m2emoji = diceemoji($m2roll);

        if ($m2attack > $mattack) {
            $out .= "_$m2 hit $m. (_ $m2emoji _ $m2attack vs _ $memoji _ $mattack)_\n";
            $mstam -= 2;
        }
        else if ($m2attack < $mattack) {
            $out .= "_$m hit $m2. (_ $memoji _ $mattack vs _ $m2emoji _ $m2attack)_\n";
            $m2stam -= 2;
        }
        else {
            $out .= "_$m and $m2 each others blows. (_ $memoji _ $mattack vs _ $m2emoji _ $m2attack)_\n";
        }

        if ($round++ == $maxrounds) {
            break;
        }
    }
    if ($mstam < 1) {
        $out .= "_*$m2 defeated $m!*_\n";
        $out .= "_(Remaining stamina: ".$m2stam.")_";
    }
    else if ($m2stam < 1) {
        $out .= "_*$m defeated $m2!*_\n";
        $out .= "_(Remaining stamina: ".$mstam.")_";
    }
    else {
        if ($maxrounds > 1) {
            $out .= "_*Combat stopped after $maxrounds rounds.*_\n";
        }
        $out .= "_($m's remaining stamina: $mstam. $m2's remaining stamina: $m2stam)_";
    }
    sendqmsg($out,":wrestlers:");
}

//// !fighttwo <name 1> <skill 1> <stamina 1> [<name 2> <skill 2> <stamina 2>]
function _cmd_fighttwo($cmd, &$player)
{
    // Set monster 1
    $m = $cmd[1];
    $mskill = $cmd[2];
    $mstam = $cmd[3];

    // Set monster 2
    if (isset($cmd[4]) && isset($cmd[5]) && isset($cmd[6])) {
        $m2 = $cmd[4];
        $mskill2 = $cmd[5];
        $mstam2 = $cmd[6];
    } else {
        $m2 = $m;
        $mskill2 = $mskill;
        $mstam2 = $mstam;
    }
    
    // Differentiate monsters
    if ($m == $m2) {
        $m = "First ".$m;
        $m2 = "Second ".$m2;
    }

    $out = run_fight($player,$m,$mskill,$mstam,50,'nobody',null,$m2,$mskill2);
    if ($player['stam'] > 0) {
        addcommand("!fight $m2 $mskill2 $mstam2");
    }
    sendqmsg($out,":crossed_swords:");
}

//// !attack <skill>
function _cmd_attack($cmd, &$player)
{
    if (isset($cmd[2])) {
        $dmg = $cmd[2];
    } else {
        $dmg = 0;
    }

    $mskill = $cmd[1];
    $mroll = rand(1,6);
    $proll = rand(1,6);
    $mattack = $mskill+$mroll;
    $pattack = $player['skill']+$player['weapon']+$proll;

    $memoji = diceemoji($mroll);
    $pemoji = diceemoji($proll);

    if ($pattack > $mattack) {
        $out = "_You hit The Opponent. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
    }
    else if ($pattack < $mattack) {
        $out = "_The Opponent hits you! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
        if ($dmg > 0) {
            $player['stam'] -= $dmg;
            if ($player['stam'] > 0) {
                $out .= "_(Remaining stamina: ".$player['stam'].")_";
            } else {
                $out .= "_*The Opponent has defeated you!*_\n";
            }
        }
    }
    else {
        $out = "_You avoid each others blows. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
    }
    sendqmsg($out,":crossed_swords:");
}

//// !echo - simply repeat the inputted text
function _cmd_echo($cmd, &$player)
{
    if (!$cmd[1]) {
        return;
    }
    
    $line = implode(" ",array_slice($cmd, 1));

    // Turn the params back in to one string
    sendqmsg("*$line*", ':speech_balloon:');
}

//// !randpage <page 1> [page 2] [page 3] [...]
function _cmd_randpage ($cmd, &$player)
{
    $pagelist = array();
    foreach ($cmd as $c) {
        if (is_numeric($c)) {
            $pagelist[] = $c;
        }
    }

    $totalpages = sizeof($pagelist);
    if ($totalpages < 1) {
        return;
    }

    $choice = rand(0,$totalpages-1);

    // Display a rolled dice, if we can. Actually calculated after the choice (above)
    if ($totalpages == 2 || $totalpages == 3) {
        $ds = 6/$totalpages;
        $de = diceemoji(rand(1+$choice*$ds,$ds+$choice*$ds));
    } elseif ($totalpages <= 6) {
        $de = diceemoji($choice+1);
    }

    sendqmsg("Rolled $de",":game_die:");
    addcommand('!'.$pagelist[$choice]);
}
