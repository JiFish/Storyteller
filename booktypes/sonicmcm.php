<?php

require_once('sonic.php');

class book_sonicmcm extends book_sonic {
    public function getId() {
        return 'sonicmcm';
    }

    public function getStats() {
        $stats = parent::getStats();
        $stats['egghits'] = [
            'friendly' => 'Egg-O-Matic Hits',
            'alias' => ['hits','egghits','eggomatichits'],
            'icons' => ':egg:',
        ];
        return $stats;
    }

    public function rollSonicCharacter($statarray = null) {
        $p = parent::rollSonicCharacter($statarray);
        $p['stuff'] = array('Red Trainers','Sega Game Gear','Botman Cartridge');
        return $p;
    }
}
