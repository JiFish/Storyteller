<?php

if (!isset($argv[1])) {
    die("Usage: bookconvert.php [book.txt]");
}

if (!file_exists($argv[1])) {
    die ("File not found.");
}

$book = array();
$input = file($argv[1]);
$page = "";
$expected_num = 1;

foreach ($input as $line) {
    $line = trim($line);
    if ($line == "") { continue; }
    if (is_numeric($line)) {
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

echo "<?php\n\n";
echo '$book = '.var_export($book, 1).";";
