<?php

function run_fight($input) {
    // Inputs
    if (!isset($input['player'])) return false;
    if (!isset($input['monstername'])) return false;
    if (!isset($input['monsterskill'])) return false;
    $player = &$input['player'];
    $m = $input['monstername'];
    $mskill = &$input['monsterskill'];
    $mstam =          (isset($input['monsterstam'])?   $input['monsterstam']:    999);
    $maxrounds =      (isset($input['maxrounds'])?     $input['maxrounds']:      50);
    $critsfor =       (isset($input['critsfor'])?      $input['critsfor']:       'nobody');
    $critchance =     (isset($input['critchance'])?    $input['critchance']:     2);
    $m2 =             (isset($input['monster2name'])?  $input['monster2name']:   null);
    $mskill2 =        (isset($input['monster2skill'])? $input['monster2skill']:  null);
    $backupname =     (isset($input['backupname'])?    $input['backupname']:   null);
    $backupskill =    (isset($input['backupskill'])?   $input['backupskill']:  null);
    $bonusdmg =       (isset($input['bonusdmg'])?      $input['bonusdmg']:       0);
    $bonusdmgchance = (isset($input['bonusdmgchance'])?$input['bonusdmgchance']: 3);
    $fasthands =      (isset($input['fasthands'])?     $input['fasthands']:      false);
    $healthstatname = (isset($input['healthstatname'])?$input['healthstatname']: 'stamina');
    $gamebook = getbook();

    // Special case for Starship Traveller Macommonian
    if ($gamebook == 'sst' && $player['race'] == 'Macommonian') {
        $fasthands = true;
    }

    // Referrers
    if (isset($player['referrers'])) {
        $referrers = $player['referrers'];
    } else {
        $referrers = ['you' => 'you', 'youare' => 'you are', 'your' => 'your'];
    }
    $you = ucfirst($referrers['you']);
    $youlc = $referrers['you'];
    $youare = ucfirst($referrers['youare']);
    $your = ucfirst($referrers['your']);

    // Prevent restore
    backup_remove();

    // Apply temp bonuses, if any
    apply_temp_stats($player);

    // Process maxrounds special cases
    $stop_when_hit_you = false;
    $stop_when_hit_them = false;
    if (strtolower($maxrounds) == 'hitme') {
        $stop_when_hit_you = true;
    } elseif (strtolower($maxrounds) == 'hitthem') {
        $stop_when_hit_them = true;
    } elseif (strtolower($maxrounds) == 'hitany') {
        $stop_when_hit_you = true;
        $stop_when_hit_them = true;
    }
    if (!is_numeric($maxrounds)) {
        $maxrounds = 50;
    }

    $out = "";
    $round = 0;
    while ($player['stam'] > 0 && $mstam > 0) {
        $round++;
        $mroll = rand(1,6); $mroll2 = rand(1,6);
        $proll = rand(1,6); $proll2 = rand(1,6);
        $memoji = diceemoji($mroll).diceemoji($mroll2);
        $pemoji = diceemoji($proll).diceemoji($proll2);

        $mattack = $mskill+$mroll+$mroll2;
        $pattack = $player['skill']+$player['weapon']+$proll+$proll2;

        // Special case for Creature of Havok instant kills
        if ($gamebook == 'coh' && $proll == $proll2) {
            $out .= "_*Instant Kill*_ $pemoji\n";
            $mstam = 0;
            break;
        }

        // Fast hands gives 1 extra dice, drop lowest for attack power
        if ($fasthands) {
            $fhroll  = rand(1,6);
            $fhroll2 = rand(1,6);
            if ($fhroll+$fhroll2 > $proll+$proll2) {
                $pattack = $player['skill']+$player['weapon']+$fhroll;
                $pemoji = "~".$pemoji."~ / ".diceemoji($fhroll).diceemoji($fhroll2);
            } else {
                $pemoji .= " / ~".diceemoji($fhroll).diceemoji($fhroll2)."~";
            }
            if ($round >= 3 && !($gamebook == 'sst' && $player['race'] == 'Macommonian')) {
                $fasthands = false;
            }
        }

        if ($critsfor != 'nobody') {
            $croll = rand(1,6);
            $cemoji = diceemoji($croll);
        }

        if ($pattack > $mattack) {
            $out .= "_$you hit $m. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)";
            if ($critsfor == 'both' || $critsfor == 'me') {
                if ($croll > 6-$critchance) {
                    $out .= " *and it was a critical strike!*_ ($cemoji)\n";
                    $mstam = 0;
                    break;
                }
                else {
                    $out .= " but failed to get a critical strike._ ($cemoji)\n";
                }
            } else {
                $out .= "_\n";
                $mstam -= 2;
            }
            if ($stop_when_hit_them) { break; }
        }
        else if ($pattack < $mattack) {
            $out .= "_$m hits $youlc! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)";
            if ($critsfor == 'both') {
                if ($croll > 6-$critchance) {
                    $out .= " *and it was a critical strike!*_ ($cemoji)\n";
                    $player['stam'] = 0;
                    break;
                } else {
                    $out .= " but failed to get a critical strike._ ($cemoji)\n";
                }
            } else {
                if ($player['shield'] && rand(1,6) == 6) {
                    $out .= " :shield: $your shield reduces the damage by 1! (_ ".diceemoji(6)." _) ";
                    $player['stam'] += 1;
                }
                $out .= "_\n";
                $player['stam'] -= 2;
            }
            if ($stop_when_hit_you) { break; }
        }
        else {
            $out .= "_$you and $m avoid each others blows. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
        }

        // Monster 2 attack
        if ($m2) {
            $mroll = rand(1,6); $mroll2 = rand(1,6);
            $proll = rand(1,6); $proll2 = rand(1,6);
            $mattack = $mskill2+$mroll+$mroll2;
            $pattack = $player['skill']+$player['weapon']+$proll+$proll2;

            $memoji = diceemoji($mroll).diceemoji($mroll2);
            $pemoji = diceemoji($proll).diceemoji($proll2);

            if ($pattack > $mattack) {
                $out .= "_$you block $m2's attack. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            }
            else if ($pattack < $mattack) {
                $out .= "_$m2 hit  $youlc! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
                $player['stam'] -= 2;
                if ($stop_when_hit_you) { break; }
            }
            else {
                $out .= "_$m2's attack fails to hit $youlc. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            }
        }

        //  Your backup attack
        if ($backupname) {
            $mroll = rand(1,6); $mroll2 = rand(1,6);
            $proll = rand(1,6); $proll2 = rand(1,6);
            $mattack = $mskill+$mroll+$mroll2;
            $pattack = $backupskill+$proll+$proll2;

            $memoji = diceemoji($mroll).diceemoji($mroll2);
            $pemoji = diceemoji($proll).diceemoji($proll2);

            if ($pattack > $mattack) {
                $out .= "_$backupname hits $m! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
                $mstam -= 2;
                if ($stop_when_hit_them) { break; }
            }
            else if ($pattack < $mattack) {
                $out .= "_$m blocks the attack of $backupname! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            }
            else {
                 $out .= "_$backupname's attack fails to hit $m. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            }
        }

        // Bonus damage
        if ($bonusdmg && $mstam > 0) {
            $bdroll = rand(1,6);
            if ($bdroll > 6-$bonusdmgchance) {
                $bdemoji = ($bonusdmgchance < 6?'(_ '.diceemoji($bdroll).' _)':'');
                $out .= "_$m hits $youlc with ".$bonusdmg." bonus damage! $bdemoji _\n";
                $player['stam'] -= $bonusdmg;
            }
        }            

        //stave off death
        if ($player['stam'] == 0 && $player['luck'] > 0) {
            // roll 2d6
            $d1 = rand(1,6);
            $d2 = rand(1,6);
            $e1 = diceemoji($d1);
            $e2 = diceemoji($d2);
            $out .= "_Testing luck to stave off death... ";
            if ($d1+$d2 <= $player['luck']) {
                $out .= " $youare lucky!_ :four_leaf_clover: ( $e1 $e2 )\n";
                $player['stam'] += 1;
            } else {
                $out .= " $youare unlucky!_ :lightning: ( $e1 $e2 )\n";
                $player['stam'] -= 1;
            }
            $player['luck']--;
        }

        if ($round == $maxrounds) {
            break;
        }
    }

    if ($player['stam'] < 1) {
        $out .= "_*$m defeated $youlc!*_\n";
    } elseif ($mstam < 1) {
        $out .= "_*$you defeated $m!*_\n";
        $out .= "_($your remaining $healthstatname: ".$player['stam'].")_";
    } else {
        if ($round > 1) {
            $out .= "_*Combat stopped after $round rounds.*_\n";
        }
        $out .= "_($m's remaining $healthstatname: $mstam. $your remaining $healthstatname: ".$player['stam'].")_";
    }

    // Remove temp bonuses, if any and clear temp bonus array
    unapply_temp_stats($player);

    return $out;
}

function run_single_attack(&$player, $mname, $mskill, $mstam, $mdamage = 2, $pdamage = 2)
{
    // Apply temp bonuses, if any
    apply_temp_stats($player);

    $mroll = rand(1,6); $mroll2 = rand(1,6);
    $proll = rand(1,6); $proll2 = rand(1,6);
    $mattack = $mskill+$mroll+$mroll2;
    $pattack = $player['skill']+$player['weapon']+$proll+$proll2;

    $memoji = diceemoji($mroll).diceemoji($mroll2);
    $pemoji = diceemoji($proll).diceemoji($proll2);

    if ($pattack > $mattack) {
        $out = "_You hit $mname. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
        if ($pdamage > 0) {
            $mstam -= $pdamage;
            if ($mstam > 0) {
                $out .= "_($mname's remaining stamina: $mstam)_";
            } else {
                $out .= "_*You have defeated $mname!*_\n";
            }
        }
    }
    else if ($pattack < $mattack) {
        $out = "_$mname hits you! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
        if ($mdamage > 0) {
            $player['stam'] -= $mdamage;
            if ($player['stam'] > 0) {
                $out .= "_(Your remaining stamina: ".$player['stam'].")_";
            } else {
                $out .= "_*$mname has defeated you!*_\n";
            }
        }
    }
    else {
        $out = "_You avoid each others blows. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
    }

    // Remove temp bonuses, if any and clear temp bonus array
    unapply_temp_stats($player);

    return $out;
}

function run_phaser_fight($input) {
    // Inputs
    if (!isset($input['player'])) return false;
    if (!isset($input['monstername'])) return false;
    if (!isset($input['monsterskill'])) return false;
    $player = &$input['player'];
    $m = $input['monstername'];
    $mskill = &$input['monsterskill'];
    $maxrounds = (isset($input['maxrounds'])? $input['maxrounds']:             50);
    $modifier  = (isset($input['modifier'])?  $input['modifier']:              0);
    $stunkill  = (isset($input['stunkill'])?  strtolower($input['stunkill']):  'stun').'ed';
    $mstunkill = (isset($input['mstunkill'])? strtolower($input['mstunkill']): 'kill').'ed';

    // Referrers
    if (isset($player['referrers'])) {
        $referrers = $player['referrers'];
    } else {
        $referrers = ['you' => 'you', 'youare' => 'you are', 'your' => 'your'];
    }
    $you = ucfirst($referrers['you']);
    $your = ucfirst($referrers['your']);
    $youare = ucfirst($referrers['youare']);

    // Apply temp bonuses, if any
    apply_temp_stats($player);

    // Fight loop
    $out = "";
    $round = 0;
    while(true) {
        $round++;
        // Player
        $roll = rand(1,6); $roll2 = rand(1,6);
        $emoji = diceemoji($roll).diceemoji($roll2).($modifier?sprintf("%+d",$modifier):'');
        if (($roll+$roll2+$modifier) >= $player['skill']) {
            $out .= "_$your shot missed!_ ($emoji vs ".$player['skill'].")\n";
        } else {
            $out .= "_$your shot hit!_ ($emoji vs ".$player['skill'].")\n";
            $out .= "_*$you $stunkill $m!*_";
            break;
        }
        // Monster
        $roll = rand(1,6); $roll2 = rand(1,6);
        $emoji = diceemoji($roll).diceemoji($roll2);
        if (($roll+$roll2) >= $mskill) {
            $out .= "_$m's shot missed!_ ($emoji vs $mskill)\n";
        } else {
            $out .= "_$m's shot hit!_ ($emoji vs $mskill)\n";
            $out .= "_*$youare $mstunkill!*_";
            if ($mstunkill == 'killed') {
                $player['stam'] = 0;
            }
            break;
        }

        if ($round == $maxrounds) {
            $out .= "_*Combat stopped after $round rounds.*_\n";
            break;
        }
    }

    // Remove temp bonuses, if any and clear temp bonus array
    unapply_temp_stats($player);

    return $out;
}

function run_ship_battle($input) {
    // Inputs
    if (!isset($input['player'])) return false;
    if (!isset($input['oppname'])) return false;
    if (!isset($input['oppweapons'])) return false;
    if (!isset($input['oppshields'])) return false;
    $player = &$input['player'];
    $m = $input['oppname'];
    $mweapons = &$input['oppweapons'];
    $mshields = &$input['oppshields'];
    $maxrounds = (isset($input['maxrounds'])? $input['maxrounds']: 50);

    // Apply temp bonuses, if any
    apply_temp_stats($player);

    // Fight loop
    $out = "";
    $round = 0;
    while(true) {
        $round++;
        // Player
        // Roll to hit
        $roll = rand(1,6); $roll2 = rand(1,6);
        $emoji = diceemoji($roll).diceemoji($roll2);
        if ($roll+$roll2 < $player['weapons'])  {
            // Roll for damage
            $roll = rand(1,6); $roll2 = rand(1,6);
            $hemoji = diceemoji($roll).diceemoji($roll2);
            if ($roll+$roll2 == 12) {
                $out .= "_Your ship made a critical hit!_ ($emoji vs ".$player['weapons']." - $hemoji)\n";
                $mshields -= 6;
            } else if (($roll+$roll2) > $mshields) {
                $out .= "_Your ship hit with a glancing blow._ ($emoji vs ".$player['weapons']." - $hemoji vs $mshields)\n";
                $mshields -= 2;
            } else {
                $out .= "_Your ship made a good hit._ ($emoji vs ".$player['weapons']." - $hemoji vs $mshields)\n";
                $mshields -= 4;
            }
        } else {
            $out .= "_Your weapon fire misses._ ($emoji vs ".$player['weapons'].")\n";
        }
        // Check Opp is dead
        if ($mshields < 0) {
            $out .= "*$m were destroyed.*\n";
            $out .= "_(Your remaining shields: ".$player['shields'].")_";
            break;
        }
        // Monster
        // Roll to hit
        $roll = rand(1,6); $roll2 = rand(1,6);
        $emoji = diceemoji($roll).diceemoji($roll2);
        if ($roll+$roll2 < $mweapons)  {
            // Roll for damage
            $roll = rand(1,6); $roll2 = rand(1,6);
            $hemoji = diceemoji($roll).diceemoji($roll2);
            if ($roll+$roll2 == 12) {
                $out .= "_$m made a critical hit!_ ($emoji vs $mweapons - $hemoji)\n";
                $player['shields'] -= 6;
            } else if (($roll+$roll2) > $player['shields']) {
                $out .= "_$m hit you with a glancing blow._ ($emoji vs $mweapons - $hemoji vs ".$player['shields'].")\n";
                $player['shields'] -= 2;
            } else {
                $out .= "_$m made a good hit against you._ ($emoji vs $mweapons - $hemoji vs ".$player['shields'].")\n";
                $player['shields'] -= 4;
            }
        } else {
            $out .= "_$m's weapon fire misses._ ($emoji vs $mweapons)\n";
        }
        // Check Opp is dead
        if ($player['shields'] < 0) {
            $out .= "*Your ship has been destroyed!*\n";
            $player['stam'] = 0;
            break;
        }

        if ($round == $maxrounds) {
            $out .= "_*Combat stopped after $round rounds.*_\n";
            $out .= "_($m's remaining shields: $mshields. Your remaining shields: ".$player['shields'].")_";
            break;
        }
    }

    // Remove temp bonuses, if any and clear temp bonus array
    unapply_temp_stats($player);

    return $out;
}
