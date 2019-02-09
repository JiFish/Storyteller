<?php

/// ----------------------------------------------------------------------------
/// Functions

// Process command text and call command's function
function processcommand($command, &$player)
{
    global $commandslist, $commandsargs;

    // If we have a trigger word right at the start, strip it now
    $command = trim($command);
    if (stripos($command,$_POST['trigger_word']) === 0) {
        $command = substr($command,strlen($_POST['trigger_word']));
    }

    $command = pre_processes_magic($command, $player);

    // Split by whitespace
    // $cmd[0] is the command
    $cmd = preg_split('/\s+/', trim($command));
    $cmd[0] = trim(strtolower($cmd[0]));

    // Special case for quick page lookup
    if (is_numeric($cmd[0])) {
        $cmd[1] = $cmd[0];
        $cmd[0] = 'page';
        _cmd_page($cmd,$player);
        return;
    }

    // look for a command function to call
    if (array_key_exists($cmd[0],$commandslist)) {
        $cmd = advanced_command_split($command,$commandsargs[$cmd[0]]);
        if (!$cmd) {
            sendqmsg("Sorry, I didn't understand that command!",":interrobang:");
        } else {
            call_user_func_array($commandslist[$cmd[0]],array($cmd,&$player));
        }
    }
}

function pre_processes_magic($command, &$player)
{
    // magic to allow semi-colons
    $command = str_replace("{sc}",";",$command);

    // magic to substitute dice rolls
    $command = preg_replace_callback(
        '/{([1-9][0-9]?)d([1-9][0-9]{0,2})?([+|\-][1-9][0-9]{0,2})?}/',
        function ($matches) {
            $roll = 0;
            if (!isset($matches[2]) || !$matches[2]) {
                $matches[2] = 6;
            }
            foreach(range(1,$matches[1]) as $i) { 
               $roll += rand(1,$matches[2]);
            }
            if (isset($matches[3])) {
                $roll += $matches[3];
            }
            return $roll;
        },
        $command
    );

    // magic to substitute player vars
    $command = preg_replace_callback(
        '/{(.+?)(\[(.+?)\])?}/',
        function ($matches) use ($player) {
            if (isset($matches[3]) && array_key_exists($matches[1],$player)
            && array_key_exists($matches[3],$player[$matches[1]])) {
                return $player[$matches[1]][$matches[3]];
            } elseif (!isset($matches[3]) && array_key_exists($matches[1],$player)) {
                if (is_array($player[$matches[1]])) {
                    return str_replace("\n"," ",var_export($player[$matches[1]],1));
                } elseif (is_bool($player[$matches[1]])) {
                    return ($player[$matches[1]]?'on':'off');
                }
                return $player[$matches[1]];
            } elseif ($matches[1] == 'all') {
                return str_replace("\n"," ",var_export($player,1));
            }
            return $matches[0];
        },
        $command
    );

    return $command;
}

function advanced_command_split($command,$def)
{
    $regex = "/^\\s*(\\S+)";
    foreach ($def as $d) {
        switch($d) {
            case 'l':  //whole line
                $regex .= "\s+(.+)";
                break;
            case 'ol':  //optional whole line
                $regex .= "(\s+.+)?";
                break;
            case 'oms':  //optional multi string (hard, doesn't match numbers)
                $regex .= "(\s+(?![0-9]+).+?)?";
                break;
            case 'ms':  //multi string (hard, doesn't match numbers)
                $regex .= "\s+((?![0-9]+).+?)";
                break;
            case 'osl':  //optional string (loose, matches numbers)
                $regex .= "(\s+[^\s]+)?";
                break;
            case 'os':  //optional string (hard, doesn't match numbers)
                $regex .= "(\s+(?![0-9]+)[^\s]+)?";
                break;
            case 's':  //string (loose, matches numbers)
                $regex .= "\s+([^\s]+)";
                break;
            case 'on':  //optional number
                $regex .= "(\s+[0-9]+)?";
                break;
            case 'n':  //number
                $regex .= "\s+([0-9]+)";
                break;
            case 'onm':  //optional number modifier
                $regex .= "(\s+[+\-]?[0-9]+)?";
                break;
            case 'nm':  //number modifier
                $regex .= "\s+([+\-]?[0-9]+)";
                break;
            case 'spell': //spell name
                require('logic/spells.php');
                $regex .= "\s+(";
                foreach ($spells as $s) {
                    $regex .= preg_quote($s['name']).'|';
                }
                $regex = substr($regex,0,-1);
                $regex .= ")";
                break;
            default:  //misc
                $regex .= $d;
                break;
        }
    }
    $regex .= '\s*$/i';
    $matches = array();

    if (!preg_match($regex, $command, $matches)) {
        return false;
    }

    array_shift($matches);
    $matches = array_map('trim', $matches);
    $matches = array_pad($matches,sizeof($def)+1,null);
    //print_r($matches);
    return $matches;
}

