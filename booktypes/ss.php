<?php

require_once 'ff_magic.php';

class book_ss extends book_ff_magic {
    public function getId() {
        return 'ss';
    }


    public function getStats() {
        $stats = parent::getStats();
        $stats['magic']['roll'] = 0;
        return $stats;
    }


    protected function getCharcterSheetAttachments(&$player) {
        return $this->getCharcterSheetAttachmentsNoMagic($player);
    }


    public function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective, $seed);
        // Starting Equipment
        $p['stuff'] = array('Sword (+0)', 'Chainmail Armor');
        return $p;
    }


    protected function getSpells() {
        $spells = parent::getSpells();
        foreach ($spells as $key => $val) {
            $spells[$key]['cost'] = 0;
            $spells[$key]['type'] = 'neutral';
        }

        $spells[] = array(
            'name' => 'Fire',
            'cost' => 0,
            'type' => 'neutral',
            'target' => false,
            'desc' => "This spell will set one medium-sized object (like a torch) on fire. The more flammable the object, the better the spell will work.",
            'reply' => "Fire shoots from your fingertips!"
        );

        $spells[] = array(
            'name' => 'Ice',
            'cost' => 0,
            'type' => 'neutral',
            'target' => false,
            'desc' => "This spell works by freezing water (or water vapour) in to ice. It works best where there is a water already present to be frozen.",
            'reply' => "Cold air shoots from your fingertips!"
        );

        $spells[] = array(
            'name' => 'Illusion',
            'cost' => 0,
            'type' => 'neutral',
            'target' => false,
            'desc' => "This spell will let you create one small, short-lived illusion. If you act in a way contrary to the illusion, the illusion will cease to fool anyone else and be dispelled.",
            'reply' => "You weave an illusion..."
        );

        $spells[] = array(
            'name' => 'Friendship',
            'cost' => 0,
            'type' => 'good',
            'target' => false,
            'desc' => "This spell will make one creature better disposed towards you. But it will not work on anyone or anything that cannot understand the idea of friendship.",
            'reply' => "You feel at ease with yourself..."
        );

        $spells[] = array(
            'name' => 'Growth',
            'cost' => 0,
            'type' => 'good',
            'target' => false,
            'desc' => "This spell will accelerate the growth of one large plant, or several smaller ones. It will not affect anything but plants.",
            'reply' => "Your thumbs turn green..."
        );

        $spells[] = array(
            'name' => 'Bless',
            'cost' => 0,
            'type' => 'good',
            'target' => false,
            'desc' => "This spell may only be cast on another being - you cannot Bless yourself. It restores lost Skill, Luck, and Stamina points to the creature that you Bless. Three points are added to each, but in no case may the spell increase anyone's points past their initial level.",
            'reply' => "Your thumbs turn green..."
        );

        $spells[] = array(
            'name' => 'Fear',
            'cost' => 0,
            'type' => 'evil',
            'target' => false,
            'desc' => "This spell will make one creature fear you - provided the creature is capable of feeling fear!",
            'reply' => "You take on a fearful form..."
        );

        $spells[] = array(
            'name' => 'Withering',
            'cost' => 0,
            'type' => 'evil',
            'target' => false,
            'desc' => "This spell will cause one large plant, or several smaller ones, to wither. It will not affect anything but plants.",
            'reply' => "The plant begins to wither..."
        );

        $spells[] = array(
            'name' => 'Curse',
            'cost' => 0,
            'type' => 'evil',
            'target' => false,
            'desc' => "This spell is a very powerful spell, and not to be used lightly. When you cast a Curse, you immediately roll one dice and lose that many stamina points. However, something terrible - there is no telling what - will immediately befall your enemy, too.",
            'func' => function() {
                $dice = rand(1, 6);
                sendqmsg('> You cast curse and pay the price... '.diceemoji($dice), ':fireworks:');
                addcommand("stam -$dice");
            }
        );
        return $spells;
    }


}
