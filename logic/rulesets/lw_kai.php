<?php

require_once 'lonewolf.php';

class book_lw_kai extends book_lonewolf {
    protected function getLoneWolfSkillsName() {
        return 'Kai Disciplines';
    }


    protected function getLoneWolfRankTitles() {
        return ['Novice', 'Intuite', 'Doan', 'Acolyte', 'Initiate',
            'Aspirant', 'Guardian', 'Warmarn', 'Savant', 'Master'];
    }


    protected function isHealer() {
        return preg_match('/healing/i', implode(" ", $this->player['skills']));
    }


    protected function getCharacterString() {
        $p = &$this->player;
        return "*".$p['name']."*, ".$p['adjective']." Wolf _(".$p['gender'].")_";
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        $p['weapons'][] = 'Axe';
        $p['specials'][] = 'Map of Sommerlund';
        // Prevent old emoji, as this contracts you being young in the story
        if (!$emoji || $emoji == '?') {
            $p['emoji'] = str_replace('older_man',  'boy',  $p['emoji']);
            $p['emoji'] = str_replace('older_woman', 'girl', $p['emoji']);
            $p['emoji'] = str_replace('older_adult', 'child', $p['emoji']);
        }
        // Starting bonus
        $d = rand(0, 9);
        $p['creationdice'] .= ' '.genericemoji($d);
        switch ($d) {
        case 1:
            $p['weapons'][] = 'Sword';
            break;
        case 2:
            $p['specials'][] = 'Helmet (giving +2 end)';
            $p['endurance'] += 2;
            $p['max']['endurance'] += 2;
            break;
        case 3:
            $p['stuff'][] = 'Meal';
            $p['stuff'][] = 'Meal';
            break;
        case 4:
            $p['specials'][] = 'Chainmail Waistcoat (giving +4 end)';
            $p['endurance'] += 4;
            $p['max']['endurance'] += 4;
            break;
        case 5:
            $p['weapons'][] = 'Mace';
            break;
        case 6:
            $p['stuff'][] = 'Healing Potion [end +4]';
            break;
        case 7:
            $p['weapons'][] = 'Quarterstaff';
            break;
        case 8:
            $p['weapons'][] = 'Spear';
            break;
        case 9:
            $p['gold'] += 12;
            break;
        case 0:
            $p['weapons'][] = 'Broadsword';
            break;
        }
        return $p;
    }


}
