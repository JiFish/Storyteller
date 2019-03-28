<?php

require_once 'ff_basic.php';

class book_rp extends book_ff_basic {
    public function getId() {
        return 'rp';
    }


    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollHumanCharacter($name, $gender, $emoji, $race, $adjective, $seed);
        // Starting Equipment
        $p['stuff'] = array('Laser Sword (+0)');
        $p['gold'] = 2000;
        return $p;
    }


    public function getStats() {
        $stats = parent::getStats();
        $stats['gold']['friendly'] = 'Credits';
        $stats['gold']['alias'][] = 'credits';
        return $stats;
    }


    protected function getCharcterSheetAttachments() {
        $attachments = parent::getCharcterSheetAttachments();
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[0]['fields'][4]['title'] = 'Credits';
        unset($attachments[0]['fields'][5]);
        return $attachments;
    }


}
