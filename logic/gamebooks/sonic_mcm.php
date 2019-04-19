<?php

require_once 'sonic.php';

class book_sonic_mcm extends book_sonic {
    protected function getStats() {
        $stats = parent::getStats();
        $stats['egghits'] = [
            'friendly' => 'Egg-O-Matic Hits',
            'alias' => ['hits', 'egghits', 'eggomatichits'],
            'icons' => ':egg:',
        ];
        return $stats;
    }


    protected function rollSonicCharacter($statarray = null) {
        $p = parent::rollSonicCharacter($statarray);
        $p['stuff'] = array('Red Trainers', 'Sega Game Gear', 'Botman Cartridge');
        return $p;
    }


}
