<?php

require_once 'ff_basic.php';

class book_ff_bnc extends book_ff_basic {
    protected function getStats() {
        $stats = parent::getStats();
        $stats['will'] = [
            'friendly' => 'Willpower',
            'alias' => ['willpower'],
            'roll' => 'ff1die',
            'testdice' => 2,
            'testpass' => '{youare} strong willed',
            'testfail' => '{youare} weak willed',
        ];
        return $stats;
    }


    // In Slack format
    protected function getCharcterSheetAttachments() {
        $player = &$this->player;
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[0]['fields'][] = [
            'title' => 'Willpower',
            'value' => $player['will']." / ".$player['max']['will'],
            'short' => true
        ];
        return $attachments;
    }


    //// !test <luck/skill/stam> (run a skill test)
    protected function _cmd_test($cmd) {
        // Extra processing for willpower check
        $result = parent::_cmd_test($cmd);
        $stat = strtolower($cmd[1]);
        if ($stat == 'will' || $stat == 'willpower') {
            $p = &$this->player;
            if ($result == false && $p['will'] < 6) {
                sendqmsg("*You willpower has failed you...*");
                $p['stam'] = 0;
            }
            $p['will'] = max(0, $p['will']-1);
        }
    }


}
