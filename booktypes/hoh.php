<?php

require_once('ff_basic.php');

class book_hoh extends book_ff_basic {
    public function getId() {
        return 'hoh';
    }

    public function isDead(&$player) {
        return (($player['stam'] < 1) || ($player['fear'] >= $player['max']['fear']));
    }

    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollHumanCharacter($name,$gender,$emoji,$race,$adjective,$seed);
        $p['weapon'] = -3;
        // Set race
        if (!$race || $race == '?') {
            $p['race'] = array('Cowardly','Ordinary','Sceptical','Open-Minded','Believer','Enlightened')[$p['creationdice'][4]-1];
        }
        return $p;
    }

    public function getStats() {
        $stats = parent::getStats();
        $stats['fear'] = [
            'friendly' => 'Fear',
            'icons' => ':scream:',
            'roll' => 'fffeardie',
            'display' => 'current_and_max',
        ];
        return $stats;
    }

    protected function getCharcterSheetAttachments(&$player) {
        $attachments = parent::getCharcterSheetAttachments($player);
        $attachments[0]['fields'][5] = array (
            'title' => 'Fear',
            'value' => $player['fear']." / ".$player['max']['fear'],
            'short' => true
        );
        return $attachments;
    }
}
