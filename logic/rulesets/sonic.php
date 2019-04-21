<?php

require_once 'character.php';

class book_sonic extends book_character {
    public function isDead() {
        return $this->player['lives'] < 1;
    }


    protected function getStats() {
        $stats = array(
            'lives' => [
                'friendly' => 'Lives',
                'alias' => ['life', 'stam'],
                'icons' => ':hedgehog:',
                'roll' => 3,
            ],
            'str' => [
                'friendly' => 'Strength',
                'alias' => ['strength'],
            ],
            'speed' => [
                'friendly' => 'Speed',
            ],
            'agility' => [
                'friendly' => 'Agility',
            ],
            'cool' => [
                'friendly' => 'Cool',
            ],
            'wits' => [
                'friendly' => 'Wits',
            ],
            'looks' => [
                'friendly' => 'Good Looks',
                'alias' => ['goodlooks'],
            ],
            'rings' => [
                'friendly' => 'Rings',
                'icons' => ':ring:',
            ],
        );
        return $stats;
    }


    protected function rollSonicCharacter($statarray = null) {
        $p['creationdice'] = '';
        $p['name'] = 'Sonic';
        $p['adjective'] = 'Hedgehog';
        $p['gender'] = 'Male';
        $p['race'] = 'Anthropomorphic Hedgehog';
        $p['emoji'] = ':hedgehog:';
        $p['referrers'] = ['you' => 'Sonic', 'youare' => 'Sonic is', 'your' =>"Sonic's"];
        $p['colourhex'] = '#0066ff';
        // Roll/Set stats!
        roll_stats($p, $this->getStats());
        // Deal with setting inital stats
        if (!$statarray) {
            $statarray = [5, 4, 3, 2, 2, 2];
            shuffle($statarray);
        }
        foreach (['speed', 'str', 'agility', 'cool', 'wits', 'looks'] as $stat) {
            $p[$stat] = array_shift($statarray);
        }
        $p['max']['lives'] = 20;
        $p['stuff'] = array();

        return $p;
    }


    protected function getCharcterSheetAttachments() {
        global $config;

        $player = &$this->player;
        $attachments[0]['color'] = $player['colourhex'];
        $attachments[0]['fields'] = [
            ['title' => 'Speed: '.$player['speed'],
                'value' => '*Strength: '.$player['str'].'*',
                'short' => true],
            ['title' => 'Agility: '.$player['agility'],
                'value' => '*Cool: '.$player['cool'].'*',
                'short' => true],
            ['title' => 'Wits: '.$player['wits'],
                'value' => '*Looks: '.$player['looks'].'*',
                'short' => true],
            ['title' => 'Lives: '.str_repeat(html_entity_decode('&#x1f994;').' ', $player['lives']),
                'value' => '*Rings: '.$player['rings'].'*',
                'short' => true],
        ];

        // Discord QOL
        if ($config->discord_mode) {
            $attachments[0]['fields'][3]['value'] = null;
            $attachments[0]['fields'][] = [
                'title' => 'Rings: '.$player['rings'],
                'value' => null,
                'short' => true];
        }

        return $attachments;
    }


