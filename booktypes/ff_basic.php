<?php

require_once 'character.php';

class book_ff_basic extends book_character {
    public function getId() {
        return 'ff_basic';
    }


    public function isDead() {
        return ($this->player['stam'] < 1);
    }


    public function storyModify($story) {
        $story = parent::storyModify($story);
        $story = preg_replace('/((Add|Subject|Deduct|Regain|Gain|Lose) )?([1-9] (points? )?from your (SKILL|LUCK|STAMINA)|([1-9] )?(SKILL|LUCK|STAMINA) points?|your (SKILL|LUCK|STAMINA))/', '*${0}*', $story);
        return $story;
    }


    public function getStats() {
        $stats = array(
            'skill' => [
                'friendly' => 'Skill',
                'icons' => [':juggling:', ':tired_face:'],
                'roll' => 'ff1die',
                'display' => 'current_and_max',
                'testdice' => 2,
                'testpass' => '{youare} skillful',
                'testfail' => '{youare} not skillful',
            ],
            'stam' => [
                'friendly' => 'Stamina',
                'alias' => ['stamina'],
                'icons' => ':face_with_head_bandage:',
                'roll' => 'ffstam',
                'display' => 'current_and_max',
                'testdice' => 3,
                'testpass' => '{youare} strong enough',
                'testfail' => '{youare} not strong enough',
            ],
            'luck' => [
                'friendly' => 'Luck',
                'icons' => [':four_leaf_clover:', ':lightning:'],
                'roll' => 'ff1die',
                'display' => 'current_and_max',
                'testdice' => 2,
                'testpass' => '{youare} lucky!',
                'testfail' => '{youare} unlucky',
            ],
            'prov' => [
                'friendly' => 'Provisions',
                'alias' => ['provisions', 'food'],
                'icons' => ':bread:',
            ],
            'weapon' => [
                'friendly' => 'Weapon Bonus',
                'alias' => ['weaponbonus', 'weapon'],
                'icons' => ':dagger_knife:',
                'display' => 'bonus_value',
            ],
            'gold' => [
                'friendly' => 'Gold',
                'alias' => ['cash', 'money'],
                'icons' => ':moneybag:',
            ],
        );
        return $stats;
    }


