<?php

// Other configuration settings. You can override these in config.php if you wish
define("MAX_EXECUTIONS",30);
require('config.php');
require('commands.php');


// Check the incoming data for the secret slack token
if ($_POST['token'] != SLACK_TOKEN) {
    header('HTTP/1.0 403 Forbidden');
    die('Access Denied. Token does not match');
}

// Uncomment for command-line debugging
/*if (isset($argv[1])) {
    $_POST['text'] = implode(" ",array_slice($argv,1));
    $_POST['trigger_word'] = '!';
}*/

$player = load();
register_commands($player['gamebook']);

// Split the command list by semi-colons. Allows multiple commands to be queued
// Note, some commands will queue other commands
// Note $commandlist is referenced as a global variable in the below functions.
$commandlist = explode(";",html_entity_decode($_POST['text']));

$executions = 0;
while (sizeof($commandlist) > 0)
{
    // Process the next command in the list
    processcommand(array_shift($commandlist),$player);

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
            case 'oms':  //optional multi string (hard, doesn't match numbers)
                $regex .= "(\s+(?![0-9]+).+?)?";
                break;
            case 'ms':  //multi string
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
                require('spells.php');
                $regex .= "\s+(";
                foreach ($spells as $s) {
                    $regex .= preg_quote($s['name']).'|';
                }
                $regex = substr($regex,0,-1);
                $regex .= ")";
                break;
            default:  //misc
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
    print_r($matches);
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
// Books:
// none: No special rules
// wofm: The Warlock on Firetop Mountain
// wofm-strict: As above, with no starting gold.
// rtfm: Return to Firetop Mountain
// rtfm-strict: As above, with no starting provisions.
// loz: Legend of Zagor
function getbook()
{
    require("book.php");

    if (!defined("BOOK_TYPE")) {
        return 'none';
    }

    $supported_books = array(
        'none','wofm','wofm-strict','rtfm','rtfm-strict','loz');

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
        $p = roll_character();
    }
    else {
        $p = unserialize($save);
    }

    return $p;
}

// Serialize and save player array
function save($p)
{
    file_put_contents("save.txt",serialize($p));
}

// Convert number to html entity of dice emoji
function diceemoji($r)
{
    if ($r < 1 || $r > 6)
        return "BADDICE";

    return mb_convert_encoding('&#x'.(2679+$r).';', 'UTF-8', 'HTML-ENTITIES');
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
function send_charsheet($player, $text = "")
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

    if ($player['max']['magic'] > 0) {
        array_splice($attachments[0]['fields'], 3, 0,
            array([
                'title' => 'Magic',
                'value' => $player['magic']." / ".$player['max']['magic'],
                'short' => true
            ])
        );
    }

    if ($player['gamebook'] == 'rtfm') {
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

    if ($player['gamebook'] == 'loz') {
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

    if ($player['stam'] < 1) {
        $icon = ":skull:";
    } else {
        $icon = $player['emoji'];
    }

    sendmsg($text."\n*".$player['name']."* the ".$player['adjective']." _(".$player['race']." ".$player['gender'].")_",$attachments,$icon);
}

// Send to slack a list of the player's stuff (inventory)
function send_stuff($player)
{
    $s = $player['stuff'];
    if (sizeof($s) == 0) {
        $s[] = "(Nothing!)";
    } else {
        natcasesort($s);
        $s = array_map("ucfirst",$s);
    }

    if ($player['shield']) {
        $s[] .= '*Shield* _(Equipped)_';
    }

    $attachments = array([
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
    ]);

    if ($player['stam'] < 1) {
        $icon = ":skull:";
    } else {
        $icon = $player['emoji'];
    }

    sendmsg("",$attachments,$icon);
}

// Send a direct message to a user or channel on slack
function senddirmsg($message, $user = false)
{
    if (!$user) {
        $user = $_POST['user_id'];
    }
    return sendmsg($message, true, ':open_book:', '@'.$user);
}

// Send a quick and basic message to slack
function sendqmsg($message, $icon = ':open_book:')
{
    return sendmsg($message, true, $icon);
}

// Send an image to slack
function sendimgmsg($message, $imgurl, $icon = ':open_book:')
{
    $attachments = array([
            'image_url'    => $imgurl
    ]);
    return sendmsg($message, $attachments, $icon);
}

// Full whistles and bells send message to slack
// Normally use one of the convenience functions above
function sendmsg($message, $attachments = array(), $icon = ':open_book:', $chan = false)
{
    $data = array(
        'text'        => $message,
        'attachments' => $attachments
    );
    if ($chan) {
        $data['channel'] = $chan;
    }
    if (strpos($icon,'https://') === false) {
        $data['icon_emoji'] = $icon;
    } else {
        $data['icon_url'] = str_replace(['<','>'],'',$icon);
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
    //echo $message."\n\n";
    //return true;
}

function format_story($page,$text) {
    require("book.php");

    // Look for choices in the text and give them bold formatting
    $story = preg_replace('/\(?turn(ing)? to [0-9]+\)?/i', '*${0}*', $text);
    $story = preg_replace('/Your (adventure|quest) (is over|ends here)\.?/i', '*${0}*', $story);
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
    $story = "> ~ *$page* ~\n".implode("\n",$story);

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
