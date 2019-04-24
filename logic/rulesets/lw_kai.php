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
        return $p;
    }


}
