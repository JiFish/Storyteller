<?php

chdir(dirname(__FILE__));

require 'bookconvert.php';

$configfn = '../config.ini';
if (!file_exists($configfn)) {
    die("Config file $configfn: not found.");
}
$config = parse_ini_file($configfn, true);

$booklist = [
    //   id       aon id        title                         group
    ['lw01', 'lw/01fftd',  'Flight from the Dark',       'lw_kai',      'Lone Wolf',  1],
    ['lw02', 'lw/02fotw',  'Fire on the Water',          'lw_kai',      'Lone Wolf',  2],
    ['lw03', 'lw/03tcok',  'The Caverns of Kalte',       'lw_kai',      'Lone Wolf',  3],
    ['lw04', 'lw/04tcod',  'The Chasm of Doom',          'lw_kai',      'Lone Wolf',  4],
    ['lw05', 'lw/05sots',  'Shadow on the Sand',         'lw_kai',      'Lone Wolf',  5],
    ['lw06', 'lw/06tkot',  'The Kingdoms of Terror',     'lw_magnakai', 'Lone Wolf',  6],
    ['lw07', 'lw/07cd',    'Castle Death',               'lw_magnakai', 'Lone Wolf',  7],
    ['lw08', 'lw/08tjoh',  'The Jungle of Horrors',      'lw_magnakai', 'Lone Wolf',  8],
    ['lw09', 'lw/09tcof',  'The Cauldron of Fear',       'lw_magnakai', 'Lone Wolf',  9],
    ['lw10', 'lw/10tdot',  'The Dungeons of Torgar',     'lw_magnakai', 'Lone Wolf', 10],
    ['lw11', 'lw/11tpot',  'The Prisoners of Time',      'lw_magnakai', 'Lone Wolf', 11],
    ['lw12', 'lw/12tmod',  'The Masters of Darkness',    'lw_magnakai', 'Lone Wolf', 12],
    ['lw13', 'lw/13tplor', 'The Plague Lords of Ruel',   'lw_grandm',   'Lone Wolf', 13],
    ['lw14', 'lw/14tcok',  'The Captives of Kaag',       'lw_grandm',   'Lone Wolf', 14],
    ['lw15', 'lw/15tdc',   'The Darke Crusade',          'lw_grandm',   'Lone Wolf', 16],
    ['lw16', 'lw/16tlov',  'The Legacy of Vashna',       'lw_grandm',   'Lone Wolf', 17],
    ['lw17', 'lw/17tdoi',  'The Deathlord of Ixia',      'lw_grandm',   'Lone Wolf', 18],
    ['lw18', 'lw/18dotd',  'Dawn of the Dragons',        'lw_grandm',   'Lone Wolf', 19],
    ['lw19', 'lw/19wb',    'Wolf\'s Bane',               'lw_grandm',   'Lone Wolf', 20],
    ['lw20', 'lw/20tcon',  'The Curse of Naar',          'lw_grandm',   'Lone Wolf', 21],
    /*['lw21', 'lw/21votm',  'Voyage of the Moonstone',    'lw_neworder', 'Lone Wolf - New Order', 1],
    ['lw22', 'lw/22tbos',  'The Buccaneers of Shadaki',  'lw_neworder', 'Lone Wolf - New Order', 2],
    ['lw23', 'lw/23mh',    'Mydnight\'s Hero',           'lw_neworder', 'Lone Wolf - New Order', 3],
    ['lw24', 'lw/24rw',    'Rune War',                   'lw_neworder', 'Lone Wolf - New Order', 4],
    ['lw25', 'lw/25totw',  'Trail of the Wolf',          'lw_neworder', 'Lone Wolf - New Order', 5],
    ['lw26', 'lw/26tfobm', 'The Fall of Blood Mountain', 'lw_neworder', 'Lone Wolf - New Order', 6],
    ['lw27', 'lw/27v',     'Vampirium',                  'lw_neworder', 'Lone Wolf - New Order', 7],
    ['lw28', 'lw/28thos',  'The Hunger of Sejanoz',      'lw_neworder', 'Lone Wolf - New Order', 8],
    ['gs01', 'gs/01gstw',  'Grey Star the Wizard',       'lw_greystar', 'Lone Wolf - Grey Star', 1],
    ['gs02', 'gs/02tfc',   'The Forbidden City',         'lw_greystar', 'Lone Wolf - Grey Star', 2],
    ['gs03', 'gs/03btng',  'Beyond the Nightmare Gate',  'lw_greystar', 'Lone Wolf - Grey Star', 3],
    ['gs04', 'gs/04wotw',  'War of the Wizards',         'lw_greystar', 'Lone Wolf - Grey Star', 4],*/
];

$list1 = array_slice($booklist, 0, ceil(sizeof($booklist) / 2));
$list2 = array_slice($booklist, ceil(sizeof($booklist) / 2));

$out  = "By downloading books from Project Aon, you agree to the Project Aon License.\n";
$out .= "https://www.projectaon.org/en/Main/License\n\n";
$out .= "BOOK LIST:\n\n";
$cnt = 1;
$longest = 0;
foreach ($list1 as $val) {
    if (strlen($val[2]) > $longest) {
        $longest = strlen($val[2]);
    }
}
$cnt2 = count($list1)+1;
foreach ($list1 as $key => $val) {
    $out .= str_pad($cnt++, 2, ' ', STR_PAD_LEFT).'. '.str_pad($val[2], $longest+2, ' ');
    if (isset($list2[$key])) {
        $out .= str_pad($cnt2++, 2, ' ', STR_PAD_LEFT).'. '.$list2[$key][2];
    }
    $out .= "\n";
}
echo $out."\nWhich book(s) do you want to install? Type the number or a range. (e.g. 1-5)\n";

