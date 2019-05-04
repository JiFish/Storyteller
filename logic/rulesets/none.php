<?php

require_once 'gamebook_base.php';

class book_none extends gamebook_base {
    public function isDead() {
        return false;
    }


    // This will work with almost all books
    protected function getPageMatchRegex() {
        return '/\(?(?:turn|go)(?:ing)?(?: back| immediately)? to (?:section |page |paragraph )?([0-9]+)\)?/i';
    }


    // This will work with almost all books
    protected function getDeathMatchRegex() {
        return '/Your (adventure|quest|story) (is over|ends here|is at an end)\.?/i';
    }


    // Customise this to match filenames in resources/help/
    protected function getHelpFileId() {
        return '';
    }


    protected function storyModify($story) {
        return $story;
    }


    protected function newCharacter() {
        return array('lastpage' => 1);
    }


    protected function registerCommands() {
        $this->registerCommand('look',           '_cmd_look');
        $this->registerCommand('page',           '_cmd_page',      ['n']);
        $this->registerCommand('background',     '_cmd_background');
        $this->registerCommand('roll',           '_cmd_roll',      ['on']);
        $this->registerCommand(['help', '?'],    '_cmd_help');
        $this->registerCommand('echo',           '_cmd_echo',      ['l']);
        $this->registerCommand('randpage',       '_cmd_randpage',  ['n', 'n', 'on', 'on', 'on', 'on', 'on', 'on']);
        $this->registerCommand('debugset',       '_cmd_debugset',  ['s', 'l']);
        $this->registerCommand('silentset',      '_cmd_debugset',  ['s', 'l']);
        $this->registerCommand('debuglist',      '_cmd_debuglist');
        $this->registerCommand(['macro', 'm'],   '_cmd_macro',     ['n'], ['m']);
        $this->registerCommand('map',            '_cmd_map');
        $this->registerCommand(['book', 'open'], '_cmd_book',      ['os'], ['open']);
        $this->registerCommand('library',        '_cmd_library');
    }


    // Look for numeric commands and treat them as a special case
    protected function processCommand($command) {
        if (is_numeric(trim($command))) {
            $command = "page $command";
        }
        parent::processCommand($command);
    }


    //// !look
    protected function _cmd_look($cmd) {
        $story = $this->getFormatedStory($this->player['lastpage']);
        sendqmsg($story);
    }


    protected function getFormatedStory($page) {
        global $config;
        require $config->book_file;

        if (!array_key_exists($page, $book)) {
            return "$page: PAGE NOT FOUND";
        }

        // Book specific specials
        $story = $this->storyModify($book[$page]);

        // Look for choices in the text and give them bold formatting
        $story = preg_replace($this->getPageMatchRegex(),  '*${0}*', $story);
        $story = preg_replace($this->getDeathMatchRegex(), '*${0}*', $story);

        // Wrapping and formatting
        $story = str_replace("\n", "\n\n", $story);
        $story = wordwrap($story, 100);
        $story = explode("\n", $story);
        for ($l = 0; $l < sizeof($story); $l++) {
            if (trim($story[$l]) == "") {
                $story[$l] = "> ";
            } else {
                // Prevent code blocks from linebreaking
                if (substr_count($story[$l], '`') % 2 != 0) {
                    if (array_key_exists($l+1, $story)) {
                        $story[$l+1] = $story[$l].' '.$story[$l+1];
                        $story[$l] = '';
                        continue;
                    }
                }

                // Deal with bold blocks across lines
                if (substr_count($story[$l], '*') % 2 != 0) {
                    $story[$l] .= '*';
                    if (array_key_exists($l+1, $story)) {
                        $story[$l+1] = "*".$story[$l+1];
                    }
                }

                // Italic and quote
                $story[$l] = "> _".$story[$l].'_';
            }
        }
        $story = "> — *$page* —\n".implode("\n", $story);

        return $story;
    }


    //// !page <num> / !<num> (Read page from book)
    protected function _cmd_page($cmd) {
        global $config;

        $player = &$this->player;
        if (!is_numeric($cmd[1])) {
            return;
        }
        $page = $cmd[1];

        $this->savePlayer('backup');

        $player['lastpage'] = $page;
        require $config->book_file;
        $story = $book[$page];

        // Exclude pages using 'if ', 'you may' or 'otherwise'
        // This isn't perfect, but will prevent many false matches
        if (stripos($story, "if ") === false && stripos($story, "you may ") === false
            && stripos($story, "otherwise") === false) {
            // Attempt to find pages that give you only one choice
            // Find pages with only one turn to and add that page to the command list
            preg_match_all($this->getPageMatchRegex(), $story, $matches, PREG_SET_ORDER, 0);
            if (sizeof($matches) == 1) {
                $this->addCommand("page ".$matches[0][1]);
            }
        }

        // Autorun
        if (isset($autorun)) {
            if (array_key_exists($page, $autorun)) {
                $cmdlist = explode(";", $autorun[$page]);
                for ($k = count($cmdlist)-1; $k >= 0; $k--) {
                    // Autorun commands are assumed to be SAFE
                    $this->addCommand($cmd, false, true);
                }
            }
        }

        $story = $this->getFormatedStory($player['lastpage']);

        $imgpath = "images/".$config->book_images_dir."/".$player['lastpage'];
        if (file_exists($imgpath.'.jpg')) {
            sendimgmsg($story, $config->root.'/'.$imgpath.'.jpg');
        } elseif (file_exists($imgpath.'.png')) {
            sendimgmsg($story, $config->root.'/'.$imgpath.'.png');
        } else {
            sendqmsg($story);
        }
    }


