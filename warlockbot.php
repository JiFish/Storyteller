<?php

// Other configuration settings. You can override these in config.php if you wish
define("MAX_EXECUTIONS",30);

require('config.php');

// Check the incoming data for the secret slack token
if ($_POST['token'] != SLACK_TOKEN) {
    header('HTTP/1.0 403 Forbidden');
    die('Access Denied. Token does not match');
}

// Note, $player and $commandlist are referenced as global variables in the
// below functions.

$player = load();

// Split the command list by semi-colons. Allows multiple commands to be queued
// Note, some commands will queue other commands
$commandlist = explode(";",$_POST['text']);

$executions = 0;
while (sizeof($commandlist) > 0)
{
    // Process the next command in the list
    processcommand(array_shift($commandlist));

    // If stamina ever drops to less than 1, the player if dead
    // Stop processing any queued commands and tell the player they are dead
    if ($player['stam'] < 1) {
        sendqmsg("_*You are dead.*_ :skull:",":skull:");
        break;
    }

    // Stop processing the queue after MAX_EXECUTIONS
    if ($executions++ > MAX_EXECUTIONS) {
        break;
    }
}

save($player);

die();

/// ----------------------------------------------------------------------------
/// Functions

function roll_character() {
    $p = array('skill' => 6+rand(1,6), 'stam' => rand(1,6)+rand(1,6)+12, 'luck' => rand(1,6)+6, 'prov' => 10, 'gold' => rand(1,6)-1,
                'weapon' => 0,
                   'lastpage' => 1,
                   'stuff' => array('Sword','Leather Armor','Lantern'));

    // random potion
    switch(rand(1,3)) {
        case 1:
            $p['stuff'][] = 'Potion of Skill';
            break;
        case 2:
            $p['stuff'][] = 'Potion of Strength';
            break;
        case 3:
            $p['stuff'][] = 'Potion of Luck';
            $p['luck']++;
            break;
    }

    // Set maximums
    $p['max']['skill']  = $p['skill'];
    $p['max']['stam']   = $p['stam'];
    $p['max']['luck']   = $p['luck'];
    $p['max']['prov']   = 99999;
    $p['max']['gold']   = 99999;
    $p['max']['weapon'] = 99999;

    // Fluff
    require('uinames.class.php');
    $uinames = new uiNames();
    $uin =$uinames->country('England')->fetch('Array');
    $p['name'] = $uin['name'];
    $p['icon'] = ($uin['gender']=='male'?':man:':':woman:');

    $male = array(':boy:',':man:',':person_with_blond_hair:',':older_man:');
    $female = array(':girl:',':woman:',':princess:',':older_woman:');
    $skintone = array(':skin-tone-2:',':skin-tone-3:',':skin-tone-4:',':skin-tone-5:');
    if ($uin['gender']=='male') {
        $p['icon'] = $male[array_rand($male)].$skintone[array_rand($skintone)];
    } else {
        $p['icon'] = $female[array_rand($female)].$skintone[array_rand($skintone)];
    }

    return $p;
}

function load()
{
    $save = file_get_contents('save.txt');
    if (!$save) {
        $p = roll_character();
    }
    else {
        $p = unserialize($save);
    }

    return $p;
}

function save($p)
{
    file_put_contents("save.txt",serialize($p));
}

function diceemoji($r)
{
    if ($r < 1 || $r > 6)
        return "BADDICE";

    return mb_convert_encoding('&#x'.(2679+$r).';', 'UTF-8', 'HTML-ENTITIES');
}

function addcommand($cmd)
{
    global $commandlist;
    return array_unshift($commandlist,$cmd);
}

function send_charsheet($text = "")
{
    global $player;

    $attachments = array([
            'color'    => '#ff6600',
            'fields'   => array(
            [
                'title' => 'Skill',
                'value' => $player['skill']." / ".$player['max']['skill'],
                'short' => true
            ],
            [
                'title' => 'Stamina (stam)',
                'value' => $player['stam']." / ".$player['max']['stam'],
                'short' => true
            ],
            [
                'title' => 'Luck',
                'value' => $player['luck']." / ".$player['max']['luck'],
                'short' => true
            ],
            [
                'title' => 'Weapon Bonus (weapon)',
                'value' => "+".$player['weapon'],
                'short' => true
            ],
            [
                'title' => 'Gold',
                'value' => $player['gold'],
                'short' => true
            ],
            [
                'title' => 'Provisons (prov)',
                'value' => $player['prov'],
                'short' => true
            ])
    ]);

    if ($player['stam'] < 1) {
        $icon = ":skull:";
    } else {
        $icon = $player['icon'];
    }

    sendmsg("\n*".$player['name']."*",$attachments,$player['icon']);
}

