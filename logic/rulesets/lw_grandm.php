<?php

require_once 'lw_magnakai.php';

class book_lw_grandm extends book_lw_magnakai {
    protected function getLoneWolfSkillsName() {
        return 'Grand Master Disciplines';
    }


    protected function getLoneWolfRankTitles() {
        return ['Kai Grand Master Senior', 'Kai Grand Master Superior', 'Kai Grand Sentinel', 'Kai Grand Defender', 'Kai Grand Guardian',
            'Sun Knight', 'Sun Lord', 'Sun Thane', 'Grand Thane', 'Grand Crown', 'Sun Prince'];
    }


    protected function getLoneWolfWeaponSkillLevel() {
        return 5;
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        foreach (array_rand($p['weaponmastery'], 3) as $k) {
            $p['weaponmastery'][$k] = 3;
        }
        // Bonus for new grandmaster characters
        $p['endurance'] = $p['max']['endurance'] = $p['endurance'] + 10;
        $p['skill'] = $p['max']['skill'] = $p['skill'] + 15;
        return $p;
    }


    protected function runImportUpdate(&$p) {
        parent::runImportUpdate($p);
        // There is some debate on how to deal with stats from an existing character
        // see https://www.projectaon.org/en/ReadersHandbook/GrandMaster
        // I've gone with my own solution: 1d%+1 extra endurance, 1d%+6 extra skill
        if ($p['ruleset'] == 'book_lw_magnakai' ||
            $p['ruleset'] == 'book_lw_kai') {
            list($d1, $emoji1) = roll_dice_string("1d%+1", true);
            list($d2, $emoji2) = roll_dice_string("1d%+6", true);
            $p['endurance'] = $p['max']['endurance'] = $p['max']['endurance'] + $d1;
            $p['skill'] += $d2;
            sendqmsg('*'.$p['name']." leveled up!* +$d1 endurance, +$d2 combat skill! $emoji1 $emoji2", $p['emoji']);
        }
    }


    // Allow imports from lw_magnakai (or lw_kai, I won't judge)
    protected function isImportValid(&$p) {
        if (isset($p['ruleset'])) {
            if ($p['ruleset'] == get_class($this) ||
                $p['ruleset'] == 'book_lw_magnakai' ||
                $p['ruleset'] == 'book_lw_kai') {
                return true;
            }
        }
        return false;
    }


}
