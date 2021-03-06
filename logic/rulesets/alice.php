<?php

require_once 'character.php';

class book_alice extends book_character {
    public function isDead() {
        return $this->player['endurance'] < 1;
    }


    protected function getHelpFileId() {
        return 'alice';
    }


    protected function getStats() {
        $stats = array(
            'agility' => [
                'friendly' => 'Agility',
                'alias' => ['agi'],
                'roll' => 6,
            ],
            'logic' => [
                'friendly' => 'Logic',
                'alias' => ['log'],
                'roll' => 6,
            ],
            'insanity' => [
                'friendly' => 'Insanity',
                'alias' => ['is'],
                'roll' => 0,
            ],
            'combat' => [
                'friendly' => 'Combat',
                'roll' => 6,
            ],
            'endurance' => [
                'friendly' => 'Endurance',
                'alias' => ['end'],
                'roll' => 20,
            ],
            'damage' => [
                'friendly' => 'Combat Damage',
                'alias' => ['dmg'],
                'roll' => 2,
            ],
            'shield' => [
                'friendly' => 'Shield',
                'roll' => false,
            ],
            'cc' => [
                'friendly' => 'Curiouser and Curiouser',
                'roll' => 3,
            ],
            'pen' => [
                'friendly' => 'The Pen is Mightier',
                'roll' => 3,
            ],
        );
        return $stats;
    }


    protected function getCharacterString() {
        return "Alice";
    }


    protected function getCharcterSheetAttachments() {
        $player = &$this->player;

        $attachments[0]['color'] = $player['colourhex'];
        $attachments[0]['fields'] = [
            ['title' => 'Agility',
                'value' => $player['agility'],
                'short' => true],
            ['title' => 'Logic',
                'value' => $player['logic'],
                'short' => true],
            ['title' => 'Combat',
                'value' => $player['combat']." (*Damage:* ".$player['damage'].")",
                'short' => true],
            ['title' => 'Insanity',
                'value' => $player['insanity'],
                'short' => true],
            ['title' => 'Endurance',
                'value' => $player['endurance'].'/'.$player['max']['endurance']." (*Shield:* ".($player['shield']?'on':'off').")",
                'short' => true],
            ['title' => 'Curiouser & Curiouser (cc): '.$player['cc'],
                'value' => '*The Pen is Mightier (pen): '.$player['pen'].'*',
                'short' => true],
        ];

        return $attachments;
    }


    protected function newCharacter() {
        return $this->rollAliceCharacter();
    }