/// register new command
function register_command($name, $function, $args = [])
{
    global $commandslist, $commandsargs;

    if (!is_array($commandslist)) {
        $commandslist = array();
    }

    $commandslist[$name] = $function;
    $commandsargs[$name] = $args;
}

// Figure out what rules we are running
function getbook()
{
    if (!defined("BOOK_TYPE")) {
        return 'none';
    }

    $supported_books = array('none','custom','wofm','dotd','coh','poe','bvp','rtfm',
                             'loz','tot','hoh','sob','sst');

    if (!in_array(BOOK_TYPE, $supported_books)) {
        return 'none';
    }

    return BOOK_TYPE;
}


// Load the player array from a serialized array
// If we can't find the file, generate a new character
function load()
{
    $save = file_get_contents('save.txt');
    if (!$save) {
        require_once('logic/roll_character.php');
        $p = roll_character();
    }
    else {
        $p = unserialize($save);
    }

    return $p;
}

// Serialize and save player array
function save(&$p, $file="save.txt")
{
    file_put_contents($file,serialize($p));
}

// Convert number to html entity of dice emoji
function diceemoji($r)
{
    if ($r >= 1 && $r <= 6) {
        return mb_convert_encoding('&#'.(9855+$r).';', 'UTF-8', 'HTML-ENTITIES');
    } elseif ($r >= 7 && $r <= 9) {
        return mb_convert_encoding('&#'.(127000+$r).';', 'UTF-8', 'HTML-ENTITIES');
    }

    return "[$r]";
}

// Adds a new command to the command list
function addcommand($cmd)
{
    global $commandlist;
    return array_unshift($commandlist,$cmd);
}

/// ----------------------------------------------------------------------------
/// Send message to slack functions

