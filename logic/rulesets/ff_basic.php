<?php

require_once 'character.php';

class book_ff_basic extends book_character {
    public function isDead() {
        return $this->player['stam'] < 1;
    }


    protected function getHelpFileId() {
        return 'ff';
    }


    protected function storyModify($story) {
        $story = parent::storyModify($story);
        $story = preg_replace('/((Add|Subject|Deduct|Regain|Gain|Lose) )?([1-9] (points? )?from your (SKILL|LUCK|STAMINA)|([1-9] )?(SKILL|LUCK|STAMINA) points?|your (SKILL|LUCK|STAMINA))/', '*${0}*', $story);
        return $story;
    }


    protected function getStats() {
        $stats = array(
            'skill' => [
                'friendly' => 'Skill',
                'icons' => [':juggling:', ':tired_face:'],
                'roll' => 'ff1die',
                'testdice' => 2,
                'testpass' => '{youare} skillful',
                'testfail' => '{youare} not skillful',
            ],
            'stam' => [
                'friendly' => 'Stamina',
                'alias' => ['stamina'],
                'icons' => [':heartpulse', ':face_with_head_bandage:'],
                'roll' => 'ffstam',
                'testdice' => 3,
                'testpass' => '{youare} strong enough',
                'testfail' => '{youare} not strong enough',
            ],
            'luck' => [
                'friendly' => 'Luck',
                'icons' => [':four_leaf_clover:', ':lightning:'],
                'roll' => 'ff1die',
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
                'alias' => ['weaponbonus', 'bonus'],
                'icons' => ':dagger_knife:',
                'allownegative' => true,
            ],
            'shield' => [
                'friendly' => 'Shield',
                'icons' => ':shield:',
                'roll' => 'boolstat',
            ],
            'gold' => [
                'friendly' => 'Gold',
                'alias' => ['cash', 'money'],
                'icons' => ':moneybag:',
            ],
        );
        return $stats;
    }


    protected function rollHumanCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        return $p;
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        // Add fantasy races
        $races = array('Human', 'Human', 'Human', 'Elf', 'Djinnin', 'Catling', 'Dwarf');
        $needsskintone = array(true, true, true, true, false, false, true);
        if (!$race || $race == '?') {
            $selection = array_rand($races);
            $p['race'] = $races[$selection];
        } else {
            $selection = array_search($race, $races);
            if ($selection === false) {
                $selection = 0;
            }
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
                        'value' => $player['stam']." / ".$player['max']['stam'].($player['shield']?' (Has shield)':''),
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


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('eat',                 '_cmd_eat');
        $this->registerCommand(['pay', 'spend'],      '_cmd_pay',         ['n']);
        $this->registerCommand('buy',                 '_cmd_buy',         ['ms', 'on']);
        $this->registerCommand(['luckyescape', 'le'], '_cmd_luckyescape');
        $this->registerCommand('test',                '_cmd_test',        ['s', 'onm', 'on', 'on']);
        $this->registerCommand('fight',               '_cmd_fight',       ['oms', 'n', 'n', 'onm', 'osl']);
        $this->registerCommand('critfight',           '_cmd_critfight',   ['oms', 'n', 'os', 'on', 'onm']);
        $this->registerCommand('bonusfight',          '_cmd_bonusfight',  ['oms', 'n', 'n', 'n', 'on', 'onm']);
        $this->registerCommand('vs',                  '_cmd_vs',          ['ms', 'n', 'n', 'ms', 'n', 'n']);
        $this->registerCommand('fighttwo',            '_cmd_fighttwo',    ['ms', 'n', 'n', 'oms', 'on', 'on', 'onm']);
        $this->registerCommand('fightbackup',         '_cmd_fightbackup', ['oms', 'n', 'n', 'oms', 'n', 'onm']);
        $this->registerCommand(['gun', 'phaser'],     '_cmd_gun',         ['(\sstun|\skill)?', 'oms', 'n', '(\sstun|\skill)?', 'on', 'onm']);
        $this->registerCommand(['attack', 'a'],       '_cmd_attack',      ['n', 'on']);
        $this->registerCommand('dead',                '_cmd_dead');
        $this->registerCommand(['π', ':pie:'],        '_cmd_easteregg');
    }


