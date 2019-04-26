<?php

require_once 'character_importable.php';

class book_lonewolf extends book_character_importable {
    protected function getLoneWolfSkillsName() {
        return 'Skills';
    }


    protected function getLoneWolfRankTitles() {
        return [];
    }


    public function isDead() {
        return $this->player['endurance'] < 1;
    }


    protected function isHealer() {
        return false;
    }


    protected function getStats() {
        $stats = array(
            'endurance' => [
                'friendly' => 'Endurance',
                'icons' => [':heartpulse', ':face_with_head_bandage:'],
                'alias' => ['end'],
                'roll' => 'lonewolfendurance',
            ],
            'skill' => [
                'friendly' => 'Combat Skill',
                'icons' => ':crossed_swords:',
                'alias' => ['combat', 'combatskill'],
                'roll' => 'lonewolfcombat',
            ],
            'gold' => [
                'friendly' => 'Gold Crowns',
                'icons' => ':moneybag:',
                'alias' => ['crowns', 'goldcrowns'],
                'roll' => 'lonewolfgold',
            ],
        );
        return $stats;
    }


    protected function getCharacterString() {
        $p = &$this->player;
        return "*".$p['name']."*, ".$p['adjective']." Wolf _(".$p['gender'].")_";
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        $p['skills'] = array();
        $p['stuff'] = array();
        $p['weapons'] = array();
        $p['specials'] = array();
        $d = rand(0, 9);
        $p['creationdice'] .= ' '.cardemoji($d);
        switch ($d) {
        case 1:
            $p['weapons'][] = 'Sword';
            break;
        case 2:
            $p['specials'][] = 'Helmet (+2 END)';
            $p['endurance'] += 2;
            $p['max']['endurance'] += 2;
            break;
        case 3:
            $p['stuff'][] = 'Meal';
            $p['stuff'][] = 'Meal';
            break;
        case 4:
            $p['specials'][] = 'Chainmail Waistcoat (+4 END)';
            $p['endurance'] += 4;
            $p['max']['endurance'] += 4;
            break;
        case 5:
            $p['weapons'][] = 'Mace';
            break;
        case 6:
            $p['stuff'][] = 'Healing Potion (Restore 4 END)';
            break;
        case 7:
            $p['weapons'][] = 'Quarterstaff';
            break;
        case 8:
            $p['weapons'][] = 'Spear';
            break;
        case 9:
            $p['gold'] += 12;
            break;
        case 0:
            $p['weapons'][] = 'Broadsword';
            break;
        }
        return $p;
    }


    protected function getCharcterSheetAttachments() {
        global $config;

        $player = &$this->player;
        $attachments[0]['color'] = $player['colourhex'];
        $attachments[0]['fields'] = [
            ['title' => 'Endurance (end)',
                'value' => $player['endurance']." / ".$player['max']['endurance'],
                'short' => true],
            ['title' => 'Combat Skill (skill)',
                'value' => $player['skill'],
                'short' => true],
            ['title' => 'Gold Crowns (gold)',
                'value' => $player['gold'].' / '.$player['max']['gold'],
                'short' => true],
        ];

        $attachments[1]['color'] = $player['colourhex'];
        $s = $player['skills'];
        $levels = $this->getLoneWolfRankTitles();
        foreach ($s as $key => $val) {
            if (!$levels) break;
            $s[$key] = '`'.array_shift($levels).':`'.$val;
        }
        $attachments[1]['fields'] = [[
                'title' => $this->getLoneWolfSkillsName(),
                'value' => implode("\n", array_slice($s, 0, ceil(sizeof($s) / 2))),
                'short' => true
            ],
            [
                'title' => html_entity_decode("&nbsp;"),
                'value' => implode("\n", array_slice($s, ceil(sizeof($s) / 2))),
                'short' => true
            ]];

        return $attachments;
    }


