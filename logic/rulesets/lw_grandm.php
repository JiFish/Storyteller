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
        // I've gone with my own solution: 1d10 extra endurance, 1d10+5 extra skill
        if ($p['ruleset'] == 'book_lw_magnakai' ||
            $p['ruleset'] == 'book_lw_kai') {
            $d1 = dice(0, 9);
            $d2 = dice(0, 9);
            $p['endurance'] = $p['max']['endurance'] = $p['max']['endurance'] + $d1 + 1;
            $p['skill'] += $d2 + 6;
            sendqmsg('*'.$p['name'].' leveled up!* +'.($d1+1).' endurance, +'.($d2+6).' combat skill! '.genericemoji($d1).' '.genericemoji($d2), $p['emoji']);
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
