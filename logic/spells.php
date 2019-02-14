<?php

if (getbook() == "loz") {
    $spells[] = array(
        'name' => 'Create Food',
        'cost' => 1,
        'type' => 'object',
        'target' => false,
        'desc' => 'This spell creates an extra provision. Add 1 to your provisions. This spell cannot be cast during combat.',
        'func' => function() {
            sendqmsg('> A '.['sandwich','hunk of cheese','meat shank','baked potato'][mt_rand(0,3)].' appears from nowhere.',':fireworks:');
            addcommand('prov +1');
        }
    );

    $spells[] = array(
        'name' => 'Create Meal',
        'cost' => 2,
        'type' => 'object',
        'target' => false,
        'desc' => 'This spell creates extra provisions. Add 2 to your provisions. This spell cannot be cast during combat.',
        'func' => function() {
            sendqmsg('> A '.['sandwich','hunk of cheese','meat shank','baked potato'][mt_rand(0,3)].' and a '.['sandwich','hunk of cheese','meat shank','baked potato'][mt_rand(0,3)].' appear from nowhere.',':fireworks:');
            addcommand('prov +2');
        }
    );

    $spells[] = array(
        'name' => 'Create Feast',
        'cost' => 3,
        'type' => 'object',
        'target' => false,
        'desc' => 'This spell creates extra provisions. Add 3 to your provisions. This spell cannot be cast during combat.',
        'func' => function() {
            sendqmsg('> A '.['sandwich','hunk of cheese','meat shank','baked potato'][mt_rand(0,3)].', a '.['sandwich','hunk of cheese','meat shank','baked potato'][mt_rand(0,3)].' and a '.['sandwich','hunk of cheese','meat shank','baked potato'][mt_rand(0,3)].' appear from nowhere.',':fireworks:');
            addcommand('prov +3');
        }
    );

    $spells[] = array(
        'name' => 'Jump',
        'cost' => 1,
        'type' => 'self',
        'target' => false,
        'desc' => 'This spell enables you to leap safely a distance of up to six meters. If you are faced with an obstacle (a chasm, a trapped corridor, etc.) which is not stated to be longer than six meters, you may use this spell to traverse the obstacle safely. This spell cannot be cast during combat.',
        'func' => function() {
            sendqmsg('> _Boing!_',':fireworks:');
        }
    );

    $spells[] = array(
        'name' => 'Light',
        'cost' => 1,
        'type' => 'utility',
        'desc' => 'If you lose your lantern, or it no longer works, this spell creates a long-lasting small sphere of light which can be stored and used as a lantern whenever the need arises. Each use of the spell illuminates only one section of pathway, however. This spell cannot be cast during combat.',
        'func' => function() {
            sendqmsg('> _Let there be light!_',':fireworks:');
        }
    );

    $spells[] = array(
        'name' => 'Luck',
        'cost' => 1,
        'type' => 'self',
        'target' => false,
        'desc' => 'This spell increases your current luck score by 1 point. You current luck score cannot exceed it\'s Initial value. This spell cannot be cast during combat.',
        'func' => function() {
            sendqmsg('> _Felicitus Populi!_',':fireworks:');
            addcommand('luck +1');
        }
    );

    $spells[] = array(
        'name' => 'Open',
        'cost' => 1,
        'type' => 'utility',
        'target' => false,
        'desc' => 'This simple spell opens one trapped treasure chest or locked door safely. If the chest or door has a trap, it is rendered harmless by the spell (even if it is a Magic Trap). This spell cannot be cast during combat.',
        'func' => function() {
            sendqmsg('> _Open Sesame!_',':fireworks:');
        }
    );

    $spells[] = array(
        'name' => 'Skill',
        'cost' => 1,
        'type' => 'self',
        'target' => false,
        'desc' => 'This spell enables you to react and move faster, adding 1 to your current skill for a short time. It can be cast just before combat begins (not during one!) to aid your fighting, or just just before you have to Test your Skill for some reason. The spell expires as soon as one combat has finished, or after you have Tested your Skill.',
        'func' => function() {
            sendqmsg('> _You feel quicker._',':fireworks:');
            addcommand('skill temp +1');
        }
    );

    $spells[] = array(
        'name' => 'Fast Hands',
        'cost' => 2,
        'type' => 'combat',
        'target' => true,
        'desc' => 'This spell can be cast immediately before any combat begins but not during one. For the first three Attack Rounds of the combat, you may roll dice twice when working out your Attack Strength and take the higher total rolled.',
        'func' => function(&$player,$name,$skill,$stam) {
            $out = run_fight(['player' => &$player,
                              'monstername' => $name,
                              'monsterskill' => $skill,
                              'monsterstam' => $stam,
                              'fasthands' => true]);
            sendqmsg($out,":crossed_swords:");
        }
    );

    $spells[] = array(
        'name' => 'Fireball',
        'cost' => 2,
        'type' => 'combat',
        'target' => true,
        'desc' => 'This spell can be used in any attack round instead of a weapon attack. If you have the higher Attack Strength, your adversary\'s body is shrouded in flames and he loses 5 Stamina points. If you have the lower attack strength, your spell is ruined and the creature hits you.',
        'func' => function(&$player,$name,$skill,$stam) {
            $out = run_single_attack($player, $name, $skill, $stam, 2, 5);
            sendqmsg($out,':fireworks:');
        }
    );


    $spells[] = array(
        'name' => 'Magic Screen',
        'cost' => 2,
        'type' => 'self',
        'target' => false,
        'desc' => 'Once this spell is cast, the next spell which is cast at you by an enemy will not effect you. The Magic screen only neutralizes one spell against you, however.',
        'func' => function() {
            sendqmsg("> _Counter-spell!_",':fireworks:');
            addcommand('get note: Immune to the next spell');
        }
    );

    $spells[] = array(
        'name' => 'Death',
        'cost' => 3,
        'type' => 'combat',
        'target' => true,
        'desc' => 'This spell can be used in any attack round instead of a weapon attack. If you have the higher Attack Strength, this spell kills any creature with a skill score of 9 or less. If you have the lower attack strength, your spell is ruined and the creature hits you. This spell will not affect any Undead creature or Zagor, your nemesis!',
        'func' => function(&$player,$name,$skill,$stam) {
            if ($skill > 9) {
                sendqmsg("*$name is immune to this spell!*",':interrobang:');
            } else {
                $out = run_single_attack($player, $name, $skill, $stam, 2, 1000);
                sendqmsg($out,':fireworks:');
            }
        }
    );

    $spells[] = array(
        'name' => 'Thunderbolt',
        'cost' => 3,
        'type' => 'combat',
        'target' => true,
        'desc' => 'This spell can be used in any attack round instead of a weapon attack. If you have the higher Attack Strength, your opponent is struck by a ball of white-hot electricity and loses 7 Stamina points. If you have the lower attack strength, your spell is ruined and the creature hits you.',
        'func' => function(&$player,$name,$skill,$stam) {
            $out = run_single_attack($player, $name, $skill, $stam, 2, 7);
            sendqmsg($out,':fireworks:');
        }
    );

    $spells[] = array(
        'name' => 'Teleport',
        'cost' => 4,
        'type' => 'utility',
        'target' => false,
        'desc' => 'This spell enables you to move long distances through a dungeon by instant magical teleportation, avoiding many hazards and enemies. You can cast this spell only at certain special magical portals. When you confront such a portal, you will be asked whether you wish to cast this spell; if you do, you will be instructed accordingly.',
        'func' => function(&$player,$name,$skill,$stam) {
            sendqmsg("> _The world shimmers away around you..._",':fireworks:');
        }
    );

    $spells[] = array(
        'name' => 'Steal Talisman',
        'cost' => 5,
        'type' => 'object',
        'target' => false,
        'desc' => 'This spell summons a Golden Talisman from the hidden treasures of the dungeon. You may only cast two steal spells during the adventure. (This includes castings of Steal Dagger.) You do not gain any Luck for acquiring Talismans this way.',
        'func' => function(&$player,$name,$skill,$stam) {
            sendqmsg('> A Golden Talisman appears from nowhere.',':fireworks:');
            addcommand('talismans +1');
        }
    );

    $spells[] = array(
        'name' => 'Steal Dagger',
        'cost' => 5,
        'type' => 'object',
        'target' => false,
        'desc' => 'This spell summons a Silver Dagger from the hidden treasures of the dungeon. You may only cast two steal spells during the adventure. (This includes castings of Steal Talisman.) You do not gain any Luck for acquiring Daggers this way.',
        'func' => function(&$player,$name,$skill,$stam) {
            sendqmsg('> A Silver Dagger appears from nowhere.',':fireworks:');
            addcommand('daggers +1');
        }
    );
}