    // In Slack format
    protected function getStuffAttachment() {
        $s  = $this->player['stuff'];
        $sp = $this->player['specials'];
        $w  = $this->player['weapons'];

        $s = array_map("ucfirst", $s);
        $sp = array_map("ucfirst", $sp);
        $w = array_map("ucfirst", $w);
        natcasesort($s);
        natcasesort($sp);

        $attachments = array(
            'color'    => '#666666',
            'fields'   => [[
                    'title' => 'Backpack ('.count($s).'/8)',
                    'value' => $s?implode("\n", $s):'(Nothing!)',
                    'short' => true
                ],
                [
                    'title' => 'Special Items',
                    'value' => $sp?implode("\n", $sp):'(None!)',
                    'short' => true
                ],
                [
                    'title' => 'Weapons ('.count($w).'/2)',
                    'value' => $w?implode("\n", $w):'(Unarmed!)',
                    'short' => true
                ]]
        );

        return $attachments;
    }


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('get',     '_cmd_get',     ['(\s+special|\s+sp)?', 'l']);
        $this->registerCommand('take',    '_cmd_get',     ['(\s+special|\s+sp)?', 'l']);
        $this->registerCommand('ng',      '_cmd_newgame', ['osl', 'osl', 'osl', 'osl']);
        $this->registerCommand('newgame', '_cmd_newgame', ['osl', 'osl', 'osl', 'osl']);
        $this->registerCommand('fight',   '_cmd_fight',   ['oms', 'n', 'n', 'onm', 'on']);
        $this->registerCommand('attack',  '_cmd_attack',  ['oms', 'n', 'n', 'onm', 'on']);
        $this->registerCommand('a',       '_cmd_attack',  ['oms', 'n', 'on', 'onm']);
        $this->registerCommand('flee',    '_cmd_flee',    ['oms', 'n', 'onm', 'on']);
        $this->registerCommand('rand',    '_cmd_roll',    ['on']);
        $this->registerCommand('learn',   '_cmd_learn',   ['l']);
        $this->registerCommand('forget',  '_cmd_forget',  ['l']);
        $this->registerCommand('wield',   '_cmd_wield',   ['l']);
        $this->registerCommand('unwield', '_cmd_unwield', ['l']);
        $this->registerCommand('eat',     '_cmd_eat');
    }


    protected function runImportUpdate(&$p) {
        parent::runImportUpdate($p);
        // Fully heal
        $p['endurance'] = $p['max']['endurance'];
        // Clear skills if ruleset changed
        if ($p['ruleset'] != get_class($this)) {
            $p['skills'] = array();
        }
    }


    //// !roll [x] (roll xd10-1) OVERRIDE
    protected function _cmd_roll($cmd) {
        $numdice = ($cmd[1]?$cmd[1]:1);
        $numdice = max(min($numdice, 100), 1);
        $out = "Result:";

        $t = 0;
        for ($a = 0; $a < $numdice; $a++) {
            $r = rand(0, 9);
            $emoji = genericemoji($r);
            $out .= " $emoji ($r)";
            $t += $r;
        }
        sendqmsg($out, ":game_die:");
    }


    //// !newgame (roll new character)
    protected function _cmd_newgame($cmd) {
        $cmd = array_pad($cmd, 5, '?');
        $cmd[5] = $cmd[4];
        $cmd[4] = 'Human';
        return parent::_cmd_newgame($cmd);
    }


    //// !get / !take (add item to inventory/stuff list) OVERRIDE
    protected function _cmd_get($cmd) {
        $item = $cmd[2];
        if ($cmd[1]) { // Goes to special items inventory
            $this->player['specials'][] = $item;
            sendqmsg("*Got the special item $item!*", ":school_satchel:");
        } else {
            if (sizeof($this->player['stuff']) >= 8) {
                sendqmsg("*Your inventory is full! `!drop` something first.*", ':interrobang:');
                return;
            }
            return parent::_cmd_get([$cmd[0], $item]);
        }
    }


    //// !eat Lone Wolf version
    protected function _cmd_eat($cmd) {
        $stuff = &$this->player['stuff'];
        $result = smart_remove_from_list($stuff, 'meal');
        if ($result === false) {
            $result = smart_remove_from_list($stuff, 'food');
        }
        // If multi-match, just eat the first
        if (is_array($result)) {
            $result = smart_remove_from_list($stuff, $result[0]);
        }

        if ($result === false) {
            $this->player['endurance'] -= 3;
            sendqmsg("*Nothing to eat! Lost 3 Endurance.*", ':interrobang:');
        } else {
            sendqmsg("*Ate the $result!*", ':bread:');
        }
    }


    //// !drop OVERRIDE (Try to drop special items after)
    protected function _cmd_drop($cmd) {
        $drop = $cmd[1];
        $verb = strtolower($cmd[0]);

        // Special case: whole backpack!
        if (strtolower($drop) == 'backpack') {
            $this->player['stuff'] = array();
            $result = 'entire backpack';
        } else {
            $result = smart_remove_from_list($this->player['stuff'], $drop);
        }
        // If we found nothing in stuff, try again in specials
        if ($result === false) {
            $result = smart_remove_from_list($this->player['specials'], $drop);
        }
        // If we found nothing in specials, try again in weapons
        if ($result === false) {
            $result = smart_remove_from_list($this->player['weapons'], $drop);
        }

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


    //// !page OVERRIDE
    protected function _cmd_page($cmd) {
        $player = &$this->player;
        // Automatically apply healing skill when hurt, have skill and no fight
        if (($player['endurance'] < $player['max']['endurance']) && $this->isHealer()) {
            global $config;
            require $config->book_file;
            $story = $book[$cmd[1]];
            if (!(strpos($story, 'COMBAT SKILL') && strpos($story, 'ENDURANCE'))) {
                $player['endurance']++;
                sendqmsg("Your healing replenishes 1 endurance.");
            }
        }
        return parent::_cmd_page($cmd);
    }


    //// !learn (learn skill)
    protected function _cmd_learn($cmd) {
        $item = $cmd[1];
        $skills = &$this->player['skills'];

        // Prevent duplicate entries
        if (array_search(strtolower($item), array_map('strtolower', $skills)) !== false) {
            sendqmsg("*You already know '$item'.*", ':interrobang:');
            return;
        }

        $skills[] = $item;
        sendqmsg("*Learned $item!*", ":school:");
    }


    //// !forget
    protected function _cmd_forget($cmd) {
        $drop = $cmd[1];
        $result = smart_remove_from_list($this->player['skills'], $drop);

        if ($result === false) {
            sendqmsg("*'$drop' didn't match any skill. Can't forget.*", ':interrobang:');
        } elseif (is_array($result)) {
            sendqmsg("*Which did you want to forget? ".implode(", ", $result)."*", ':interrobang:');
        } else {
            sendqmsg("*Forgot $result!*", ":confounded:");
        }
    }


    //// !wield (add weapon)
    protected function _cmd_wield($cmd) {
        $weapons = &$this->player['weapons'];
        $weapon = $cmd[1];

        // Attempt to take weapon from backpack
        $result = smart_remove_from_list($this->player['stuff'], $weapon);
        if (is_array($result)) {
            sendqmsg("*Which did you want to wield? ".implode(", ", $result)."*", ':interrobang:');
            return;
        } elseif ($result === false) {
            $out = "*Weilding $weapon!*";
        } else {
            $weapon = $result;
            $out = "*Weilding $weapon!* (Taken from backpack.)";
        }

        // Unwield weapon if already holding 2
        if (sizeof($weapons) >= 2) {
            $this->_cmd_unwield(['unwield', end($weapons)]);
        }

        $weapons[] = $weapon;
        sendqmsg($out, ":crossed_swords:");
    }


    //// !unwield (remove weapon)
    protected function _cmd_unwield($cmd) {
        $drop = $cmd[1];
        $result = smart_remove_from_list($this->player['weapons'], $drop);

        if ($result === false) {
            sendqmsg("*'$drop' didn't match any weapon. Can't unwield.*", ':interrobang:');
        } elseif (is_array($result)) {
            sendqmsg("*Which did you want to unwield? ".implode(", ", $result)."*", ':interrobang:');
        } else {
            if (sizeof($this->player['stuff']) < 8) {
                $this->player['stuff'][] = $result;
                $out = "(Placed in backpack.)";
            } else {
                $out = "(Dropped on floor.)";
            }
            sendqmsg("*Unwielded $result!* $out", ":crossed_swords:");
        }
    }


    //// !fight Lone Wolf version
    protected function _cmd_fight($cmd) {
        $out = $this->runLoneWolfFight(
            $cmd[1]?ucfirst($cmd[1]):'Opponent',
            $cmd[2],
            $cmd[3],
            $cmd[4]?(int)$cmd[4]:0,
            $cmd[5]?$cmd[5]:100);

        sendqmsg($out, ":crossed_swords:");
    }


    //// !attack Lone Wolf version
    protected function _cmd_attack($cmd) {
        $out = $this->runLoneWolfFight(
            $cmd[1]?ucfirst($cmd[1]):'Opponent',
            $cmd[2],
            $cmd[3]?$cmd[3]:0,
            $cmd[4]?(int)$cmd[4]:0,
            1);

        sendqmsg($out, ":crossed_swords:");
    }


    //// !fight Lone Wolf version
    protected function _cmd_flee($cmd) {
        $out = $this->runLoneWolfFight(
            $cmd[1]?ucfirst($cmd[1]):'Opponent',
            $cmd[2],
            9999,
            $cmd[3]?(int)$cmd[3]:0,
            $cmd[4]?$cmd[4]:1,
            true);

        sendqmsg($out, ":crossed_swords:");
    }


    //// !fight Lone Wolf version
    protected function runLoneWolfFight($opp_name, $opp_skill, $opp_end, $bonus = 0, $max_turns = 100, $flee = false) {
        $p = &$this->player;

        apply_temp_stats($p);
        $sk = $p['skill']+$bonus;
        $cr = $sk - $opp_skill;

        $crt = [
            'lu' => [    -11,      -9,      -7,      -5,      -3,      -1,       0,       2,       4,       6,       8,      10, PHP_INT_MAX],
            1 => [[ 0, 99], [ 0, 99], [ 0, 8], [ 0, 6], [ 1, 6], [ 2, 5], [ 3, 5], [ 4, 5], [ 5, 4], [ 6, 4], [ 7, 4], [ 8, 3], [ 9, 3]],
            2 => [[ 0, 99], [ 0, 8], [ 0, 7], [ 1, 6], [ 2, 5], [ 3, 5], [ 4, 4], [ 5, 4], [ 6, 3], [ 7, 3], [ 8, 3], [ 9, 3], [10, 2]],
            3 => [[ 0, 8], [ 0, 7], [ 1, 6], [ 2, 5], [ 3, 5], [ 4, 4], [ 5, 4], [ 6, 3], [ 7, 3], [ 8, 3], [ 9, 2], [10, 2], [11, 2]],
            4 => [[ 0, 8], [ 1, 7], [ 2, 6], [ 3, 5], [ 4, 4], [ 5, 4], [ 6, 3], [ 7, 3], [ 8, 3], [ 9, 2], [10, 2], [11, 2], [12, 2]],
            5 => [[ 1, 7], [ 2, 6], [ 3, 5], [ 4, 4], [ 5, 4], [ 6, 3], [ 7, 2], [ 8, 2], [ 9, 2], [10, 2], [11, 2], [12, 2], [14, 1]],
            6 => [[ 2, 6], [ 3, 6], [ 4, 5], [ 5, 4], [ 6, 3], [ 7, 2], [ 8, 2], [ 9, 2], [10, 2], [11, 1], [12, 1], [14, 1], [16, 1]],
            7 => [[ 3, 5], [ 4, 5], [ 5, 4], [ 6, 3], [ 7, 2], [ 8, 2], [ 9, 1], [10, 1], [11, 1], [12, 0], [14, 0], [16, 0], [18, 0]],
            8 => [[ 4, 4], [ 5, 4], [ 6, 3], [ 7, 2], [ 8, 1], [ 9, 1], [10, 0], [11, 0], [12, 0], [14, 0], [16, 0], [18, 0], [99, 0]],
            9 => [[ 5, 3], [ 6, 3], [ 7, 2], [ 8, 0], [ 9, 0], [10, 0], [11, 0], [12, 0], [14, 0], [16, 0], [18, 0], [99, 0], [99, 0]],
            0 => [[ 6, 0], [ 7, 0], [ 8, 0], [ 9, 0], [10, 0], [11, 0], [12, 0], [14, 0], [16, 0], [18, 0], [99, 0], [99, 0], [99, 0]],
        ];

        foreach ($crt['lu'] as $key => $val) {
            if ($cr <= $val) {
                $crt_lu = $key;
                break;
            }
        }

        $out = "";
        $turns = 0;
        while (1) {
            $rand = rand(0, 9);
            $r = $crt[$rand][$crt_lu];
            // Opponent
            if ($r[1] == 99) {
                $out .= $opp_name.' got a *critical hit*!';
                $p['endurance'] = 0;
            } elseif ($r[1] > 0) {
                $out .= $opp_name.' hit '.$p['name'].' for *'.$r[1].'* damage! ';
                $p['endurance'] -= $r[1];
            }
            // You
            if ($r[0] > 0 && $flee) {
                $out .= $p['name']." was able to escape!\n";
                $out .= "_(".$p['endurance']."/".$p['max']['endurance']." Endurance remaining.)_";
                break;
            } elseif ($r[0] == 99) {
                $out .= $p['name'].' got a *critical hit*! (Instant Kill.)';
                $opp_end = 0;
            } elseif ($r[0] > 0) {
                $out .= $p['name'].' hit '.$opp_name.' for *'.$r[0].'* damage!';
                $opp_end -= $r[0];
            }
            $out .= " ".genericemoji($rand)."\n";
            if (++$turns >= $max_turns) {
                if ($max_turns > 1) {
                    $out .= "*Stopped after $max_turns turns.*\n";
                }
                $out .= "Your remaining endurance: ".$p['endurance'].". $opp_name's remaining endurance: ".$opp_end;
                break;
            }
            if ($p['endurance'] < 1) {
                $out .= "*".$opp_name." has defeated you!*";
                break;
            } elseif ($opp_end < 1) {
                $out .= "*You are victorious!*\n";
                $out .= "_(".$p['endurance']."/".$p['max']['endurance']." Endurance remaining.)_";
                break;
            }
        }

        unapply_temp_stats($p);
        sendqmsg($out, ":crossed_swords:");
    }


}