// Convert the player array to a character sheet and send it to slack
// along with message $text
function send_charsheet($player, $text = "", $sendstuff = false)
{
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
            'value' => sprintf("%+d",$player['weapon']),
            'short' => true
        ],
        [
            'title' => 'Gold',
            'value' => $player['gold'],
            'short' => true
        ],
        [
            'title' => 'Provisions (prov)',
            'value' => $player['prov'],
            'short' => true
        ])
    ]);

    if (isset($player['max']['magic'])) {
        array_splice($attachments[0]['fields'], 3, 0,
            array([
                'title' => 'Magic',
                'value' => $player['magic']." / ".$player['max']['magic'],
                'short' => true
            ])
        );
    }

    $gamebook = getbook();
    if ($gamebook == 'rtfm') {
        $attachments[0]['fields'][3] = array (
            'title' => 'Weapon: '.sprintf("%+d",$player['weapon']),
            'value' => '*Provisions: '.$player['prov'].'*',
            'short' => true
        );
        $attachments[0]['fields'][5] = array (
            'title' => 'Gold Zagors (gz)',
            'value' => $player['goldzagors'],
            'short' => true
        );
    }

    if ($gamebook == 'loz') {
        $attachments[0]['fields'] = array_merge($attachments[0]['fields'],
        array([
                'title' => 'Talismans: '.$player['talismans'],
                'value' => '*Daggers: '.$player['daggers'].'*',
                'short' => true
            ],[
                'title' => 'Advantages',
                'value' => $player['advantages'],
                'short' => false
            ],[
                'title' => 'Disadvantages',
                'value' => $player['disadvantages'],
                'short' => false
            ])
        );
    }

    if ($gamebook == 'hoh') {
        $attachments[0]['fields'][4] = array (
            'title' => 'Fear',
            'value' => $player['fear']." / ".$player['max']['fear'],
            'short' => true
        );
        unset($attachments[0]['fields'][5]);
    }

    // Sea of Blood Ship Stats
    if ($gamebook == 'sob') {
        // QOL for discord with 3 per row instead of two
        if (DISCORD_MODE) {
            $attachments[0]['fields'][0]['value'] .= '  (Weapon: '.sprintf("%+d",$player['weapon']).')';
            unset($attachments[0]['fields'][3]);
        }
        unset($attachments[0]['fields'][4]);
        unset($attachments[0]['fields'][5]);
        $attachments[] = [
            'color'    => '#8b4513',
            'fields'   => array(
            [
                'title' => 'Ship Name',
                'value' => $player['shipname'],
                'short' => true
            ],
            [
                'title' => 'Crew Strike (strike)',
                'value' => $player['strike']." / ".$player['max']['strike'],
                'short' => true
            ],
            [
                'title' => 'Crew Strength (str)',
                'value' => $player['str']." / ".$player['max']['str'],
                'short' => true
            ],
            [
                'title' => 'Booty (Gold)',
                'value' => $player['gold'],
                'short' => true
            ],
            [
                'title' => 'Slaves',
                'value' => $player['slaves'],
                'short' => true
            ],
            [
                'title' => 'Log',
                'value' => $player['log'].' days',
                'short' => true
            ])
        ];
    }

    // starship traveller crew & ship
    if ($gamebook == 'sst') {
        // ship
        $attachments[0]['fields'][3] = [
            'title' => 'Ship',
            'value' => $player['shipname'],
            'short' => true
        ];
        $attachments[0]['fields'][4] = [
            'title' => 'Weapons (weapons)',
            'value' => $player['weapons']." / ".$player['max']['weapons'],
            'short' => true
        ];
        $attachments[0]['fields'][5] = [
            'title' => 'Shields',
            'value' => $player['shields']." / ".$player['max']['shields'],
            'short' => true
        ];
        // crew
        $cname = "";
        $cskill = "";
        $cstam = "";
        $cboth = "";
        foreach ($player['crew'] as $cm) {
            $thisname = '*'.($cm['awayteam']?' *⇓*':'').$cm['position'].':* '.$cm['name']." ".($cm['gender']=='Male'?'♂':'♀')." ".$cm['race'];
            $cname .= mb_substr($thisname, 0, 36)."\n";
            $cskill .= $cm['skill'].' / '.$cm['max']['skill'].($cm['combatpenalty']?' *†*':'')."\n";
            $cstam .= $cm['stam'].' / '.$cm['max']['stam'].($cm['replacement']?' *R*':'')."\n";
            $cboth .= 'SKILL: '.$cm['skill'].' / '.$cm['max']['skill'].' | STAMINA: '.$cm['stam'].' / '.$cm['max']['stam'].($cm['combatpenalty']?' *†*':'').($cm['replacement']?' *R*':'')."\n";
        }
        $fields = array([ 'title' => 'Crew (⇓: away team)',
                          'value' => $cname,
                          'short' => true ]);
        // Discord QOL
        if (DISCORD_MODE) {
            $fields[] = ['title' => 'Skill (†: -2 in combat)',
                         'value' => $cskill,
                         'short' => true ];
            $fields[] = ['title' => 'Stamina (R: Replaced)',
                         'value' => $cstam,
                         'short' => true ];
        } else {
            $fields[] = ['title' => 'Stats (†: -2 in combat, R: Replaced)',
                         'value' => $cboth,
                         'short' => true ];
        }
        $attachments[] = [
            'color'    => '#BB0000',
            'fields'   => $fields ];
    }

    if ($sendstuff) {
        $attachments[] = get_stuff_attachment($player);
    }

    if ($player['stam'] < 1) {
        $icon = ":skull:";
    } else {
        $icon = $player['emoji'];
    }

    sendmsg(($text?$text."\n":'').'*'.$player['name']."* the ".$player['adjective']." _(".$player['gender']." ".$player['race'].")_",$attachments,$icon);
}

