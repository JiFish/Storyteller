<?php

require_once 'ff_basic.php';

class book_dotd extends book_ff_basic {
    public function getId() {
        return 'dotd';
    }


    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollHumanCharacter($name, $gender, $emoji, $race, $adjective, $seed);
        // Starting Equipment
        $p['stuff'] = array('Sword (+0)', 'Leather Armor');
        $p['prov'] = 3;
        // Set race
        if (!$race || $race == '?') {
            $p['race'] = array('Sailor', 'Pirate', 'Seafarer', 'Mariner', 'Seaswab', 'Deck Hand', 'Navigator')[rand(0, 6)];
        }
        return $p;
    }


}
