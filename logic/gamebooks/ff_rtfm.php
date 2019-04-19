<?php

require_once 'ff_basic.php';

class book_ff_rtfm extends book_ff_basic {
    protected function getStats() {
        $stats = parent::getStats();
        $stats['goldzagors'] = [
            'friendly' => 'Gold Zagors',
            'alias' => ['zagors','gz'],
            'icons' => ':moneybag:',
        ];
        return $stats;
    }


    protected function getCharcterSheetAttachments() {
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[0]['fields'][4] = array (
            'title' => 'Gold: '.$this->player['gold'],
            'value' => '*Gold Zagors (gz): '.$this->player['goldzagors'].'*',
            'short' => true
        );
        return $attachments;
    }


}
