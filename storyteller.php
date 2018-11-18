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
$commandlist = explode(";",$_POST['text']);

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
    
    $command = pre_processes_magic($command, $player);

    // Split by whitespace
    // $cmd[0] is the command
    $cmd = preg_split('/\s+/', trim($command));

    // Remove trigger word from command
    $cmd[0] = substr($cmd[0],strlen($_POST['trigger_word']));
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
    $regex = "/^\\s*\\!([A-Za-z]+)";
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
            default:  //misc
                break;
        }
    }
    $regex .= '\s*$/';
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

// Roll a new random character and return a 'player' array ready to be used elsewhere
function roll_character($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
    // Get the type of book
    $gamebook = getbook();

    $p = array('skill' => rand(1,6) + 6,             //1d6+6
               'stam' => rand(1,6) + rand(1,6) + 12, //2d6+12
               'luck' => rand(1,6) + 6,              //1d6+6
               'prov' => 10,
               'gold' => 0,
               'weapon' => 0,
               'shield' => false,
               'lastpage' => 1,
               'stuff' => array('Sword (+0)','Leather Armor','Lantern'),
               'gamebook' => $gamebook);

    // Set maximums
    // The game won't (normally) allow you to exceed your initial scores
    $p['max']['skill']  = $p['skill'];
    $p['max']['stam']   = $p['stam'];
    $p['max']['luck']   = $p['luck'];
    $p['max']['prov']   = 999;
    $p['max']['gold']   = 999;
    $p['max']['weapon'] = 999;

    // Character Fluff - Gender, name, race etc.
    if (!$gender || $gender == '?') {
        $gender = (rand(0,1)?'Male':'Female');
    } elseif ($gender == 'm' || $gender == 'M') {
        $gender = 'Male';
    } elseif ($gender == 'f' || $gender == 'F') {
        $gender = 'Female';
    }
    $p['gender'] = ucfirst($gender);
    if (!$name || $name == '?') {
        $names = file($gender=='Male'?'resources/male_names.txt':'resources/female_names.txt');
        $p['name'] = trim($names[array_rand($names)]);
    } else {
        $p['name'] = ucfirst($name);
    }
    if (!$adjective || $adjective == '?') {
        $adjectives = file('resources/adjectives.txt');
        $p['adjective'] = ucfirst(trim($adjectives[array_rand($adjectives)]));
    } else {
        $p['adjective'] = ucfirst($adjective);
    }
    if (!$emoji || $emoji == '?') {
        // Get a random emoji to represent the character
        $male = array(':man:',':blond-haired-man:',':older_man:',':male_elf:',':male_genie:',':smirk_cat:',':bearded_person:');
        $female = array(':woman:',':blond-haired-woman:',':older_woman:',':female_elf:',':female_genie:',':smile_cat:',':bearded_person:');
        $races = array('Human','Human','Human','Elven','Djinnin','Catling','Dwarf');
        $skintone = array(':skin-tone-2:',':skin-tone-3:',':skin-tone-4:',':skin-tone-5:',':skin-tone-2:');

        $selection = array_rand($male);
        $p['race'] = $races[$selection];
        if ($gender == 'Male') {
            $p['emoji'] = $male[$selection].$skintone[array_rand($skintone)];
        } else {
            $p['emoji'] = $female[$selection].$skintone[array_rand($skintone)];
        }
    } else {
        $p['emoji'] = $emoji;
        $p['race'] = 'Human';
    }
    if ($race && $race !== '?') { // Override avatar generated race
        $p['race'] = ucfirst($race);
    }
    
    // End of bare character generation.
    
    // Book customisations
    if ($gamebook == 'wofm' || $gamebook == 'wofm-strict') {
        // Random Potion
        // The book rules actually give you a choice, but this is a bit more fun
        switch(rand(1,3)) {
            case 1:
                $p['stuff'][] = 'Potion of Skill';
                break;
            case 2:
                $p['stuff'][] = 'Potion of Strength';
                break;
            case 3:
                $p['stuff'][] = 'Potion of Luck';
                // If the potion of luck is chosen, the player get 1 bonus luck
                $p['luck']++;
                $p['max']['luck']++;
                break;
        }
        if ($gamebook == 'wofm') {
            // Random Gold (Note this is a customisation from the book's rules)
            $p['gold'] = rand(0,5); //1d6-1
        }
    } elseif ($gamebook == 'rtfm' || $gamebook == 'rtfm-strict') {
        $p['prov'] = rand(0,5); // 1d6-1
        $p['goldzagors'] = 0;
        $p['max']['goldzagors'] = 999;
        if ($gamebook == 'rtfm-strict') {
            $p['prov'] = 0; // No provisions!!
            $p['gamebook'] = 'rtfm';
        }
    }

    return $p;
}

// Figure out what rules we are running
// Books:
// none: No special rules
// wofm: The Warlock on Firetop Mountain
// wofm-strict: As above, with no starting gold.
// rtfm: Return to Firetop Mountain
// rtfm-strict: As above, with no starting provisions.
function getbook()
{
    require("book.php");
    
    if (!isset($gamebook)) {
        return 'none';
    }
    
    $supported_books = array(
        'none','wofm','wofm-strict','rtfm','rtfm-strict');
        
    if (!in_array($gamebook, $supported_books)) {
        return 'none';
    }
    
    return $gamebook;
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
            'title' => 'Provisons (prov)',
            'value' => $player['prov'],
            'short' => true
        ])
    ]);
    
    if ($player['gamebook'] == 'rtfm') {
        $attachments[0]['fields'][4] = array (
            'title' => 'Coins',
            'value' => $player['gold'].' Gold, '.$player['goldzagors'].' Gold Zagors (gz)',
            'short' => true
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
        $s[] .= '*Shield _(Equipped)_*';
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
    return sendmsg($message, true, ':green_book:', '@'.$user);
}

// Send a quick and basic message to slack
function sendqmsg($message, $icon = ':green_book:')
{
    return sendmsg($message, true, $icon);
}

// Send an image to slack
function sendimgmsg($message, $imgurl, $icon = ':green_book:')
{
    $attachments = array([
            'image_url'    => $imgurl
    ]);
    return sendmsg($message, $attachments, $icon);
}

// Full whistles and bells send message to slack
// Normally use one of the convenience functions above
function sendmsg($message, $attachments = array(), $icon = ':green_book:', $chan = false)
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
    require_once("book.php");

    // Look for choices in the text and give them bold formatting
    $story = preg_replace('/\(?turn(ing)? to [0-9]+\)?/i', '*${0}*', $text);
    $story = preg_replace('/Your (adventure|quest) (is over|ends here)\./i', '*${0}*', $story);
    
    // Wrapping and formatting
    $story = str_replace("\n","\n\n",$story);
    $story = wordwrap($story,100);
    $story = explode("\n", $story);
    for ($l = 0; $l < sizeof($story); $l++) {
        if (trim($story[$l]) == "") {
            $story[$l] = "> ";
        } else {
            $story[$l] = "> _".$story[$l];
            if (substr_count($story[$l],'*') % 2 != 0) {
                $story[$l] .= '*_';
                if (array_key_exists($l+1,$story)) {
                    $story[$l+1] = "*".$story[$l+1];
                }
            } else {
                $story[$l] .= '_';
            }
        }
    }
    $story = "> ~ *$page* ~\n".implode("\n",$story);
    
    return $story;
}
