<?php

require_once 'ff_basic.php';

class book_ff_magic extends book_ff_basic {
    public function getId() {
        return 'ff_magic';
    }


    public function getStats() {
        $stats = parent::getStats();
        $stats['magic'] = [
            'friendly' => 'Magic',
            'icons' => ':fireworks:',
            'roll' => 'ff2die',
            'display' => 'current_and_max',
        ];
        return $stats;
    }


    protected function getCharcterSheetAttachments() {
        $attachments = parent::getCharcterSheetAttachments();
        array_splice($attachments[0]['fields'], 3, 0,
            array([
                    'title' => 'Magic',
                    'value' => $this->player['magic']." / ".$this->player['max']['magic'],
                    'short' => true
                ])
        );
        return $attachments;
    }


    protected function getCharcterSheetAttachmentsNoMagic() {
        return parent::getCharcterSheetAttachments();
    }


    protected function getSpells() {
        $spells[] = array(
            'name' => 'Luck',
            'cost' => 1,
            'type' => 'self',
            'target' => false,
            'desc' => "This spell will restore your Luck score by half of it's maximum value. This spell is special in that it may be cast at any time during your adventure, except in a battle. You need not wait for a choice to appear on the page.",
            'reply' => 'You cast the luck spell...',
            'addcmd' => ($this->player?'luck +'.ceil($this->player['max']['luck']/2):'')
        );

        $spells[] = array(
            'name' => 'Skill',
            'cost' => 1,
            'type' => 'self',
            'target' => false,
            'desc' => "This spell will restore your Skill score by half of it's maximum value. This spell is special in that it may be cast at any time during your adventure, except in a battle. You need not wait for a choice to appear on the page.",
            'reply' => 'You cast the skill spell...',
            'addcmd' => ($this->player?'skill +'.ceil($this->player['max']['skill']/2):'')
        );

        $spells[] = array(
            'name' => 'Stamina',
            'cost' => 1,
            'type' => 'self',
            'target' => false,
            'desc' => "This spell will restore your Stamina score by half of it's maximum value. This spell is special in that it may be cast at any time during your adventure, except in a battle. You need not wait for a choice to appear on the page.",
            'reply' => 'You cast the stamina spell...',
            'addcmd' => ($this->player?'stam +'.ceil($this->player['max']['stam']/2):'')
        );

        return $spells;
    }


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('spellbook', '_cmd_spellbook', ['osl']);
        $spellsregex = "\s+(";
        foreach ($this->getSpells() as $s) {
            $spellsregex .= preg_quote($s['name']).'|';
        }
        $spellsregex = substr($spellsregex, 0, -1).")";
        $this->registerCommand('cast', '_cmd_cast', [$spellsregex, 'oms', 'on', 'on']);
    }


    //// !spellbook - read spellbook
    public function _cmd_spellbook($cmd) {
        $spells = $this->getSpells();

        $typeslist = array();
        foreach ($spells as $s) {
            $typeslist[] = $s['type'];
        }
        $typeslist = array_unique($typeslist);
        $pagesize = 4;
        $total = ceil(count($spells)/$pagesize);

        $in = strtolower($cmd[1]);

        if ($in == 'all') {
            $out = "_*— All Spells —*_\n";
            $list = $spells;
            usort($list, function($a, $b) {
                    return strcmp($a["name"], $b["name"]);
                });
        } elseif (in_array($in, $typeslist)) {
            $out = "_*— ".ucfirst($in)." Spells —*_\n";
            $list = array_filter($spells, function($v) use ($in) {
                    return $v['type'] == $in;
                });
            usort($list, function($a, $b) {
                    if ($a['cost'] == $b['cost']) { return strcmp($a["name"], $b["name"]); }
                    return ($a['cost'] < $b['cost']) ? -1 : 1;
                });
        } elseif (is_numeric($in)) {
            if ($in < 1 || $in > $total) {
                $in = 1;
            }
            $out = "_*— PAGE $in of $total —*_\n";
            usort($spells, function($a, $b) {
                    return strcmp($a["name"], $b["name"]);
                });
            $list = array_slice($spells, ($in-1)*$pagesize, $pagesize);
        } else {
            $out = "_*— Spellbook Contents —*_\n";
            $out .= "By Page: `!spellbook 1` ... `!spellbook $total`\n";
            $out .= "By Type: ";
            foreach ($typeslist as $t) {
                $out .= "`!spellbook $t`, ";
            }
            $out = substr($out, 0, -2)."\n";
            $out .= "Everything: `!spellbook all`\n";
            $list = array();
        }

        foreach ($list as $s) {
            $out .= "*".$s['name']."* _(".($s['cost']>0?"Cost: ".$s['cost']." Magic, ":"").($s['target']?"Requires Target, ":"")."Type: ".ucfirst($s['type']).")_\n";
            $out .= wordwrap($s['desc'], 100)."\n\n";
        }

        // Turn the params back in to one string
        sendqmsg($out, ':green_book:');
    }


    //// !echo - simply repeat the input text
    public function _cmd_cast($cmd) {
        $player = &$this->player;
        $spells = $this->getSpells();

        foreach ($spells as $s) {
            if (strtolower($s['name']) == strtolower($cmd[1])) {
                break;
            }
        }

        if ($s['cost'] > 0 && $player['magic'] < $s['cost']) {
            sendqmsg("*You don't have ".$s['cost']." Magic to spend!*", ':interrobang:');
        } elseif ($s['target'] && (!$cmd[3] || !$cmd[4])) {
            sendqmsg("*This spell requires a target!* e.g. `!cast ".$s['name']." Monster 6 7`", ':interrobang:');
        } else {
            // Deal with cost of magic
            if ($s['cost'] > 0) {
                $player['magic'] -= $s['cost'];
            }
            // Send reply if given
            if (isset($s['reply'])) {
                sendqmsg("> ".$s['reply'], ':fireworks:');
            }
            // Deal with the spell based on type
            if (isset($s['func'])) {
                if ($s['target']) {
                    call_user_func([$this, $s['func']], ($cmd[2]?$cmd[2]:'Opponent'), $cmd[3], $cmd[4]);
                } else {
                    call_user_func([$this, $s['func']]);
                }
            } elseif (isset($s['addcmd'])) {
                $this->addCommand($s['addcmd']);
            }
        }
    }


}
