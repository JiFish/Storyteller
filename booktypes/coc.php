<?php

require_once('ff_magic.php');

class book_coc extends book_ff_magic {
    public function getId() {
        return 'coc';
    }

    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollCharacter($name,$gender,$emoji,$race,$adjective,$seed);
        // Starting Equipment
        $p['stuff'] = array('Sword (+0)','Leather Armor','Lantern');
        return $p;
    }

    protected function getSpells() {
        $spells = parent::getSpells();
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
        return $spells;
    }
}
