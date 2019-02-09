<?php

/// This is messy. But it was quick.
function register_commands(&$player)
{
    register_command('look',        '_cmd_look');
    register_command('page',        '_cmd_page',['n','os']);
    register_command('background',  '_cmd_background');
    register_command('eat',         '_cmd_eat');
    register_command('pay',         '_cmd_pay',['n']);
    register_command('spend',       '_cmd_pay',['n']);
    register_command('buy',         '_cmd_buy',['ms','on']);
    register_command('luckyescape', '_cmd_luckyescape');
    register_command('le',          '_cmd_luckyescape');
    register_command('get',         '_cmd_get',['l']);
    register_command('take',        '_cmd_get',['l']);
    register_command('drop',        '_cmd_drop',['l']);
    register_command('lose',        '_cmd_drop',['l']);
    register_command('use',         '_cmd_drop',['l']);
    register_command('roll',        '_cmd_roll',['on']);
    register_command('test',        '_cmd_test',['s','on','on']);
    register_command('ng',          '_cmd_newgame',['osl','osl','osl','osl','osl','on']);
    register_command('newgame',     '_cmd_newgame',['osl','osl','osl','osl','osl','on']);
    register_command('info',        '_cmd_info');
    register_command('status',      '_cmd_info');
    register_command('stats',       '_cmd_stats');
    register_command('s',           '_cmd_stats');
    register_command('stuff',       '_cmd_stuff');
    register_command('i',           '_cmd_stuff');
    register_command('help',        '_cmd_help');
    register_command('?',           '_cmd_help');
    register_command('fight',       '_cmd_fight',['oms','n','n','osl']);
    register_command('critfight',   '_cmd_critfight',['oms','n','os','on']);
    register_command('bonusfight',  '_cmd_bonusfight',['oms','n','n','n','on']);
    register_command('vs',          '_cmd_vs',['ms','n','n','ms','n','n']);
    register_command('fighttwo',    '_cmd_fighttwo',['ms','n','n','oms','on','on']);
    register_command('phaser',      '_cmd_phaser',['onm','(\sstun|\skill)?','oms','n','(\sstun|\skill)?','on']);
    register_command('gun',         '_cmd_phaser',['onm','(\sstun|\skill)?','oms','n','(\sstun|\skill)?','on']);
    register_command('attack',      '_cmd_attack',['n','on']);
    register_command('a',           '_cmd_attack',['n','on']);
    register_command('echo',        '_cmd_echo',['l']);
    register_command('randpage',    '_cmd_randpage',['n','on','on','on','on','on','on','on']);
    register_command('shield',      '_cmd_shield',['os']);
    register_command('dead',        '_cmd_dead');
    register_command('debugset',    '_cmd_debugset',['s','l']);
    register_command('silentset',   '_cmd_debugset',['s','l']);
    register_command('macro',       '_cmd_macro',['n']);
    register_command('m',           '_cmd_macro',['n']);
    register_command('undo',        '_cmd_undo');
    register_command('map',         '_cmd_map');
    register_command('π',           '_cmd_easteregg');
    register_command(':pie:',       '_cmd_easteregg');

    $gamebook = getbook();
    if ($gamebook == 'loz' ||
        $gamebook == 'custom') {
            register_command('spellbook',   '_cmd_spellbook',['osl']);
            register_command('cast',        '_cmd_cast',['spell','oms','on','on']);
    }
    if ($gamebook == 'sob') {
            register_command('battle',      '_cmd_battle',['oms','n','n','osl']);
    }
    if ($gamebook == 'sst') {
            register_command('shipbattle',  '_cmd_shipbattle',['oms','n','n']);
            foreach($player['crew'] as $key => $val) {
                register_command($key, '_cmd_order',['s','ol']);
            }
            register_command('everyone', '_cmd_everyone',['l']);
            register_command('recruit',  '_cmd_recruit', ['s','s','n','n','os','os']);
            register_command('beam',     '_cmd_beam',['(\sup|\sdown)','os','os','os']);
    }

    // Stats commands
    $stats = array('skill', 'stam', 'stamina', 'luck', 'prov',
                   'provisons', 'gold', 'weapon', 'weaponbonus', 'bonus');
    if ($gamebook == 'rtfm') {
        $stats = array_merge($stats,['goldzagors','gz']);
    } elseif ($gamebook == 'loz') {
        $stats = array_merge($stats,['magic','talismans','daggers']);
    } elseif ($gamebook == 'hoh') {
        $stats = array_merge($stats,['fear']);
    } elseif ($gamebook == 'sob') {
        $stats = array_merge($stats,['str','strength','strike','log','slaves']);
    } elseif ($gamebook == 'sst') {
        $stats = array_merge($stats,['weapons','shields']);
    }
    foreach($stats as $s) {
        register_command($s, '_cmd_stat_adjust',['os','nm']);
    }
}

