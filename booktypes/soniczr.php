<?php

require_once 'sonic.php';

class book_soniczr extends book_sonic {
    public function getId() {
        return 'soniczr';
    }


    public function rollSonicCharacter($statarray = null) {
        $p = parent::rollSonicCharacter($statarray);
        $p['stuff'] = array('Red Trainers', 'White Gloves');

        // Tails
        $tails['name'] = 'Tails';
        $tails['adjective'] = 'Fox';
        $tails['gender'] = 'Male';
        $tails['race'] = 'Anthropomorphic Fox';
        $tails['emoji'] = ':fox:';
        $tails['referrers'] = ['you' => 'Tails', 'youare' => 'Tails is', 'your' =>"Tail's"];
        // Roll/Set stats!
        roll_stats($tails, $this->getStats());
        $statarray = [5, 4, 3, 2, 2, 2];
        shuffle($statarray);
        $statarray[] = 0; $statarray[] = 3;
        foreach (['speed', 'str', 'agility', 'cool', 'wits', 'looks'] as $stat) {
            $tails[$stat] = array_shift($statarray);
        }
        $p['tails'] = $tails;

        return $p;
    }


    function getCharcterSheetAttachments() {
        $player = &$this->player;
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[1]['color'] = '#ff6600';
        $attachments[1]['fields'] = [
            ['title' => 'Tails Speed: '.$player['tails']['speed'],
                'value' => '*Tails Strength: '.$player['tails']['str'].'*',
                'short' => true],
            ['title' => 'Tails Agility: '.$player['tails']['agility'],
                'value' => '*Tails Cool: '.$player['tails']['cool'].'*',
                'short' => true],
            ['title' => 'Tails Wits: '.$player['tails']['wits'],
                'value' => '*Tails Looks: '.$player['tails']['looks'].'*',
                'short' => true],
            ['title' => 'Tails Lives: '.str_repeat(html_entity_decode('&#x1f98a;').' ', $player['tails']['stam']),
                'value' => '*Tails Rings: '.$player['tails']['rings'].'*',
                'short' => true],
        ];

        // Discord QOL
        if (DISCORD_MODE) {
            $attachments[1]['fields'][3]['value'] = null;
            $attachments[1]['fields'][] = [
                'title' => 'Tails Rings: '.$player['tails']['rings'],
                'value' => null,
                'short' => true];
        }

        return $attachments;
    }


    public function registerCommands() {
        parent::registerCommands();
        register_command('tails', '_cmd_tails', ['s', 'ol']);
    }


    //// !help (send sonic help) OVERRIDE
    public function _cmd_help($cmd) {
        $help = file_get_contents('resources/sonic_help.txt');
        $help .= "`!tails [command]` Ask tails to do something. e.g. `!tails test agility 4`\n";
        // Replace "!" with whatever the trigger word is
        $help = str_replace("!", $_POST['trigger_word'], $help);
        sendqmsg($help);
    }


    //// Special case, order various tails to do commands
    public function _cmd_tails($cmd) {
        global $commandslist, $commandsargs;
        $player = &$this->player;

        $order = strtolower($cmd[1]);
        if (array_key_exists($order, $commandsargs)) {
            $cmd = advanced_command_split(trim($order.' '.$cmd[2]), $commandsargs[$order]);
        } else {
            $cmd = false;
        }
        if (!$cmd) {
            sendqmsg("Sorry, I didn't understand that command!", ":interrobang:");
            return;
        }

        // Set the player to tails
        $mainplayer = &$this->player;
        $this->player = &$this->player['tails'];
        call_user_func_array([$this, $commandslist[$cmd[0]]], array($cmd));
        // Set the player back
        $this->player = &$mainplayer;

        if ($player['tails']['stam'] < 1) {
            $out = "*Tails is dead!* :skull:\n";
            sendqmsg($out, ':dead:');
        }
    }


}
