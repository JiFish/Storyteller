<?php

function run_fight(&$player, $m, $mskill, $mstam = 999, $maxrounds = 50, $critsfor = 'nobody', $critchance = 2, $m2 = null, $mskill2 = null, $bonusdmg = 0, $fasthands = false) {
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
        $mroll = rand(1,6);
        $proll = rand(1,6);
        $memoji = diceemoji($mroll);
        $pemoji = diceemoji($proll);

        $mattack = $mskill+$mroll;
        $pattack = $player['skill']+$player['weapon']+$proll;

        // Fast hands gives 1 extra dice, drop lowest for attack power
        if ($fasthands) {
            $fhroll = rand(1,6);
            if ($fhroll > $proll) {
                $pattack = $player['skill']+$player['weapon']+$fhroll;
                $pemoji = "~".$pemoji."~ :fireworks:".diceemoji($fhroll);
            } else {
                $pemoji .= " ~:fireworks:".diceemoji($fhroll)."~";
            }
            if ($round >= 3) {
                $fasthands = false;
            }
        }

        if ($critsfor != 'nobody') {
            $croll = rand(1,6);
            $cemoji = diceemoji($croll);
        }

        if ($pattack > $mattack) {
            $out .= "_You hit $m. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)";
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
            $out .= "_$m hits you! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)";
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
                    $out .= " :shield: Your shield reduces the damage by 1! (_ ".diceemoji(6)." _) ";
                    $player['stam'] += 1;
                }
                $out .= "_\n";
                $player['stam'] -= 2;
            }
            if ($stop_when_hit_you) { break; }
        }
        else {
            $out .= "_You avoid each others blows. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
        }

        // Monster 2 attack
        if ($m2) {
            $mroll = rand(1,6);
            $proll = rand(1,6);
            $mattack = $mskill2+$mroll;
            $pattack = $player['skill']+$player['weapon']+$proll;

            $memoji = diceemoji($mroll);
            $pemoji = diceemoji($proll);

            if ($pattack > $mattack) {
                $out .= "_You block $m2's attack. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            }
            else if ($pattack < $mattack) {
                $out .= "_$m2 hits you! (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
                $player['stam'] -= 2;
                if ($stop_when_hit_you) { break; }
            }
            else {
                $out .= "_You avoid $m2's attack. (_ $pemoji _ $pattack vs _ $memoji _ $mattack)_\n";
            }
        }

        // Bonus damage
        if ($bonusdmg && $mstam > 0) {
            $bdroll = rand(1,6);
            if ($bdroll > 3) {
                $bdemoji = diceemoji($bdroll);
                $out .= "_$m hits you with ".$bonusdmg." bonus damage! (_ $bdemoji _)_\n";
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
                $out .= " You are lucky!_ :four_leaf_clover: ( $e1 $e2 )\n";
                $player['stam'] += 1;
            } else {
                $out .= " You are unlucky!_ :lightning: ( $e1 $e2 )\n";
                $player['stam'] -= 1;
            }
            $player['luck']--;
        }

        if ($round == $maxrounds) {
            break;
        }
    }

    if ($player['stam'] < 1) {
        $out .= "_*$m has defeated you!*_\n";
    } elseif ($mstam < 1) {
        $out .= "_*You have defeated $m!*_\n";
        $out .= "_(Remaining stamina: ".$player['stam'].")_";
    } else {
        if ($round > 1) {
            $out .= "_*Combat stopped after $round rounds.*_\n";
        }
        $out .= "_($m's remaining stamina: $mstam. Your remaining stamina: ".$player['stam'].")_";
    }

    // Remove temp bonuses, if any and clear temp bonus array
    unapply_temp_stats($player);

    return $out;
}

function run_single_attack(&$player, $mname, $mskill, $mstam, $mdamage = 2, $pdamage = 2)
{
    // Apply temp bonuses, if any
    apply_temp_stats($player);

    $mroll = rand(1,6);
    $proll = rand(1,6);
    $mattack = $mskill+$mroll;
    $pattack = $player['skill']+$player['weapon']+$proll;

    $memoji = diceemoji($mroll);
    $pemoji = diceemoji($proll);

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
