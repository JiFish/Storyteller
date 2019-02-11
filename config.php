<?php

// URL of incoming webhook
define("SLACK_HOOK","https://hooks.slack.com/services/YOUR/WEBHOOK/HERE");

// Token for outgoing webhook
define("SLACK_TOKEN","YOURTOKENHERE");

// Book type code. This determines which rules are used. See book_support.html in extras for codes.
define("BOOK_TYPE",'wofm');

// Types of dice rolling rules when creating characters
// normal : Roll as normal
// d5+1   : Rolls of 1 are re-rolled
// min3   : Rolls of 1 or 2 are replaced with 3
// min4   : Rolls of 1, 2 or 3 are replaced with 4
// roll2  : Roll 2 dice and take the highest
// roll3  : Roll 3 dice and take the highest
// all6   : Use 6 for all dice, instead of rolling
define("CHARACTER_ROLLS",'normal');

// Images subdirectory. This is the subdirectory within the images directory to serve this book's
// illustrations from. You'll need to change this when changing books to prevent caching issues.
// Set to false if you don't want to send illustrations.
define("IMAGES_SUBDIR",'jofm');

// Command disabling. Any command in the list cannot be run by anyone. (This excludes macros and
// $autorun in book.php) If a command has aliases, they must also be included. A few of the more
// powerful commands are disabled by default. Remove them from this list to enable them.
$disabledcommands = ['debugset', 'silentset'];

// Discord mode formats output to work with Discord's *Slack Compatible* incoming webhooks
define("DISCORD_MODE",false);

// Maximum command executions in one go. You probably won't need to change this.
define("MAX_EXECUTIONS",30);
