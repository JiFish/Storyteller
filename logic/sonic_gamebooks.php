<?php
// Functions for the sonic the hedgehog gamebooks


//// !test <luck/skill/stam> (run a skill test) SONIC VERSION
function _cmd_sonictest($cmd, &$player)
{
    // Apply temp bonuses, if any
    apply_temp_stats($player);

    $stat = strtolower($cmd[1]);
    if (in_array($stat,['speed','str','strength','agility','cool','wits','looks'])) {
        $mod = $player[$stat];
    } elseif (!is_numeric($stat)) {
        sendqmsg("*Don't know how to test ".$stat."*",':interrobang:');
        return;
    } else {
        $mod = (int)$stat;
    }
    $target = $cmd[2];
    // Setup outcome pages to read if provided
    if ($cmd[3]) {
        $success_page = "page ".$cmd[3]." nobackup";
    }
    if ($cmd[4]) {
        $fail_page = "page ".$cmd[4]." nobackup";
    }

    // Aliases
    if ($stat == "strength") $stat = "str";
    
    // Describer
    switch ($stat) {
        case 'speed':
            $desc = 'fast';
            break;
        case 'agility':
            $desc = 'agile';
            break;
        case 'wits':
            $desc = 'quick witted';
            break;
        case 'str':
            $desc = 'strong';
            break;
        case 'cool':
            $desc = 'cool';
            break;
        case 'looks':
            $desc = 'looking good';
            break;
        default:
            $desc = $stat;
    }

    // Roll dice
    $d1 = rand(1,6);
    $emojidice = diceemoji($d1).'+'.$mod;

    // Check roll versus target number
    if ($d1+$mod >= $target) {
        if (!is_numeric($stat)) {
            sendqmsg("_*".$player['name']." is $desc!*_\n_(_ $emojidice _ vs $target)_",':smile:');
        } else {
            sendqmsg("_*Test passed!*_\n_(_ $emojidice _ vs $target)_",':smile:');
        }
        // Show follow up page
        if (isset($success_page)) {
            addcommand($success_page);
        }
    }
    else {
        if (!is_numeric($stat)) {
            sendqmsg("_*".$player['name']." is not $desc!*_\n_(_ $emojidice _ vs $target)_",':frowning:');
        } else {
            sendqmsg("_*Test failed!*_\n_(_ $emojidice _ vs $target)_",':frowning:');
        }
        // Show follow up page
        if (isset($fail_page)) {
            addcommand($fail_page);
        }
    }

    // Remove temp bonuses, if any and clear temp bonus array
    unapply_temp_stats($player);
}

//// !test <luck/skill/stam> (run a skill test) SONIC VERSION
function _cmd_sonichit($cmd, &$player)
{
    if ($player['rings'] > 0) {
        $player['rings'] = 0;
        sendqmsg("_*".$player['name']." lost all ".($player['gender']=='Male'?'his':'her')." rings!*_",':ring:');
        return;
    }
    $player['stam']--;
    sendqmsg("_*".$player['name']." lost a life! ".$player['stam']." lives left!*_",':frowning:');
}

//// !newgame (roll new character) SONIC VERSION
function _cmd_sonicnewgame($cmd, &$player)
{
    require_once('logic/roll_character.php');
    $stats = array_slice($cmd, 1);
    $stattotal = array_sum($stats);
    $extratext = "";
    if ($stattotal > 0 && $stattotal != 18) {
        sendqmsg("*Stats should add to 18. $stattotal given. Use any combination of 5, 4, 3, 2, 2, 2.*",':interrobang:');
        return;
    } elseif ($stattotal < 1) {
        $stats = null;
        $extratext = "\nYou can customise sonic by providing his stats in the order speed, strength, agility, cool, wits and looks. e.g. `!".$cmd[0]." 5 4 3 2 2 2`"; 
    }
    $player = roll_sonic_character($stats);
    send_charsheet($player, "_*NEW GAME!*_ ".$extratext,true);
}

