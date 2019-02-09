<?php

function dice() {
    $r = rand(1,6);
    switch(CHARACTER_ROLLS) {
        case 'd5+1':
            $r = rand(2,6);
            break;
        case 'min3':
            $r = max(3,$r);
            break;
        case 'min4':
            $r = max(4,$r);
            break;
        case 'roll2':
            $r = max(rand(1,6),$r);
            break;
        case 'roll3':
            $r = max(rand(1,6),rand(1,6),$r);
            break;
        case 'all6':
            $r = 6;
            break;
        case 'heaven':
            $r = 7;
            break;
        default:
    }
    return $r;
}

// Roll a new random character and return a 'player' array ready to be used elsewhere
function roll_character($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?', $seed = '?')
{
    // Seed random
    if (!is_numeric($seed)) {
        $seed = make_seed();
    }
    srand($seed);

    // Roll dice!
    for ($c = 0; $c < 4; $c++) {
        $dice[$c] = dice();
    }

    // Get the type of book
    $gamebook = getbook();

    $p = array('skill' => $dice[0] + 6,             //1d6+6
               'stam' => $dice[1] + $dice[2] + 12, //2d6+12
               'luck' => $dice[3] + 6,              //1d6+6
               'prov' => 10,
               'gold' => 0,
               'weapon' => 0,
               'shield' => false,
               'lastpage' => 1,
               'stuff' => array('Sword (+0)','Leather Armor','Lantern'),
               'creationdice' => $dice,
               'temp' => array(),
               'seed' => $seed);

    // Set maximums
    // The game won't (normally) allow you to exceed your initial scores
    $p['max']['skill']  = $p['skill'];
    $p['max']['stam']   = $p['stam'];
    $p['max']['luck']   = $p['luck'];
    $p['max']['prov']   = 999;
    $p['max']['gold']   = 999;
    $p['max']['weapon'] = 999;

    // Character Fluff - Gender, name, race etc.
    if (!$gender || $gender == '?') {
        $gender = (rand(0,1)?'Male':'Female');
        if (rand(0,99) == 0) {
            $gender = array('Agender','Androgynous','Gender neutral', 'Genderfluid',
                            'Genderless','Non-binary','Transgender')[rand(0,6)];
        }
    } elseif ($gender == 'm' || $gender == 'M') {
        $gender = 'Male';
    } elseif ($gender == 'f' || $gender == 'F') {
        $gender = 'Female';
    }
    $p['gender'] = ucfirst(strtolower($gender));

    // Note this array should match with the emoji arrays below
    $races = array('Human','Human','Human','Elf','Djinnin','Catling','Dwarf');
    $needsskintone = array(true,true,true,true,false,false,true);
    // Determine race
    if (in_array(ucfirst(strtolower($race)),$races)) {
        $keys = array_keys($races, ucfirst(strtolower($race)));
        $selection = $keys[array_rand($keys)];
        $p['race'] = ucfirst(strtolower($race));
    } elseif (!$race || $race == '?') {
        $selection = array_rand($races);
        $p['race'] = $races[$selection];
    } else {
        $selection = array_rand($races);
        $p['race'] = ucfirst(strtolower($race));
    }

    // Name and adjective
    if (!$name || $name == '?') {
        if ($p['race'] == 'Catling') {
            $namesfile = 'resources/cat_names.txt';
        } elseif ($gender=='Male') {
            $namesfile = 'resources/male_names.txt';
        } else {
            $namesfile = 'resources/female_names.txt';
        }
        $names = file($namesfile);
        $p['name'] = trim($names[array_rand($names)]);
    } else {
        $p['name'] = ucfirst($name);
    }
    if (!$adjective || $adjective == '?') {
        $adjectives = file('resources/adjectives.txt');
        $p['adjective'] = ucfirst(trim($adjectives[array_rand($adjectives)]));
    } else {
        $p['adjective'] = ucfirst($adjective);
    }

    // Race, Gender and emoji are linked
    // Determine emoji
    if (!$emoji || $emoji == '?') {
        $skintone = array(':skin-tone-2:',':skin-tone-3:',':skin-tone-4:',':skin-tone-5:',':skin-tone-2:');
        if ($gender == 'Male') {
            $emojilist = array(':man:',':blond-haired-man:',':older_man:',':male_elf:',':male_genie:',':smirk_cat:',':bearded_person:');
        } elseif ($gender == 'Female') {
            $emojilist = array(':woman:',':blond-haired-woman:',':older_woman:',':female_elf:',':female_genie:',':smile_cat:',':bearded_person:');
        } else {
            $emojilist = array(':adult:',':person_with_blond_hair:',':older_adult:',':elf:',':genie:',':smiley_cat:',':bearded_person:');
        }
        $p['emoji'] = $emojilist[$selection];
        if ($needsskintone[$selection]) {
            $p['emoji'] .= $skintone[array_rand($skintone)];
        }
    } else {
        $p['emoji'] = $emoji;
    }

    // End of bare character generation.

    // Book customisations
    if ($gamebook == 'wofm') {
        // Random Potion
        // The book rules actually give you a choice, but this is a bit more fun
        $p['creationdice'][] = dice();
        switch($p['creationdice'][4]) {
            case 1: case 2:
                $p['stuff'][] = 'Potion of Skill';
                break;
            case 3: case 4:
                $p['stuff'][] = 'Potion of Strength';
                break;
            case 5: case 6:
                $p['stuff'][] = 'Potion of Luck';
                // If the potion of luck is chosen, the player get 1 bonus luck
                $p['luck']++;
                $p['max']['luck']++;
                break;
        }
    } elseif ($gamebook == 'dotd') {
        // Make human
        if ($gender == 'Male') {
            $emojilist = array(':man:',':blond-haired-man:',':older_man:');
        } elseif ($gender == 'Female') {
            $emojilist = array(':woman:',':blond-haired-woman:',':older_woman:');
        } else {
            $emojilist = array(':adult:',':person_with_blond_hair:',':older_adult:');
        }
        $p['emoji'] = $emojilist[array_rand($emojilist)].$skintone[array_rand($skintone)];
        $p['race'] = array('Sailor','Pirate','Seafarer','Mariner','Seaswab','Deck Hand','Navigator')[rand(0,6)];
        // Remove lantern
        unset($p['stuff'][2]);
    } elseif ($gamebook == 'sob') {
        // Make human
        if ($gender == 'Male') {
            $emojilist = array(':man:',':blond-haired-man:',':older_man:');
        } elseif ($gender == 'Female') {
            $emojilist = array(':woman:',':blond-haired-woman:',':older_woman:');
        } else {
            $emojilist = array(':adult:',':person_with_blond_hair:',':older_adult:');
        }
        $p['emoji'] = $emojilist[array_rand($emojilist)].$skintone[array_rand($skintone)];
        $p['race'] = 'Pirate';
        $shipnames = file('resources/ship_names.txt');
        $p['shipname'] = trim($shipnames[array_rand($shipnames)]);
        $adjectives = array('Bold','Bloodthirsty','Cut-throat','Despicable','Dread-Pirate','Foul','Fearsome','Horrible',
                            'Hook-handed','Killer','Loathsome','Low','Mad','Murderous','Nasty','Navigator','Peg-legged',
                            'Reviled','Ruthless','Strong','Scurviest','Tough','Terrible','Weird','Vile','Villainous');
        $p['adjective'] = trim($adjectives[array_rand($adjectives)]);
        // new stats
        $p['creationdice'][] = dice();
        $p['strike'] = $p['creationdice'][4]+6; // 1d6+6
        $p['max']['strike'] = $p['strike'];
        $p['creationdice'][] = dice();
        $p['str'] = $p['creationdice'][5]+6; // 1d6+6
        $p['max']['str'] = $p['str'];
        $p['log'] = 0;
        $p['max']['log'] = 999;
        $p['slaves'] = 0;
        $p['max']['slaves'] = 999;
        // starting items
        $p['prov'] = 0;
        $p['gold'] = 20;
        $p['stuff'] = array('Cutlass (+0)');
    } elseif ($gamebook == 'hoh') {
        // Make human
        if ($gender == 'Male') {
            $emojilist = array(':man:',':blond-haired-man:',':older_man:');
        } elseif ($gender == 'Female') {
            $emojilist = array(':woman:',':blond-haired-woman:',':older_woman:');
        } else {
            $emojilist = array(':adult:',':person_with_blond_hair:',':older_adult:');
        }
        $p['emoji'] = $emojilist[array_rand($emojilist)].$skintone[array_rand($skintone)];
        $p['stuff'] = array();
        $p['prov'] = 0;
        $p['weapon'] = -3;
        $p['creationdice'][] = dice();
        $p['fear'] = 0;
        $p['max']['fear'] = $p['creationdice'][4]+6; // 1d6+6
        $p['race'] = array('Cowardly','Ordinary','Sceptical','Open-Minded','Believer','Enlightened')[$p['creationdice'][4]-1];
    } elseif ($gamebook == 'none') {
        // No starting anything!
        $p['prov'] = 0;
        $p['stuff'] = array();
    } elseif ($gamebook == 'poe') {
        $p['prov'] = 2;
    } elseif ($gamebook == 'coc') {
        $p['prov'] = 0;
    } elseif ($gamebook == 'ss') {
        $p['prov'] = 0;
        $p['stuff'] = array('Sword (+0)','Chainmail Armor');
    } elseif ($gamebook == 'bvp') {
        $p['creationdice'] = array();
        $p['stam'] = $p['max']['stam'] = 1;
        $p['skill'] = $p['max']['skill'] = 1;
        $p['luck'] = $p['max']['luck'] = 1;
        $p['prov'] = 0;
        $p['stuff'] = array();
    } elseif ($gamebook == 'coh') {
        // No starting anything!
        $p['prov'] = 0;
        $p['stuff'] = array();
        // Change fluff
        $p['race'] = 'Beast';
        $p['adjective'] = 'Creature of Havoc';
        $p['realname'] = $p['name'];
        $p['name'] = '';
        for ($c = 0; $c < strlen($p['realname']); $c++) {
            if ($c == 0 || rand(0,3) == 0) {
                $p['name'] .= $p['realname'][$c];
            } else {
                $p['name'] .= '?';
            }
        }
        $p['emoji'] = ':japanese_ogre:';
    } elseif ($gamebook == 'rtfm') {
        $p['goldzagors'] = 0;
        $p['max']['goldzagors'] = 999;
    } elseif ($gamebook == 'loz') {
        $p['prov'] = 12;
        $p['max']['prov'] = 12;
        $p['talismans'] = 0;
        $p['max']['talismans'] = 999;
        $p['daggers'] = 0;
        $p['max']['daggers'] = 999;

        $p['stuff'] = array(
            'Knife (+0, -1 dmg)',
            'Leather Armor',
            'Small Shield',
            );

        // Gold dice
        $p['gold'] = 2;
        for ($c = 0; $c < 3; $c++) {
            $d = dice();
            $p['creationdice'][] = $d;
            $p['gold'] += $d;
        }

        // Reuse adjective as player class
        // Miner is the old Dwarf class (to separate from races above)
        $classes = ['Barbarian','Warrior','Miner','Wizard'];
        if (in_array(ucfirst(strtolower($adjective)),$classes)) {
            $p['adjective'] = ucfirst(strtolower($adjective));
        } else {
            $p['adjective'] = $classes[rand(0,3)];
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
                $p['emoji'] .= $skintone[array_rand($skintone)];
        }
    } elseif ($gamebook == 'sst') {
        // Overriding default races
        $races = array('Human','Human','Human','Vulcan','Andorian','Caitian','Droid');
        if (!$race || $race == '?') {
            $p['race'] = $races[$selection];
            unset($races[$selection]);
        }
        if (!$name || $name == '?') {
            if ($p['race'] == 'Droid') {
                $p['name'] = chr(mt_rand(68,90)).chr(mt_rand(65,87)).'-'.mt_rand(1,9);
            } elseif ($p['race'] == 'Vulcan') {
                $p['name'] = generate_vulcan_name($p['gender']);
            } elseif ($p['race'] == 'Andorian') {
                $p['name'] = generate_andorian_name();
            }
        }
        if ($p['race'] == 'Droid' && (!$emoji || $emoji == '?')) {
            $p['emoji'] = ':robot:';
        }
        $p['adjective'] = 'Captain';
        $p['prov'] = 0;
        $p['stuff'] = array();
        // Ship
        $d1 = dice();
        $d2 = dice();
        array_push($p['creationdice'],$d1,$d2);
        $p['weapons'] = 6+$d1;
        $p['shields'] = 12+$d2;
        $p['max']['weapons'] = $p['weapons'];
        $p['max']['shields']  = $p['shields'];
        $names = file('resources/starship_names.txt');
        $p['shipname'] = trim($names[array_rand($names)]);
        // Crew
        $cl = ['no1','science','medic','engineer','security','guard'];
        $races = array_pad($races,9,'Human');
        foreach ($cl as $k => $c) {
            $d1 = dice();
            $d2 = dice();
            $d3 = dice();
            array_push($p['creationdice'],$d1,$d2,$d3);
            $cm = roll_sst_crew($c, ($k > 0 && $k < 4), $races, [$d1, $d2, $d3]);
            $p['crew'][$c] = $cm;
        }
    }  elseif ($gamebook == 'tot') {
        //nothing to do
        null;
    } elseif ($gamebook == 'custom') {
        $p['creationdice'][] = dice();
        $p['magic'] = max(0,$p['creationdice'][4]-3); // 1d6-3
        $p['max']['magic'] = $p['magic'];
    }

    // Undocumented hook to allow the config file to alter new players
    if (function_exists('hook_alter_new_player')) {
        hook_alter_new_player($p);
    }

    return $p;
}

function roll_sst_crew($position, $combatpenalty, &$races = null, $dice = null) {
    if ($races == null) {
        $races = ['Vulcan','Andorian','Caitian','Droid'];
        $races = array_pad($races,8,'Human');
    }
    if ($dice == null) {
        $dice = [dice(),dice(),dice()];
    }
    $cm = array(
        'skill' =>  6+$dice[0],
        'stam' => 12+$dice[1]+$dice[2],
        'position' => ($position=='redshirt'?'RedShirt':ucfirst($position)),
        'gender' => (rand(0,1)?'Male':'Female'),
        'combatpenalty' => $combatpenalty,
        'replacement' => false,
        'awayteam' => false,
        'luck' => 0,
        'weapon' => 0,
        'shield' => false,
        'temp' => []
    );
    $cm['max']['skill'] = $cm['skill'];
    $cm['max']['stam']  = $cm['stam'];
    // Set race and unset for choice string to avoid repeats
    $r = array_rand($races);
    $cm['race'] = trim($races[$r]);
    unset($races[$r]);
    if ($cm['race'] == 'Droid') {
        $cm['name'] = chr(mt_rand(68,90)).chr(mt_rand(65,87)).'-'.mt_rand(1,9);
    } elseif ($cm['race'] == 'Caitian') {
        $names = file('resources/cat_names.txt');
        $cm['name'] = trim($names[array_rand($names)]);
    } elseif ($cm['race'] == 'Vulcan') {
        $cm['name'] = generate_vulcan_name($cm['gender']);
    } elseif ($cm['race'] == 'Andorian') {
        $cm['name'] = generate_andorian_name();
    } else {
        $names = file($cm['gender']=='Male'?'resources/male_names.txt':'resources/female_names.txt');
        $cm['name'] = trim($names[array_rand($names)]);
    }
    $cm['referrers'] = ['you' => $cm['name'], 'youare' => $cm['name'].' is', 'your' => $cm['name']."'s"];

    return $cm;
}

function generate_vulcan_name($gender) {
    $c = ["b","c","d","f","g","h","j","k","l","m","n","p","q","r","s","t","v","x","z"];
    $v = ["a","e","i","o","u"];

    if (strtolower($gender) == 'male') {
        return 'S'.$v[rand(0,4)].$c[rand(0,18)].$v[rand(0,4)].'k';
    } else {
        $b = array_merge($c,$v);
        return "T'P".$v[rand(0,4)].$b[rand(0,23)];
    }
}

function generate_andorian_name() {
    $c = ["g","h","k","l","m","n","p","r","s","t","v"];
    $v = ["a","e","i","o","u"];
    $s = ["Ta","Th","Sh","Ke"];

    if (rand(0,1) == 1) {
        return ['Th','Sh'][rand(0,1)].$v[rand(0,4)].$c[rand(0,10)].$v[rand(0,4)].$c[rand(0,10)];
    } else {
        return ['Ta','Ke'][rand(0,1)].$c[rand(0,10)].$v[rand(0,4)].$c[rand(0,10)];
    }
}