// Send to slack a list of the player's stuff (inventory)
function send_stuff($player)
{
    $attachments[] = get_stuff_attachment($player);

    if ($player['stam'] < 1) {
        $icon = ":skull:";
    } else {
        $icon = $player['emoji'];
    }

    sendmsg("",$attachments,$icon);
}

function get_stuff_attachment(&$player) {
    $s = $player['stuff'];

    // Special inventory
    if ($player['shield']) {
        $s[] .= 'Shield *(Equipped)*';
    }

    if (sizeof($s) == 0) {
        $s[] = "(Nothing!)";
    } else {
        natcasesort($s);
        $s = array_map("ucfirst",$s);
    }

    $attachments = array(
            'color'    => '#0066ff',
            'fields'   => array(
            [
                'title' => 'Inventory',
                'value' => implode("\n",array_slice($s, 0, ceil(sizeof($s) / 2))),
                'short' => true
            ],
            [
                'title' => "",
                'value' => html_entity_decode("&nbsp;")."\n".implode("\n",array_slice($s, ceil(sizeof($s) / 2))),
                'short' => true
            ])
    );

    return $attachments;
}

function format_story($page, $text, &$player) {
    require("book.php");

    // Book specific specials
    $gamebook = getbook();
    if ($gamebook == 'sob') {
        $text = str_ireplace('The Banshee',$player['shipname'],$text);
    }
    if ($gamebook == 'sst') {
        $text = str_ireplace('The Traveller',$player['shipname'],$text);
        $text = str_ireplace('Starship Traveller','Starship '.substr($player['shipname'],4),$text);
    }

    // Look for choices in the text and give them bold formatting
    $story = preg_replace('/\(?turn(ing)? to [0-9]+\)?/i', '*${0}*', $text);
    $story = preg_replace('/Your (adventure|quest) (is over|ends here|is at an end)\.?/i', '*${0}*', $story);
    $story = preg_replace('/((Add|Subject|Deduct|Regain|Gain|Lose) )?([1-9] (points? )?from your (SKILL|LUCK|STAMINA)|([1-9] )?(SKILL|LUCK|STAMINA) points?|your (SKILL|LUCK|STAMINA))/', '*${0}*', $story);

    // Wrapping and formatting
    $story = str_replace("\n","\n\n",$story);
    $story = wordwrap($story,100);
    $story = explode("\n", $story);
    for ($l = 0; $l < sizeof($story); $l++) {
        if (trim($story[$l]) == "") {
            $story[$l] = "> ";
        } else {
            // Prevent code blocks from linebreaking
            if (substr_count($story[$l],'`') % 2 != 0) {
                if (array_key_exists($l+1,$story)) {
                    $story[$l+1] = $story[$l].' '.$story[$l+1];
                    $story[$l] = '';
                    continue;
                }
            }

            // Deal with bold blocks across lines
            if (substr_count($story[$l],'*') % 2 != 0) {
                $story[$l] .= '*';
                if (array_key_exists($l+1,$story)) {
                    $story[$l+1] = "*".$story[$l+1];
                }
            }

            // Italic and quote
            $story[$l] = "> _".$story[$l].'_';
        }
    }
    $story = "> — *$page* —\n".implode("\n",$story);

    return $story;
}

function make_seed()
{
  list($usec, $sec) = explode(' ', microtime());
  return (int)($sec + $usec * 1000000);
}

function apply_temp_stats(&$player)
{
    foreach ($player['temp'] as $k => $v) {
        if (array_key_exists($k,$player)) {
            $player[$k] += $v;
        }
    }
}

function unapply_temp_stats(&$player)
{
    foreach ($player['temp'] as $k => $v) {
        if (array_key_exists($k,$player)) {
            $player[$k] -= $v;
        }
    }
    $player['temp'] = array();
}

function backup_player(&$p)
{
    save($p, 'save_backup.txt');
}

function backup_remove()
{
    if (file_exists('save_backup.txt')) {
        unlink('save_backup.txt');
    }
}

function restore_player(&$p)
{
    if (file_exists('save_backup.txt')) {
        unlink('save.txt');
        copy('save_backup.txt','save.txt');
        $p = load();
        return true;
    }
    return false;
}

function basic_num_to_word($num) {
    switch($num) {
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
