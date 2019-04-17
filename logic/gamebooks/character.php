<?php

require_once 'none.php';
require_once 'logic/RandomColor.php';
require_once 'logic/dice.php';

class book_character extends book_none {
    public function getId() {
        return 'character';
    }


    protected function getStats() {
        return [];
    }


    protected function getAllStatCommands() {
        $statcmds = [];
        foreach ($this->getStats() as $s => $val) {
            $statcmds[] = $s;
            if (isset($val['alias'])) {
                foreach ($val['alias'] as $a) {
                    $statcmds[] = $a;
                }
            }
        }
        return $statcmds;
    }


    protected function getStatFromAlias($alias) {
        foreach ($this->getStats() as $s => $val) {
            if ($s == $alias) {
                $thisstat = $s;
                break;
            }
            if (isset($val['alias'])) {
                foreach ($val['alias'] as $a) {
                    if ($a == $alias) {
                        $thisstat = $s;
                        break 2;
                    }
                }
            }
        }

        return $thisstat;
    }


    protected function getCharacterString() {
        $p = &$this->player;
        return "*".$p['name']."* the ".$p['adjective']." _(".$p['gender']." ".$p['race'].")_";
    }


    protected function newCharacter() {
        return $this->rollCharacter();
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = array('lastpage' => 1,
            'stuff' => [],
            'creationdice' => [],
            'temp' => []);
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


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('get',        '_cmd_get', ['l']);
        $this->registerCommand('take',       '_cmd_get', ['l']);
        $this->registerCommand('drop',       '_cmd_drop', ['l']);
        $this->registerCommand('lose',       '_cmd_drop', ['l']);
        $this->registerCommand('use',        '_cmd_drop', ['l']);
        $this->registerCommand('ng',         '_cmd_newgame', ['osl', 'osl', 'osl', 'osl', 'osl']);
        $this->registerCommand('newgame',    '_cmd_newgame', ['osl', 'osl', 'osl', 'osl', 'osl']);
        $this->registerCommand('undo',       '_cmd_undo');
        $this->registerCommand('save',       '_cmd_save', ['on']);
        $this->registerCommand('load',       '_cmd_load', ['on']);
        $this->registerCommand('clearslots', '_cmd_clearslots', ['osl']);
        $this->registerCommand('info',       '_cmd_info');
        $this->registerCommand('status',     '_cmd_info');
        $this->registerCommand('stats',      '_cmd_stats');
        $this->registerCommand('s',          '_cmd_stats');
        $this->registerCommand('stuff',      '_cmd_stuff');
        $this->registerCommand('i',          '_cmd_stuff');
        // Stats commands
        foreach ($this->getAllStatCommands() as $s) {
            $this->registerCommand($s, '_cmd_stat_adjust', ['os', 'nm']);
        }
    }


    //// !newgame (roll new character)
    protected function _cmd_newgame($cmd) {
        $player = &$this->player;
        $cmd = array_pad($cmd, 7, '?');
        $player = $this->rollCharacter($cmd[1], $cmd[2], $cmd[3], $cmd[4], $cmd[5]);

        $icon = $player['emoji'];
        $attach = $this->getCharcterSheetAttachments();
        $attach[] = $this->getStuffAttachment();

        sendmsg("_*NEW CHARACTER!*_ ".implode(' ', array_map("diceemoji", $player['creationdice']))."\n".$this->getCharacterString(), $attach, $icon);
    }


    //// !get / !take (add item to inventory/stuff list)
    protected function _cmd_get($cmd) {
        $item = $cmd[1];

        // Prevent duplicate entries
        if (!smart_add_to_list($this->player['stuff'], $item)) {
            sendqmsg("*You already have '".$item."'. Try giving this item a different name.*", ':interrobang:');
            return;
        }

        sendqmsg("*Got the $item!*", ":school_satchel:");
    }


