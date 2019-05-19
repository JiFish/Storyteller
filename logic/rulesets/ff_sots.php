<?php

require_once 'ff_basic.php';

class book_ff_sots extends book_ff_basic {
    protected function getStats() {
        $stats = parent::getStats();
        $stats['honor'] = [
            'friendly' => 'Honor',
            'alias' => ['honour'],
            'roll' => 3,
        ];
        $stats['prov']['roll'] = 10;
        return $stats;
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollHumanCharacter($name, $gender, $emoji, $race, $adjective);
        // Random Skill
        switch (rand(0, 3)) {
        case 0:
            $p['sskill'] = 'Kyujutsu (Archery)';
            $p['stuff'] = ['Willow-leaf Arrow (2 dmg)', 'Willow-leaf Arrow (2 dmg)', 'Willow-leaf Arrow (2 dmg)',
                'Bowel-Raker Arrow (3 dmg)', 'Bowel-Raker Arrow (3 dmg)', 'Bowel-Raker Arrow (3 dmg)',
                'Armour-piercing Arrow (2 dmg)', 'Armour-piercing Arrow (2 dmg)', 'Armour-piercing Arrow (2 dmg)',
                'Humming-bulb Arrow (1 dmg)', 'Humming-bulb Arrow (1 dmg)', 'Humming-bulb Arrow (1 dmg)'];
            break;
        case 1:
            $p['sskill'] = 'Iaijutsu (Fast draw)';
            break;
        case 2:
            $p['sskill'] = 'Karumijutsu (Heroic leaping)';
            break;
        case 3:
            $p['sskill'] = 'Ni-to-Kenjutsu (Dual-wielding)';
            break;
        }
        return $p;
    }


    protected function getCharcterSheetAttachments() {
        $player = &$this->player;
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[0]['fields'][] = [
            'title' => 'Honor',
            'value' => $player['honor'],
            'short' => true
        ];
        $attachments[0]['fields'][] = [
            'title' => 'Special Skill',
            'value' => $player['sskill'],
            'short' => true
        ];
        return $attachments;
    }


    protected function _cmd_stat_adjust($cmd) {
        parent::_cmd_stat_adjust($cmd);
        $stat = strtolower($cmd[0]);
        if ($stat == 'honor' || $stat == 'honour') {
            if ($this->player['honor'] < 1) {
                $this->addCommand('dead');
                $this->addCommand('page 99');
            }
        }
    }


}
