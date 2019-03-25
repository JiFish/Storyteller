<?php

require_once 'logic/RandomColor.php';
require_once 'logic/dice.php';

class book_none {
    public function getId() {
        return 'none';
    }


    public function getStats() {
        return [];
    }


    public function isDead(&$player) {
        return false;
    }


    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = array('lastpage' => 1,
            'stuff' => [],
            'creationdice' => [],
            'temp' => [],
            'seed' => $seed);
        // Roll/Set stats!
        roll_stats($p, $this->getStats());
        // Character Fluff - Gender, name, race etc.
        if (!$gender || $gender == '?') {
            $gender = (rand(0, 1)?'Male':'Female');
            if (rand(0, 99) == 0) {
                $gender = array('Agender', 'Androgynous', 'Gender neutral', 'Genderfluid',
                    'Genderless', 'Non-binary', 'Transgender')[rand(0, 6)];
            }
        } elseif ($gender == 'm' || $gender == 'M') {
            $gender = 'Male';
        } elseif ($gender == 'f' || $gender == 'F') {
            $gender = 'Female';
        }
        $p['gender'] = ucfirst(strtolower($gender));
        $p['race'] = 'Human';
        // Name and adjective
        if (!$name || $name == '?') {
            if ($gender=='Male') {
                $namesfile = 'resources/male_names.txt';
            } else {
                $namesfile = 'resources/female_names.txt';
            }
            $names = file($namesfile);
            $p['name'] = trim($names[array_rand($names)]);
        } else {
            $p['name'] = ucfirst($name);
        }
        // Adjective
        if (!$adjective || $adjective == '?') {
            $adjectives = file('resources/adjectives.txt');
            $p['adjective'] = ucfirst(trim($adjectives[array_rand($adjectives)]));
        } else {
            $p['adjective'] = ucfirst($adjective);
        }
        // Determine emoji
        if (!$emoji || $emoji == '?') {
            $skintone = array(':skin-tone-2:', ':skin-tone-3:', ':skin-tone-4:', ':skin-tone-5:', ':skin-tone-2:');
            if ($gender == 'Male') {
                $emojilist = array(':man:', ':blond-haired-man:', ':older_man:');
            } elseif ($gender == 'Female') {
                $emojilist = array(':woman:', ':blond-haired-woman:', ':older_woman:');
            } else {
                $emojilist = array(':adult:', ':person_with_blond_hair:', ':older_adult:');
            }
            $p['emoji'] = $emojilist[array_rand($emojilist)].$skintone[array_rand($skintone)];
        } else {
            $p['emoji'] = $emoji;
        }
        // Random Colour
        $p['colourhex'] = \Colors\RandomColor::one();

