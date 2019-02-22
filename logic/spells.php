<?php

$gamebook = getbook();

if ($gamebook == "coc") {
    $spells[] = array(
        'name' => 'Creature Copy',
        'cost' => 1,
        'type' => 'combat',
        'target' => true,
        'desc' => 'This spell will allow you to conjure up an exact duplicate of any creature you are fighting. The duplicate will have the same skill and stamina scores, and the same powers, as its orginal. But the duplicate will be under the control of your will and you may, for example, instruct it to attack the orginal creature and then sit back and watch the battle!',
        'func' => function(&$player,$name,$skill,$stam) {
            sendqmsg("> A duplicate of the $name appears!",':fireworks:');
            addcommand("vs $name Duplicate $skill $stam $name $skill $stam");
        }
    );

    $spells[] = array(
        'name' => 'ESP',
        'cost' => 1,
        'type' => 'utility',
        'target' => false,
        'desc' => "With this spell you will be able to tune in to psychic wavelengths. It may help you to read a creature's mind or may tell you what is behind a locked door. However, it is sometimes prone to give misleading information is more than one psychic source is close to another.",
        'reply' => "You open your mind!"
    );

    $spells[] = array(
        'name' => 'Fire',
        'cost' => 1,
        'type' => 'utility',
        'target' => false,
        'desc' => "Every creature is afraid of fire, and this spell allows you to conjure up fire at will. You may cause a small explosion on the ground which will burn for several seconds or you may create a wall of fire to keep creatures at bay.",
        'reply' => "Fire shoots from your fingertips!"
    );

    $spells[] = array(
        'name' => 'Fools Gold',
        'cost' => 1,
        'type' => 'utility',
        'target' => false,
        'desc' => "This spell will turn ordinary rock in to a pile of what appears to be gold. However, the spell is merely a form of illusion spell - although more reliable than the Illusion Spell - and the pile of gold will soon turn back to rock.",
        'reply' => "Fire shoots from your fingertips!"
    );

    $spells[] = array(
        'name' => 'Illusion',
        'cost' => 1,
        'type' => 'utility',
        'target' => false,
        'desc' => "This is a powerful spell, but one which is a little unreliable. Through this spell you may create a convincing illusion (e.g. that you have turned into a snake, or that the floor is covered in hot coals) with which to fool a creature. The spell will immediately be cancelled if anything happens which dispels the illusion (e.g. you convince a creature that you have turned into a snake and then promptly crack it over the head with your sword!) It is most effective against intelligent creatures.",
        'reply' => "You weave an illusion..."
    );

    $spells[] = array(
        'name' => 'Levitation',
        'cost' => 1,
        'type' => 'utility',
        'target' => false,
        'desc' => "This may cast this spell onto objects, opponents and even yourself. It frees its receiver from the effects of gravity and as such will cause that receiver to float freely in the air, under your control.",
        'reply' => "You cast the levitation spell..."
    );

    $spells[] = array(
        'name' => 'Shielding',
        'cost' => 1,
        'type' => 'combat',
        'target' => false,
        'desc' => "Casting this spell creates an invisible shield in front of you which will protect you from physical objects, e.g. arrows, swords or creatures. The shield is not effective against magic and, of course, if nothing outside it can touch you, you will not be able to touch anything outside it.",
        'reply' => "You create an invisible shield..."
    );

    $spells[] = array(
        'name' => 'Strength',
        'cost' => 1,
        'type' => 'self',
        'target' => false,
        'desc' => "This spell has the effect of increasing your strength greatly and is very useful when battling strong creatures. However, it must be exercised with caution as it is difficult to control your own strength when it is suddenly increased by so much!",
        'reply' => "Your strength increases greatly..."
    );

    $spells[] = array(
        'name' => 'Weakness',
        'cost' => 1,
        'type' => 'combat',
        'target' => false,
        'desc' => "Strong creatures are reduced by this spell to miserable weaklings. It is not successful against all creatures but when effective, those creatures become puny and much less of a challenge in a battle.",
        'reply' => "You cast the weakness spell..."
    );
}

if ($gamebook == "coc" || $gamebook == 'ss') {
    $spells[] = array(
        'name' => 'Luck',
        'cost' => 1,
        'type' => 'self',
        'target' => false,
        'desc' => "This spell will restore your Luck score by half of it's maximum value. This spell is special in that it may be cast at any time during your adventure, except in a battle. You need not wait for a choice to appear on the page.",
        'func' => function($player) {
            sendqmsg('> You cast the luck spell...',':fireworks:');
            addcommand('luck +'.ceil($player['max']['luck']/2));
        }
    );

    $spells[] = array(
        'name' => 'Skill',
        'cost' => 1,
        'type' => 'self',
        'target' => false,
        'desc' => "This spell will restore your Skill score by half of it's maximum value. This spell is special in that it may be cast at any time during your adventure, except in a battle. You need not wait for a choice to appear on the page.",
        'func' => function($player) {
            sendqmsg('> You cast the skill spell...',':fireworks:');
            addcommand('skill +'.ceil($player['max']['skill']/2));
        }
    );

    $spells[] = array(
        'name' => 'Stamina',
        'cost' => 1,
        'type' => 'self',
        'target' => false,
        'desc' => "This spell will restore your Stamina score by half of it's maximum value. This spell is special in that it may be cast at any time during your adventure, except in a battle. You need not wait for a choice to appear on the page.",
        'func' => function($player) {
            sendqmsg('> You cast the stamina spell...',':fireworks:');
            addcommand('stamina +'.ceil($player['max']['stamina']/2));
        }
    );
}
if ($gamebook == "loz") {
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
        'reply' => "_Open Sesame!_"
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
        'reply' => "_The world shimmers away around you..._"
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