function send_stuff()
{
    global $player;
    $s = $player['stuff'];
    if (sizeof($s) == 0) {
        $s[] = "(Nothing!)";
    } else {
        natcasesort($s);
        $s = array_map("ucfirst",$s);
    }

    $attachments = array([
            'color'    => '#0066ff',
            'fields'   => array(
            [
                'title' => 'Inventory',
                'value' => implode("\n",array_slice($s, 0, floor(sizeof($s) / 2))),
                'short' => true
            ],
            [
                'title' => "",
                'value' => "\n".implode("\n",array_slice($s, floor(sizeof($s) / 2))),
                'short' => true
            ])
    ]);

    if ($player['stam'] < 1) {
        $icon = ":skull:";
    } else {
        $icon = $player['icon'];
    }

    sendmsg("",$attachments,$icon);
}

function senddirmsg($message, $user = false)
{
    if (!$user) {
        $user = $_POST['user_id'];
    }
    return sendmsg($message, true, ':green_book:', '@'.$user);
}

function sendqmsg($message, $icon = ':green_book:')
{
    return sendmsg($message, true, $icon);
}

function sendmsg($message, $attachments = array(), $icon = ':green_book:', $chan = false)
{
    global $player;

    $data = array(
        'text'        => $message,
        'icon_emoji'  => $icon,
        'attachments' => $attachments
    );
    if ($chan) {
        $data['channel'] = $chan;
    }
    $data_string = json_encode($data);
    $ch = curl_init(SLACK_HOOK);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
    //Execute CURL
    $result = curl_exec($ch);
    return $result;        
}