//// !fight [stat] <+/-mod> <name> [skill] (run fight logic) SONIC VERSION
function _cmd_sonicfight($cmd, &$player)
{
    $stat = $cmd[1];
    if ($stat == 'strength') $stat = 'str';

    $validstats = ['speed','agility','cool','wits','looks','stam','str'];
    if (!in_array($stat, $validstats)) {
        sendqmsg("*$stat is not a valid stat.*",':interrobang:');
        return;
    }

    $out = run_sonic_fight($player, $player[$stat], $cmd[2], $cmd[3], $cmd[4]);
    sendqmsg($out,":crossed_swords:");
}

function roll_sonic_character($statarray = null) {
    $gamebook = getbook();

    $p['creationdice'] = [];
    $p['name'] = 'Sonic';
    $p['adjective'] = 'Hedgehog';
    $p['gender'] = 'Male';
    $p['race'] = 'Anthropomorphic Hedgehog';
    $p['emoji'] = ':hedgehog:';
    $p['referrers'] = ['you' => 'Sonic', 'youare' => 'Sonic is', 'your' =>"Sonic's"];
    $p['colourhex'] = '#0066ff';
    if (!$statarray) {
        $statarray = [5,4,3,2,2,2];
        shuffle($statarray);
    }
    $statarray[] = 0; $statarray[] = 3;
    foreach (['speed','str','agility','cool','wits','looks','rings','stam'] as $stat) {
        $p[$stat] = array_shift($statarray);
        $p['max'][$stat] = 999;
        $p['temp'][$stat] = 0;
    }
    $p['max']['stam'] = 20;
    
    if ($gamebook == 'sonicmcm') {
        $p['egghits'] = 0;
        $p['max']['egghits'] = 999;
    }
    
    switch ($gamebook) {
        case 'sonicmcm':
            $p['stuff'] = array('Red Trainers','Sega Game Gear','Botman Cartridge');
            break;
        case 'soniczr':
            $p['stuff'] = array('Red Trainers','White Gloves');
            break;
        default:
            $p['stuff'] = array();
    }
    
    if ($gamebook == 'soniczr') {
        $tails['name'] = 'Tails';
        $tails['adjective'] = 'Fox';
        $tails['gender'] = 'Male';
        $tails['race'] = 'Anthropomorphic Fox';
        $tails['emoji'] = ':fox:';
        $tails['referrers'] = ['you' => 'Tails', 'youare' => 'Tails is', 'your' =>"Tail's"];
        $tails['temp'] = [];
        $statarray = [5,4,3,2,2,2];
        shuffle($statarray);
        $statarray[] = 0; $statarray[] = 3;
        foreach (['speed','str','agility','cool','wits','looks','rings','stam'] as $stat) {
            $tails[$stat] = array_shift($statarray);
            $tails['max'][$stat] = 999;
            $tails['temp'][$stat] = 0;
        }
        $p['crew']['tails'] = $tails;
    }

    // Undocumented hook to allow the config file to alter new players
    if (function_exists('hook_alter_new_player')) {
        hook_alter_new_player($p);
    }

    return $p;
}

function run_sonic_fight(&$player, $skill, $mod, $monster, $mskill) {
    $mod = ($mod?(int)$mod:0);
    $monster = ($monster?$monster:'Badnik');
    $out = '';
    
    while (1) {
        // Sonic hit
        $roll = rand(1,6);
        if ($mod == 0) {
            $teststr = diceemoji($roll)."+$skill vs $mskill";
        } else {
            $teststr = diceemoji($roll)."+$skill ".sprintf("%+d",$mod)." vs $mskill";
        }
        if ($roll+$skill+$mod >= $mskill) {
            $out .= "_*".$player['name']." has defeated the $monster!*_ ($teststr)\n";
            break;
        } else {
            $out .= "_".$player['name']." missed the $monster._ ($teststr)\n";
        }
        
        // Monster hit
        $roll = rand(1,6);
        $teststr = diceemoji($roll)."+$mskill vs 10";
        if ($roll+$mskill >= 10) {
            $out .= "_$monster has hit ".$player['name']."!_ ($teststr)\n";
            if ($player['rings'] > 0) {
                $out .= "_*".$player['name']." lost all ".($player['gender']=='Male'?'his':'her')." rings!*_\n";
                $player['rings'] = 0;
            } else {
                $out .= "_*".$player['name']." lost a life!*_\n";
                $player['stam']--;
                if ($player['stam'] > 0) {
                    $out .= "Sonic Lives: ".str_repeat(html_entity_decode('&#x1f994;').' ',$player['stam']);
                } else {
                    $out .= "*GAME OVER*";
                }
                break;
            }
        } else {
            $out .= "_$monster missed ".$player['name']."._ ($teststr)\n";
        }
    }
    
    return $out;
}

