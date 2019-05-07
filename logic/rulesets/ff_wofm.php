<?php

require_once 'ff_basic.php';

class book_ff_wofm extends book_ff_basic {
    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        // Starting Equipment
        $p['stuff'] = array('Sword (+0)', 'Leather Armor', 'Lantern');
        $p['prov'] = 3;
        // Random Potion
        // The book rules actually give you a choice, but this is a bit more fun
        $d = dice();
        $p['creationdice'] .= ' '.diceemoji($d);
        switch ($d) {
        case 1: case 2:
            $p['stuff'][] = 'Potion of Skill [skill full]';
            break;
        case 3: case 4:
            $p['stuff'][] = 'Potion of Strength [stam full]';
            break;
        case 5: case 6:
            $p['stuff'][] = 'Potion of Luck [luck full]';
            // If the potion of luck is chosen, the player get 1 bonus luck
            $p['luck']++;
            $p['max']['luck']++;
            break;
        }
        return $p;
    }


}