        return $p;
    }


    // In Slack format
    protected function getCharcterSheetAttachments(&$player) {
        return [];
    }


    // In Slack format
    protected function getStuffAttachment(&$player) {
        $s = $player['stuff'];

        if (sizeof($s) == 0) {
            $s[] = "(Nothing!)";
        } else {
            natcasesort($s);
            $s = array_map("ucfirst", $s);
        }

        $attachments = array(
            'color'    => '#666666',
            'fields'   => array(
                [
                    'title' => 'Inventory',
                    'value' => implode("\n", array_slice($s, 0, ceil(sizeof($s) / 2))),
                    'short' => true
                ],
                [
                    'title' => html_entity_decode("&nbsp;"),
                    'value' => implode("\n", array_slice($s, ceil(sizeof($s) / 2))),
                    'short' => true
                ])
        );

        return $attachments;
    }


    public function registerCommands() {
        register_command('look',       '_cmd_look');
        register_command('page',       '_cmd_page', ['n', 'os']);
        register_command('background', '_cmd_background');
        register_command('get',        '_cmd_get', ['l']);
        register_command('take',       '_cmd_get', ['l']);
        register_command('drop',       '_cmd_drop', ['l']);
        register_command('lose',       '_cmd_drop', ['l']);
        register_command('use',        '_cmd_drop', ['l']);
        register_command('roll',       '_cmd_roll', ['on']);
        register_command('ng',         '_cmd_newgame', ['osl', 'osl', 'osl', 'osl', 'osl', 'on']);
        register_command('newgame',    '_cmd_newgame', ['osl', 'osl', 'osl', 'osl', 'osl', 'on']);
        register_command('help',       '_cmd_help');
        register_command('?',          '_cmd_help');
        register_command('echo',       '_cmd_echo', ['l']);
        register_command('randpage',   '_cmd_randpage', ['n', 'on', 'on', 'on', 'on', 'on', 'on', 'on']);
        register_command('debugset',   '_cmd_debugset', ['s', 'l']);
        register_command('silentset',  '_cmd_debugset', ['s', 'l']);
        register_command('debuglist',  '_cmd_debuglist');
        register_command('macro',      '_cmd_macro', ['n']);
        register_command('m',          '_cmd_macro', ['n']);
        register_command('undo',       '_cmd_undo');
        register_command('save',       '_cmd_save', ['on']);
        register_command('load',       '_cmd_load', ['on']);
        register_command('clearslots', '_cmd_clearslots', ['osl']);
        register_command('map',        '_cmd_map');
        register_command('info',       '_cmd_info');
        register_command('status',     '_cmd_info');
        register_command('stats',      '_cmd_stats');
        register_command('s',          '_cmd_stats');
        register_command('stuff',      '_cmd_stuff');
        register_command('i',          '_cmd_stuff');
    }


    //// !newgame (roll new character)
    function _cmd_newgame($cmd, &$player) {
        $cmd = array_pad($cmd, 7, '?');
        $player = $this->rollCharacter($cmd[1], $cmd[2], $cmd[3], $cmd[4], $cmd[5], $cmd[6]);

        $icon = $player['emoji'];
        $attach = $this->getCharcterSheetAttachments($player);
        $attach[] = $this->getStuffAttachment($player);

        sendmsg("_*NEW CHARACTER!*_ ".implode(' ', array_map("diceemoji", $player['creationdice']))."\n*".$player['name']."* the ".$player['adjective']." _(".$player['gender']." ".$player['race'].")_", $attach, $icon);
    }


    //// !look
    public function _cmd_look($cmd, &$player) {
        require "book.php";
        $story = format_story($player['lastpage'], $book[$player['lastpage']], $player);
        sendqmsg($story);
    }


    //// !page <num> / !<num> (Read page from book)
    public function _cmd_page($cmd, &$player) {
        if (!is_numeric($cmd[1])) {
            return;
        }
        $page = $cmd[1];
        $backup = (isset($cmd[2])?strtolower($cmd[2])!='nobackup':false);

        require "book.php";

        if (array_key_exists($page, $book)) {
            // Save a backup of the player for undo
            if ($backup) {
                backup_player($player);
            }

            $player['lastpage'] = $page;
            $story = $book[$page];

            // Exclude pages using 'if ', 'you may' or 'otherwise'
            // This isn't perfect, but will prevent many false matches
            if (stripos($story, "if ") === false && stripos($story, "you may ") === false
                && stripos($story, "otherwise") === false) {
                // Attempt to find pages that give you only one choice
                // Find pages with only one turn to and add that page to the command list
                preg_match_all('/turn to (section )?([0-9]+)/i', $story, $matches, PREG_SET_ORDER, 0);
                if (sizeof($matches) == 1) {
                    addcommand("page ".$matches[0][2]." nobackup");
                }
                // Attempt to find pages that end the story, kill the player if found
                elseif (sizeof($matches) < 1 &&
                    preg_match('/Your (adventure|quest) (is over|ends here|is at an end)\.?/i', $story, $matches)) {
                    $player['stam'] = 0;
                }
            }

            // Autorun
            if (isset($autorun)) {
                if (array_key_exists($page, $autorun)) {
                    $cmdlist = explode(";", $autorun[$page]);
                    for ($k = count($cmdlist)-1; $k >= 0; $k--) {
                        addcommand($cmdlist[$k]);
                    }
                }
            }

            $story = format_story($player['lastpage'], $story, $player);
        } else {
            sendqmsg("*$page: PAGE NOT FOUND*", ":interrobang:");
            return;
        }

        if (IMAGES_SUBDIR && file_exists('images'.DIRECTORY_SEPARATOR.IMAGES_SUBDIR.DIRECTORY_SEPARATOR.$player['lastpage'].'.jpg')) {
            sendimgmsg($story, 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'images/'.IMAGES_SUBDIR.'/'.$player['lastpage'].'.jpg');
        } else {
            sendqmsg($story);
        }
    }


    //// !background
    public function _cmd_background($cmd, &$player) {
        require "book.php";
        $story = format_story(0, $book[0], $player);
        senddirmsg($story);
    }


    //// !get / !take (add item to inventory/stuff list)
    public function _cmd_get($cmd, &$player) {
        $item = $cmd[1];

        // Prevent duplicate entries
        if (array_search(strtolower($item), array_map('strtolower', $player['stuff'])) !== false) {
            sendqmsg("*You already have '".$item."'. Try giving this item a different name.*", ':interrobang:');
            return;
        }

        // Otherwise just append it to the stuff array
        $player['stuff'][] = $item;
        sendqmsg("*Got the ".$item."!*", ":school_satchel:");
    }


    //// !drop / !lose / !use
    public function _cmd_drop($cmd, &$player) {
        $drop = strtolower($cmd[1]);

        // lazy item search
        $foundkey = null;
        $foundlist = array();
        foreach ($player['stuff'] as $k => $i) {
            // An exact match always drops
            if ($drop == strtolower($i)) {
                $foundkey = $k;
                $foundlist = array($i);
                break;
            }
            // otherwise look for partial matches
            elseif (strpos(strtolower($i), $drop) !== false) {
                $foundkey = $k;
                $foundlist[] = $i;
            }
        }

        if (sizeof($foundlist) < 1) {
            sendqmsg("*'".$drop."' didn't match anything in inventory. Can't ".strtolower($cmd[0]).".*", ':interrobang:');
        } elseif (sizeof($foundlist) > 1) {
            sendqmsg("*Which did you want to ".$cmd[0]."? ".implode(", ", $foundlist)."*", ':interrobang:');
        } else {
            $i = $player['stuff'][$foundkey];
            unset($player['stuff'][$foundkey]);
            switch ($cmd[0]) {
            case 'lose':
                sendqmsg("*Lost the ".$i."!*");
                break;
            case 'drop':
                sendqmsg("*Dropped the ".$i."!*", ":put_litter_in_its_place:");
                break;
            case 'use':
                sendqmsg("*Used the ".$i."!*");
                break;
            }
        }
    }


    //// !roll [x] (roll xd6)
    public function _cmd_roll($cmd, &$player) {
        $numdice = ($cmd[1]?$cmd[1]:1);
        $numdice = max(min($numdice, 100), 1);
        $out = "Result:";

        $t = 0;
        for ($a = 0; $a < $numdice; $a++) {
            $r = rand(1, 6);
            $emoji = diceemoji($r);
            $out .= " $emoji ($r)";
            $t += $r;
        }
        if ($cmd[1] > 1) {
            $out .= " *Total: $t*";
        }
        sendqmsg($out, ":game_die:");
    }


    //// Various statistic adjustment commands
    public function _cmd_stat_adjust($cmd, &$player) {
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
        // $icons contains icons to send
        $thisstat = get_stat_from_alias(strtolower($cmd[0]), $this->getStats());
        $statname = $stats[$thisstat]['friendly'];
        if (strtolower($cmd[1]) == "max") {
            $icons = [':arrow_up:', ':arrow_down:'];
        } elseif (isset($stats[$thisstat]['icons'])) {
            if (is_array($stats[$thisstat]['icons'])) {
                $icons = $stats[$thisstat]['icons'];
            } else {
                $icons = [$stats[$thisstat]['icons'], $stats[$thisstat]['icons']];
            }
        } else {
            $icons = [':open_book:', ':open_book:'];
        }
        if (strtolower($cmd[1]) == "max") {
            $statref = &$player['max'][$thisstat];
            $max = 999;
            $statname = "Maximum $statname";
        } elseif (strtolower($cmd[1]) == "temp") {
            $player['temp'][$thisstat] = 0;
            $statref = &$player['temp'][$thisstat];
            $max = 999;
            $statname = "Temp $statname Bonus";
        } elseif (!$cmd[1]) {
            $statref = &$player[$thisstat];
            $max = $player['max'][$thisstat];
        }
        $val = $cmd[2];

        // apply adjustment to stat
        $oldval = $statref;
        if ($val[0] == "+") {
            $val = substr($val, 1);
            $statref += (int)$val;
            if ($statref > $max) {
                $statref = $max;
            }
            $msg = "*Added $val to $your$statname, now $statref.*";
        } else if ($val[0] == "-") {
            $val = substr($val, 1);
            $statref -= (int)$val;
            // Allow negative weapon bonuses and temp values, but others have a min 0.
            if ($statref < 0 && $thisstat != 'weapon' && $cmd[1] != 'temp') {
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
            ($player[$thisstat] > $statref)) {
            $player[$thisstat] = $statref;
        }

        // Extra message when using temp stat adjustment
        if (strtolower($cmd[1]) == "temp") {
            $msg .= " _(This will reset after the next fight or test.)_";
        }

        sendqmsg($msg, $icons[($oldval <= $statref?0:1)]);
    }


    //// !help (send basic help)
    public function _cmd_help($cmd, &$player) {
        $help = file_get_contents('resources/help.txt');
        // Replace "!" with whatever the trigger word is
        $help = str_replace("!", $_POST['trigger_word'], $help);
        sendqmsg($help);
    }


    //// !echo - simply repeat the input text
    public function _cmd_echo($cmd, &$player) {
        if (!$cmd[1]) {
            return;
        }

        // Turn the params back in to one string
        sendqmsg($cmd[1], ':open_book:');
    }


    //// !randpage <page 1> [page 2] [page 3] [...]
    public function _cmd_randpage($cmd, &$player) {
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

        $choice = rand(0, $totalpages-1);

        // Display a rolled dice, if we can. Actually calculated after the choice (above)
        if ($totalpages == 2 || $totalpages == 3) {
            $ds = 6/$totalpages;
            $de = diceemoji(rand(1+$choice*$ds, $ds+$choice*$ds));
        } elseif ($totalpages <= 6) {
            $de = diceemoji($choice+1);
        }

        sendqmsg("Rolled $de", ":game_die:");
        addcommand("page ".$pagelist[$choice]." nobackup");
    }


    //// !debugset - Set any value
    public function _cmd_debugset($cmd, &$player) {
        $key = $cmd[1];
        $val = $cmd[2];
        $silent = (strtolower($cmd[0]) == 'silentset');
        $sa = array();
        recursive_flatten_player($player, $sa);

        if (array_key_exists($key, $sa) && !is_array($sa[$key])) {
            if (is_int($sa[$key])) {
                $sa[$key] = (int)$val;
            } elseif (is_bool($sa[$key])) {
                $sa[$key] = (strtolower($val)=='yes');
                $val = ($sa[$key]?'yes':'no');
            } else {
                $sa[$key] = $val;
            }
            $msg = "*$key set to $val*";
        } else {
            $msg = "*$key is invalid.*";
        }
        if (!$silent) {
            sendqmsg($msg, ':desktop_computer:');
        }
    }


    //// !debuglist - List all debug values
    public function _cmd_debuglist($cmd, &$player) {
        $sa = array();
        recursive_flatten_player($player, $sa);
        ksort($sa);

        $msg = "";
        foreach ($sa as $key => $val) {
            if (is_bool($val)) {
                $msg .= "*$key:* ".($val?'yes':'no')."\n";
            } else {
                $msg .= "*$key:* $val\n";
            }
        }
        sendqmsg($msg, ':desktop_computer:');
    }


    //// !macro - Run macro from macro.txt
    public function _cmd_macro($cmd, &$player) {
        $macros = file('macros.txt');
        if ($cmd[1] < 1 || $cmd[1] > sizeof($macros)) {
            sendqmsg('Macro '.$cmd[1].' not found.', ':interrobang:');
        }
        $fullcmd = trim($macros[$cmd[1]-1]);

        $cmdlist = explode(";", $fullcmd);
        for ($k = count($cmdlist)-1; $k >= 0; $k--) {
            addcommand($cmdlist[$k]);
        }
    }


    //// !undo - restore to the previous save
    public function _cmd_undo($cmd, &$player) {
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


    //// !save - save copy of player
    public function _cmd_save($cmd, &$player) {
        $slot = ($cmd[1]?$cmd[1]:0);
        if ($slot < 0 || $slot > 10) {
            sendqmsg("*Slot must be between 0 and 10*", ':interrobang:');
            return;
        }
        save($player, "save_$slot.txt");
        sendqmsg("*Game saved in slot $slot*", ':floppy_disk:');
    }


    //// !load - save copy of player
    public function _cmd_load($cmd, &$player) {
        $slot = ($cmd[1]?$cmd[1]:0);
        if ($slot < 0 || $slot > 10) {
            sendqmsg("*Slot must be between 0 and 10*", ':interrobang:');
            return;
        }
        if (!file_exists("save_$slot.txt")) {
            sendqmsg("*No save found in slot $slot*", ':interrobang:');
            return;
        }
        $player = load("save_$slot.txt");
        sendqmsg("*Loaded game in slot $slot*", $player['emoji']);
        addcommand("look");
        addcommand("info");
    }


    //// !load - save copy of player
    public function _cmd_clearslots($cmd, &$player) {
        $slot = ($cmd[1]?$cmd[1]:0);
        if (strtolower($cmd[1]) != 'confirm') {
            sendqmsg("*Use `!clearslots confirm` to confirm clear of all save slots.*", ':interrobang:');
            return;
        }
        foreach (glob("save_*.txt") as $f) {
            unlink($f);
        }
        sendqmsg("*All save slots cleared*", ':floppy_disk:');
    }


    //// !map - Sends a map image if map.jpg exists in images dir
    public function _cmd_map($cmd, &$player) {
        if (file_exists('images'.DIRECTORY_SEPARATOR.IMAGES_SUBDIR.DIRECTORY_SEPARATOR.'map.jpg')) {
            sendimgmsg("*Map*", 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'images/map.jpg');
        } else {
            sendqmsg("*No map found!*", ':interrobang:');
        }
    }


    //// !info / !status (send character sheet and inventory)
    public function _cmd_info($cmd, &$player) {
        $icon = ($player['stam'] < 1?":skull:":$player['emoji']);
        $attach = $this->getCharcterSheetAttachments($player);
        $attach[] = $this->getStuffAttachment($player);

        sendmsg(($text?$text."\n":'').'*'.$player['name']."* the ".$player['adjective']." _(".$player['gender']." ".$player['race'].")_", $attach, $icon);
    }


    //// !stats / !s (send character sheet)
    public function _cmd_stats($cmd, &$player) {
        $icon = ($player['stam'] < 1?":skull:":$player['emoji']);
        $attach = $this->getCharcterSheetAttachments($player);

        sendmsg(($text?$text."\n":'').'*'.$player['name']."* the ".$player['adjective']." _(".$player['gender']." ".$player['race'].")_", $attach, $icon);
    }


    //// !stuff / !i (send inventory)
    public function _cmd_stuff($cmd, &$player) {
        sendmsg("", [$this->getStuffAttachment($player)], $player['emoji']);
    }


}
