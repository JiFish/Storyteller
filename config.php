<?php

// URL of incoming webhook
define("SLACK_HOOK","https://hooks.slack.com/services/YOUR/WEBHOOK/HERE");

// Types of dice rolling rules when creating characters
// normal : Roll as normal
// min3   : Rolls of 1 or 2 are replaced with 3
// min4   : Rolls of 1, 2 or 3 are replaced with 4
// roll2  : Roll 2 dice and take the highest
// roll3  : Roll 3 dice and take the highest
// all6   : Use 6 for all dice, instead of rolling
define("CHARACTER_ROLLS",'normal');

// Token for outgoing webhook
define("SLACK_TOKEN","YOURTOKENHERE");
define("BOOK_TYPE",'wofm');

define("MAX_EXECUTIONS",30);
define("DISCORD_MODE",false);
