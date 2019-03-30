<?php

require_once 'ff_basic.php';

class book_ff_sst extends book_ff_basic {
    public function getId() {
        return 'ff_sst';
    }


    public function storyModify($story) {
        $story = parent::storyModify($story);
        $story = str_ireplace('The Traveller', $this->player['shipname'], $story);
        $story = str_ireplace('Starship Traveller', 'Starship '.substr($this->player['shipname'], 4), $story);
        return $story;
    }


    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective, $seed);
        if (!$race || $race == '?') {
            if ($p['race'] == 'Elf') {
                $p['race'] = 'Vulcan';
            } elseif ($p['race'] == 'Djinnin') {
                $p['race'] = 'Andorian';
            } elseif ($p['race'] == 'Catling') {
                $p['race'] = 'Caitian';
            } elseif ($p['race'] == 'Dwarf') {
                $p['race'] = 'Droid';
            }
        }
        if (!$name || $name == '?') {
            if ($p['race'] == 'Droid') {
                $p['name'] = chr(mt_rand(68, 90)).chr(mt_rand(65, 87)).'-'.mt_rand(1, 9);
            } elseif ($p['race'] == 'Vulcan') {
                $p['name'] = $this->generateVulcanName($p['gender']);
            } elseif ($p['race'] == 'Andorian') {
                $p['name'] = $this->generateAndorianName();
            }
        }
        if ($p['race'] == 'Droid' && (!$emoji || $emoji == '?')) {
            $p['emoji'] = ':robot:';
        }
        if (!$adjective || $adjective == '?') {
            $p['adjective'] = 'Captain';
        }
        // Ship name
        $names = file('resources/starship_names.txt');
        $p['shipname'] = trim($names[array_rand($names)]);
        // Crew
        $cl = ['no1', 'science', 'medic', 'engineer', 'security', 'guard'];
        $races = array('Human', 'Human', 'Human', 'Human', 'Human', 'Vulcan', 'Andorian', 'Caitian', 'Droid');
        foreach ($cl as $k => $c) {
            $d1 = dice();
            $d2 = dice();
            $d3 = dice();
            array_push($p['creationdice'], $d1, $d2, $d3);
            $cm = $this->rollCrew($c, ($k > 0 && $k < 4), $races, [$d1, $d2, $d3]);
            $p['crew'][$c] = $cm;
        }
        return $p;
    }


    protected function rollCrew($position, $combatpenalty, &$races = null, $dice = null) {
        if ($races == null) {
            $races = array('Human', 'Human', 'Human', 'Vulcan', 'Andorian', 'Caitian', 'Droid');
        }
        if ($dice == null) {
            $dice = [dice(), dice(), dice()];
        }
        $cm = array(
            'skill' =>  6+$dice[0],
            'stam' => 12+$dice[1]+$dice[2],
            'position' => ($position=='redshirt'?'RedShirt':ucfirst($position)),
            'gender' => (rand(0, 1)?'Male':'Female'),
            'combatpenalty' => $combatpenalty,
            'replacement' => false,
            'awayteam' => false,
            'luck' => 0,
            'weapon' => 0,
            'shield' => false,
            'temp' => []
        );
        $cm['max']['skill'] = $cm['skill'];
        $cm['max']['stam']  = $cm['stam'];
        // Set race and unset for choice string to avoid repeats
        $r = array_rand($races);
        $cm['race'] = trim($races[$r]);
        unset($races[$r]);
        if ($cm['race'] == 'Droid') {
            $cm['name'] = chr(mt_rand(68, 90)).chr(mt_rand(65, 87)).'-'.mt_rand(1, 9);
        } elseif ($cm['race'] == 'Caitian') {
            $names = file('resources/cat_names.txt');
            $cm['name'] = trim($names[array_rand($names)]);
        } elseif ($cm['race'] == 'Vulcan') {
            $cm['name'] = $this->generateVulcanName($cm['gender']);
        } elseif ($cm['race'] == 'Andorian') {
            $cm['name'] = $this->generateAndorianName();
        } else {
            $names = file($cm['gender']=='Male'?'resources/male_names.txt':'resources/female_names.txt');
            $cm['name'] = trim($names[array_rand($names)]);
        }
        $cm['referrers'] = ['you' => $cm['name'], 'youare' => $cm['name'].' is', 'your' => $cm['name']."'s"];

        return $cm;
    }


    private function generateVulcanName($gender) {
        $c = ["b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "q", "r", "s", "t", "v", "x", "z"];
        $v = ["a", "e", "i", "o", "u"];

        if (strtolower($gender) == 'male') {
            return 'S'.$v[rand(0, 4)].$c[rand(0, 18)].$v[rand(0, 4)].'k';
        } else {
            $b = array_merge($c, $v);
            return "T'P".$v[rand(0, 4)].$b[rand(0, 23)];
        }
    }


    private function generateAndorianName() {
        $c = ["g", "h", "k", "l", "m", "n", "p", "r", "s", "t", "v"];
        $v = ["a", "e", "i", "o", "u"];
        $s = ["Ta", "Th", "Sh", "Ke"];

        if (rand(0, 1) == 1) {
            return ['Th', 'Sh'][rand(0, 1)].$v[rand(0, 4)].$c[rand(0, 10)].$v[rand(0, 4)].$c[rand(0, 10)];
        } else {
            return ['Ta', 'Ke'][rand(0, 1)].$c[rand(0, 10)].$v[rand(0, 4)].$c[rand(0, 10)];
        }
    }


    public function getStats() {
        $stats = parent::getStats();
        $stats['weapons'] = [
            'friendly' => 'Ship Weapons',
            'alias' => ['shipweapons'],
            'icons' => ':rocket:',
            'roll' => 'ff1die',
        ];
        $stats['shields'] = [
            'friendly' => 'Ship Shields',
            'alias' => ['shipshields'],
            'icons' => ':rocket:',
            'roll' => 'ff1die',
            'testdice' => 2,
            'testpass' => 'Your shields hold up',
            'testfail' => 'Your shields do not protect you',
        ];
        return $stats;
    }


    protected function getCharcterSheetAttachments() {
        $player = &$this->player;
        $attachments = parent::getCharcterSheetAttachments();
        // ship
        $attachments[0]['fields'][3] = [
            'title' => 'Ship',
            'value' => $player['shipname'],
            'short' => true
        ];
        $attachments[0]['fields'][4] = [
            'title' => 'Weapons (weapons)',
            'value' => $player['weapons']." / ".$player['max']['weapons'],
            'short' => true
        ];
        $attachments[0]['fields'][5] = [
            'title' => 'Shields',
            'value' => $player['shields']." / ".$player['max']['shields'],
            'short' => true
        ];
        $attachments[0]['fields'][0]['value'] .= '  (Weapon: '.sprintf("%+d", $player['weapon']).')';
        // crew
        $cname = "";
        $cskill = "";
        $cstam = "";
        $cboth = "";
        foreach ($player['crew'] as $cm) {
            $thisname = '*'.($cm['awayteam']?' *⇓*':'').$cm['position'].':* '.$cm['name']." ".($cm['gender']=='Male'?'♂':'♀')." ".$cm['race'];
            $cname .= mb_substr($thisname, 0, 36)."\n";
            $cskill .= $cm['skill'].' / '.$cm['max']['skill'].($cm['combatpenalty']?' *†*':'')."\n";
            $cstam .= $cm['stam'].' / '.$cm['max']['stam'].($cm['replacement']?' *R*':'')."\n";
            $cboth .= 'SKILL: '.$cm['skill'].' / '.$cm['max']['skill'].' | STAMINA: '.$cm['stam'].' / '.$cm['max']['stam'].($cm['combatpenalty']?' *†*':'').($cm['replacement']?' *R*':'')."\n";
        }
        $fields = array([ 'title' => 'Crew (⇓: away team)',
                'value' => $cname,
                'short' => true ]);
        // Discord QOL
        if (DISCORD_MODE) {
            $fields[] = ['title' => 'Skill (†: -2 in combat)',
                'value' => $cskill,
                'short' => true ];
            $fields[] = ['title' => 'Stamina (R: Replaced)',
                'value' => $cstam,
                'short' => true ];
        } else {
            $fields[] = ['title' => 'Stats (†: -2 in combat, R: Replaced)',
                'value' => $cboth,
                'short' => true ];
        }
        $attachments[] = [
            'color'    => '#BB0000',
            'fields'   => $fields ];
        return $attachments;
    }


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('phaser',     '_cmd_gun', ['onm', '(\sstun|\skill)?', 'oms', 'n', '(\sstun|\skill)?', 'on']);
        $this->registerCommand('shipbattle', '_cmd_shipbattle', ['oms', 'n', 'n']);
        $this->registerCommand('recruit',    '_cmd_recruit', ['s', 'os', 'on', 'on', 'os', 'os']);
        $this->registerCommand('beam',       '_cmd_beam', ['(\sup|\sdown)', 'os', 'os', 'os', 'os', 'os']);
        $this->registerCommand('everyone',   '_cmd_everyone', ['l']);
        $this->registerCommand('awayteam',   '_cmd_everyone', ['l']);
        foreach (['no1', 'science', 'medic', 'engineer', 'security', 'guard'] as $pos) {
            $this->registerCommand($pos, '_cmd_order', ['s', 'ol']);
        }
    }


    //// !shipbattle [name] <skill> <stamina> (run ship battle logic)
    public function _cmd_shipbattle($cmd) {
        $out = run_ship_battle(['player' => &$this->player,
                'oppname' => ($cmd[1]?$cmd[1]:"Opponent"),
                'oppweapons' => $cmd[2],
                'oppshields' => $cmd[3],
            ]);
        sendqmsg($out, ":rocket:");
    }


    //// Replace crew
    public function _cmd_recruit($cmd) {
        $pos = $cmd[1];

        if (!array_key_exists($pos, $player['crew'])) {
            sendqmsg("*$pos: invalid position*", ':interrobang:');
        }

        $c = &$this->player['crew'][$pos];
        $c = $this->rollCrew($pos, $c['combatpenalty']);
        if ($cmd[2] && $cmd[2] != '?') {
            $c['name'] = ucfirst($cmd[2]);
        }
        if ($cmd[3] && $cmd[3] != '?') {
            $c['skill'] = $cmd[3];
            $c['max']['skill'] = $c['skill'];
        }
        if ($cmd[4] && $cmd[4] != '?') {
            $c['stam'] = $cmd[4];
            $c['max']['stam'] = $c['stam'];
        }
        if ($cmd[5] && $cmd[5] != '?') {
            $c['gender'] = ucfirst($cmd[5]);
        }
        if ($cmd[6] && $cmd[6] != '?') {
            $c['race'] = ucfirst($cmd[6]);
        }
        $c['referrers'] = ['you' => $c['name'], 'youare' => $c['name'].' is', 'your' => $c['name']."'s"];

        sendqmsg("*".$c['name']." recruited!*", ':handshake:');
    }


    //// !beam <up/down> [crew] [crew] [crew]
    public function _cmd_beam($cmd) {
        $player = &$this->player;
        $out = "";
        $crew = array();
        $dir = strtolower($cmd[1]);
        for ($c = 2; $c <= 6; $c++) {
            if ($cmd[$c]) $crew[] = strtolower($cmd[$c]);
        }

        if (sizeof($crew) < 1 && $dir == 'up') {
            $crew = array_keys($player['crew']);
        }
        foreach ($crew as $k => $c) {
            if (!array_key_exists($c, $player['crew']) ||
                $player['crew'][$c]['awayteam'] == ($dir == 'down') ||
                $player['crew'][$c]['replacement'] == true) {
                unset($crew[$k]);
            } else {
                $player['crew'][$c]['awayteam'] = ($dir == 'down');
                $crew[$k] = $player['crew'][$c]['name'];
            }
        }
        array_unshift($crew, "You");
        $out = "_".basic_num_to_word(count($crew))." to beam $dir!_\n";
        $out .= "*".implode(', ', array_slice($crew, 0, -1)) . (count($crew)>1?' and ':'') . end($crew)." have beamed $dir.*\n";

        // If we are beaming up and have our original MO, we get some healing
        if ($dir == 'up' && $player['crew']['medic']['replacement'] == false) {
            if ($player['stam'] < $player['max']['stam']) {
                $r = min($player['max']['stam']-$player['stam'], 2);
                $out .= "_Your medic treats you restoring $r stamina._\n";
                $player['stam'] += $r;
            }
            foreach ($player['crew'] as $key => $c) {
                if ($c['stam'] < $c['max']['stam']) {
                    $r = min($c['max']['stam']-$c['stam'], 2);
                    $out .= "_Your medic treats ".($key=='medic'?'themself':$c['name'])." restoring $r stamina._\n";
                    $player['crew'][$key]['stam'] += $r;
                }
            }
        }

        sendqmsg($out, ":rocket:");
    }


    //// Special case, order various crew to do commands
    public function _cmd_order($cmd) {
        $officer = strtolower($cmd[0]);
        $crew = &$this->player['crew'][$officer];
        $order = strtolower($cmd[1]);
        $args = $cmd[2];

        // Check command is a valid order
        $valid_orders = ['fight', 'phaser', 'gun', 'critfight', 'bonusfight', 'fighttwo', 'fightbackup',
            'skill', 'stam', 'stamina', 'test', 'dead'];
        $combat_orders = ['fight', 'phaser', 'gun', 'critfight', 'bonusfight', 'fighttwo', 'fightbackup'];
        if (!in_array($order, $valid_orders)) {
            sendqmsg('Cannot order crew to '.$order, ':interrobang:');
            return;
        }

        // Apply combat penalty
        if ($crew['combatpenalty'] && in_array($order, $combat_orders)) {
            $crew['temp']['skill'] += -2;
        }

        // Set the player to crew member
        $mainplayer = &$this->player;
        $this->player = &$crew;
        $this->processCommand($order.' '.$args);
        // Set the player back
        $this->player = &$mainplayer;

        if ($crew['stam'] < 1) {
            $out = "*".$crew['name']." is dead!* :skull:\n";
            $newskill = max(1, $crew['max']['skill']-2);
            $crew = $this->rollCrew($officer, $crew['combatpenalty']);
            $crew['max']['skill'] = $newskill;
            $crew['skill'] = $newskill;
            $crew['replacement'] = true;
            $crew['awayteam'] = false;
            $out .= "Their assistant, ".$crew['name'].", is promoted to the ".$crew['position']." position. ";
            $out .= "(Replacement crew cannot beam down to planets.)";
            sendqmsg($out, ':dead:');
        }
    }


    //// Special case, order WHOLE crew, or away team, to do command
    public function _cmd_everyone($cmd) {
        $this->addCommand($cmd[1]);
        foreach ($this->player['crew'] as $key => $val) {
            if ($cmd[0] != 'awayteam' || $val['awayteam']) {
                $this->addCommand($key.' '.$cmd[1]);
            }
        }
    }


}