function processcommand($command)
{
    $cmd = preg_split('/\s+/', trim($command));

    $cmd[0] = substr(strtolower($cmd[0]),1);
    if (sizeof($cmd) > 1) {
        $cmd[1] = trim($cmd[1]);
    } else {
        $cmd[1] = false;
    }

    if ($cmd[0] == "look")
    {
        require("book.php");
        sendqmsg($book[$player['lastpage']]);
    }
    if (($cmd[0] == "page" && is_numeric($cmd[1])) || is_numeric($cmd[0]))
    {
        if ($cmd[0] == "page") {
            $p = $cmd[1];
        } else {
            $p = $cmd[0];
        }
        if ($p >= 0 && $p <= 400) {
            $player['lastpage'] = $p;
            require("book.php");
            $story = $book[$p];

            // Check for one option stories
            preg_match_all('/turn to ([0-9]+)/i', $story, $matches, PREG_SET_ORDER, 0);
            if (sizeof($matches) == 1 && stripos($story,"if ") === false && stripos($story,"you may ") === false) {
                addcommand("!".$matches[0][1]);
            }
            $story = preg_replace('/\(?turn(ing)? to [0-9]+\)?/i', '*${0}*', $story);
            sendqmsg($story);
        }
    }
    if ($cmd[0] == "eat" && $player['prov'] > 0) {
        $player['prov']--;
        $player['stam']+=4;
        if ($player['stam'] > $player['max']['stam']) {
            $player['stam'] = $player['max']['stam'];
        }
        sendqmsg("*Yum! Stamina now ".$player['stam']." and ".$player['prov']." provisions left.*",":bread:");
    }
    if (in_array($cmd[0],array('skill','stam','stamina','luck','prov','provisons','gold','weapon','weaponbonus')))
    {
        //aliases
        if ($cmd[0] == 'stamina') $cmd[0] = 'stam';
        if ($cmd[0] == 'provisons') $cmd[0] = 'prov';
        if ($cmd[0] == 'weaponbonus') $cmd[0] = 'weapon';

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
            sendqmsg("Nope.".$cmd[1]);
            continue;
        }

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
    if (($cmd[0] == "pay" || $cmd[0] == "spend") && is_numeric($cmd[1]))
    {
        addcommand("!gold -".$cmd[1]);
        continue;
    }
    if ($cmd[0] == "luckyescape" || $cmd[0] == "le")
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
    if (($cmd[0] == "get" || $cmd[0] == "take") && $cmd[1])
    {
        $cmd[1] = implode(" ",array_slice($cmd, 1));

        // Look for special cases
        // Gold
        preg_match_all('/^([0-9]+) gold/i', $cmd[1], $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            addcommand("!gold +".$matches[0][1]);
            continue;
        }
        // Provision
        if (strtolower($cmd[1]) == "provision") {
            addcommand("!prov +1");
            continue;
        }
        preg_match_all('/^([0-9]+) provisions/i', $cmd[1], $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            addcommand("!prov +".$matches[0][1]);
            continue;
        }

        $player['stuff'][] = $cmd[1];
        sendqmsg("*Got the ".$cmd[1]."!*",":moneybag:");
    }
    if (($cmd[0] == "drop" || $cmd[0] == "lose" || $cmd[0] == "use") && $cmd[1])
    {
        $cmd[1] = implode(" ",array_slice($cmd, 1));

        // Look for special cases
        // Gold
        preg_match_all('/^([0-9]+) gold/i', $cmd[1], $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            addcommand("!gold -".$matches[0][1]);
            continue;
        }
        // Provision
        if (strtolower($cmd[1]) == "provision") {
            addcommand("!prov -1");
            continue;
        }
        preg_match_all('/^([0-9]+) provisions/i', $cmd[1], $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            addcommand("!prov -".$matches[0][1]);
            continue;
        }

        $dropped = false;
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
        if (!$dropped) {
            sendqmsg("*No ".$cmd[1]." to loose!*");
        }
    }
    if ($cmd[0] == "roll")
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
    if ($cmd[0] == "test" && ($cmd[1] == "luck" || $cmd[1] == "skill" || $cmd[1] == "stam"))
    {
        $d1 = rand(1,6);
        $d2 = rand(1,6);
        $e1 = diceemoji($d1);
        $e2 = diceemoji($d2);
        $target = $player[$cmd[1]];
        if (is_numeric($cmd[2])) {
            $success_page = "!".$cmd[2];
        }
        if (is_numeric($cmd[3])) {
            $fail_page = "!".$cmd[3];
        }

        if ($d1+$d2 <= $target) {
            if ($cmd[1] == "luck") {
                $player['luck']--;
                sendqmsg("_*You are lucky*_\n_(_ $e1 $e2 _ vs $target, Remaining luck ".$player['luck'].")_",':four_leaf_clover:');
            } else if ($cmd[1] == "skill") {
                sendqmsg("_*You are skillful*_\n_(_ $e1 $e2 _ vs $target)_",':runner:');
            } else {
                sendqmsg("_*You are strong enough*_\n_(_ $e1 $e2 _ vs $target)_",':muscle:');
            }
            //run follow up page
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
            //run follow up page
            if (isset($fail_page)) {
                addcommand($fail_page);
            }
        }
    }
    if ($cmd[0] == "newgame")
    {
        $player = roll_character();
        send_charsheet("*NEW CHARACTER!*\nType `!0` to begin, or `!newgame` to roll again.");
        send_stuff();
    }
    if ($cmd[0] == "info" || $cmd[0] == "status")
    {
        send_charsheet();
        send_stuff();
    }
    if ($cmd[0] == "stats" || $cmd[0] == "s")
    {
        send_charsheet();
    }
    if ($cmd[0] == "stuff" || $cmd[0] == "i")
    {
        send_stuff();
    }
    if ($cmd[0] == 'help')
    {
        sendqmsg(file_get_contents('help.txt'));
    }
    if ($cmd[0] == 'helpmore')
    {
        senddirmsg(file_get_contents('helpmore.txt'));
    }
    if ($cmd[0] == "fight" && $cmd[1] && is_numeric($cmd[2]))
    {
        if (is_numeric($cmd[1])) {
            $m = "opponent";
            $mskill = $cmd[1];
            $mstam = $cmd[2];
            $maxrounds = $cmd[3];
        }
        else if (is_numeric($cmd[3])) {
            $m = $cmd[1];
            $mskill = $cmd[2];
            $mstam = $cmd[3];
            $maxrounds = $cmd[4];
        }
        else {
            //invalid inputs
            continue;
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
}