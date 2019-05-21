<?php

require_once 'ff_basic.php';

class book_ff_stst extends book_ff_basic {
    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollHumanCharacter($name, $gender, $emoji, $race, $adjective);
        return $p;
    }


    protected function getStuffAttachment() {
        $attachments = parent::getStuffAttachment();
        $attachments['fields'][0]['title'] = 'Clues / Inventory';
        return $attachments;
    }


    protected function getStats() {
        $stats = parent::getStats();
        $stats['time'] = [
            'friendly' => 'Time',
            'icons' => ':clock:',
            'roll' => 48
        ];
        $stats['fear'] = [
            'friendly' => 'Fear',
            'icons' => ':scream:',
            'roll' => '1d6+6',
            'max' => 'roll',
            'testdice' => 2,
            'testpass' => '{youare} scared!',
            'testfail' => '{youare} not afraid',
        ];
        return $stats;
    }


    protected function getCharcterSheetAttachments() {
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[0]['fields'][4] = array (
            'title' => 'Fear',
            'value' => $this->player['fear'],
            'short' => true
        );
        $attachments[0]['fields'][5] = array (
            'title' => 'Time',
            'value' => $this->player['time'],
            'short' => true
        );
        return $attachments;
    }


}
