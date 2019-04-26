<?php

$config = new Config();

class Config {
    private $ini;
    public $books;
    public $slack_hook;
    public $slack_token;
    public $default_book;
    public $character_rolls;
    public $disabled_commands;
    public $discord_mode;
    public $max_executions;
    public $book_id;
    public $book_name;
    public $book_file;
    public $book_rules;
    public $book_images_dir;

    public function __construct($fn = 'config.ini') {
        if (!file_exists($fn)) {
            die("Config file $fn: not found.");
        }
        $this->ini = parse_ini_file($fn, true);

        // Check sections defined
        if (!array_key_exists('general', $this->ini)) {
            die("Config file $fn: missing [general] section.");
        }
        if (!array_key_exists('slack', $this->ini)) {
            die("Config file $fn: missing [slack] section.");
        }
        if (sizeof($this->ini) < 3) {
            die("Config file $fn: no books defined.");
        }

        // Load slack section
        $this->slack_hook = $this->get('slack_hook', '', 'slack');
        $this->slack_token = $this->get('slack_token', '', 'slack');
        $this->discord_mode = $this->get('discord_mode', 'slack');

        // Load general section
        $this->default_book = $this->get('default_book');
        $this->character_rolls = $this->get('character_rolls', 'normal');
        $this->disabled_commands = $this->get('disable_cmd', []);
        $this->max_executions = $this->get('max_executions', 30);
        $this->root = $this->get('override_root', null);
        if ($this->root === null) {
            $this->root = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']);
        }

        // Load book sections
        foreach ($this->ini as $key => $val) {
            if ($key != 'general' && $key != 'slack') {
                $this->books[strtolower($key)] = $this->loadBook($key);
            }
        }

        $this->getBookSetting();
    }


    private function get($setting, $default = false, $section = 'general') {
        if (!isset($this->ini[$section][$setting])) {
            return $default;
        }

        return $this->ini[$section][$setting];
    }


    private function loadBook($bookid) {
        $out = [];
        $out['name'] = $this->get('name', 'Unnamed Book', $bookid);
        $out['file'] = $this->get('file', null, $bookid);
        $out['rules'] = $this->get('rules', 'none', $bookid);
        $out['images_dir'] = $this->get('images_dir', $bookid, $bookid);
        $out['group'] = $this->get('group', 'none', $bookid);

        if (!file_exists($out['file'])) {
            die("ERROR in $bookid: book file '".$out['file']."' not found!");
        }
        if (!file_exists('logic/rulesets/'.$out['rules'].'.php')) {
            die("ERROR in $bookid: rules '".$out['rules']."' doesn't exist!");
        }

        return $out;
    }


    private function getBookSetting() {
        if (!file_exists('current_bookid.txt')) {
            $bookid = $this->default_book;
        } else {
            $bookid = file_get_contents('current_bookid.txt');
        }
        if (!array_key_exists($bookid, $this->books)) {
            return $this->changeBookSetting();
        }

        $this->book_id = $bookid;
        $this->book_name = $this->books[$bookid]['name'];
        $this->book_file = $this->books[$bookid]['file'];
        $this->book_rules = $this->books[$bookid]['rules'];
        $this->book_images_dir = $this->books[$bookid]['images_dir'];
    }


    public function changeBookSetting($bookid = null) {
        if (!array_key_exists($bookid, $this->books)) {
            $bookid = array_keys($this->books)[0];
        }

        $this->book_id = $bookid;
        $this->book_name = $this->books[$bookid]['name'];
        $this->book_file = $this->books[$bookid]['file'];
        $this->book_rules = $this->books[$bookid]['rules'];
        $this->book_image_dir = $this->books[$bookid]['images_dir'];

        file_put_contents('current_bookid.txt', $bookid);
    }


}
