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

        $file = 'saves/save_'.$config->book_id.($tag!==false?'_'.$tag:'').'.txt';
        if (!file_exists($file)) {
            $this->player = $this->newCharacter();
        } else {
            $this->player = unserialize(file_get_contents($file));
        }
    }


    // Serialize and save player array
    public function savePlayer($tag = false) {
        global $config;

        $file = 'saves/save_'.$config->book_id.($tag!==false?'_'.$tag:'').'.txt';
        file_put_contents($file, serialize($this->player));
    }


    protected function newCharacter() {
        return [];
    }


    public function processCommandList() {
        global $config;

        $commandqueue = &$this->commandqueue;

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


    // Take a command with params and determine if it is on the disabled list
    protected function isCommandDisabled($str) {
        global $config;

        if (sizeof($config->disabled_commands) < 1) {
            return false;
        }

        // Split command from params
        $parts = preg_split('/\s+/', strtolower($str));
        $cmd = $parts[0];
        // Look for match
        foreach ($config->disabled_commands as $dc) {
            if ($cmd == strtolower($dc)) {
                return true;
            }
        }

        return false;
    }


    // Adds a new command to the command list
    public function addCommand($cmd, $end = false, $safe = true) {
        if ($safe) {
            if ($this->isCommandDisabled($cmd)) {
                return;
            }
        }

        if ($end) {
            array_push($this->commandqueue, $cmd);
        } else {
            array_unshift($this->commandqueue, $cmd);
        }
    }


    protected function registerCommands() {
    }


    protected function registerCommand($name, $function, $args = []) {
        if (is_array($name)) {
            $aliases = $name;
            $name = array_shift($aliases);
        } else {
            $aliases = [];
        }
        $this->commands[$name] = array(
            'func' => $function,
            'args' => $args
        );
        foreach ($aliases as $a) {
            $this->commands[$a] = $this->commands[$name];
        }
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
            case 'omsg':  //optional multi string (very hard, greedy)
                $regex .= "(\s+[a-zA-Z ']+)?";
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
            '/{(([1-9]?[0-9])?d((?:[1-9][0-9]{0,2}|%))?([+|\-][1-9][0-9]{0,2})?)}/',
            function ($matches) {
                return roll_dice_string($matches[1])[0];
            },
            $command
        );

        // magic to substitute player vars
        // keep $sa outside the func so we don't have to rebuild
        $sa = array();
        $command = preg_replace_callback(
            '/{(.+?)}/',
            function ($matches) use ($sa) {
                // build substitute array
                if (!$sa) {
                    recursive_flatten_player($this->player, $sa);
                }
                if (array_key_exists($matches[1], $sa)) {
                    if (is_bool($sa[$matches[1]])) {
                        return $sa[$matches[1]]?'on':'off';
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
