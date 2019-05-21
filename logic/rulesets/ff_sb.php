<?php

require_once 'ff_basic.php';

class book_ff_sb extends book_ff_basic {
    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        $p['stuff'] = array('Sword (+0)', 'Leather Armor', 'Lantern', 'Tinderbox');
        return $p;
    }


    protected function getStats() {
        $stats = parent::getStats();
        $stats['faith'] = [
            'friendly' => 'Faith',
            'icons' => '::place_of_worship:',
            'roll' => 1,
            'testdice' => '1d6+4',
            'testpass' => '{you} have faith',
            'testfail' => '{you} lack faith',
        ];
        return $stats;
    }


    protected function getCharcterSheetAttachments() {
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[0]['fields'][0]['value'] .= '  (Weapon: '.sprintf("%+d", $player['weapon']).')';
        $attachments[0]['fields'][3] = array (
            'title' => 'Faith',
            'value' => $this->player['faith'],
            'short' => true
        );
        return $attachments;
    }


}
