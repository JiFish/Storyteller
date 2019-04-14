<?php

require_once 'ff_basic.php';

class book_ff_tot extends book_ff_basic {
    public function getId() {
        return 'ff_tot';
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        // Starting Equipment
        $p['stuff'] = array('Sword (+0)', 'Leather Armor', 'Lantern');
        $p['prov'] = 3;
        return $p;
    }


}