    //// Gain a life after collecting 100 rings
    protected function _cmd_stat_adjust($cmd) {
        $player = &$this->player;
        // Get the current value
        $thisstat = $this->getStatFromAlias(strtolower($cmd[0]), $this->getStats());
        $before = $player[$thisstat];

        parent::_cmd_stat_adjust($cmd, $player);

        if ($thisstat == 'rings') {
            if (floor($player[$thisstat]/100) > floor($before/100)) {
                sendqmsg("*".$player['name']." got an extra life!*");
                $player['lives'] = min($player['max']['lives'], $player['lives']+1);
            }
        }
    }


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('test',    '_cmd_test', ['s', 'n', 'on', 'on']);
        $this->registerCommand('ng',      '_cmd_newgame', ['on', 'on', 'on', 'on', 'on', 'on']);
        $this->registerCommand('newgame', '_cmd_newgame', ['on', 'on', 'on', 'on', 'on', 'on']);
        $this->registerCommand('fight',   '_cmd_fight', ['s', 'onm', 'oms', 'n']);
        $this->registerCommand('hit',     '_cmd_hit');
    }


    //// !help (send sonic help) OVERRIDE
    protected function _cmd_help($cmd) {
        $help = file_get_contents('resources/sonic_help.txt');
        // Replace "!" with whatever the trigger word is
        $help = str_replace("!", $_POST['trigger_word'], $help);
        sendqmsg($help);
    }


    //// !newgame (roll new character) OVERRIDE
    protected function _cmd_newgame($cmd) {
        $player = &$this->player;
        // Check stats
        $stats = array_slice($cmd, 1);
        $stattotal = array_sum($stats);
        $extratext = "";
        if ($stattotal > 0 && $stattotal != 18) {
            sendqmsg("*Stats should add to 18. $stattotal given. Use any combination of 5, 4, 3, 2, 2, 2.*", ':interrobang:');
            return;
        } elseif ($stattotal < 1) {
            $stats = null;
            $extratext = "\nYou can customise sonic by providing his stats in the order speed, strength, agility, cool, wits and looks. e.g. `!".$cmd[0]." 5 4 3 2 2 2`";
        }
        $player = $this->rollSonicCharacter($stats);

        $icon = $player['emoji'];
        $attach = $this->getCharcterSheetAttachments();
        $attach[] = $this->getStuffAttachment();

        sendmsg("_*NEW CHARACTER!*_ ".$player['name']."* the ".$player['adjective']." _(".$player['gender']." ".$player['race'].")_".$extratext, $attach, $icon);
    }


    //// !test <stat> <target> SONIC VERSION
    protected function _cmd_test($cmd) {
        $player = &$this->player;
        // Apply temp bonuses, if any
        apply_temp_stats($player);

        $stat = strtolower($cmd[1]);
        if (in_array($stat, ['speed', 'str', 'strength', 'agility', 'cool', 'wits', 'looks'])) {
            $mod = $player[$stat];
        } elseif (!is_numeric($stat)) {
            sendqmsg("*Don't know how to test ".$stat."*", ':interrobang:');
            return;
        } else {
            $mod = (int)$stat;
        }
        $target = $cmd[2];
        // Setup outcome pages to read if provided
        if ($cmd[3]) {
            $success_page = "page ".$cmd[3]." nobackup";
        }
        if ($cmd[4]) {
            $fail_page = "page ".$cmd[4]." nobackup";
        }

        // Aliases
        if ($stat == "strength") $stat = "str";

        // Describer
        switch ($stat) {
        case 'speed':
            $desc = 'fast';
            break;
        case 'agility':
            $desc = 'agile';
            break;
        case 'wits':
            $desc = 'quick witted';
            break;
        case 'str':
            $desc = 'strong';
            break;
        case 'cool':
            $desc = 'cool';
            break;
        case 'looks':
            $desc = 'looking good';
            break;
        default:
            $desc = $stat;
        }

        // Roll dice
        $d1 = rand(1, 6);
        $emojidice = diceemoji($d1).'+'.$mod;

        // Check roll versus target number
        if ($d1+$mod >= $target) {
            if (!is_numeric($stat)) {
                sendqmsg("_*".$player['name']." is $desc!*_\n_(_ $emojidice _ vs $target)_", ':smile:');
            } else {
                sendqmsg("_*Test passed!*_\n_(_ $emojidice _ vs $target)_", ':smile:');
            }
            // Show follow up page
            if (isset($success_page)) {
                $this->addCommand($success_page);
            }
        }
        else {
            if (!is_numeric($stat)) {
                sendqmsg("_*".$player['name']." is not $desc!*_\n_(_ $emojidice _ vs $target)_", ':frowning:');
            } else {
                sendqmsg("_*Test failed!*_\n_(_ $emojidice _ vs $target)_", ':frowning:');
            }
            // Show follow up page
            if (isset($fail_page)) {
                $this->addCommand($fail_page);
            }
        }

        // Remove temp bonuses, if any and clear temp bonus array
        unapply_temp_stats($player);
    }


    //// !hit - took damage
    protected function _cmd_hit($cmd) {
        $player = &$this->player;
        if ($player['rings'] > 0) {
            $player['rings'] = 0;
            sendqmsg("_*".$player['name']." lost all ".($player['gender']=='Male'?'his':'her')." rings!*_", ':ring:');
            return;
        }
        $player['lives']--;
        sendqmsg("_*".$player['name']." lost a life! ".$player['lives']." lives left!*_", ':frowning:');
    }


    //// !fight [stat] <+/-mod> <name> [skill] (run fight logic)
    protected function _cmd_fight($cmd) {
        $player = &$this->player;
        $stat = $cmd[1];
        if ($stat == 'strength') $stat = 'str';

        $validstats = ['speed', 'agility', 'cool', 'wits', 'looks', 'str'];
        if (!in_array($stat, $validstats)) {
            sendqmsg("*$stat is not a valid stat.*", ':interrobang:');
            return;
        }

        $out = $this->runSonicFight($player, $player[$stat], $cmd[2], $cmd[3], $cmd[4]);
        sendqmsg($out, ":crossed_swords:");
    }


    protected function runSonicFight(&$player, $skill, $mod, $monster, $mskill) {
        $mod = ($mod?(int)$mod:0);
        $monster = ($monster?$monster:'Badnik');
        $out = '';

        while (1) {
            // Sonic hit
            $roll = rand(1, 6);
            if ($mod == 0) {
                $teststr = diceemoji($roll)."+$skill vs $mskill";
            } else {
                $teststr = diceemoji($roll)."+$skill ".sprintf("%+d", $mod)." vs $mskill";
            }
            if ($roll+$skill+$mod >= $mskill) {
                $out .= "_*".$player['name']." has defeated the $monster!*_ ($teststr)\n";
                break;
            } else {
                $out .= "_".$player['name']." missed the $monster._ ($teststr)\n";
            }

            // Monster hit
            $roll = rand(1, 6);
            $teststr = diceemoji($roll)."+$mskill vs 10";
            if ($roll+$mskill >= 10) {
                $out .= "_$monster has hit ".$player['name']."!_ ($teststr)\n";
                if ($player['rings'] > 0) {
                    $out .= "_*".$player['name']." lost all ".($player['gender']=='Male'?'his':'her')." rings!*_\n";
                    $player['rings'] = 0;
                } else {
                    $out .= "_*".$player['name']." lost a life!*_\n";
                    $player['lives']--;
                    if ($player['lives'] > 0) {
                        $out .= "Sonic Lives: ".str_repeat(html_entity_decode('&#x1f994;').' ', $player['lives']);
                    } else {
                        $out .= "*GAME OVER*";
                    }
                    break;
                }
            } else {
                $out .= "_$monster missed ".$player['name']."._ ($teststr)\n";
            }
        }

        return $out;
    }


}
