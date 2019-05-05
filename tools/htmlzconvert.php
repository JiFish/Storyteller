<?php

if (!isset($argv[1]) || !isset($argv[2])) {
    die("Usage: bookconvert.php [book.htmlz] [bookid]\n(bookid will be used for output filenames)");
}

require 'bookconvert.php';

$path = $argv[1];
$bookid = $argv[2];
$downloadpics = 1;

echo "Converting $bookid\n";
$localimgbase = dirname(__FILE__)."/../images/$bookid/";

$zip = new ZipArchive;
$res = $zip->open($path);
if ($res !== TRUE) {
    die("Couldn't open $path");
}

echo 'Fetching Text...'.PHP_EOL;
$book = $zip->getFromName('index.html');

if ($downloadpics) {
    $book = strip_tags($book, '<img>');
} else {
    $book = strip_tags($book);
}
$book = convert_text($book);
foreach ($book as $k => $p) {
    $book[$k] = html_entity_decode($book[$k]);
}

if ($downloadpics) {
    //look for images
    $madeoutputdir = false;
    $re = '/<img .*?src="(.+?)".*?\/>/';
    $dupestore = [];
    foreach ($book as $k => $p) {
        if (preg_match_all($re, $p, $matches, PREG_SET_ORDER) === 1) {
            $local = $localimgbase.$k.".png";
            // Pull image down
            if (!file_exists($local)) {
                // Create output dir if needed
                if (!$madeoutputdir) {
                    if (!is_dir($localimgbase)) {
                        mkdir($localimgbase);
                        $localimgbase = realpath($localimgbase);
                        echo "Creating image directory at $localimgbase".PHP_EOL;
                    }
                    $madeoutputdir = true;
                }
                $remote = $matches[0][1];
                echo "Extracting Image for page $k...".PHP_EOL;
                file_put_contents($local, $zip->getFromName($remote));
            }
        }
        // Remove Tags
        $book[$k] = preg_replace($re, '', $p);
    }
}

echo "Exporting book to books/$bookid.php...".PHP_EOL;
file_put_contents(dirname(__FILE__)."/../books/$bookid.php", "<?php\n\n\$book = ".var_export($book, 1).";");
$zip->close();
