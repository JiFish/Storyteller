<?php

require_once 'ff_basic.php';

class book_ff_poe extends book_ff_basic {
    public function getId() {
        return 'ff_poe';
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        // Starting Equipment
        $p['stuff'] = array('Sword (+0)', 'Leather Armor', 'Lantern');
        $p['prov'] = 2;
        return $p;
    }


}