    //// !get / !take (add item to inventory/stuff list)
    protected function _cmd_get($cmd) {
        $item = $cmd[1];
        // Attempt to catch cases where people get or take gold or provisions
        // and turn them in to stat adjustments
        // "x Gold"
        preg_match_all('/^([0-9]+) gold/i', $item, $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            $this->addCommand("gold +".$matches[0][1]);
            return;
        }
        // "provision"
        if (strtolower($item) == "provision") {
            $this->addCommand("prov +1");
            return;
        }
        // "x provisions"
        preg_match_all('/^([0-9]+) provisions/i', $item, $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            $this->addCommand("prov +".$matches[0][1]);
            return;
        }
        // "shield"
        if (strtolower($item) == "shield") {
            $cmd[1] = "Shield <shield +1>";
        }
        parent::_cmd_get($cmd);
    }


    //// !get / !take (add item to inventory/stuff list)
    protected function _cmd_drop($cmd) {
        $drop = strtolower($cmd[1]);
        // TODO: This is code repetition
        // Attempt to catch cases where people get or take gold or provisions
        // and turn them in to stat adjustments
        // "x Gold"
        preg_match_all('/^([0-9]+) gold/i', $drop, $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            $this->addCommand("gold -".$matches[0][1]);
            return;
        }
        // "provision"
        if ($drop == "provision") {
            $this->addCommand("prov -1");
            return;
        }
        // "x provisions"
        preg_match_all('/^([0-9]+) provisions/i', $drop, $matches, PREG_SET_ORDER, 0);
        if (sizeof($matches) > 0) {
            $this->addCommand("prov -".$matches[0][1]);
            return;
        }
        parent::_cmd_drop($cmd);
    }


