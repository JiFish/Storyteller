<?php

ini_set('mbstring.substitute_character', "none");

// If we were called directly
if ( basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]) ) {
    if (!isset($argv[1])) {
        die("Usage: bookconvert.php [book.txt]");
    }

    if (!file_exists($argv[1])) {
        die ("File not found.");
    }

    $book = convert_text(file_get_contents($argv[1]));

    echo "<?php\n\n";
    echo '$book = '.var_export($book, 1).";";
}

// Outputs assoc array
function convert_text($text) {
    $text = mb_convert_encoding($text, 'UTF-8', "auto");
    $text = preg_split('/\r\n|\r|\n/', $text);
    $page = "";
    $expected_num = 1;
    foreach ($text as $line) {
        $line = trim($line);
        if ($line == "") { continue; }
        if (ctype_digit($line)) {
            if ($line != $expected_num) {
                die("Unexpected page number. Expected $expected_num, got $line");
            }
            $book[] = trim($page);
            $page = "";
            $expected_num++;
            continue;
        }
        $page .= $line . "\n";
    }
    $book[] = trim($page);
    return $book;
}
