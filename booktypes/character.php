<?php

require_once 'none.php';
require_once 'logic/RandomColor.php';
require_once 'logic/dice.php';

class book_character extends book_none {
    public function getId() {
        return 'character';
    }


    public function getStats() {
        return [];
    }


    public function newCharacter() {
        return $this->rollCharacter();
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
    protected function getCharcterSheetAttachments() {
        return [];
    }


    // In Slack format
    protected function getStuffAttachment() {
        $s = $this->player['stuff'];

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
        parent::registerCommands();
        register_command('get',        '_cmd_get', ['l']);
        register_command('take',       '_cmd_get', ['l']);
        register_command('drop',       '_cmd_drop', ['l']);
        register_command('lose',       '_cmd_drop', ['l']);
        register_command('use',        '_cmd_drop', ['l']);
        register_command('ng',         '_cmd_newgame', ['osl', 'osl', 'osl', 'osl', 'osl', 'on']);
        register_command('newgame',    '_cmd_newgame', ['osl', 'osl', 'osl', 'osl', 'osl', 'on']);
        register_command('undo',       '_cmd_undo');
        register_command('save',       '_cmd_save', ['on']);
        register_command('load',       '_cmd_load', ['on']);
        register_command('clearslots', '_cmd_clearslots', ['osl']);
        register_command('info',       '_cmd_info');
        register_command('status',     '_cmd_info');
        register_command('stats',      '_cmd_stats');
        register_command('s',          '_cmd_stats');
        register_command('stuff',      '_cmd_stuff');
        register_command('i',          '_cmd_stuff');
        // Stats commands
        foreach ($this->getStats() as $s => $val) {
            register_command($s, '_cmd_stat_adjust', ['os', 'nm']);
            if (isset($val['alias'])) {
                foreach ($val['alias'] as $a) {
                    register_command($a, '_cmd_stat_adjust', ['os', 'nm']);
                }
            }
        }
    }


    //// !newgame (roll new character)
    function _cmd_newgame($cmd) {
        $player = &$this->player;
        $cmd = array_pad($cmd, 7, '?');
        $player = $this->rollCharacter($cmd[1], $cmd[2], $cmd[3], $cmd[4], $cmd[5], $cmd[6]);

        $icon = $player['emoji'];
        $attach = $this->getCharcterSheetAttachments();
        $attach[] = $this->getStuffAttachment();

        sendmsg("_*NEW CHARACTER!*_ ".implode(' ', array_map("diceemoji", $player['creationdice']))."\n*".$player['name']."* the ".$player['adjective']." _(".$player['gender']." ".$player['race'].")_", $attach, $icon);
    }


    //// !get / !take (add item to inventory/stuff list)
    public function _cmd_get($cmd) {
        $player = &$this->player;
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
    public function _cmd_drop($cmd) {
        $player = &$this->player;
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
        $help = file_get_contents('resources/help.txt');
        // Replace "!" with whatever the trigger word is
        $help = str_replace("!", $_POST['trigger_word'], $help);
        sendqmsg($help);
    }


    //// !undo - restore to the previous save
    public function _cmd_undo($cmd) {
        $player = &$this->player;
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
    public function _cmd_save($cmd) {
        $slot = ($cmd[1]?$cmd[1]:0);
        if ($slot < 0 || $slot > 10) {
            sendqmsg("*Slot must be between 0 and 10*", ':interrobang:');
            return;
        }
        save($this->player, "save_$slot.txt");
        sendqmsg("*Game saved in slot $slot*", ':floppy_disk:');
    }


    //// !load - save copy of player
    public function _cmd_load($cmd) {
        $player = &$this->player;
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
    public function _cmd_clearslots($cmd) {
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


    //// !info / !status (send character sheet and inventory)
    public function _cmd_info($cmd) {
        $player = &$this->player;
        $icon = ($player['stam'] < 1?":skull:":$player['emoji']);
        $attach = $this->getCharcterSheetAttachments();
        $attach[] = $this->getStuffAttachment();

        sendmsg(($text?$text."\n":'').'*'.$player['name']."* the ".$player['adjective']." _(".$player['gender']." ".$player['race'].")_", $attach, $icon);
    }


    //// !stats / !s (send character sheet)
    public function _cmd_stats($cmd) {
        $player = &$this->player;
        $icon = ($player['stam'] < 1?":skull:":$player['emoji']);
        $attach = $this->getCharcterSheetAttachments();

        sendmsg(($text?$text."\n":'').'*'.$player['name']."* the ".$player['adjective']." _(".$player['gender']." ".$player['race'].")_", $attach, $icon);
    }


    //// !stuff / !i (send inventory)
    public function _cmd_stuff($cmd) {
        sendmsg("", [$this->getStuffAttachment()], $this->player['emoji']);
    }


}