$line = readline("? ");
$line = explode("-", $line);
if (count($line) < 2) {
    $line[1] = $line[0];
}
$line[0] = trim($line[0]);
$line[1] = trim($line[1]);
if (!is_numeric($line[0]) || $line[0] < 1 || $line[0] > count($booklist) ||
    !is_numeric($line[1]) || $line[1] < 1 || $line[1] > count($booklist) ||
    $line[0] > $line[1]) {
    echo 'Invalid choice.';
    die();
}
$start = floor($line[0])-1;
$end   = floor($line[1])-$start;

echo "\nDo you want to download the illustrations? (yes/no) default: yes\n";
$line = readline("? ");
$line = $line?$line:'y';
$downloadpics = (trim(strtolower($line))[0] != 'n');

$worklist = array_slice($booklist, $start, $end, true);

foreach ($worklist as $book) {
    $bookid    = $book[0];
    $bookpaid  = $book[1];
    $booktitle = $book[2];
    $bookrules = $book[3];
    $bookgroup = $book[4];
    $booknum   = $book[5];

    echo "Installing $booktitle\n";
    $remotebase = "https://www.projectaon.org/en/xhtml-less-simple/$bookpaid/";
    $localimgbase = "../images/$bookid/";

    echo 'Fetching Text...'.PHP_EOL;
    $book = file_get_contents($remotebase.'title.htm');

    // Start
    $pos = strpos($book, 'The Story So Far');
    $pos = strpos($book, 'The Story So Far', $pos+16);
    if (!$pos) {
        die('Book start not found...');
    }
    $book = substr($book, $pos);
    // end
    $pos = strrpos( $book, '<a name="map">');
    if (!$pos) {
        die('Book end not found...');
    }
    $book = substr($book, 0, $pos);

    if ($downloadpics) {
        $book = strip_tags($book, '<img>');
    } else {
        $book = strip_tags($book);
    }
    $book = convert_text($book);

    if ($downloadpics) {
        //look for images
        if (!is_dir($localimgbase)) {
            echo "Creating image directory at $localimgbase".PHP_EOL;
            mkdir($localimgbase);
        }
        $re = '/<img .*?src="(.+?)".*?\/>/';
        $dupestore = [];
        foreach ($book as $k => $p) {
            if (preg_match_all($re, $p, $matches, PREG_SET_ORDER) === 1) {
                $local = $localimgbase.$k.".png";
                // Pull image down
                if (!file_exists($local)) {
                    $remote = $remotebase.$matches[0][1];
                    if (array_key_exists($remote, $dupestore)) {
                        echo "Copying Image for page $k...".PHP_EOL;
                        copy($dupestore[$remote], $local);
                    } else {
                        echo "Fetching Image for page $k... ";
                        file_put_contents($local, file_get_contents($remote));
                        $dupestore[$remote] = $localimgbase.$k.".png";
                        // autocrop
                        $img = imagecreatefrompng($local);
                        if (imageistruecolor($img)) {
                            $cropped = imagecropauto($img, IMG_CROP_THRESHOLD, 0.5, 0xFFFFFF);
                        } else {
                            $whiteidx = -1;
                            for ($c = imagecolorstotal($img)-1; $c >= 0 ; $c--) {
                                $idx = imagecolorsforindex($img, $c);
                                if ($idx['red'] > 250 && $idx['green'] > 250 && $idx['blue'] > 250) {
                                    $whiteidx = $c;
                                    break;
                                }
                            }
                            if ($whiteidx == -1) {
                                echo '(Palette white not found - couldn\'t crop!)';
                                $cropped = false;
                            } else {
                                $cropped = @imagecropauto($img, IMG_CROP_THRESHOLD, 0, $whiteidx);
                            }
                        }
                        if ($cropped !== false) { // in case a new image resource was returned
                            echo "Cropping image...";
                            imagepng($cropped, $local);
                            imagedestroy($cropped);
                        }
                        imagedestroy($img);
                        echo PHP_EOL;
                    }
                }
            }
            // Remove Tags
            $book[$k] = preg_replace($re, '', $p);
        }
        // Fetch map
        $local = $localimgbase."map.png";
        // Pull image down
        if (!file_exists($local)) {
            $remote = $remotebase.'map.png';
            echo "Fetching Map Image...".PHP_EOL;
            file_put_contents($local, file_get_contents($remote));
        }
    }

    echo "Exporting book...".PHP_EOL;
    file_put_contents("../books/$bookid.php", "<?php\n\n\$book = ".var_export($book, 1).";");

    if (array_key_exists($bookid, $config)) {
        echo "Already exists in config.ini\n";
    } else {
        $ct  = "[$bookid]\n";
        $ct .= "name = \"$booknum. $booktitle\"\n";
        $ct .= "file = books/$bookid.php\n";
        $ct .= "rules = $bookrules\n";
        $ct .= "group = \"$bookgroup\"\n";
        $ct .= "images_dir = $bookid\n\n\n";

        $ini = file_get_contents($configfn);
        $pos = strpos($ini, '[general]');
        if ($pos === false) {
            $ini .= "\n";
            $pos = strlen($ini);
        }
        $ini = substr_replace($ini, $ct, $pos, 0);
        file_put_contents($configfn, $ini);
        echo "Adding book to config.ini\n";
    }
    echo "\n";
}
