<?php

require_once 'lw_grandm.php';

class book_lw_neworder extends book_lw_grandm {
    protected function getLoneWolfSkillsName() {
        return "New Order Grand Kai\nMaster Disciplines";
    }


    protected function getLoneWolfRankTitles() {
        $t = parent::getLoneWolfRankTitles();
        $t[] = 'Kai Supreme Master';
        return $t;
    }


    protected function getCharacterString() {
        $p = &$this->player;
        return "*".$p['name']."*, ".$p['adjective']." _(".$p['gender'].")_";
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        if (!$adjective || $adjective == '?') {
            $a = ['Swift', 'Sun', 'True', 'Bold', 'Moon', 'Sword', 'Wise', 'Storm', 'Rune', 'Brave'];
            $b = ['Blade', 'Fire', 'Hawk', 'Heart', 'Friend', 'Star', 'Dancer', 'Helm', 'Strider', 'Shield'];
            $p['adjective'] = $a[rand(0,9)].' '.$b[rand(0,9)];
        }
        return $p;
    }


    // DON'T Allow imports from other rulesets
    protected function isImportValid(&$p) {
        if (isset($p['ruleset']) && $p['ruleset'] == get_class($this)) {
            return true;
        }
        return false;
    }


}
