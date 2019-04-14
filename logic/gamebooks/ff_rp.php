<?php

require_once 'ff_basic.php';

class book_ff_rp extends book_ff_basic {
    public function getId() {
        return 'ff_rp';
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollHumanCharacter($name, $gender, $emoji, $race, $adjective);
        // Starting Equipment
        $p['stuff'] = array('Laser Sword (+0)');
        $p['gold'] = 2000;
        return $p;
    }


    protected function getStats() {
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
