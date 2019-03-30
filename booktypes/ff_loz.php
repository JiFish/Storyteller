<?php

require_once 'ff_magic.php';

class book_ff_loz extends book_ff_magic {
    public function getId() {
        return 'ff_loz';
    }


    public function getStats() {
        $stats = parent::getStats();
        $stats['talismans'] = [
            'friendly' => 'Talismans',
            'alias' => ['talisman'],
            'icons' => ':moneybag:',
        ];
        $stats['daggers'] = [
            'friendly' => 'Daggers',
            'alias' => ['dagger'],
            'icons' => ':dagger_knife:',
        ];
        // This is a dummy stat, not displayed used for !test only
        $stats['spot'] = [
            'friendly' => 'Spot',
            'icons' => ':eyes:',
            'testdice' => 2,
            'testpass' => '{you} spotted something',
            'testfail' => '{you} didn\'t spot anything}',
        ];
        $stats['magic']['roll'] = 0;
        $stats['gold']['roll'] = 'loz3die';
        return $stats;
    }


    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective, $seed);
        // Starting Equipment
        $p['stuff'] = array(
            'Knife (+0, -1 dmg)',
            'Leather Armor',
            'Small Shield',
        );

        // Reuse adjective as player class
        // Miner is the old Dwarf class (to separate from races above)
        $classes = ['Barbarian', 'Warrior', 'Miner', 'Wizard'];
        if (in_array(ucfirst(strtolower($adjective)), $classes)) {
            $p['adjective'] = ucfirst(strtolower($adjective));
        } else {
            $p['adjective'] = $classes[rand(0, 3)];
        }
        switch ($p['adjective']) {
        case 'Barbarian':
            $p['magic'] = 1;
            $p['advantages'] = "Can't be surprised.";
            $p['disadvantages'] = "Can't wear plate mail. No bonus to attack strength with chain mail. Subtract 2 from attack strength with crossbow.";
            $p['stuff'][] = "Axe (+0)";
            break;
        case 'Warrior':
            $p['magic'] = 3;
            $p['advantages'] = "Can use any weapons.";
            $p['disadvantages'] = "None.";
            $p['stuff'][] = "Sword (+0)";
            break;
        case 'Miner':
            $p['magic'] = 2;
            $p['advantages'] = "Add 2 to attack strength vs. stone monsters.";
            $p['disadvantages'] = "Can't use longbow or two-handed weapons.";
            $p['stuff'][] = "Axe (+0)";
            $p['gold'] += 5;
            break;
        case 'Wizard':
            $p['magic'] = 7;
            $p['advantages'] = "Add 2 to skill when testing spot skill.";
            $p['disadvantages'] = "Can't use metal armour, bow or two-handed weapons.";
            $p['stuff'][] = "Wooden Staff (+0)";
            break;
        }
        $p['max']['magic'] = $p['magic'];
        // Special emoji for human wizards
        if ((!$emoji || $emoji == '?') && $p['adjective'] == 'Wizard' && $p['race'] == 'Human') {
            if ($p['gender'] == 'Male') {
                $p['emoji'] = ':male_mage:';
            } elseif ($p['gender'] == 'Female') {
                $p['emoji'] = ':female_mage:';
            } else {
                $p['emoji'] = ':mage:';
            }
            $skintone = array(':skin-tone-2:', ':skin-tone-3:', ':skin-tone-4:', ':skin-tone-5:', ':skin-tone-2:');
            $p['emoji'] .= $skintone[array_rand($skintone)];
        }
        return $p;
    }


    protected function getCharcterSheetAttachments() {
        $player = &$this->player;
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[0]['fields'] = array_merge($attachments[0]['fields'],
            array([
                    'title' => 'Talismans: '.$player['talismans'],
                    'value' => '*Daggers: '.$player['daggers'].'*',
                    'short' => true
                ], [
                    'title' => 'Advantages',
                    'value' => $player['advantages'],
                    'short' => false
                ], [
                    'title' => 'Disadvantages',
                    'value' => $player['disadvantages'],
                    'short' => false
                ])
        );
        return $attachments;
    }


    protected function getSpells() {
        $spells[] = array(
            'name' => 'Create Food',
            'cost' => 1,
            'type' => 'object',
            'target' => false,
            'desc' => 'This spell creates an extra provision. Add 1 to your provisions. This spell cannot be cast during combat.',
            'reply' => 'A '.['sandwich', 'hunk of cheese', 'meat shank', 'baked potato'][mt_rand(0, 3)].' appears from nowhere.',
            'addcmd' => 'prov +1'
        );

        $spells[] = array(
            'name' => 'Create Meal',
            'cost' => 2,
            'type' => 'object',
            'target' => false,
            'desc' => 'This spell creates extra provisions. Add 2 to your provisions. This spell cannot be cast during combat.',
            'reply' => 'A '.['sandwich', 'hunk of cheese', 'meat shank', 'baked potato'][mt_rand(0, 3)].' and a '.['sandwich', 'hunk of cheese', 'meat shank', 'baked potato'][mt_rand(0, 3)].' appear from nowhere.',
            'addcmd' => 'prov +2'
        );

        $spells[] = array(
            'name' => 'Create Feast',
            'cost' => 3,
            'type' => 'object',
            'target' => false,
            'desc' => 'This spell creates extra provisions. Add 3 to your provisions. This spell cannot be cast during combat.',
            'reply' => 'A '.['sandwich', 'hunk of cheese', 'meat shank', 'baked potato'][mt_rand(0, 3)].', a '.['sandwich', 'hunk of cheese', 'meat shank', 'baked potato'][mt_rand(0, 3)].' and a '.['sandwich', 'hunk of cheese', 'meat shank', 'baked potato'][mt_rand(0, 3)].' appear from nowhere.',
            'addcmd' => 'prov +3'
        );

        $spells[] = array(
            'name' => 'Jump',
            'cost' => 1,
            'type' => 'self',
            'target' => false,
            'desc' => 'This spell enables you to leap safely a distance of up to six meters. If you are faced with an obstacle (a chasm, a trapped corridor, etc.) which is not stated to be longer than six meters, you may use this spell to traverse the obstacle safely. This spell cannot be cast during combat.',
            'reply' => "_Boing!_"
        );

        $spells[] = array(
            'name' => 'Light',
            'cost' => 1,
            'type' => 'utility',
            'desc' => 'If you lose your lantern, or it no longer works, this spell creates a long-lasting small sphere of light which can be stored and used as a lantern whenever the need arises. Each use of the spell illuminates only one section of pathway, however. This spell cannot be cast during combat.',
            'reply' => "_Let there be light!_"
        );

        $spells[] = array(
            'name' => 'Luck',
            'cost' => 1,
            'type' => 'self',
            'target' => false,
            'desc' => 'This spell increases your current luck score by 1 point. You current luck score cannot exceed it\'s Initial value. This spell cannot be cast during combat.',
            'reply' => '_Felicitus Populi!_',
            'addcmd' => 'luck +1'
        );

        $spells[] = array(
            'name' => 'Open',
            'cost' => 1,
            'type' => 'utility',
            'target' => false,
            'desc' => 'This simple spell opens one trapped treasure chest or locked door safely. If the chest or door has a trap, it is rendered harmless by the spell (even if it is a Magic Trap). This spell cannot be cast during combat.',
            'reply' => "_Open Sesame!_"
        );

        $spells[] = array(
            'name' => 'Skill',
            'cost' => 1,
            'type' => 'self',
            'target' => false,
            'desc' => 'This spell enables you to react and move faster, adding 1 to your current skill for a short time. It can be cast just before combat begins (not during one!) to aid your fighting, or just just before you have to Test your Skill for some reason. The spell expires as soon as one combat has finished, or after you have Tested your Skill.',
            'reply' => '_You feel quicker._',
            'addcmd' => 'skill temp +1'
        );

        $spells[] = array(
            'name' => 'Fast Hands',
            'cost' => 2,
            'type' => 'combat',
            'target' => true,
            'desc' => 'This spell can be cast immediately before any combat begins but not during one. For the first three Attack Rounds of the combat, you may roll dice twice when working out your Attack Strength and take the higher total rolled.',
            'func' => '_spell_fast_hands'
        );

        $spells[] = array(
            'name' => 'Fireball',
            'cost' => 2,
            'type' => 'combat',
            'target' => true,
            'desc' => 'This spell can be used in any attack round instead of a weapon attack. If you have the higher Attack Strength, your adversary\'s body is shrouded in flames and he loses 5 Stamina points. If you have the lower attack strength, your spell is ruined and the creature hits you.',
            'func' => '_spell_fireball'
        );


        $spells[] = array(
            'name' => 'Magic Screen',
            'cost' => 2,
            'type' => 'self',
            'target' => false,
            'desc' => 'Once this spell is cast, the next spell which is cast at you by an enemy will not effect you. The Magic screen only neutralizes one spell against you, however.',
            'reply' => "_Counter-spell!_"
        );

        $spells[] = array(
            'name' => 'Death',
            'cost' => 3,
            'type' => 'combat',
            'target' => true,
            'desc' => 'This spell can be used in any attack round instead of a weapon attack. If you have the higher Attack Strength, this spell kills any creature with a skill score of 9 or less. If you have the lower attack strength, your spell is ruined and the creature hits you. This spell will not affect any Undead creature or Zagor, your nemesis!',
            'func' => '_spell_death'
        );

        $spells[] = array(
            'name' => 'Thunderbolt',
            'cost' => 3,
            'type' => 'combat',
            'target' => true,
            'desc' => 'This spell can be used in any attack round instead of a weapon attack. If you have the higher Attack Strength, your opponent is struck by a ball of white-hot electricity and loses 7 Stamina points. If you have the lower attack strength, your spell is ruined and the creature hits you.',
            'func' => '_spell_thunderbolt'
        );

        $spells[] = array(
            'name' => 'Teleport',
            'cost' => 4,
            'type' => 'utility',
            'target' => false,
            'desc' => 'This spell enables you to move long distances through a dungeon by instant magical teleportation, avoiding many hazards and enemies. You can cast this spell only at certain special magical portals. When you confront such a portal, you will be asked whether you wish to cast this spell; if you do, you will be instructed accordingly.',
            'reply' => "_The world shimmers away around you..._"
        );

        $spells[] = array(
            'name' => 'Steal Talisman',
            'cost' => 5,
            'type' => 'object',
            'target' => false,
            'desc' => 'This spell summons a Golden Talisman from the hidden treasures of the dungeon. You may only cast two steal spells during the adventure. (This includes castings of Steal Dagger.) You do not gain any Luck for acquiring Talismans this way.',
            'reply' => 'A Golden Talisman appears from nowhere.',
            'addcmd' => 'talismans +1'
        );

        $spells[] = array(
            'name' => 'Steal Dagger',
            'cost' => 5,
            'type' => 'object',
            'target' => false,
            'desc' => 'This spell summons a Silver Dagger from the hidden treasures of the dungeon. You may only cast two steal spells during the adventure. (This includes castings of Steal Talisman.) You do not gain any Luck for acquiring Daggers this way.',
            'reply' => 'A Silver Dagger appears from nowhere.',
            'addcmd' => 'daggers +1'
        );
        return $spells;
    }


    protected function _spell_fast_hands($name, $skill, $stam) {
        $out = run_fight(['player' => &$this->player,
                'monstername' => $name,
                'monsterskill' => $skill,
                'monsterstam' => $stam,
                'fasthands' => true]);
        sendqmsg($out, ":crossed_swords:");
    }


    protected function _spell_fireball($name, $skill, $stam) {
        $out = run_single_attack($this->player, $name, $skill, $stam, 2, 5);
        sendqmsg($out, ':fireworks:');
    }


    protected function _spell_death($name, $skill, $stam) {
        if ($skill > 9) {
            sendqmsg("*$name is immune to this spell!*", ':interrobang:');
        } else {
            $out = run_single_attack($this->player, $name, $skill, $stam, 2, 1000);
            sendqmsg($out, ':fireworks:');
        }
    }


    protected function _spell_thunderbolt($name, $skill, $stam) {
        $out = run_single_attack($player, $name, $skill, $stam, 2, 7);
        sendqmsg($out, ':fireworks:');
    }


    //// !test <luck/skill/stam> (run a skill test) OVERRIDE
    public function _cmd_test($cmd) {
        $player = &$this->player;
        if (strtolower($cmd[1] == 'spot')) {
            $player['spot'] = $player['skill'] + ($player['adjective'] == 'Wizard'?2:0);
        }
        parent::_cmd_test($cmd, $player);
    }


}