    //// !background
    protected function _cmd_background($cmd) {
        $story = getFormatedStory(0);
        senddirmsg($story);
    }


    //// !roll [x] (roll xd6)
    protected function _cmd_roll($cmd) {
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


    //// !help (send help)
    protected function _cmd_help($cmd) {
        global $config;
        $helpid = $this->getHelpFileId();
        // Look for help file
        if (file_exists("resources/help/help_$helpid.txt")) {
            $help = file_get_contents("resources/help/help_$helpid.txt");
        } else {
            $help = "Type `![page]` to turn to a page or section. e.g. `!42`";
        }
        // Replace "!" with whatever the trigger word is
        $help = str_replace("!", $_POST['trigger_word'], $help);
        // Look for advanced help
        if (file_exists("resources/help/help_$helpid.md")) {
            $helpurl = $config->root."/help.php?t=$helpid";
            $help .= "\nMore detailed help can be found here: $helpurl\n";
        }
        // Get complete command list
        $clist = array_keys($this->commands);
        sort($clist);
        // Remove disabled commands and Easter eggs
        $clist = array_diff($clist, $config->disabled_commands, [':pie:', 'π']);
        $help .= "\nComplete list of available commands: `!".implode("` `!", $clist).'`';


        sendqmsg($help, ':question:');
    }


    //// !echo - simply repeat the input text
    protected function _cmd_echo($cmd) {
        if (!$cmd[1]) {
            return;
        }

        // Turn the params back in to one string
        sendqmsg($cmd[1], ':open_book:');
    }


    //// !randpage <page 1> [page 2] [page 3] [...]
    protected function _cmd_randpage($cmd) {
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
        $this->addCommand("page ".$pagelist[$choice]);
    }


    //// !debugset - Set any value
    protected function _cmd_debugset($cmd) {
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
    protected function _cmd_debuglist($cmd) {
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
    protected function _cmd_macro($cmd) {
        $macros = file('macros.txt');
        if ($cmd[1] < 1 || $cmd[1] > sizeof($macros)) {
            sendqmsg('Macro '.$cmd[1].' not found.', ':interrobang:');
            return;
        }
        $fullcmd = trim($macros[$cmd[1]-1]);

        $cmdlist = explode(";", $fullcmd);
        for ($k = count($cmdlist)-1; $k >= 0; $k--) {
            // Macro commands are assumed to be SAFE
            $this->addCommand($cmd, false, true);
        }
    }


    //// !map - Sends a map image if map.jpg exists in images dir
    protected function _cmd_map($cmd) {
        global $config;

        $imgpath = "images/".$config->book_images_dir."/map";
        if (file_exists($imgpath.'.jpg')) {
            sendimgmsg("*Map*", $config->root.'/'.$imgpath.'.jpg');
        } elseif (file_exists($imgpath.'.png')) {
            sendimgmsg("*Map*", $config->root.'/'.$imgpath.'.png');
        } else {
            sendqmsg("*No map found!*", ':interrobang:');
        }
    }


    //// !book - Open another book
    protected function _cmd_book($cmd) {
        global $config;
        $bookid = strtolower($cmd[1]);

        if (!$bookid) {
            return $this->_cmd_library();
        }
        if ($bookid == $config->book_id) {
            sendqmsg("*".$config->book_name.": Already open!*", ':interrobang:');
            return;
        }
        if (!array_key_exists($bookid, $config->books)) {
            sendqmsg("*$bookid: Book not found!*", ':interrobang:');
            return;
        }

        $this->savePlayer();
        $config->changeBookSetting($bookid);
        sendqmsg("*Opening ".$config->book_name."!*", ':interrobang:');
        // Bit of a hack, we have to die() here so we don't run any commands under the wrong rules
        die();
    }


    //// !library - List books
    protected function _cmd_library($cmd = null) {
        global $config;

        $out = "*List of available books:*\n";
        $lib = [];
        foreach ($config->books as $key => $b) {
            $title = "- ".ucfirst($b['name']).' ';
            if ($key == $config->book_id) {
                $title .= "- _Currently open_";
            } else {
                $title .= "- `!book $key`";
            }
            $lib[$b['group']][] = $title;
        }
        // Sorting
        ksort($lib, SORT_NATURAL);
        foreach ($lib as $k => $v) {
            sort($lib[$k], SORT_NATURAL);
        }
        // Books without group first
        if (array_key_exists('none', $lib)) {
            $out .= implode("\n", $lib['none']);
            unset($lib['none']);
        }
        // Then each group
        foreach ($lib as $group => $titles) {
            $out .= "\n_*$group*_\n";
            $out .= implode("\n", $titles);
        }
        sendqmsg($out, ':books:');
    }


}