    //// !drop / !lose / !use
    protected function _cmd_drop($cmd) {
        $verb = strtolower($cmd[0]);
        $drop = strtolower($cmd[1]);
        $result = smart_remove_from_list($this->player['stuff'], $drop);

        if ($result === false) {
            sendqmsg("*'$drop' didn't match anything in inventory. Can't $verb.*", ':interrobang:');
        } elseif (is_array($result)) {
            sendqmsg("*Which did you want to $verb? ".implode(", ", $result)."*", ':interrobang:');
        } else {
            switch ($verb) {
            case 'lose':
                sendqmsg("*Lost the $result!*");
                break;
            case 'drop':
                sendqmsg("*Dropped the $result!*", ":put_litter_in_its_place:");
                break;
            case 'use':
                sendqmsg("*Used the $result!*");
                break;
            }
        }
    }


    //// Various statistic adjustment commands
    protected function _cmd_stat_adjust($cmd) {
        $player = &$this->player;
        $stats = $this->getStats();
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
        $thisstat = $this->getStatFromAlias(strtolower($cmd[0]), $this->getStats());
        $statname = $stats[$thisstat]['friendly'];
        // Allow negative for temp values, others have a min 0 unless allownegative set
        if ($cmd[1] != 'temp' || !isset($stats[$thisstat]['allownegative']) || !$stats[$thisstat]['allownegative']) {
            $allownegative = false;
        } else {
            $allownegative = true;
        }

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
            if ($statref < 0 && !$allownegative) {
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
    protected function _cmd_help($cmd) {
        $help = file_get_contents('resources/help.txt');
        // Replace "!" with whatever the trigger word is
        $help = str_replace("!", $_POST['trigger_word'], $help);
        sendqmsg($help);
    }


    //// !undo - restore to the previous save
    protected function _cmd_undo($cmd) {
        if (!$this->isDead()) {
            sendqmsg("*You can only undo when dead.*", ':interrobang:');
            return;
        } else if ($this->restorePlayer()) {
            sendqmsg("*...or maybe this happened...*", ':rewind:');
            $this->addCommand("look");
        } else {
            sendqmsg("*There are some things that cannot be undone...*", ':skull:');
        }
    }


    protected function restorePlayer() {
        global $config;

        $file = 'saves/save_'.$config->game_id.'_backup.txt';
        if (file_exists($file)) {
            $this->loadPlayer('backup');
            $this->savePlayer();
            return true;
        }
        return false;
    }


    //// !save - save copy of player
    protected function _cmd_save($cmd) {
        $slot = ($cmd[1]?$cmd[1]:0);
        if ($slot < 0 || $slot > 10) {
            sendqmsg("*Slot must be between 0 and 10*", ':interrobang:');
            return;
        }
        $this->savePlayer($slot);
        sendqmsg("*Game saved in slot $slot*", ':floppy_disk:');
    }


    //// !load - save copy of player
    protected function _cmd_load($cmd) {
        global $config;

        $player = &$this->player;
        $slot = ($cmd[1]?$cmd[1]:0);
        $file = 'saves/save_'.$config->book_id.$slot.'.txt';
        if ($slot < 0 || $slot > 10) {
            sendqmsg("*Slot must be between 0 and 10*", ':interrobang:');
            return;
        }
        if (!file_exists($file)) {
            sendqmsg("*No save found in slot $slot*", ':interrobang:');
            return;
        }
        $this->loadPlayer($slot);
        sendqmsg("*Loaded game in slot $slot*", $player['emoji']);
        $this->addCommand("look");
        $this->addCommand("info");
    }


    //// !load - save copy of player
    protected function _cmd_clearslots($cmd) {
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
    protected function _cmd_info($cmd) {
        $player = &$this->player;
        $icon = ($this->isDead()?":skull:":$player['emoji']);
        $attach = $this->getCharcterSheetAttachments();
        $attach[] = $this->getStuffAttachment();

        sendmsg($this->getCharacterString(), $attach, $icon);
    }


    //// !stats / !s (send character sheet)
    protected function _cmd_stats($cmd) {
        $player = &$this->player;
        $icon = ($this->isDead()?":skull:":$player['emoji']);
        $attach = $this->getCharcterSheetAttachments();

        sendmsg($this->getCharacterString(), $attach, $icon);
    }


    //// !stuff / !i (send inventory)
    protected function _cmd_stuff($cmd) {
        sendmsg("", [$this->getStuffAttachment()], $this->player['emoji']);
    }


}
