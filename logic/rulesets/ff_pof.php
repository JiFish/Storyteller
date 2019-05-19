<?php

require_once 'ff_magic.php';

class book_ff_pof extends book_ff_magic {
    protected function getStats() {
        $stats = parent::getStats();
        $stats['magic']['friendly'] = 'Power';
        $stats['magic']['alias'] = ['power', 'pow'];
        return $stats;
    }


    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        // Set race to elf unless otherwise specified
        if (!$race || $race == '?') {
            $race = 'Elf';
        }
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        // Random Potion
        // The book rules actually give you a choice, but this is a bit more fun
        list($d, $emojidice) = roll_dice_string("1d6");
        $p['creationdice'] .= " $emojidice";
        switch ($d) {
        case 1: case 2:
            $p['stuff'][] = 'Potion of Skill [skill full]';
            break;
        case 3: case 4:
            $p['stuff'][] = 'Potion of Strength [stam full]';
            break;
        case 5: case 6:
            $p['stuff'][] = 'Potion of Luck [luck full]';
            // If the potion of luck is chosen, the player get 1 bonus luck
            $p['luck']++;
            $p['max']['luck']++;
            break;
        }
        return $p;
    }


    protected function getCharcterSheetAttachments() {
        $attachments = parent::getCharcterSheetAttachments();
        $attachments[0]['fields'][3]['title'] = 'Power';
        return $attachments;
    }


    protected function getSpells() {
        $spells[] = array(
            'name' => 'Protect',
            'cost' => 1,
            'type' => 'elf',
            'target' => false,
            'desc' => "Casts a pall of invisibility over the area surrounding you.",
            'reply' => 'You cast the protect spell...'
        );

        $spells[] = array(
            'name' => 'Illusion',
            'cost' => 1,
            'type' => 'elf',
            'target' => false,
            'desc' => "Allows you to make anything, yourself included, appear to be anything else, within reason.",
            'reply' => 'You cast the illusion spell...'
        );

        $spells[] = array(
            'name' => 'Weaken',
            'cost' => 1,
            'type' => 'elf',
            'target' => false,
            'desc' => "Reduces an opponent's stamina by 4 points.",
            'reply' => "You cast the weaken spell... (Reduce your opponent's stamina by 4 points.)"
        );

        $spells[] = array(
            'name' => 'Levitation',
            'cost' => 1,
            'type' => 'elf',
            'target' => false,
            'desc' => "Allows you to float freely up to a ceiling of about 4 meters. Since you also float gently to the ground, it can also be used for descending falls.",
            'reply' => "You cast the levitation spell..."
        );

        $spells[] = array(
            'name' => 'Finding',
            'cost' => 1,
            'type' => 'elf',
            'target' => false,
            'desc' => "Allows you to detect the presence of something - it may be treasure, a secret passage, or even a lurking enemy.",
            'reply' => "You cast the finding spell..."
        );

        $spells[] = array(
            'name' => 'Fire',
            'cost' => 1,
            'type' => 'elf',
            'target' => false,
            'desc' => "Allows you to produce flames from your fingertips.",
            'reply' => "You cast the fire spell..."
        );

        return $spells;
    }


}
