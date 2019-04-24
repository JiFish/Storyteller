<?php

require_once 'character.php';

abstract class book_character_importable extends book_character {
    protected function rollCharacter($name = '?', $gender = '?', $emoji = '?', $race = '?', $adjective = '?') {
        $p = parent::rollCharacter($name, $gender, $emoji, $race, $adjective);
        $p['ruleset'] = get_class($this);
        return $p;
    }


    protected function registerCommands() {
        parent::registerCommands();
        $this->registerCommand('import', '_cmd_import', ['os']);
    }


    //// !import (book) - import character from another book
    protected function _cmd_import($cmd) {
        global $config;

        $loadbook = $cmd[1];
        $books = [];

        foreach ($config->books as $bkey => $bval) {
            if ($config->book_id != $bkey) {
                $fn = "saves/save_$bkey.txt";
                if (file_exists($fn)) {
                    $lp = unserialize(file_get_contents($fn));
                    if ($this->isImportValid($lp)) {
                        $books[$bkey] = "*".$lp['name'].'* in _'.$bval['name']."_ (`!import $bkey`)";
                    }
                }
            }
        }
        if (sizeof($books) < 1) {
            sendqmsg("No saves to import from.", ':interrobang:');
            return;
        }
        if (!$loadbook) {
            natcasesort($books);
            sendqmsg("Import choices are:\n".implode("\n", $books), ':family:');
            return;
        }
        if (!array_key_exists($loadbook, $books)) {
            natcasesort($books);
            sendqmsg("*'$loadbook' isn't a valid book.* Choices are:\n".implode("\n", $books), ':family:');
            return;
        }
        $this->player = unserialize(file_get_contents("saves/save_$loadbook.txt"));
        $this->runImportUpdate($this->player);
        // Update rules tag
        $this->player['ruleset'] = get_class($this);
        sendqmsg($this->player['name'].' imported!', $this->player['emoji']);
        $this->addCommand('info');
    }


    protected function isImportValid(&$p) {
        if (isset($p['ruleset'])) {
            if ($p['ruleset'] == get_class($this)) {
                return true;
            }
        }
        return false;
    }


    protected function runImportUpdate(&$p) { }


}