    public function rollHumanCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective, $seed);
        // Add shield flag
        $p['shield'] = false;
        return $p;
    }


    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective, $seed);
        // Add shield flag
        $p['shield'] = false;
        // Add fantasy races
        if (!$race || $race = '?') {
            $races = array('Human', 'Human', 'Human', 'Elf', 'Djinnin', 'Catling', 'Dwarf');
            $needsskintone = array(true, true, true, true, false, false, true);
            $selection = array_rand($races);
            $p['race'] = $races[$selection];
        }
        if ((!$name || $name == '?') && $p['race'] == 'Catling') {
            $namesfile = 'resources/cat_names.txt';
            $names = file($namesfile);
            $p['name'] = trim($names[array_rand($names)]);
        }
        if (!$emoji || $emoji == '?') {
            $skintone = array(':skin-tone-2:', ':skin-tone-3:', ':skin-tone-4:', ':skin-tone-5:', ':skin-tone-2:');
            if ($gender == 'Male') {
                $emojilist = array(':man:', ':blond-haired-man:', ':older_man:', ':male_elf:', ':male_genie:', ':smirk_cat:', ':bearded_person:');
            } elseif ($gender == 'Female') {
                $emojilist = array(':woman:', ':blond-haired-woman:', ':older_woman:', ':female_elf:', ':female_genie:', ':smile_cat:', ':bearded_person:');
            } else {
                $emojilist = array(':adult:', ':person_with_blond_hair:', ':older_adult:', ':elf:', ':genie:', ':smiley_cat:', ':bearded_person:');
            }
            $p['emoji'] = $emojilist[$selection];
            if ($needsskintone[$selection]) {
                $p['emoji'] .= $skintone[array_rand($skintone)];
            }
        }
        return $p;
    }


    // In Slack format
    protected function getCharcterSheetAttachments() {
        $player = &$this->player;
        $attachments = array([
                'color'    => $player['colourhex'],
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
                        'value' => sprintf("%+d", $player['weapon']),
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
        return $attachments;
    }


    protected function getStuffAttachment() {
        $player = &$this->player;
        // Shield
        if ($player['shield']) {
            $player['stuff'][] = 'Shield *(Equipped)*';
        }
        $attach = parent::getStuffAttachment();
        // Remove shield Shield
        if ($player['shield']) {
            array_pop($player['stuff']);
        }
        return $attach;
    }


    public function registerCommands() {
        parent::registerCommands();
        register_command('eat',         '_cmd_eat');
        register_command('pay',         '_cmd_pay', ['n']);
        register_command('spend',       '_cmd_pay', ['n']);
        register_command('buy',         '_cmd_buy', ['ms', 'on']);
        register_command('luckyescape', '_cmd_luckyescape');
        register_command('le',          '_cmd_luckyescape');
        register_command('shield',      '_cmd_shield', ['os']);
        register_command('test',        '_cmd_test', ['s', 'onm', 'on', 'on']);
        register_command('fight',       '_cmd_fight', ['oms', 'n', 'n', 'osl']);
        register_command('critfight',   '_cmd_critfight', ['oms', 'n', 'os', 'on']);
        register_command('bonusfight',  '_cmd_bonusfight', ['oms', 'n', 'n', 'n', 'on']);
        register_command('vs',          '_cmd_vs', ['ms', 'n', 'n', 'ms', 'n', 'n']);
        register_command('fighttwo',    '_cmd_fighttwo', ['ms', 'n', 'n', 'oms', 'on', 'on']);
        register_command('fightbackup', '_cmd_fightbackup', ['oms', 'n', 'n', 'oms', 'n']);
        register_command('gun',         '_cmd_gun', ['onm', '(\sstun|\skill)?', 'oms', 'n', '(\sstun|\skill)?', 'on']);
        register_command('attack',      '_cmd_attack', ['n', 'on']);
        register_command('a',           '_cmd_attack', ['n', 'on']);
        register_command('dead',        '_cmd_dead');
        register_command('π',           '_cmd_easteregg');
        register_command(':pie:',       '_cmd_easteregg');
    }


    //// !get / !take (add item to inventory/stuff list)
    public function _cmd_get($cmd) {
        $item = $cmd[1];
        // Attempt to catch cases where people get or take gold or provisions
        // and turn them in to stat adjustments
        // "x Gold"
        preg_match_all('/^([0-9]+) gold/i', $item, $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            addcommand("gold +".$matches[0][1]);
            return;
        }
        // "provision"
        if (strtolower($item) == "provision") {
            addcommand("prov +1");
            return;
        }
        // "x provisions"
        preg_match_all('/^([0-9]+) provisions/i', $item, $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            addcommand("prov +".$matches[0][1]);
            return;
        }
        // "shield"
        if (strtolower($item) == "shield") {
            addcommand("shield on");
            return;
        }
        parent::_cmd_get($cmd);
    }


    //// !get / !take (add item to inventory/stuff list)
    public function _cmd_drop($cmd) {
        $drop = strtolower($cmd[1]);
        // TODO: This is code repetition
        // Attempt to catch cases where people get or take gold or provisions
        // and turn them in to stat adjustments
        // "x Gold"
        preg_match_all('/^([0-9]+) gold/i', $drop, $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            addcommand("gold -".$matches[0][1]);
            return;
        }
        // "provision"
        if ($drop == "provision") {
            addcommand("prov -1");
            return;
        }
        // "x provisions"
        preg_match_all('/^([0-9]+) provisions/i', $drop, $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            addcommand("prov -".$matches[0][1]);
            return;
        }
        // "shield"
        if ($drop == "shield") {
            addcommand("shield off");
            return;
        }
        parent::_cmd_drop($cmd);
    }


    //// !help (send basic help)
    public function _cmd_help($cmd) {
        $help = file_get_contents('resources/help.txt');
        // Replace "!" with whatever the trigger word is
        $help = str_replace("!", $_POST['trigger_word'], $help);
        $helpurl = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'commands.html';
        sendqmsg($help."\nMore commands can be found here: ".$helpurl);
    }


    //// !eat
    public function _cmd_eat($cmd) {
        $player = &$this->player;
        if ($player['prov'] < 1) {
            sendqmsg("*No food to eat!*", ':interrobang:');
        } else {
            $player['prov']--;
            $player['stam']+=4;
            if ($player['stam'] > $player['max']['stam']) {
                $player['stam'] = $player['max']['stam'];
            }
            $icon = array(":bread:", ":cheese_wedge:", ":meat_on_bone:")[rand(0, 2)];
            sendqmsg("*Yum! Stamina now ".$player['stam']." and ".$player['prov']." provisions left.*", $icon);
        }
    }


    //// !pay (alias for losing gold)
    public function _cmd_pay($cmd) {
        if (!is_numeric($cmd[1])) {
            return;
        } else if ($this->player['gold'] < $cmd[1]) {
            sendqmsg("* You don't have ".$cmd[1]." gold! *", ':interrobang');
        } else {
            addcommand("gold -".$cmd[1]);
        }
    }


    //// !buy (alias for get & losing gold)
    public function _cmd_buy($cmd) {
        $player = &$this->player;
        if ($cmd[2]) {
            $cost = $cmd[2];
        } else {
            $cost = 2;
        }
        $item = $cmd[1];

        if ($player['gold'] < $cost) {
            sendqmsg("* You don't have $cost gold! *", ':interrobang');
        } else if (array_search(strtolower($item), array_map('strtolower', $player['stuff'])) !== false) {
            sendqmsg("*You already have '".$item."'. Try giving this item a different name.*", ':interrobang:');
        } else {
            $player['gold'] -= $cost;
            $player['stuff'][] = $item;
            sendqmsg("*Bought $item for $cost Gold*", ':handshake:');
        }
    }


    //// !luckyescape (roll for running away)
    public function _cmd_luckyescape($cmd) {
        $player = &$this->player;
        $d1 = rand(1, 6);
        $d2 = rand(1, 6);
        $e1 = diceemoji($d1);
        $e2 = diceemoji($d2);
        $out = "_Testing luck to negate escape damage!_\n";
        $target = $player['luck'];
        $player['luck']--;

        if ($d1+$d2 <= $target) {
            $player['stam'] -= 1;
            if ($player['stam'] < 0) $player['stam'] = 0;
            $out .= "_*You are lucky*_\n_(_ $e1 $e2 _ vs $target, Remaining luck ".$player['luck'].")_\n";
            $out .= "_*Lost 1 stamina!* Remaining stamina ".$player['stam']."_";
            $icon = ":four_leaf_clover:";
        }
        else {
            $player['stam'] -= 3;
            if ($player['stam'] < 0) $player['stam'] = 0;
            $out .= "_*You are unlucky.*_\n_(_ $e1 $e2 _ vs $target, Remaining luck ".$player['luck'].")_\n";
            $out .= "_*Lost 3 stamina!* Remaining stamina ".$player['stam']."_";
            $icon = ':lightning:';
        }

        sendqmsg($out, $icon);
    }


    //// !shield [on/off] - Toggle shield
    public function _cmd_shield($cmd) {
        $player = &$this->player;
        $state = strtolower($cmd[1]);
        if ($state != 'on' && $state != 'off') {
            $state = ($player['shield']?'off':'on');
        }

        $player['shield'] = ($state == 'on');
        $state = ($player['shield']?'Equipped':'Un-Equipped');
        sendqmsg("*Shield $state*", ':shield:');
    }


    //// !test <luck/skill/stam> (run a skill test)
    public function _cmd_test($cmd) {
        $player = &$this->player;
        // Prevent restore
        backup_remove();

        $stats = $this->getStats();
        $stat = get_stat_from_alias(strtolower($cmd[1]), $stats);
        $sinfo = &$stats[$stat];
        $dicemod = ($cmd[2]?(int)$cmd[2]:0);
        // Setup outcome pages to read if provided
        if ($cmd[3]) {
            $success_page = "page ".$cmd[3]." nobackup";
        }
        if ($cmd[4]) {
            $fail_page = "page ".$cmd[4]." nobackup";
        }

        // Referrers
        if (isset($player['referrers'])) {
            $youare = ucfirst($player['referrers']['youare']);
            $you = ucfirst($player['referrers']['you']);
        } else {
            $youare = 'You are';
            $you = 'You';
        }

        // Check for valid test types
        if (!isset($sinfo['testdice'])) {
            sendqmsg("*Don't know how to test ".$stat."*", ':interrobang:');
            return;
        }

        // Roll dice
        $roll = 0;
        $emojidice = '';
        for ($a = 0; $a < $sinfo['testdice']; $a++) {
            $r = rand(1, 6);
            $roll += $r;
            $emojidice .= diceemoji($r).' ';

        }

        // Dice modifier
        if ($dicemod != 0) {
            $emojidice .= ($dicemod>0?'+':'').$dicemod;
        }

        // Apply temp bonuses, if any
        apply_temp_stats($player);
        // Check roll versus target number
        $target = $player[$stat];
        if ($roll+$dicemod <= $target) {
            if ($stat == "luck") {
                $player['luck']--;
                sendqmsg("_*$youare lucky*_\n_(_ $emojidice _ vs $target, Remaining luck ".$player['luck'].")_", ':four_leaf_clover:');
            } else {
                if (!isset($sinfo['icons'])) {
                    $icon = ':smile:';
                } elseif (is_array($sinfo['icons'])) {
                    $icon = $sinfo['icons'][0];
                } else {
                    $icon = $sinfo['icons'];
                }
                $text = str_replace('{youare}', $youare, $sinfo['testpass']);
                $text = str_replace('{you}', $you, $text);
                sendqmsg("_*$text*_\n_(_ $emojidice _ vs $target)_", $icon);
            }
            // Show follow up page
            if (isset($success_page)) {
                addcommand($success_page);
            }
        }
        else {
            if ($stat == "luck") {
                $player['luck']--;
                sendqmsg("_*$youare unlucky.*_\n_(_ $emojidice _ vs $target, Remaining luck ".$player['luck'].")_", ':lightning:');
            } else {
                if (!isset($sinfo['icons'])) {
                    $icon = ':frowning:';
                } elseif (is_array($sinfo['icons'])) {
                    $icon = $sinfo['icons'][1];
                } else {
                    $icon = $sinfo['icons'];
                }
                $text = str_replace('{youare}', $youare, $sinfo['testfail']);
                $text = str_replace('{you}', $you, $text);
                sendqmsg("_*$text*_\n_(_ $emojidice _ vs $target)_", $icon);
            }
            // Show follow up page
            if (isset($fail_page)) {
                addcommand($fail_page);
            }
        }

        // Remove temp bonuses, if any and clear temp bonus array
        unapply_temp_stats($player);
    }


    //// !fight [name] <skill> <stamina> [maxrounds] (run fight logic)
    public function _cmd_fight($cmd) {
        $out = run_fight(['player' => &$this->player,
                'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                'monsterskill' => $cmd[2],
                'monsterstam' => $cmd[3],
                'maxrounds' => ($cmd[4]?$cmd[4]:50)
            ]);
        sendqmsg($out, ":crossed_swords:");
    }


    //// !critfight [name] <skill> [who] [critchance] (run crit fight logic)
    public function _cmd_critfight($cmd) {
        $critsfor = ($cmd[3]?$cmd[3]:'me');
        $critchance = ($cmd[4]?$cmd[4]:2);
        if (!in_array($critsfor, ['both', 'me'])) {
            $critsfor = 'me';
        }
        if (!is_numeric($critchance) || $critchance < 1 || $critchance > 6) {
            $critchance = 2;
        }

        $out = "_*You".($critsfor == 'both'?' both':'')." have to hit critical strikes!* ($critchance in 6 chance)_\n";
        $out = run_fight(['player' => &$this->player,
                'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                'monsterskill' => $cmd[2],
                'critsfor' => $critsfor,
                'critchance' => $critchance]);
        sendqmsg($out, ":crossed_swords:");
    }


    //// !bonusfight [name] <skill> <stamina> <bonusdamage> [bonusdmgchance] (run bonus attack fight logic)
    public function _cmd_bonusfight($cmd) {
        $out = run_fight(['player' => &$this->player,
                'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                'monsterskill' => $cmd[2],
                'monsterstam' => $cmd[3],
                'bonusdmg' => $cmd[4],
                'bonusdmgchance' => ($cmd[5]?$cmd[5]:3)
            ]);
        sendqmsg($out, ":crossed_swords:");
    }


    //// !vs <name 1> <skill 1> <stamina 1> <name 2> <skill 2> <stamina 2>
    public function _cmd_vs($cmd) {
        $vsplayer = array(
            'name' => $cmd[1],
            'referrers' => ['you' => $cmd[1], 'youare' => $cmd[1].' is', 'your' => $cmd[1]."'s"],
            'skill' => $cmd[2],
            'stam' => $cmd[3],
            'luck' => 0,
            'weapon' => 0,
            'shield' => false,
            'temp' => []
        );
        $out = run_fight(['player' => &$vsplayer,
                'monstername' => $cmd[4],
                'monsterskill' => $cmd[5],
                'monsterstam' => $cmd[6]
            ]);
        sendqmsg($out, ":crossed_swords:");
    }


    //// !fighttwo <name 1> <skill 1> <stamina 1> [<name 2> <skill 2> <stamina 2>]
    public function _cmd_fighttwo($cmd) {
        // Set monster 1
        $m = $cmd[1];
        $mskill = $cmd[2];
        $mstam = $cmd[3];

        // Set monster 2
        if ($cmd[4] && $cmd[5] && $cmd[6]) {
            $m2 = $cmd[4];
            $mskill2 = $cmd[5];
            $mstam2 = $cmd[6];
        } else {
            $m2 = $m;
            $mskill2 = $mskill;
            $mstam2 = $mstam;
        }

        // Differentiate monsters
        if ($m == $m2) {
            $m = "First ".$m;
            $m2 = "Second ".$m2;
        }

        $out = run_fight(['player' => &$this->player,
                'monstername' => $m,
                'monsterskill' => $mskill,
                'monsterstam' => $mstam,
                'monster2name' => $m2,
                'monster2skill' => $mskill2]);
        if ($this->player['stam'] > 0) {
            addcommand("fight $m2 $mskill2 $mstam2");
        }
        sendqmsg($out, ":crossed_swords:");
    }


    //// !fightbackup [name 1] <skill 1> <stamina 1> [backup's name] <backup's skill>
    public function _cmd_fightbackup($cmd) {
        // Set monster
        $m = ($cmd[1]?$cmd[1]:'Opponent');
        $mskill = $cmd[2];
        $mstam = $cmd[3];

        // Set backup
        $backupname = ($cmd[4]?$cmd[4]:'The backup');
        $backupskill = $cmd[5];

        $out = run_fight(['player' => &$this->player,
                'monstername' => $m,
                'monsterskill' => $mskill,
                'monsterstam' => $mstam,
                'backupname' => $backupname,
                'backupskill' => $backupskill]);
        sendqmsg($out, ":crossed_swords:");
    }


    //// !attack <skill>
    public function _cmd_attack($cmd) {
        $dmg = ($cmd[2]?$cmd[2]:0);
        $out = run_single_attack($this->player, 'Opponent', $cmd[1], 999, $dmg, 0);

        sendqmsg($out, ":crossed_swords:");
    }


    //// !dead - Kill your character.
    public function _cmd_dead($cmd) {
        $this->player['stam'] = 0;
    }


    //// !phaser/gun [-/+modifier] [stun/kill] [name] <skill> [stun/kill] [maxrounds] (run phaser fight logic)
    public function _cmd_gun($cmd) {
        $out = run_phaser_fight(['player' => &$this->player,
                'modifier' => ($cmd[1]?$cmd[1]:0),
                'stunkill' => ($cmd[2]?$cmd[2]:'stun'),
                'monstername' => ($cmd[3]?$cmd[3]:"Opponent"),
                'monsterskill' => $cmd[4],
                'mstunkill' => ($cmd[5]?$cmd[5]:'kill'),
                'maxrounds' => ($cmd[6]?$cmd[6]:50)
            ]);
        sendqmsg($out, ":gun:");
    }


    //// !π - Easter egg
    public function _cmd_easteregg($cmd) {
        $eggs = file('resources/easter_eggs.txt');
        $fullcmd = trim($eggs[array_rand($eggs)]);

        $cmdlist = explode(";", $fullcmd);
        for ($k = count($cmdlist)-1; $k >= 0; $k--) {
            addcommand($cmdlist[$k]);
        }
    }


}