//// !help (send sonic help)
function _cmd_sonichelp($cmd, &$player)
{
    $help = file_get_contents('resources/sonic_help.txt');
    // Replace "!" with whatever the trigger word is
    $help = str_replace("!",$_POST['trigger_word'],$help);
    if (getbook() == 'soniczr') {
        $help .= "`!tails [command]` Ask tails to do something. e.g. `!tails test agility 4`\n";
    }
    $helpurl = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'commands.html';
    sendqmsg($help."\nMore commands can be found here: ".$helpurl);
}

function send_charsheet_sonic(&$player, $text = "", $sendstuff = false) {
    $gamebook = getbook();

    $attachments[0]['fields'] = [
        ['title' => 'Speed: '.$player['speed'],
         'value' => '*Strength: '.$player['str'].'*',
         'short' => true],
        ['title' => 'Agility: '.$player['agility'],
         'value' => '*Cool: '.$player['cool'].'*',
         'short' => true],
        ['title' => 'Wits: '.$player['wits'],
         'value' => '*Looks: '.$player['looks'].'*',
         'short' => true],
        ['title' => 'Lives: '.str_repeat(html_entity_decode('&#x1f994;').' ',$player['stam']),
         'value' => '*Rings: '.$player['rings'].'*',
         'short' => true],
    ];
    if ($gamebook == 'sonicmcm') {
        $attachments[0]['fields'][] = ['title' => 'Egghits: '.$player['egghits'],
                                       'value' => null,
                                       'short' => true];
    }

    // Discord QOL
    if (DISCORD_MODE) {
        $attachments[0]['fields'][3]['value'] = null;
        $attachments[0]['fields'][] = [
            'title' => 'Rings: '.$player['rings'],
            'value' => null,
            'short' => true];
    }

    if ($gamebook == 'soniczr') {
        $attachments[1]['color'] = '#ff6600';
        $attachments[1]['fields'] = [
            ['title' => 'Tails Speed: '.$player['crew']['tails']['speed'],
             'value' => '*Tails Strength: '.$player['crew']['tails']['str'].'*',
             'short' => true],
            ['title' => 'Tails Agility: '.$player['crew']['tails']['agility'],
             'value' => '*Tails Cool: '.$player['crew']['tails']['cool'].'*',
             'short' => true],
            ['title' => 'Tails Wits: '.$player['crew']['tails']['wits'],
             'value' => '*Tails Looks: '.$player['crew']['tails']['looks'].'*',
             'short' => true],
            ['title' => 'Tails Lives: '.str_repeat(html_entity_decode('&#x1f98a;').' ',$player['crew']['tails']['stam']),
             'value' => '*Tails Rings: '.$player['crew']['tails']['rings'].'*',
             'short' => true],
        ];

        // Discord QOL
        if (DISCORD_MODE) {
            $attachments[1]['fields'][3]['value'] = null;
            $attachments[1]['fields'][] = [
                'title' => 'Tails Rings: '.$player['crew']['tails']['rings'],
                'value' => null,
                'short' => true];
        }
    }

    if ($sendstuff) {
        $attachments[] = get_stuff_attachment($player);
    }

    if ($player['stam'] < 1) {
        $icon = ":skull:";
    } else {
        $icon = $player['emoji'];
    }

    sendmsg(($text?$text."\n":'').'*'.$player['name']."* the ".$player['adjective'],$attachments,$icon);
}
