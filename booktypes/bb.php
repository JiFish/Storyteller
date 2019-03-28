<?php

require_once 'ff_basic.php';

class book_bb extends book_ff_basic {
    public function getId() {
        return 'bb';
    }


    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective, $seed);
        $p['stuff'] = array('Sword (+0)', 'Leather Armor', 'Lantern', 'Tinderbox');
        return $p;
    }


    public function getStats() {
        $stats = parent::getStats();
        $stats['gold']['roll'] = 'twodieplus12';
        $stats['time'] = [
            'friendly' => 'Time',
            'icons' => ':clock:',
        ];
        return $stats;
    }


    protected function getCharcterSheetAttachments() {
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[0]['fields'][5] = array (
            'title' => 'Time',
            'value' => $this->player['time'],
            'short' => true
        );
        return $attachments;
    }


}