//// !look
function _cmd_look($cmd, &$player)
{
    require("book.php");
    $story = format_story($player['lastpage'],$book[$player['lastpage']],$player);
    sendqmsg($story);
}

//// !page <num> / !<num> (Read page from book)
function _cmd_page($cmd, &$player)
{
    if (!is_numeric($cmd[1])) {
        return;
    }
    $page = $cmd[1];
    $backup = (isset($cmd[2])?strtolower($cmd[2])!='nobackup':false);

    require("book.php");

    if (array_key_exists($page, $book)) {
        // Save a backup of the player for undo
        if ($backup) {
            backup_player($player);
        }

        $player['lastpage'] = $page;
        $story = $book[$page];

        // Exclude pages using 'if ', 'you may' or 'otherwise'
        // This isn't perfect, but will prevent many false matches
        if (stripos($story,"if ") === false && stripos($story,"you may ") === false
            && stripos($story,"otherwise") === false) {
            // Attempt to find pages that give you only one choice
            // Find pages with only one turn to and add that page to the command list
            preg_match_all('/turn to ([0-9]+)/i', $story, $matches, PREG_SET_ORDER, 0);
            if (sizeof($matches) == 1) {
                    addcommand("page ".$matches[0][1]." nobackup");
                }
            // Attempt to find pages that end the story, kill the player if found
            elseif (sizeof($matches) < 1 &&
                    preg_match('/Your (adventure|quest) (is over|ends here|is at an end)\.?/i', $story, $matches)) {
                $player['stam'] = 0;
            }
        }

        // Autorun
        if (isset($autorun)) {
            if (array_key_exists($page,$autorun)) {
                $cmdlist = explode(";",$autorun[$page]);
                for ($k = count($cmdlist)-1; $k >= 0; $k--) {
                    addcommand($cmdlist[$k]);
                }
            }
        }

        $story = format_story($player['lastpage'],$story,$player);
    } else {
        sendqmsg("*$page: PAGE NOT FOUND*",":interrobang:");
        return;
    }

    if (IMAGES_SUBDIR && file_exists('images'.DIRECTORY_SEPARATOR.IMAGES_SUBDIR.DIRECTORY_SEPARATOR.$player['lastpage'].'.jpg')) {
        sendimgmsg($story,'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'images/'.IMAGES_SUBDIR.'/'.$player['lastpage'].'.jpg');
    } else {
        sendqmsg($story);
    }
}

//// !background
function _cmd_background($cmd, &$player)
{
    require("book.php");
    $story = format_story(0,$book[0], $player);
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
        $icon = array(":bread:",":cheese_wedge:",":meat_on_bone:")[rand(0,2)];
        sendqmsg("*Yum! Stamina now ".$player['stam']." and ".$player['prov']." provisions left.*",$icon);
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
    if ($cmd[0] == 'strength') $cmd[0] = 'str';
    if ($cmd[0] == 'booty') $cmd[0] = 'gold';

    // Referrers
    if (isset($player['referrers'])) {
        $your = $player['referrers']['your'].' ';
    } else {
        $your = '';
    }

    // Setup the details of the adjustment
    // $statref is a reference to the stat that will be changed
    // $max is the maximum we will allow it to be set to
    // $statname is what we will send back to slack
    // $val is the adjustment or new value
    switch ($cmd[0]) {
        case 'stam':
            $statname = "Stamina";
            break;
        case 'prov':
            $statname = "Provisions";
            break;
        case 'weapon':
            $statname = "Weapon Bonus";
            break;
        case 'goldzagors':
            $statname = "Gold Zagors";
            break;
        case 'strike':
            $statname = "Crew Strike";
            break;
        case 'str':
            $statname = "Crew Strength";
            break;
        default:
            $statname = ucfirst($cmd[0]);
    }
    if (strtolower($cmd[1]) == "max") {
        $statref = &$player['max'][$cmd[0]];
        $max = 999;
        $statname = "Maximum $statname";
    } elseif (strtolower($cmd[1]) == "temp") {
        $player['temp'][$cmd[0]] = 0;
        $statref = &$player['temp'][$cmd[0]];
        $max = 999;
        $statname = "Temp $statname Bonus";
    } elseif (!$cmd[1]) {
        $statref = &$player[$cmd[0]];
        $max = $player['max'][$cmd[0]];
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
        $msg = "*Added $val to $your$statname, now $statref.*";
    } else if ($val[0] == "-") {
        $val = substr($val,1);
        $statref -= (int)$val;
        // Allow negative weapon bonuses and temp values, but others have a min 0.
        if ($statref < 0 && $cmd[0] != 'weapon' && $cmd[1] != 'temp') {
            $statref = 0;
        }
        $msg = "*Subtracted $val from $your$statname, now $statref.*";
    } else {
        $statref = (int)$val;
        if ($statref > $max) {
            $statref = $max;
        }
        $msg = "*Set $your$statname to $statref.*";
    }

    // When reducing the max value, we may also need to reduce the current value
    if (($oldval > $statref) &&
        (strtolower($cmd[1]) == "max") &&
        ($player[$cmd[0]] > $statref))
    {
        $player[$cmd[0]] = $statref;
    }

    // Extra message when using temp stat adjustment
    if (strtolower($cmd[1]) == "temp") {
        $msg .= " _(This will reset after the next fight or test.)_";
    }

    // Extra test for max fear
    if ($cmd[0] == 'fear' && $statref == $max) {
        $msg .= "\n*You are frightened to death!*";
        $player['stam'] = 0;
    }

    if ($oldval <= $statref && strtolower($cmd[1]) == "max") {
        $icon = ':arrow_up:';
    } elseif ($oldval > $statref && strtolower($cmd[1]) == "max") {
        $icon = ':arrow_down:';
    } elseif ($cmd[0] == 'stam') {
        $icon = ':face_with_head_bandage:';
    } elseif ($oldval <= $statref && $cmd[0] == 'luck') {
        $icon = ':four_leaf_clover:';
    } elseif ($oldval > $statref && $cmd[0] == 'luck') {
        $icon = ':lightning:';
    } elseif ($cmd[0] == 'gold' || $cmd[0] == 'goldzagors') {
        $icon = ':moneybag:';
    } elseif ($cmd[0] == 'weapon') {
        $icon = ':dagger_knife:';
    } elseif ($cmd[0] == 'prov') {
        $icon = ':bread:';
    } elseif ($cmd[0] == 'skill') {
        $icon = ':juggling:';
    } elseif ($cmd[0] == 'fear') {
        $icon = ':scream:';
    } else {
        $icon = ':open_book:';
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
        addcommand("gold -".$cmd[1]);
    }
}

//// !buy (alias for get & losing gold)
function _cmd_buy($cmd, &$player)
{
    if ($cmd[2]) {
        $cost = $cmd[2];
    } else {
        $cost = 2;
    }
    $item = $cmd[1];

    if ($player['gold'] < $cost) {
        sendqmsg("* You don't have $cost gold! *",':interrobang');
    } else if (array_search(strtolower($item), array_map('strtolower', $player['stuff'])) !== false) {
        sendqmsg("*You already have '".$item."'. Try giving this item a different name.*",':interrobang:');
    } else {
        $player['gold'] -= $cost;
        $player['stuff'][] = $item;
        sendqmsg("*Bought $item for $cost Gold*",':handshake:');
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
        addcommand("gold +".$matches[0][1]);
        return;
    }
    // "provision"
    if (strtolower($item) == "provision") {
        addcommand("prov +1");
        return;
    }
    // "x provisions"
    preg_match_all('/^([0-9]+) provisions/i', $item, $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("prov +".$matches[0][1]);
        return;
    }
    // "shield"
    if (strtolower($item) == "shield") {
        addcommand("shield on");
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
    $drop = strtolower($cmd[1]);
    // TODO: This is code repetition
    // Attempt to catch cases where people get or take gold or provisions
    // and turn them in to stat adjustments
    // "x Gold"
    preg_match_all('/^([0-9]+) gold/i', $drop, $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("gold -".$matches[0][1]);
        return;
    }
    // "provision"
    if ($drop == "provision") {
        addcommand("prov -1");
        return;
    }
    // "x provisions"
    preg_match_all('/^([0-9]+) provisions/i', $drop, $matches, PREG_SET_ORDER, 0);
    if (sizeof($matches) > 0) {
        addcommand("prov -".$matches[0][1]);
        return;
    }
    // "shield"
    if ($drop == "shield") {
        addcommand("shield off");
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
        sendqmsg("*'".$drop."' didn't match anything in inventory. Can't ".strtolower($cmd[0]).".*",':interrobang:');
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
    $numdice = ($cmd[1]?$cmd[1]:1);
    $numdice = max(min($numdice, 100), 1);
    $out = "Result:";

    $t = 0;
    for ($a = 0; $a < $numdice; $a++) {
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
    // Prevent restore
    backup_remove();

    $cmd[1] = strtolower($cmd[1]);
    // Aliases
    if ($cmd[1] == "stamina") $cmd[1] = "stam";
    if ($cmd[1] == "strength") $cmd[1] = "str";

    // Referrers
    if (isset($player['referrers'])) {
        $youare = ucfirst($player['referrers']['youare']);
        $you = ucfirst($player['referrers']['you']);
    } else {
        $youare = 'You are';
        $you = 'You';
    }

    // Check for valid test types
    $vtt = array('luck','skill','stam','spot');
    $gamebook = getbook();
    if ($gamebook == 'sob') {
        $vtt[] = 'str';
    } elseif ($gamebook == 'sst') {
        $vtt[] = 'shields';
    }
    if (!in_array($cmd[1], $vtt)) {
        sendqmsg("*Don't know how to test ".$cmd[1]."*",':interrobang:');
        return;
    }

    // Apply temp bonuses, if any
    apply_temp_stats($player);

    // Setup outcome pages to read if provided
    if ($cmd[2]) {
        $success_page = "page ".$cmd[2]." nobackup";
    }
    if ($cmd[3]) {
        $fail_page = "page ".$cmd[3]." nobackup";
    }

    // Roll dice
    $d1 = rand(1,6);
    $d2 = rand(1,6);
    $roll = $d1 + $d2;
    $emojidice = diceemoji($d1).' '.diceemoji($d2);
    if ($cmd[1] == 'str') {
        $d3 = rand(1,6);
        $roll += $d3;
        $emojidice .= ' '.diceemoji($d3);
    }

    // Set the target value from stat
    // Spot is a special case
    if ($cmd[1] == 'spot') {
        $target = $player['skill'] + ($player['adjective'] == 'Wizard'?2:0);
    } else {
        $target = $player[$cmd[1]];
    }

    // Check roll versus target number
    if ($roll <= $target) {
        if ($cmd[1] == "luck") {
            $player['luck']--;
            sendqmsg("_*$youare lucky*_\n_(_ $emojidice _ vs $target, Remaining luck ".$player['luck'].")_",':four_leaf_clover:');
        } else if ($cmd[1] == "skill") {
            sendqmsg("_*$youare skillful*_\n_(_ $emojidice _ vs $target)_",':juggling:');
        } else if ($cmd[1] == "stam") {
            sendqmsg("_*$youare strong enough*_\n_(_ $emojidice _ vs $target)_",':muscle:');
        } else if ($cmd[1] == "spot") {
            sendqmsg("_*$you spotted something*_\n_(_ $emojidice _ vs $target)_",':eyes:');
        } else if ($cmd[1] == "str") {
            sendqmsg("_*Your crew is strong enough*_\n_(_ $emojidice _ vs $target)_",':muscle:');
        } else if ($cmd[1] == "shields") {
            sendqmsg("_*Your shields hold up*_\n_(_ $emojidice _ vs $target)_",':rocket:');
        }
        // Show follow up page
        if (isset($success_page)) {
            addcommand($success_page);
        }
    }
    else {
        if ($cmd[1] == "luck") {
            $player['luck']--;
            sendqmsg("_*$youare unlucky.*_\n_(_ $emojidice _ vs $target, Remaining luck ".$player['luck'].")_",':lightning:');
        } else if ($cmd[1] == "skill") {
            sendqmsg("_*$youare not skillful*_\n_(_ $emojidice _ vs $target)_",':tired_face:');
        } else if ($cmd[1] == "stam") {
            sendqmsg("_*$youare not strong enough*_\n_(_ $emojidice _ vs $target)_",':sweat:');
        } else if ($cmd[1] == "spot") {
            sendqmsg("_*$you didn't spot anything*_\n_(_ $emojidice _ vs $target)_",':persevere:');
        } else if ($cmd[1] == "str") {
            sendqmsg("_*Your crew is not strong enough*_\n_(_ $emojidice _ vs $target)_",':sweat:');
        } else if ($cmd[1] == "shields") {
            sendqmsg("_*Your shields do not protect you*_\n_(_ $emojidice _ vs $target)_",':rocket:');
        }
        // Show follow up page
        if (isset($fail_page)) {
            addcommand($fail_page);
        }
    }

    // Remove temp bonuses, if any and clear temp bonus array
    unapply_temp_stats($player);
}

//// !newgame (roll new character)
function _cmd_newgame($cmd, &$player)
{
    require_once('logic/roll_character.php');

    $cmd = array_pad($cmd, 7, '?');
    $player = roll_character($cmd[1],$cmd[2],$cmd[3],$cmd[4],$cmd[5],$cmd[6]);
    send_charsheet($player, "_*NEW CHARACTER!*_ ".implode(' ',array_map("diceemoji",$player['creationdice'])),true);
}

//// !info / !status (send character sheet and inventory)
function _cmd_info($cmd, &$player)
{
    send_charsheet($player,'',true);
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
    $helpurl = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'commands.html';
    sendqmsg($help."\nMore commands can be found here: ".$helpurl);
}

//// !fight [name] <skill> <stamina> [maxrounds] (run fight logic)
function _cmd_fight($cmd, &$player)
{
    $out = run_fight(['player' => &$player,
                      'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                      'monsterskill' => $cmd[2],
                      'monsterstam' => $cmd[3],
                      'maxrounds' => ($cmd[4]?$cmd[4]:50)
                      ]);
    sendqmsg($out,":crossed_swords:");
}

//// !phaser/gun [-/+modifier] [stun/kill] [name] <skill> [stun/kill] [maxrounds] (run phaser fight logic)
function _cmd_phaser($cmd, &$player)
{
    $out = run_phaser_fight(['player' => &$player,
                             'modifier' => ($cmd[1]?$cmd[1]:0),
                             'stunkill' => ($cmd[2]?$cmd[2]:'stun'),
                             'monstername' => ($cmd[3]?$cmd[3]:"Opponent"),
                             'monsterskill' => $cmd[4],
                             'mstunkill' => ($cmd[5]?$cmd[5]:'kill'),
                             'maxrounds' => ($cmd[6]?$cmd[6]:50)
                             ]);
    sendqmsg($out,":gun:");
}

//// !critfight [name] <skill> [who] [critchance] (run crit fight logic)
function _cmd_critfight($cmd, &$player)
{
    $critsfor = ($cmd[3]?$cmd[3]:'me');
    $critchance = ($cmd[4]?$cmd[4]:2);
    if (!in_array($critsfor,['both','me'])) {
        $critsfor = 'me';
    }
    if (!is_numeric($critchance) || $critchance < 1 || $critchance > 6) {
        $critchance = 2;
    }

    $out = "_*You".($critsfor == 'both'?' both':'')." have to hit critical strikes!* ($critchance in 6 chance)_\n";
    $out = run_fight(['player' => &$player,
                      'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                      'monsterskill' => $cmd[2],
                      'critsfor' => $critsfor,
                      'critchance' => $critchance]);
    sendqmsg($out,":crossed_swords:");
}

//// !bonusfight [name] <skill> <stamina> <bonusdamage> [bonusdmgchance] (run bonus attack fight logic)
function _cmd_bonusfight($cmd, &$player)
{
    $out = run_fight(['player' => &$player,
                      'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                      'monsterskill' => $cmd[2],
                      'monsterstam' => $cmd[3],
                      'bonusdmg' => $cmd[4],
                      'bonusdmgchance' => ($cmd[5]?$cmd[5]:3)
                      ]);
    sendqmsg($out,":crossed_swords:");
}

//// !vs <name 1> <skill 1> <stamina 1> <name 2> <skill 2> <stamina 2> 
function _cmd_vs($cmd, &$player)
{
    $vsplayer = array(
        'name' => $cmd[1],
        'referrers' => ['you' => $cmd[1], 'youare' => $cmd[1].' is', 'your' => $cmd[1]."'s"],
        'skill' => $cmd[2],
        'stam' => $cmd[3],
        'luck' => 0,
        'weapon' => 0,
        'shield' => false,
        'temp' => []
    );
    $out = run_fight(['player' => &$vsplayer,
                      'monstername' => $cmd[4],
                      'monsterskill' => $cmd[5],
                      'monsterstam' => $cmd[6]
                      ]);
    sendqmsg($out,":crossed_swords:");
}

//// !fighttwo <name 1> <skill 1> <stamina 1> [<name 2> <skill 2> <stamina 2>]
function _cmd_fighttwo($cmd, &$player)
{
    // Set monster 1
    $m = $cmd[1];
    $mskill = $cmd[2];
    $mstam = $cmd[3];

    // Set monster 2
    if ($cmd[4] && $cmd[5] && $cmd[6]) {
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

    $out = run_fight(['player' => &$player,
                      'monstername' => $m,
                      'monsterskill' => $mskill,
                      'monsterstam' => $mstam,
                      'monster2name' => $m2,
                      'monster2skill' => $mskill2]);
    if ($player['stam'] > 0) {
        addcommand("fight $m2 $mskill2 $mstam2");
    }
    sendqmsg($out,":crossed_swords:");
}

//// !attack <skill>
function _cmd_attack($cmd, &$player)
{
    $dmg = ($cmd[2]?$cmd[2]:0);
    $out = run_single_attack($player, 'Opponent', $cmd[1], 999, $dmg, 0);

    sendqmsg($out,":crossed_swords:");
}

//// !echo - simply repeat the input text
function _cmd_echo($cmd, &$player)
{
    if (!$cmd[1]) {
        return;
    }

    // Turn the params back in to one string
    sendqmsg($cmd[1], ':open_book:');
}

//// !randpage <page 1> [page 2] [page 3] [...]
function _cmd_randpage ($cmd, &$player)
{
    // Prevent restore
    backup_remove();

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
    addcommand("page ".$pagelist[$choice]." nobackup");
}

//// !shield [on/off] - Toggle shield
function _cmd_shield($cmd, &$player)
{
    $state = strtolower($cmd[1]);
    if ($state != 'on' && $state != 'off') {
        $state = ($player['shield']?'off':'on');
    }

    $player['shield'] = ($state == 'on');
    $state = ($player['shield']?'Equipped':'Un-Equipped');
    sendqmsg("*Shield $state*", ':shield:');
}

//// !dead - Kill your character.
function _cmd_dead($cmd, &$player)
{
    $player['stam'] = 0;
}

//// !debugset - Set any value
function _cmd_debugset($cmd, &$player)
{
    $key = $cmd[1];
    $val = $cmd[2];
    $silent = (strtolower($cmd[0]) == 'silentset');

    if (array_key_exists($key,$player) && !is_array($player[$key])) {
        if (is_numeric($val)) {
            $val = (int)$val;
        }
        $player[$key] = $val;
        $msg = "*$key set to $val*";
    } else {
        $msg = "*$key is invalid.*";
    }
    if (!$silent) {
        sendqmsg($msg, ':desktop_computer:');
    }
}

//// !π - Easter egg
function _cmd_easteregg($cmd, &$player)
{
    $eggs = file('resources/easter_eggs.txt');
    $fullcmd = trim($eggs[array_rand($eggs)]);

    $cmdlist = explode(";",$fullcmd);
    for ($k = count($cmdlist)-1; $k >= 0; $k--) {
        addcommand($cmdlist[$k]);
    }
}

//// !macro - Run macro from macro.txt
function _cmd_macro($cmd, &$player)
{
    $macros = file('macros.txt');
    if ($cmd[1] < 1 || $cmd[1] > sizeof($macros)) {
        sendqmsg('Macro '.$cmd[1].' not found.', ':interrobang:');
    }
    $fullcmd = trim($macros[$cmd[1]-1]);

    $cmdlist = explode(";",$fullcmd);
    for ($k = count($cmdlist)-1; $k >= 0; $k--) {
        addcommand($cmdlist[$k]);
    }
}

//// !spellbook - read spellbook
function _cmd_spellbook($cmd, &$player)
{
    require('logic/spells.php');

    $typeslist = array();
    foreach ($spells as $s) {
        $typeslist[] = $s['type'];
    }
    $typeslist = array_unique($typeslist);
    $pagesize = 4;
    $total = ceil(count($spells)/$pagesize);

    $in = strtolower($cmd[1]);

    if ($in == 'all') {
        $out = "_*~ All Spells ~*_\n";
        $list = $spells;
        usort($list, function($a, $b) {
            return strcmp($a["name"], $b["name"]);
        });
    } elseif (in_array($in,$typeslist)) {
        $out = "_*~ ".ucfirst($in)." Spells ~*_\n";
        $list = array_filter($spells,function($v) use ($in){
            return ($v['type'] == $in);
        });
        usort($list, function($a, $b) {
            if ($a['cost'] == $b['cost']) { return strcmp($a["name"], $b["name"]); }
            return ($a['cost'] < $b['cost']) ? -1 : 1;
        });
    } elseif (is_numeric($in)) {
        if ($in < 1 || $in > $total) {
            $in = 1;
        }
        $out = "_*~ PAGE $in of $total ~*_\n";
        usort($spells, function($a, $b) {
            return strcmp($a["name"], $b["name"]);
        });
        $list = array_slice($spells,($in-1)*$pagesize,$pagesize);
    } else {
        $out = "_*~ Spellbook Contents ~*_\n";
        $out .= "By Page: `!spellbook 1` ... `!spellbook $total`\n";
        $out .= "By Type: ";
        foreach ($typeslist as $t) {
            $out .= "`!spellbook $t`, ";
        }
        $out = substr($out, 0, -2)."\n";
        $out .= "Everything: `!spellbook all`\n";
        $list = array();
    }

    foreach ($list as $s) {
        $out .= "*".$s['name']."* _(Cost: ".$s['cost']." Magic".($s['target']?", Requires Target":"").", Type: ".ucfirst($s['type']).")_\n";
        $out .= wordwrap($s['desc'],100)."\n\n";
    }

    // Turn the params back in to one string
    sendqmsg($out, ':green_book:');
}

//// !echo - simply repeat the input text
function _cmd_cast($cmd, &$player)
{
    require('logic/spells.php');

    foreach ($spells as $s) {
        if (strtolower($s['name']) == strtolower($cmd[1])) {
            break;
        }
    }

    if ($player['magic'] < $s['cost']) {
        sendqmsg("*You don't have ".$s['cost']." Magic to spend!*", ':interrobang:');
    } elseif ($s['target'] && (!$cmd[3] || !$cmd[4])) {
        sendqmsg("*This spell requires a target!* e.g. `!cast ".$s['name']." Monster 6 7`", ':interrobang:');
    } elseif ($s['target']) {
        $player['magic'] -= $s['cost'];
        $s['func']($player,($cmd[2]?$cmd[2]:'Opponent'),$cmd[3],$cmd[4]);
    } else {
        $player['magic'] -= $s['cost'];
        $s['func']();
    }
}

//// !undo - restore to the previous save
function _cmd_undo($cmd, &$player)
{
    if ($player['stam'] > 0) {
        sendqmsg("*You can only undo when dead.*", ':interrobang:');
        return;
    } else if (restore_player($player)) {
        sendqmsg("*...or maybe this happened...*", ':rewind:');
        addcommand("look");
    } else {
        sendqmsg("*There are some things that cannot be undone...*", ':skull:');
    }
}

//// !map - Sends a map image if map.jpg exists in images dir
function _cmd_map($cmd, &$player)
{
    if (file_exists('images'.DIRECTORY_SEPARATOR.'map.jpg')) {
        sendimgmsg("*Map*",'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'images/map.jpg');
    } else {
        sendqmsg("*No map found!*", ':interrobang:');
    }
}

//// !battle [name] <skill> <stamina> [maxrounds] (run battle logic)
function _cmd_battle($cmd, &$player)
{
    // Construct battle player
    $bp = array(
        'name' => "The crew of ".$player['shipname'],
        'referrers' => ['you' => 'your crew', 'youare' => 'your crew is', 'your' => "your crew's"],
        'skill' => $player['strike'],
        'stam' => $player['str'],
        'luck' => 0,
        'weapon' => 0,
        'shield' => false,
        'temp' => []
    );
    $out = run_fight(['player' => &$bp,
                      'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                      'monsterskill' => $cmd[2],
                      'monsterstam' => $cmd[3],
                      'maxrounds' => ($cmd[4]?$cmd[4]:50),
                      'healthstatname' => 'strength']);

    $player['str'] = $bp['stam'];
    if ($player['str'] < 1) {
        $player['stam'] = 0;
    }

    sendqmsg($out,":crossed_swords:");
}

//// Special case, order various crew to do commands
function _cmd_order($cmd, &$player)
{
    global $commandslist, $commandsargs;

    $officer = strtolower($cmd[0]);
    $order = strtolower($cmd[1]);
    if (array_key_exists($order, $commandsargs)) {
        $cmd = advanced_command_split(trim($order.' '.$cmd[2]), $commandsargs[$order]);
    } else {
        $cmd = false;
    }
    if (!$cmd) {
        sendqmsg("Sorry, I didn't understand that command!",":interrobang:");
        return;
    }

    $crew = &$player['crew'][$officer];
    switch ($order) {
        case 'fight':
        case 'phaser':
        case 'gun':
        case 'critfight':
        case 'bonusfight':
        case 'fighttwo':
            if ($crew['combatpenalty']) {
                $tmpskill = $crew['skill'];
                $crew['skill'] = max(0,$crew['skill']-2);
            }
            call_user_func_array($commandslist[$order],array($cmd,&$crew));
            if ($crew['combatpenalty']) {
                $crew['skill'] = $tmpskill;
            }
            break;
        case 'skill':
        case 'stam':
        case 'stamina':
        case 'test':
        case 'dead':
        case 'debugset':
            call_user_func_array($commandslist[$order],array($cmd,&$crew));
            break;
        default:
            sendqmsg('Cannot order crew to '.$order,':interrobang:');
    }

    if ($crew['stam'] < 1) {
        require_once('roll_character.php');
        $out = "*".$crew['name']." is dead!* :skull:\n";
        $newskill = max(1,$crew['max']['skill']-2);
        $crew = roll_sst_crew($officer, $crew['combatpenalty']);
        $crew['max']['skill'] = $newskill;
        $crew['skill'] = $newskill;
        $crew['replacement'] = true;
        $crew['awayteam'] = false;
        $out .= "Their assistant, ".$crew['name'].", is promoted to the ".$crew['position']." position. ";
        $out .= "(Replacement crew cannot beam down to planets.)";
        sendqmsg($out,':dead:');
    }
}

//// Special case, order WHOLE crew to do command
function _cmd_everyone($cmd, &$player)
{
    addcommand($cmd[1]);
    foreach ($player['crew'] as $key => $val) {
        addcommand($key.' '.$cmd[1]);
    }
}

//// Replace crew
function _cmd_recruit($cmd, &$player)
{
    $pos = $cmd[1];

    if (!array_key_exists($pos,$player['crew'])) {
        sendqmsg("*$pos: invalid position*", ':interrobang:');
    }

    $c = &$player['crew'][$pos];
    $c['replacement'] = false;
    $c['awayteam'] = false;
    $c['name'] = ucfirst($cmd[2]);
    $c['skill'] = $cmd[3];
    $c['stam'] = $cmd[4];
    $c['max']['skill'] = $c['skill'];
    $c['max']['stam'] = $c['stam'];
    if ($cmd[5]) {
        $c['gender'] = ucfirst($cmd[5]);
    }
    if ($cmd[6]) {
        $c['race'] = ucfirst($cmd[6]);
    }
    $c['referrers'] = ['you' => $c['name'], 'youare' => $c['name'].' is', 'your' => $c['name']."'s"];

    sendqmsg("*".$c['name']." recruited!*", ':handshake:');
}

//// !shipbattle [name] <skill> <stamina> (run ship battle logic)
function _cmd_shipbattle($cmd, &$player)
{
    $out = run_ship_battle(['player' => &$player,
                            'oppname' => ($cmd[1]?$cmd[1]:"Opponent"),
                            'oppweapons' => $cmd[2],
                            'oppshields' => $cmd[3],
                           ]);
    sendqmsg($out,":rocket:");
}

//// !beam <up/down> [crew] [crew] [crew]
function _cmd_beam($cmd, &$player)
{
    $out = "";
    $crew = array();
    $dir = strtolower($cmd[1]);
    if ($cmd[2]) $crew[] = strtolower($cmd[2]);
    if ($cmd[3]) $crew[] = strtolower($cmd[3]);
    if ($cmd[4]) $crew[] = strtolower($cmd[4]);

    if (sizeof($crew) < 1 && $dir == 'up') {
        $crew = array_keys($player['crew']);
    }
    foreach ($crew as $k => $c) {
        if (!array_key_exists($c, $player['crew']) ||
            $player['crew'][$c]['awayteam'] == ($dir == 'down') ||
            $player['crew'][$c]['replacement'] == true) {
            unset($crew[$k]);
        } else {
            $player['crew'][$c]['awayteam'] = ($dir == 'down');
            $crew[$k] = $player['crew'][$c]['name'];
        }
    }
    array_unshift($crew,"You");
    $out = "_".basic_num_to_word(count($crew))." to beam $dir!_\n";
    $out .= "*".implode(', ', array_slice($crew, 0, -1)) . (count($crew)>1?' and ':'') . end($crew)." have beamed $dir.*\n";
    sendqmsg($out,":rocket:");
}
