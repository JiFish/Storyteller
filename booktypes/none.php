<?php

class book_none {
    protected $player = null;


    public function __construct(&$player) {
        $this->player = &$player;
    }


    public function getId() {
        return 'none';
    }


    public function isDead() {
        return false;
    }


    public function storyModify($story) {
        return $story;
    }


    public function newCharacter() {
        return array('lastpage' => 1);
    }


    public function registerCommands() {
        register_command('look',       '_cmd_look');
        register_command('page',       '_cmd_page', ['n', 'os']);
        register_command('background', '_cmd_background');
        register_command('roll',       '_cmd_roll', ['on']);
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
        register_command('map',        '_cmd_map');
    }


    //// !look
    public function _cmd_look($cmd) {
        require "book.php";
        $story = format_story($player['lastpage'], $book[$player['lastpage']], $player);
        sendqmsg($story);
    }


    //// !page <num> / !<num> (Read page from book)
    public function _cmd_page($cmd) {
        $player = &$this->player;
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
    public function _cmd_background($cmd) {
        require "book.php";
        $story = format_story(0, $book[0], $this->player);
        senddirmsg($story);
    }


    //// !roll [x] (roll xd6)
    public function _cmd_roll($cmd) {
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
    public function _cmd_stat_adjust($cmd) {
        $player = &$this->player;
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
    public function _cmd_help($cmd) {
        $help = "Type `![page]` to turn to a page or section. e.g. `!42`";
        $help = str_replace("!", $_POST['trigger_word'], $help);
        sendqmsg($help);
    }


    //// !echo - simply repeat the input text
    public function _cmd_echo($cmd) {
        if (!$cmd[1]) {
            return;
        }

        // Turn the params back in to one string
        sendqmsg($cmd[1], ':open_book:');
    }


    //// !randpage <page 1> [page 2] [page 3] [...]
    public function _cmd_randpage($cmd) {
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
    public function _cmd_debugset($cmd) {
        $key = $cmd[1];
        $val = $cmd[2];
        $silent = (strtolower($cmd[0]) == 'silentset');
        $sa = array();
        recursive_flatten_player($this->player, $sa);

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
    public function _cmd_debuglist($cmd) {
        $sa = array();
        recursive_flatten_player($this->player, $sa);
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
    public function _cmd_macro($cmd) {
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


    //// !map - Sends a map image if map.jpg exists in images dir
    public function _cmd_map($cmd) {
        if (file_exists('images'.DIRECTORY_SEPARATOR.IMAGES_SUBDIR.DIRECTORY_SEPARATOR.'map.jpg')) {
            sendimgmsg("*Map*", 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'images/map.jpg');
        } else {
            sendqmsg("*No map found!*", ':interrobang:');
        }
    }


}
