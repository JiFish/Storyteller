<?php

require_once('ff_basic.php');

class book_poe extends book_ff_basic {
    public function getId() {
        return 'poe';
    }

    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollCharacter($name,$gender,$emoji,$race,$adjective,$seed);
        // Starting Equipment
        $p['stuff'] = array('Sword (+0)','Leather Armor','Lantern');
        $p['prov'] = 2;
        return $p;
    }
}