    protected function rollAliceCharacter($statarray = null) {
        global $config;
        $p = array(
            'lastpage' => 1,
            'creationdice' => '',
            'stuff' => [],
            'name' => 'Alice',
            'adjective' => '',
            'gender' => 'Female',
            'race' => 'Human',
            'colourhex' => '#02a2dc',
            'emoji' => $config->root.'/images/custom_avatars/alice.png',
            'deck' => range(0, 51)
        );
        shuffle($p['deck']);
        // Roll/Set stats!
        roll_stats($p, $this->getStats());
        // Random stats array
        if ($statarray === null) {
            $statarray = array_pad([], 4, 0);
            for ($c = 0; $c < 10; $c++) {
                $statarray[rand(0, 3)]++;
            }
        }
        // Apply stat array
        $stats = ['agility', 'logic', 'combat', 'endurance'];
        foreach ($statarray as $k => $v) {
            $p[$stats[$k]] += $v;
        }
        $p['max']['endurance'] = $p['endurance'];
        return $p;
    }


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand(['newgame', 'ng'], '_cmd_newgame', ['on', 'on', 'on', 'on']);
        $this->registerCommand('test',            '_cmd_test',    ['s', 'onm', 'on', 'on']);
        $this->registerCommand(['draw', 'deal'],  '_cmd_deal',    ['on']);
        $this->registerCommand('peek',            '_cmd_peek');
        $this->registerCommand('shuffle',         '_cmd_shuffle');
        $this->registerCommand('fight',           '_cmd_fight',   ['(\sinit|\sinitiative)?',
                'oms', 'n', 'n',
                'omsg', 'on', 'on',
                'omsg', 'on', 'on',
                'omsg', 'on', 'on',
                'omsg', 'on', 'on',
                'omsg', 'on', 'on']);
    }


    //// !newgame (roll new character) OVERRIDE
    protected function _cmd_newgame($cmd) {
        $player = &$this->player;
        // Check stats
        $stats = array_slice($cmd, 1);
        $stattotal = array_sum($stats);
        $extratext = "";
        if ($stattotal > 0 && $stattotal != 10) {
            sendqmsg("*Stats should add to 10. $stattotal given.*", ':interrobang:');
            return;
        } elseif ($stattotal < 1) {
            $stats = null;
            $extratext = "\nYou can customise Alice by providing her stats in the order agility, logic, combat and endurance totalling 10 points. e.g. `!".$cmd[0]." 3 3 2 2`";
        }
        $player = $this->rollAliceCharacter($stats);

        $icon = $player['emoji'];
        $attach = $this->getCharcterSheetAttachments();
        $attach[] = $this->getStuffAttachment();

        sendmsg("_*NEW CHARACTER!*_ Alice. ".$extratext, $attach, $icon);
    }


    //// !test <stat> <target> ALICE VERSION
    protected function _cmd_test($cmd) {
        $player = &$this->player;

        $stat = $this->getStatFromAlias(strtolower($cmd[1]), $this->getStats());
        if (!in_array($stat, ['agility', 'logic', 'insanity', 'combat', 'endurance'])) {
            sendqmsg("*Don't know how to test ".$stat."*", ':interrobang:');
            return;
        }
        $bonus = $cmd[2]?(int)$cmd[2]:0;
        // Setup outcome pages to read if provided
        if ($cmd[3]) {
            $success_page = "page ".$cmd[3];
        }
        if ($cmd[4]) {
            $fail_page = "page ".$cmd[4];
        }

        // Describer
        switch ($stat) {
        case 'agility':
            $desc = ['agile', 'clumsy'];
            break;
        case 'logic':
            $desc = ['logical', 'illogical'];
            break;
        case 'insanity':
            $desc = ['sane', 'insane'];
            break;
        case 'combat':
            $desc = ['strong', 'weak'];
            break;
        case 'endurance':
            $desc = ['not hurt', 'hurt'];
            break;
        default:
        }

        // Roll dice
        if ($stat == 'endurance') {
            $d = $this->deck(2);
            $pass = $d['val'] <= $player[$stat]+$bonus;
        } elseif ($stat == 'insanity') {
            $d = $this->deck();
            $pass = $d['val'] >= $player[$stat]+$bonus;
        } else {
            $d = $this->deck();
            $pass = $d['val'] <= $player[$stat]+$bonus;
        }
        $pass = $d['autopass']?true:$pass;
        $emojidice = $d['emoji'];
        $vs = $d['val'].' vs '.$player[$stat].($bonus?sprintf("%+d", $bonus):'');

        // Check roll versus target number
        if ($pass) {
            $desc = $desc[0];
            if ($d['autopass']) {
                sendqmsg("_*Alice is $desc!*_\n($emojidice Ace-in-the-hole!)", ':smile:');
            } else {
                sendqmsg("_*Alice is $desc!*_\n($emojidice $vs)", ':smile:');
            }
            // Show follow up page
            if (isset($success_page)) {
                $this->addCommand($success_page);
            }
        }
        else {
            $desc = $desc[1];
            sendqmsg("_*Alice is $desc!*_\n($emojidice $vs)", ':frowning:');
            // Show follow up page
            if (isset($fail_page)) {
                $this->addCommand($fail_page);
            }
        }
    }


    private function deck($todraw = 1) {
        $suitnames = ['Spades', 'Hearts', 'Diamonds', 'Clubs'];
        $cardnames = ['Ace', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Jack', 'Queen', 'King'];
        $suitcodes = [9824, 9829, 9830, 9827];
        $deck = &$this->player['deck'];
        $out = [
            'val' => 0,
            'emoji' => "",
            'text' => "",
            'autopass' => false
        ];
        for ($c = 0; $c < $todraw; $c++) {
            if (count($deck) < 1) {
                $deck = range(0, 51);
                shuffle($deck);
                $out['emoji'] .= "The deck of fate has been shuffled... ";
            }
            $draw = array_pop($deck);
            $v = floor($draw/4);
            $s = $draw % 4;
            $out['emoji'] .= cardemoji($v+1, $s).' ';
            $out['text'] .= $cardnames[$v]." of ".$suitnames[$s].' ';
            $out['text'] .= mb_convert_encoding('&#'.$suitcodes[$s].';', 'UTF-8', 'HTML-ENTITIES').' ';
            if ($v == 0) {              // ACE
                $out['val'] += 12;
                $out['autopass'] = true;
            } elseif ($v < 10) {        // TWO - TEN
                $out['val'] += $v+1;
            } else {                    // FACE CARDS
                $out['val'] += 11;
            }
        }
        $out['emoji'] = trim($out['emoji']);
        $out['text'] = trim($out['text']);
        return $out;
    }


    protected function _cmd_fight($cmd) {
        $aliceinit = $cmd[1]?true:false;
        $opps = [];
        for ($c = 0; $c < 6; $c++) {
            $pos = 2+($c*3);
            if ($cmd[$pos+2]) {
                $opps[] = [
                    'name' => $cmd[$pos]?ucfirst($cmd[$pos]):"Opponent".($c?' '.($c+1):''),
                    'combat' => $cmd[$pos+1],
                    'endurance' => $cmd[$pos+2]
                ];
            }
        }
        $out = $this->runAliceFight($opps, $aliceinit);
        sendqmsg($out, ":crossed_swords:");
    }


    protected function runAliceFight($fighters, $aliceinit = true) {
        $p = &$this->player;
        $out = "";
        foreach ($fighters as $k => $v) {
            $fighters[$k]['init'] = !$aliceinit;
        }
        while (count($fighters) > 0) {
            // Step 1 (+ step 3)
            $d = $this->deck();
            $acr = $p['combat'] + $d['val'] + ($aliceinit?1:0);
            $alicedice = $d['emoji'];
            $alicenewinit = true;
            foreach ($fighters as $k => $opp) {
                // Step 2 (+ step 3)
                $d = $this->deck();
                $ocr = $opp['combat'] + $d['val'] + ($opp['init']?1:0);
                $dicestr = $alicedice." $acr".($aliceinit?' (i)':'')." vs ".$d['emoji']." $ocr".($opp['init']?' (i)':'');
                // Alice hits
                if ($acr > $ocr) {
                    $out .= "_Alice hits ".$opp['name']."._ $dicestr\n";
                    $fighters[$k]['init'] = false;
                    $fighters[$k]['endurance'] -= $p['damage'];
                }
                // Opp hits
                elseif ($acr < $ocr) {
                    $out .= "_".$opp['name']." hits alice._ $dicestr\n";
                    $fighters[$k]['init'] = true;
                    $alicenewinit = false;
                    $dmg = ($p['shield']?1:2);
                    $p['endurance'] -= $dmg;
                }
                // Same
                else {
                    $coin = rand(0, 1);
                    if ($coin) {
                        $out .= "_Alice and ".$opp['name']." deflect each other's attacks._ $dicestr\n";
                    } else {
                        $out .= "_Alice and ".$opp['name']." injure each other._ $dicestr\n";
                        $fighters[$k]['endurance'] -= 1;
                        $p['endurance'] -= 1;
                        $fighters[$k]['init'] = false;
                        $alicenewinit = false;
                    }
                }
                // ALICE dead
                if ($p['endurance'] < 1) {
                    $out .= "*Alice has been defeated!*\n";
                    return $out;
                }
                // OPP dead
                if ($fighters[$k]['endurance'] < 1) {
                    $out .= "*".$opp['name']." has been defeated!*\n";
                    unset($fighters[$k]);
                }
            }
            $aliceinit = $alicenewinit;
        }
        $out .= "*Alice is victorious!*\n";
        $out .= "_Remaining endurance: ".$p['endurance'].'/'.$p['max']['endurance']."_";
        return $out;
    }


    //// !deal [x] (deal x cards)
    protected function _cmd_deal($cmd) {
        $numcards = ($cmd[1]?$cmd[1]:1);
        $numcards = max(min($numcards, 52), 1);
        $out = "";
        $t = 0;
        for ($a = 0; $a < $numcards; $a++) {
            $card = $this->deck();
            $out .= $card['emoji'].": ".$card['text']."\n";
            $t += $card['val'];
        }
        if ($numcards > 1) {
            $out .= " *Total: $t*";
        }
        sendqmsg($out, ":flower_playing_cards:");
    }


    //// !peek (peek at draw pile and then shuffle it)
    protected function _cmd_peek($cmd) {
        $pile = $this->player['deck'];
        sort($pile);
        $out = "Cards left in draw pile...\n";
        foreach ($pile as $d) {
            $out .= ' '.cardemoji(floor($d/4)+1, $d%4);
        }
        sendqmsg($out, ":flower_playing_cards:");
    }


    //// !shuffle (Put discard pile back in deck and shuffle)
    protected function _cmd_shuffle($cmd) {
        $deck = &$this->player['deck'];
        $deck = range(0, 51);
        shuffle($deck);
        sendqmsg('The deck of fate has been shuffled...', ":flower_playing_cards:");
    }


}
