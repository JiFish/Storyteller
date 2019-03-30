<?php

class gamebook_base {
    protected $player = null;
    protected $commandqueue = [];


    public function __construct(&$player) {
        $this->player = &$player;
        $this->registerCommands();
        $this->loadPlayer();
    }
    
    
    // Load the player array from a serialized array
    // If we can't find the file, generate a new character
    protected function loadPlayer($file = 'save.txt') {
        if (!file_exists($file)) {
            $this->player = $this->newCharacter();
        } else {
            $this->player = unserialize(file_get_contents($file));
        }
    }
    
    
     // Serialize and save player array
    public function savePlayer($file="save.txt") {
        file_put_contents($file, serialize($this->player));
    }


    public function getId() {
        return null;
    }


    public function newCharacter() {
        return [];
    }


    public function processCommandList($commandqueue) {
        global $disabledcommands;

        $this->commandqueue = $commandqueue;
        $commandqueue = &$this->commandqueue;
        
        // Filter commands using the disabled list. Do this here so macros and $autorun
        // can still use disabled commands
        $this->filterCommandList();

        $executions = 0;
        while (sizeof($commandqueue) > 0) {
            // Process the next command in the list
            processcommand(array_shift($commandqueue), $this->player);

            // If stamina ever drops to less than 1, the player if dead
            // Stop processing any queued commands and tell the player they are dead
            if ($this->isDead()) {
                if (isset($this->player['referrers'])) {
                    sendqmsg("_*".$this->player['referrers']['youare']." dead.*_ :skull:", ":skull:");
                } else {
                    sendqmsg("_*You are dead.*_ :skull:", ":skull:");
                }
                break;
            }

            // Stop processing the queue after MAX_EXECUTIONS
            if ($executions++ > MAX_EXECUTIONS) {
                break;
            }
        }
    }


    // Filter an array of commands by a disabled list and remove entries
    // that are on the disabled list
    protected function filterCommandList() {
        global $disabledcommands;

        if (isset($disabledcommands) && is_array($disabledcommands)) {
            foreach ($this->commandqueue as $key => $cmd) {
                // Determine end of command word.
                // Either the 1st space of the end of the string
                $cmdend = stripos($cmd, ' ');
                if ($cmdend === false) {
                    $cmdend = strlen($cmd);
                }
                foreach ($disabledcommands as $dc) {
                    // Look for match
                    if (strtolower(substr($cmd, 0, $cmdend)) == strtolower($dc)) {
                        unset($this->commandqueue[$key]);
                        break;
                    }
                }
            }
        }
    }


    // Adds a new command to the command list
    protected function addCommand($cmd) {
        return array_unshift($this->commandqueue, $cmd);
    }


    protected function registerCommands() {
    }
}
