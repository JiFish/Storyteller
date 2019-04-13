<?php

require_once 'character.php';

class book_narnia extends book_character {
    public function getId() {
        return 'narnia';
    }


    protected function getCharacterString() {
        $p = &$this->player;
        return "*".$p['name']."* the ".$p['adjective']." _(".($p['gender']=='Male'?'Son of Adam':'Daughter of Eve').")_";
    }

    public function getStats() {
        $stats = array(
            'fight' => [
                'friendly' => 'Fighting Skill',
                'alias' => ['fighting', 'fightingskill'],
                'allownegative' => true,
            ],
            'trick' => [
                'friendly' => 'Trickery Skill',
                'alias' => ['trickery', 'trickeryskill'],
                'allownegative' => true,
            ],
            'action' => [
                'friendly' => 'Action Skill',
                'alias' => ['actionskill'],
                'allownegative' => true,
            ],
            'talk' => [
                'friendly' => 'Talking Skill',
                'alias' => ['talking', 'talkingkill'],
                'allownegative' => true,
            ],
            'perception' => [
                'friendly' => 'Perception Skill',
                'alias' => ['perceptionskill'],
                'allownegative' => true,
            ],
            'innerstrength' => [
                'friendly' => 'Inner Strength',
                'alias' => ['is', 'strength', 'str','inner strength'],
                'allownegative' => true,
            ],
        );
        return $stats;
    }


    public function rollNarniaCharacter($name = '?', $gender = '?', $statarray = null) {
        $p = parent::rollCharacter($name, $gender);
        $skintone = array(':skin-tone-2:', ':skin-tone-3:', ':skin-tone-4:', ':skin-tone-5:', ':skin-tone-2:');
        if ($p['gender'] == 'Male') {
            $p['emoji'] = ':boy:'.$skintone[array_rand($skintone)];;
        } elseif ($p['gender'] == 'Female') {
            $p['emoji'] = ':girl:'.$skintone[array_rand($skintone)];;
        }
        // Roll/Set stats!
        roll_stats($p, $this->getStats());
        // Deal with setting inital stats
        if (!$statarray) {
            $statarray = [1, 1, 1, 1, 1, 1];
            for ($a = 0; $a < rand(0, 3); $a++) {
                $keys = array_rand($statarray, 2);
                if ($statarray[$keys[0]] < 1) {
                    continue;
                }
                $statarray[$keys[0]]--;
                $statarray[$keys[1]]++;
            }
        }
        foreach ($statarray as $key => $val) {
            if ($val < 1) {
                $statarray[$key] = -2;
            }
        }
        foreach (['fight', 'trick', 'action', 'talk', 'perception', 'innerstrength'] as $stat) {
            $p[$stat] = array_shift($statarray);
        }

        return $p;
    }


    function getCharcterSheetAttachments() {
        $player = &$this->player;
        $attachments[0]['color'] = $player['colourhex'];
        $attachments[0]['fields'] = [
            ['title' => 'Fighting Skill (fight)',
                'value' => $player['fight'],
                'short' => true],
            ['title' => 'Trickery Skill (trick)',
                'value' => $player['trick'],
                'short' => true],
            ['title' => 'Action Skill (action)',
                'value' => $player['action'],
                'short' => true],
            ['title' => 'Talking Skill (talk)',
                'value' => $player['talk'],
                'short' => true],
            ['title' => 'Perception Skill (perception)',
                'value' => $player['perception'],
                'short' => true],
            ['title' => 'Inner Strength (is)',
                'value' => $player['innerstrength'],
                'short' => true],
        ];

        return $attachments;
    }


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('test',    '_cmd_test', ['ms']);
        $this->registerCommand('ng',      '_cmd_newgame', ['os','os','on', 'on', 'on', 'on', 'on', 'on']);
        $this->registerCommand('newgame', '_cmd_newgame', ['os','os','on', 'on', 'on', 'on', 'on', 'on']);
    }


    //// !help (send narnia help) OVERRIDE
    function _cmd_help($cmd) {
        $help = file_get_contents('resources/narnia_help.txt');
        // Replace "!" with whatever the trigger word is
        $help = str_replace("!", $_POST['trigger_word'], $help);
        sendqmsg($help);
    }


    //// !newgame (roll new character) OVERRIDE
    function _cmd_newgame($cmd) {
        $player = &$this->player;
        // Check stats
        $name = $cmd[1];
        $gender = $cmd[2];
        $stats = array_slice($cmd, 2);
        foreach ($stats as $key => $val) {
            if ($val < 1) {
                $stats[$key] = 0;
            }
        }
        $stattotal = array_sum($stats);
        $extratext = "";
        if ($stattotal < 1) {
            $stats = null;
            $extratext = "\nYou can customise the character by providing his stats in the order fight, trick, action, talk, perception and is. e.g. `!".$cmd[0]." 3 2 0 0 1 0`";
            $extratext .= "\n(You have 6 points to spend, stats with a value of 0 will be set to -2.)";
        } elseif ($stattotal != 6) {
            sendqmsg("*Stats should add to 6. $stattotal given.*", ':interrobang:');
            return;
        }
        if (!$name) {
            $extratext .= "\nYou can choose a name and gender e.g. `!".$cmd[0]." Bob male` or with stats e.g. `!".$cmd[0]." Jane female 2 1 1 1 1 0`";
        }
        $player = $this->rollNarniaCharacter($name, $gender, $stats);

        $icon = $player['emoji'];
        $attach = $this->getCharcterSheetAttachments();
        $attach[] = $this->getStuffAttachment();

        sendmsg("_*NEW CHARACTER!*_ ".$extratext.implode(' ', array_map("diceemoji", $player['creationdice']))."\n".$this->getCharacterString(), $attach, $icon);
    }


    //// !test <stat> <target> NARNIA VERSION
    function _cmd_test($cmd) {
        $player = &$this->player;
        // Apply temp bonuses, if any
        apply_temp_stats($player);

        $stat = strtolower($cmd[1]);
        if (in_array($stat, $this->getAllStatCommands())) {
            $stat = $this->getStatFromAlias($stat);
            $statname = $this->getStats()[$stat]['friendly'];
            $mod = $player[$stat];
        } else {
            sendqmsg("*Don't know how to test ".$stat."*", ':interrobang:');
            return;
        }

        // Roll dice
        $d1 = rand(1, 6);
        $d2 = rand(1, 6);
        $emojidice = diceemoji($d1).diceemoji($d2).'+'.$mod;
        $total = $d1+$d2+$mod;
        sendqmsg("_".$player['name']." tests $statname and got *$total*!_ ($emojidice)", ':game_die:');

        // Remove temp bonuses, if any and clear temp bonus array
        unapply_temp_stats($player);
    }


}
