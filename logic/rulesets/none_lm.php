<?php

require_once 'none.php';

class book_none_lm extends gamebook_none {
    // This is a much looser than the default
    protected function getPageMatchRegex() {
        return '/page ([0-9]+)/i';
    }


    // Look for 'The End'
    protected function getDeathMatchRegex() {
        return '/The End/i';
    }


}
