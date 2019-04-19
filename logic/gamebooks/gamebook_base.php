<?php

abstract class gamebook_base {
    protected $player = null;
    protected $commandqueue = [];
    protected $commands = [];


    public function __construct(&$player) {
        $this->player = &$player;
        $this->registerCommands();
        $this->loadPlayer();
    }


    // Load the player array from a serialized array
    // If we can't find the file, generate a new character
    protected function loadPlayer($tag = false) {
        global $config;

        $file = 'saves/save_'.$config->book_id.($tag?'_'.$tag:'').'.txt';
        if (!file_exists($file)) {
            $this->player = $this->newCharacter();
        } else {
            $this->player = unserialize(file_get_contents($file));
        }
    }


    // Serialize and save player array
    public function savePlayer($tag = false) {
        global $config;

        $file = 'saves/save_'.$config->book_id.($tag?'_'.$tag:'').'.txt';
        file_put_contents($file, serialize($this->player));
    }


    protected function newCharacter() {
        return [];
    }


    public function processCommandList($commandqueue) {
        global $config;

        $this->commandqueue = $commandqueue;
        $commandqueue = &$this->commandqueue;

        // Filter commands using the disabled list. Do this here so macros and $autorun
        // can still use disabled commands
        $this->filterCommandList();

        $executions = 0;
        while (sizeof($commandqueue) > 0) {
            // Process the next command in the list
            $this->processCommand(array_shift($commandqueue));

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
            if ($executions++ > $config->max_executions) {
                break;
            }
        }
    }


    // Filter an array of commands by a disabled list and remove entries
    // that are on the disabled list
    protected function filterCommandList() {
        global $config;

        if (sizeof($config->disabled_commands) > 0) {
            foreach ($this->commandqueue as $key => $cmd) {
                // Determine end of command word.
                // Either the 1st space of the end of the string
                $cmdend = stripos($cmd, ' ');
                if ($cmdend === false) {
                    $cmdend = strlen($cmd);
                }
                foreach ($config->disabled_commands as $dc) {
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


    protected function registerCommand($name, $function, $args = []) {
        $this->commands[$name] = array(
            'func' => $function,
            'args' => $args
        );
    }


    // Process command text and call command's function
    protected function processCommand($command) {
        $command = $this->preProcessesMagic($command);

        // Split by whitespace
        // $cmd[0] is the command
        $cmd = preg_split('/\s+/', trim($command));
        $cmd[0] = trim(strtolower($cmd[0]));

        // look for a command function to call
        if (array_key_exists($cmd[0], $this->commands)) {
            $cmd = $this->advancedCommandSplit($command, $this->commands[$cmd[0]]['args']);
            if (!$cmd) {
                sendqmsg("Sorry, I didn't understand that command!", ":interrobang:");
            } else {
                call_user_func_array([$this, $this->commands[$cmd[0]]['func']], array($cmd));
            }
        }
    }


    protected function advancedCommandSplit($command, $def) {
        $regex = "/^\\s*(\\S+)";
        foreach ($def as $d) {
            switch ($d) {
            case 'l':  //whole line
                $regex .= "\s+(.+)";
                break;
            case 'ol':  //optional whole line
                $regex .= "(\s+.+)?";
                break;
            case 'oms':  //optional multi string (hard, doesn't match numbers)
                $regex .= "(\s+(?![0-9]+).+?)?";
                break;
            case 'ms':  //multi string (hard, doesn't match numbers)
                $regex .= "\s+((?![0-9]+).+?)";
                break;
            case 'osl':  //optional string (loose, matches numbers)
                $regex .= "(\s+[^\s]+)?";
                break;
            case 'os':  //optional string (hard, doesn't match numbers)
                $regex .= "(\s+(?![0-9]+)[^\s]+)?";
                break;
            case 's':  //string (loose, matches numbers)
                $regex .= "\s+([^\s]+)";
                break;
            case 'on':  //optional number
                $regex .= "(\s+[0-9]+)?";
                break;
            case 'n':  //number
                $regex .= "\s+([0-9]+)";
                break;
            case 'onm':  //optional number modifier
                $regex .= "(\s+[+\-][0-9]+)?";
                break;
            case 'nm':  //number modifier
                $regex .= "\s+([+\-]?[0-9]+)";
                break;
            default:  //misc
                $regex .= $d;
                break;
            }
        }
        $regex .= '\s*$/i';
        $matches = array();

        if (!preg_match($regex, $command, $matches)) {
            return false;
        }

        array_shift($matches);
        $matches = array_map('trim', $matches);
        $matches = array_pad($matches, sizeof($def)+1, null);
        //print_r($matches);
        return $matches;
    }


    private function preProcessesMagic($command) {
        // magic to allow semi-colons
        $command = str_replace("{sc}", ";", $command);

        // magic to substitute dice rolls
        $command = preg_replace_callback(
            '/{([1-9][0-9]?)d([1-9][0-9]{0,2})?([+|\-][1-9][0-9]{0,2})?}/',
            function ($matches) {
                $roll = 0;
                if (!isset($matches[2]) || !$matches[2]) {
                    $matches[2] = 6;
                }
                foreach (range(1, $matches[1]) as $i) {
                    $roll += rand(1, $matches[2]);
                }
                if (isset($matches[3])) {
                    $roll += $matches[3];
                }
                return $roll;
            },
            $command
        );

        // magic to substitute player vars
        // build substitute array
        $sa = array();
        recursive_flatten_player($this->player, $sa);
        // perform substitution
        $command = preg_replace_callback(
            '/{(.+?)}/',
            function ($matches) use ($sa) {
                if (array_key_exists($matches[1], $sa)) {
                    if (is_bool($sa[$matches[1]])) {
                        return $sa[$matches[1]]?'yes':'no';
                    }
                    return $sa[$matches[1]];
                }
                return $matches[0];
            },
            $command
        );

        return $command;
    }


}