    //// !eat
    protected function _cmd_eat($cmd) {
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
    protected function _cmd_pay($cmd) {
        if (!is_numeric($cmd[1])) {
            return;
        } else if ($this->player['gold'] < $cmd[1]) {
            sendqmsg("* You don't have ".$cmd[1]." gold! *", ':interrobang');
        } else {
            $this->addCommand("gold -".$cmd[1]);
        }
    }


    //// !buy (alias for get & losing gold)
    protected function _cmd_buy($cmd) {
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
    protected function _cmd_luckyescape($cmd) {
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


    //// !test <luck/skill/stam> (run a skill test)
    protected function _cmd_test($cmd) {
        $player = &$this->player;

        $stats = $this->getStats();
        $stat = $this->getStatFromAlias(strtolower($cmd[1]), $stats);
        $sinfo = &$stats[$stat];
        $dicemod = ($cmd[2]?(int)$cmd[2]:0);
        // Setup outcome pages to read if provided
        if ($cmd[3]) {
            $success_page = "page ".$cmd[3];
        }
        if ($cmd[4]) {
            $fail_page = "page ".$cmd[4];
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

        // Check roll versus target number
        $target = $player[$stat];
        if ($roll+$dicemod <= $target) {
            if ($stat == "luck") {
                $player['luck']--;
                sendqmsg("_*$youare lucky*_\n_(_ $emojidice _vs $target, Remaining luck ".$player['luck'].")_", ':four_leaf_clover:');
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
                sendqmsg("_*$text*_\n_(_ $emojidice _vs $target)_", $icon);
            }
            // Show follow up page
            if (isset($success_page)) {
                $this->addCommand($success_page);
            }
            // return successful
            return true;
        }
        else {
            if ($stat == "luck") {
                $player['luck']--;
                sendqmsg("_*$youare unlucky.*_\n_(_ $emojidice _vs $target, Remaining luck ".$player['luck'].")_", ':lightning:');
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
                sendqmsg("_*$text*_\n_(_ $emojidice _vs $target)_", $icon);
            }
            // Show follow up page
            if (isset($fail_page)) {
                $this->addCommand($fail_page);
            }
            // return failure
            return false;
        }
    }


    //// !fight [name] <skill> <stamina> [maxrounds] (run fight logic)
    protected function _cmd_fight($cmd) {
        $out = $this->runFight(['player' => &$this->player,
                'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                'monsterskill' => $cmd[2],
                'monsterstam' => $cmd[3],
                'playerdicemod' => ($cmd[4]?$cmd[4]:0),
                'maxrounds' => ($cmd[5]?$cmd[5]:50)
            ]);
        sendqmsg($out, ":crossed_swords:");
    }


    //// !critfight [name] <skill> [who] [critchance] [+/-dicemod] (run crit fight logic)
    protected function _cmd_critfight($cmd) {
        $critsfor = ($cmd[3]?$cmd[3]:'me');
        $critchance = ($cmd[4]?$cmd[4]:2);
        if (!in_array($critsfor, ['both', 'me'])) {
            $critsfor = 'me';
        }
        if (!is_numeric($critchance) || $critchance < 1 || $critchance > 6) {
            $critchance = 2;
        }

        $out = "_*You".($critsfor == 'both'?' both':'')." have to hit critical strikes!* ($critchance in 6 chance)_\n";
        $out = $this->runFight(['player' => &$this->player,
                'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                'monsterskill' => $cmd[2],
                'critsfor' => $critsfor,
                'critchance' => $critchance,
                'playerdicemod' => ($cmd[5]?$cmd[5]:0)
            ]);
        sendqmsg($out, ":crossed_swords:");
    }


    //// !bonusfight [name] <skill> <stamina> <bonusdamage> [bonusdmgchance] [+/-dicemod] (run bonus attack fight logic)
    protected function _cmd_bonusfight($cmd) {
        $out = $this->runFight(['player' => &$this->player,
                'monstername' => ($cmd[1]?$cmd[1]:"Opponent"),
                'monsterskill' => $cmd[2],
                'monsterstam' => $cmd[3],
                'bonusdmg' => $cmd[4],
                'bonusdmgchance' => ($cmd[5]?$cmd[5]:3),
                'playerdicemod' => ($cmd[6]?$cmd[6]:0)
            ]);
        sendqmsg($out, ":crossed_swords:");
    }


    //// !vs <name 1> <skill 1> <stamina 1> <name 2> <skill 2> <stamina 2>
    protected function _cmd_vs($cmd) {
        $vsplayer = array(
            'name' => $cmd[1],
            'referrers' => ['you' => $cmd[1], 'youare' => $cmd[1].' is', 'your' => $cmd[1]."'s"],
            'skill' => $cmd[2],
            'stam' => $cmd[3],
            'luck' => 0,
            'weapon' => 0,
            'shield' => false
        );
        $out = $this->runFight(['player' => &$vsplayer,
                'monstername' => $cmd[4],
                'monsterskill' => $cmd[5],
                'monsterstam' => $cmd[6]
            ]);
        sendqmsg($out, ":crossed_swords:");
    }


    //// !fighttwo <name 1> <skill 1> <stamina 1> [<name 2> <skill 2> <stamina 2>]
    protected function _cmd_fighttwo($cmd) {
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

        $out = $this->runFight(['player' => &$this->player,
                'monstername' => $m,
                'monsterskill' => $mskill,
                'monsterstam' => $mstam,
                'monster2name' => $m2,
                'monster2skill' => $mskill2,
                'playerdicemod' => ($cmd[7]?$cmd[7]:0)
            ]);
        if ($this->player['stam'] > 0) {
            $this->addCommand("fight $m2 $mskill2 $mstam2");
            $this->runFight(['player' => &$this->player,
                    'monstername' => $m2,
                    'monsterskill' => $mskill2,
                    'monsterstam' => $mstam2,
                    'playerdicemod' => ($cmd[7]?$cmd[7]:0)
                ]);
        }
        sendqmsg($out, ":crossed_swords:");
    }


    //// !fightbackup [name 1] <skill 1> <stamina 1> [backup's name] <backup's skill>
    protected function _cmd_fightbackup($cmd) {
        // Set monster
        $m = ($cmd[1]?$cmd[1]:'Opponent');
        $mskill = $cmd[2];
        $mstam = $cmd[3];

        // Set backup
        $backupname = ($cmd[4]?$cmd[4]:'The backup');
        $backupskill = $cmd[5];

        $out = $this->runFight(['player' => &$this->player,
                'monstername' => $m,
                'monsterskill' => $mskill,
                'monsterstam' => $mstam,
                'backupname' => $backupname,
                'backupskill' => $backupskill,
                'playerdicemod' => ($cmd[6]?$cmd[6]:0)
            ]);
        sendqmsg($out, ":crossed_swords:");
    }


    //// !attack <skill>
    protected function _cmd_attack($cmd) {
        $dmg = ($cmd[2]?$cmd[2]:0);
        $out = $this->runSingleAttack($this->player, 'Opponent', $cmd[1], 999, $dmg, 0);

        sendqmsg($out, ":crossed_swords:");
    }


    //// !dead - Kill your character.
    protected function _cmd_dead($cmd) {
        $this->player['stam'] = 0;
    }


    //// !phaser/gun [-/+modifier] [stun/kill] [name] <skill> [stun/kill] [maxrounds] (run phaser fight logic)
    protected function _cmd_gun($cmd) {
        $out = $this->runGunFight(['player' => &$this->player,
                'stunkill' => ($cmd[1]?$cmd[1]:'stun'),
                'monstername' => ($cmd[2]?$cmd[2]:"Opponent"),
                'monsterskill' => $cmd[3],
                'mstunkill' => ($cmd[4]?$cmd[4]:'kill'),
                'maxrounds' => ($cmd[5]?$cmd[5]:50),
                'modifier' => ($cmd[6]?$cmd[6]:0),
            ]);
        sendqmsg($out, ":gun:");
    }


    //// !π - Easter egg
    protected function _cmd_easteregg($cmd) {
        $eggs = file('resources/easter_eggs.txt');
        $fullcmd = trim($eggs[array_rand($eggs)]);

        $cmdlist = explode(";", $fullcmd);
        foreach ($cmdlist as $cmd) {
            // Easter eggs are assumed to be SAFE
            $this->addCommand($cmd, true, true);
        }
    }


    protected function runFight($input) {
        // Inputs
        if (!isset($input['player'])) return false;
        if (!isset($input['monstername'])) return false;
        if (!isset($input['monsterskill'])) return false;
        $player = &$input['player'];
        $m = $input['monstername'];
        $mskill = &$input['monsterskill'];
        $mstam =          (isset($input['monsterstam'])?   $input['monsterstam']:    999);
        $maxrounds =      (isset($input['maxrounds'])?     $input['maxrounds']:      50);
        $critsfor =       (isset($input['critsfor'])?      $input['critsfor']:       'nobody');
        $critchance =     (isset($input['critchance'])?    $input['critchance']:     2);
        $m2 =             (isset($input['monster2name'])?  $input['monster2name']:   null);
        $mskill2 =        (isset($input['monster2skill'])? $input['monster2skill']:  null);
        $backupname =     (isset($input['backupname'])?    $input['backupname']:     null);
        $backupskill =    (isset($input['backupskill'])?   $input['backupskill']:    null);
        $bonusdmg =       (isset($input['bonusdmg'])?      $input['bonusdmg']:       0);
        $bonusdmgchance = (isset($input['bonusdmgchance'])?$input['bonusdmgchance']: 3);
        $fasthands =      (isset($input['fasthands'])?     $input['fasthands']:      false);
        $healthstatname = (isset($input['healthstatname'])?$input['healthstatname']: 'stamina');
        $playerdicemod =  (isset($input['playerdicemod'])? $input['playerdicemod']:  0);
        $gamebook = getbook();

        // Special case for Starship Traveller Macommonian
        if ($gamebook == 'ff_sst' && $player['race'] == 'Macommonian') {
            $fasthands = true;
        }

        // Special case for rebel planet: players ALWAYS have critchance 1/6
        if ($gamebook = 'ff_rp') {
            if ($critsfor == 'them') {
                $crtisfor = 'both';
            } elseif ($critsfor != 'both') {
                $crtisfor = 'me';
            }
            $critchance = 1;
        }

        // Referrers
        if (isset($player['referrers'])) {
            $referrers = $player['referrers'];
        } else {
            $referrers = ['you' => 'you', 'youare' => 'you are', 'your' => 'your'];
        }
        $you = ucfirst($referrers['you']);
        $youlc = $referrers['you'];
        $youare = ucfirst($referrers['youare']);
        $your = ucfirst($referrers['your']);

        // Process maxrounds special cases
        $stop_when_hit_you = false;
        $stop_when_hit_them = false;
        if (strtolower($maxrounds) == 'hitme') {
            $stop_when_hit_you = true;
        } elseif (strtolower($maxrounds) == 'hitthem') {
            $stop_when_hit_them = true;
        } elseif (strtolower($maxrounds) == 'hitany') {
            $stop_when_hit_you = true;
            $stop_when_hit_them = true;
        }
        if (!is_numeric($maxrounds)) {
            $maxrounds = 50;
        }

        $out = "";
        $round = 0;
        while ($player['stam'] > 0 && $mstam > 0) {
            $round++;
            $mroll = rand(1, 6); $mroll2 = rand(1, 6);
            $proll = rand(1, 6); $proll2 = rand(1, 6);
            $memoji = diceemoji($mroll).diceemoji($mroll2);
            $pemoji = diceemoji($proll).diceemoji($proll2).($playerdicemod?sprintf("%+d", $playerdicemod):'');

            $mattack = $mskill+$mroll+$mroll2;
            $pattack = $player['skill']+$player['weapon']+$proll+$proll2+$playerdicemod;

            // Special case for Creature of Havok instant kills
            if ($gamebook == 'ff_coh' && $proll == $proll2) {
                $out .= "_*Instant Kill*_ $pemoji\n";
                $mstam = 0;
                break;
            }

            // Fast hands gives 1 extra dice, drop lowest for attack power
            if ($fasthands) {
                $fhroll  = rand(1, 6);
                $fhroll2 = rand(1, 6);
                $fhemoji = diceemoji($fhroll).diceemoji($fhroll2).($playerdicemod?sprintf("%+d", $playerdicemod):'');
                if ($fhroll+$fhroll2 > $proll+$proll2) {
                    $pattack = $player['skill']+$player['weapon']+$fhroll+$fhroll2+$playerdicemod;
                    $pemoji = "~$pemoji~ / $fhemoji";
                } else {
                    $pemoji = "$pemoji / ~$fhemoji~";
                }
                if ($round >= 3 && !($gamebook == 'ff_sst' && $player['race'] == 'Macommonian')) {
                    $fasthands = false;
                }
            }

            if ($critsfor != 'nobody') {
                $croll = rand(1, 6);
                $cemoji = diceemoji($croll);
            }

            if ($pattack > $mattack) {
                $out .= "_$you hit $m. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)";
                if ($critsfor == 'both' || $critsfor == 'me') {
                    if ($croll > 6-$critchance) {
                        $out .= " *and it was a critical strike!* (_ $cemoji _)_\n";
                        $mstam = 0;
                        break;
                    }
                    else {
                        $out .= " but failed to get a critical strike._ (_ $cemoji _)";
                    }
                }
                $out .= "_\n";
                $mstam -= 2;
                if ($stop_when_hit_them) { break; }
            }
            else if ($pattack < $mattack) {
                $out .= "_$m hits $youlc! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)";
                if ($critsfor == 'both') {
                    if ($croll > 6-$critchance) {
                        $out .= " *and it was a critical strike!* (_ $cemoji _)_\n";
                        $player['stam'] = 0;
                        break;
                    } else {
                        $out .= " but failed to get a critical strike. (_ $cemoji _)";
                    }
                }
                if ($player['shield'] && rand(1, 6) == 6) {
                    $out .= " :shield: $your shield reduces the damage by 1! (_ ".diceemoji(6)." _) ";
                    $player['stam'] += 1;
                }
                $out .= "_\n";
                $player['stam'] -= 2;
                if ($stop_when_hit_you) { break; }
            }
            else {
                $out .= "_$you and $m avoid each others blows. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            }

            // Monster 2 attack
            if ($m2) {
                $mroll = rand(1, 6); $mroll2 = rand(1, 6);
                $proll = rand(1, 6); $proll2 = rand(1, 6);
                $mattack = $mskill2+$mroll+$mroll2;
                $pattack = $player['skill']+$player['weapon']+$proll+$proll2;

                $memoji = diceemoji($mroll).diceemoji($mroll2);
                $pemoji = diceemoji($proll).diceemoji($proll2);

                if ($pattack > $mattack) {
                    $out .= "_$you block $m2's attack. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
                }
                else if ($pattack < $mattack) {
                    $out .= "_$m2 hit  $youlc! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
                    $player['stam'] -= 2;
                    if ($stop_when_hit_you) { break; }
                }
                else {
                    $out .= "_$m2's attack fails to hit $youlc. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
                }
            }

            //  Your backup attack
            if ($backupname) {
                $mroll = rand(1, 6); $mroll2 = rand(1, 6);
                $proll = rand(1, 6); $proll2 = rand(1, 6);
                $mattack = $mskill+$mroll+$mroll2;
                $pattack = $backupskill+$proll+$proll2;

                $memoji = diceemoji($mroll).diceemoji($mroll2);
                $pemoji = diceemoji($proll).diceemoji($proll2);

                if ($pattack > $mattack) {
                    $out .= "_$backupname hits $m! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
                    $mstam -= 2;
                    if ($stop_when_hit_them) { break; }
                }
                else if ($pattack < $mattack) {
                    $out .= "_$m blocks the attack of $backupname! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
                }
                else {
                    $out .= "_$backupname's attack fails to hit $m. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
                }
            }

            // Bonus damage
            if ($bonusdmg && $mstam > 0) {
                $bdroll = rand(1, 6);
                if ($bdroll > 6-$bonusdmgchance) {
                    $bdemoji = ($bonusdmgchance < 6?'(_ '.diceemoji($bdroll).' _)':'');
                    $out .= "_$m hits $youlc with ".$bonusdmg." bonus damage! $bdemoji _\n";
                    $player['stam'] -= $bonusdmg;
                }
            }

            //stave off death
            if ($player['stam'] == 0 && $player['luck'] > 0) {
                // roll 2d6
                $d1 = rand(1, 6);
                $d2 = rand(1, 6);
                $e1 = diceemoji($d1);
                $e2 = diceemoji($d2);
                $out .= "_Testing luck to stave off death... ";
                if ($d1+$d2 <= $player['luck']) {
                    $out .= " $youare lucky!_ :four_leaf_clover: ( $e1 $e2 )\n";
                    $player['stam'] += 1;
                } else {
                    $out .= " $youare unlucky!_ :lightning: ( $e1 $e2 )\n";
                    $player['stam'] -= 1;
                }
                $player['luck']--;
            }

            if ($round == $maxrounds) {
                break;
            }
        }

        if ($player['stam'] < 1) {
            $out .= "_*$m defeated $youlc!*_\n";
        } elseif ($mstam < 1) {
            $out .= "_*$you defeated $m!*_\n";
            $out .= "_($your remaining $healthstatname: ".$player['stam'].")_";
        } else {
            if ($round > 1) {
                $out .= "_*Combat stopped after $round rounds.*_\n";
            }
            $out .= "_($m's remaining $healthstatname: $mstam. $your remaining $healthstatname: ".$player['stam'].")_";
        }

        return $out;
    }


    protected function runSingleAttack(&$player, $mname, $mskill, $mstam, $mdamage = 2, $pdamage = 2) {
        $mroll = rand(1, 6); $mroll2 = rand(1, 6);
        $proll = rand(1, 6); $proll2 = rand(1, 6);
        $mattack = $mskill+$mroll+$mroll2;
        $pattack = $player['skill']+$player['weapon']+$proll+$proll2;

        $memoji = diceemoji($mroll).diceemoji($mroll2);
        $pemoji = diceemoji($proll).diceemoji($proll2);

        if ($pattack > $mattack) {
            $out = "_You hit $mname. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            if ($pdamage > 0) {
                $mstam -= $pdamage;
                if ($mstam > 0) {
                    $out .= "_($mname's remaining stamina: $mstam)_";
                } else {
                    $out .= "_*You have defeated $mname!*_\n";
                }
            }
        }
        else if ($pattack < $mattack) {
            $out = "_$mname hits you! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            if ($mdamage > 0) {
                $player['stam'] -= $mdamage;
                if ($player['stam'] > 0) {
                    $out .= "_(Your remaining stamina: ".$player['stam'].")_";
                } else {
                    $out .= "_*$mname has defeated you!*_\n";
                }
            }
        }
        else {
            $out = "_You avoid each others blows. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
        }

        return $out;
    }


    protected function runGunFight($input) {
        // Inputs
        if (!isset($input['player'])) return false;
        if (!isset($input['monstername'])) return false;
        if (!isset($input['monsterskill'])) return false;
        $player = &$input['player'];
        $m = $input['monstername'];
        $mskill = &$input['monsterskill'];
        $maxrounds = (isset($input['maxrounds'])? $input['maxrounds']:             50);
        $modifier  = (isset($input['modifier'])?  $input['modifier']:              0);
        $stunkill  = (isset($input['stunkill'])?  strtolower($input['stunkill']):  'stun').'ed';
        $mstunkill = (isset($input['mstunkill'])? strtolower($input['mstunkill']): 'kill').'ed';

        // Referrers
        if (isset($player['referrers'])) {
            $referrers = $player['referrers'];
        } else {
            $referrers = ['you' => 'you', 'youare' => 'you are', 'your' => 'your'];
        }
        $you = ucfirst($referrers['you']);
        $your = ucfirst($referrers['your']);
        $youare = ucfirst($referrers['youare']);

        // Fight loop
        $out = "";
        $round = 0;
        while (true) {
            $round++;
            // Player
            $roll = rand(1, 6); $roll2 = rand(1, 6);
            $emoji = diceemoji($roll).diceemoji($roll2).($modifier?sprintf("%+d", $modifier):'');
            if (($roll+$roll2+$modifier) >= $player['skill']) {
                $out .= "_$your shot missed!_ ($emoji vs ".$player['skill'].")\n";
            } else {
                $out .= "_$your shot hit!_ ($emoji vs ".$player['skill'].")\n";
                $out .= "_*$you $stunkill $m!*_";
                break;
            }
            // Monster
            $roll = rand(1, 6); $roll2 = rand(1, 6);
            $emoji = diceemoji($roll).diceemoji($roll2);
            if (($roll+$roll2) >= $mskill) {
                $out .= "_$m's shot missed!_ ($emoji vs $mskill)\n";
            } else {
                $out .= "_$m's shot hit!_ ($emoji vs $mskill)\n";
                $out .= "_*$youare $mstunkill!*_";
                if ($mstunkill == 'killed') {
                    $player['stam'] = 0;
                }
                break;
            }

            if ($round == $maxrounds) {
                $out .= "_*Combat stopped after $round rounds.*_\n";
                break;
            }
        }

        return $out;
    }


}
