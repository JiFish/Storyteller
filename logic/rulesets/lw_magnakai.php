<?php

require_once 'lw_kai.php';

class book_lw_magnakai extends book_lw_kai {
    protected function getLoneWolfSkillsName() {
        return 'Magnakai Disciplines';
    }


    protected function getLoneWolfRankTitles() {
        return ['Kai Master', 'Kai Senior', 'Kai Superior', 'Primate', 'Tutelary',
            'Principalin', 'Mentora', 'Scion-kai', 'Archmaster'];
    }


    protected function getLoneWolfWeaponSkillLevel() {
        return 3;
    }


    protected function isHealer() {
        return true;
    }


    protected function getLoneWolfWeaponTypes() {
        // This order is important, when looking for weapon matches check from 0 onwards.
        foreach (['Dagger', 'Mace', 'Warhammer', 'Axe', 'Quarterstaff', 'Spear',
                'Short Sword', 'Bow', 'Broadsword', 'Sword'] as $w) {
            $o[$w] = 0;
        }
        return $o;
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        $p['weaponmastery'] = $this->getLoneWolfWeaponTypes();

        return $p;
    }


    protected function getCharcterSheetAttachments() {
        $attachments = parent::getCharcterSheetAttachments();
        $wml = "";
        foreach ($this->player['weaponmastery'] as $weapon => $val) {
            if ($val) {
                $wml .= "$weapon: ".sprintf("%+d", $val)."\n";
            }
        }
        $attachments[1]['fields'][] = [
            'title' => 'Weapon Skills',
            'value' => $wml,
            'short' => true
        ];

        return $attachments;
    }


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('master', '_cmd_master', ['ms', 'on']);
    }


    //// !learn (learn skill)
    protected function _cmd_master($cmd) {
        $weapon = strtolower($cmd[1]);
        $level = ($cmd[2]===null?$this->getLoneWolfWeaponSkillLevel():$cmd[2]);
        $wm = &$this->player['weaponmastery'];
        $master = (strtolower($cmd[0])=='master');
        $key = -1;
        foreach ($wm as $w => $v) {
            if ($weapon == strtolower($w)) {
                $key = $w;
            }
        }

        // Not found
        if ($key == -1) {
            sendqmsg("*$weapon not a valid weapon type.* Choices are: ".implode(", ", array_keys($wm)).".", ':interrobang:');
            return;
        }

        $wm[$key] = $level;
        if ($cmd[2]===null) {
            sendqmsg("*You have mastered the $weapon.*", ':crossed_swords:');
        } else {
            sendqmsg("*$weapon skill set to $level.*", ':crossed_swords:');
        }
    }


    protected function runImportUpdate(&$p) {
        // Update weaponskill to new system
        if (!isset($p['weaponmastery'])) {
            $p['weaponmastery'] = $this->getLoneWolfWeaponTypes();
            // Attempt to id weapon skill
            $foundskill = false;
            foreach ($p['skills'] as $skill) {
                $skill = strtolower($skill);
                foreach ($p['weaponmastery'] as $weapon => $val) {
                    if (strpos($skill, strtolower($weapon)) !== false) {
                        $p['weaponmastery'][$weapon] = 2;
                        $foundskill = true;
                        break 2;
                    }
                }
            }
            // Found no weapon skill, give one at random
            if (!$foundskill) {
                $p['weaponmastery'][array_rand($p['weaponmastery'])] = 2;
            }
        }
        // Clear skills if moving between rulesets, it's assumed
        // you have obtained all the skills
        if ($this->player['ruleset'] != get_class($this)) {
            $p['skills'] = [];
        }

        parent::runImportUpdate($p);
    }


    // Allow imports from lw_kai
    protected function isImportValid(&$p) {
        if (isset($p['ruleset'])) {
            if ($p['ruleset'] == get_class($this) ||
                $p['ruleset'] == 'book_lw_kai') {
                return true;
            }
        }
        return false;
    }


}
