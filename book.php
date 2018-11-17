<?php

// This is only an example adventure. See readme.md

$gamebook = 'wofm';

$book = array(
0 => "_*The Janitor of Firetop Mountain*_\nYou are the weary janitor to The Warlock of Firetop Mountain. Your task today is to clean up the corpse of an unfortunate adventurer who is making a mess in one of the rooms. If you can survive that long...\nNow turn to 1\n(THIS IS AN EXAMPLE ADVENTURE. SEE README.MD FOR INFO ON CHANGING THE ADVENTURE.)",
1 => "You are in your own room. There is a small cot here with your mop to one side.\nYou may take the mop. (Type `!get mop`)\nWill you exit the room (Turn to 2 by typing `!2`) or get some sleep (Turn to 3 by typing `!3`)?",
2 => "You exit your room. There's no point returning until your task is done. In front of you are two doors, one labelled \"Brains\" and the other \"Brawn\".\nWill you take the Brains door (Turn to 4) or take the Brawn door (Turn to 5)",
3 => "\"Foolish slave, how dare you sleep on the job?\"\nThe voice is of your boss, The Warlock. You must fight.\nWARLOCK SKILL 11 STAMINA 18\n(Type `!fight Warlock 11 18`)\nIf you win, turn to 6. Otherwise, your quest ends here.",
4 => "Entering the room, the door locks behind you. There are three exits, one shaped like a square, another a circle and the last a triangle. Carved in to the wall is a message:\n\"May I have a large container of coffee?\"\nWhich door will you take? The triangular door (Turn to 7), The circular door (Turn to 8) or The square door (Turn to 9)?",
5 => "Entering the room, you see a sleeping orc. Do you wish to sneak past him? If so, test your luck. (Type `!test luck`) If you are lucky, turn to 10. If you are unlucky, turn to 11. If you would prefer to attack the creature in it's sleep turn to 12.",
6 => "Amazing. You are now the new Warlock of firetop mountain. You pick up the Warlock's staff (Type `!get staff`) which has a weapon bonus of 6. (Type `!weapon 6`)\nEven so, that adventurer's corpse is bothering you. You decide you go deal with it. You can have the new janitor clear this one up.\nTurn to 2.",
7 => "Turn to 13",
8 => "Weather through luck or smarts, you make it to the other side of the room and out the door.\nTurn to 14",
9 => "Turn to 13",
10 => "You sneak past the snoozing orc and out of the room.\nTurn to 14",
11 => "You awaken the orc. You must fight!\nORC SKILL 6 STAMINA 5\n(Type `!fight Orc 6 5`)\nIf you win the fight, you may exit the room (Turn to 14). Otherwise, your quest ends here.",
12 => "You attack the orc before he wakes, weakening him. But you must still fight!\nORC (Weakened) SKILL 5 STAMINA 4\n(Type `!fight Orc 5 4`)\nIf you win the fight, you may exit the room (Turn to 14). Otherwise, your quest ends here.",
13 => "Before you can get the door open, the ceiling of the room collapses burying you alive.\nYour quest ends here.",
14 => "Almost there! Your final challenge is an unstable looking bridge. Test your SKILL. (Type `!test skill`) If you are skillful, turn to 15. If you are not skillful, turn to 16.",
15 => "You cross the bridge with dexterity and make it to the other side. You are here. On the other side of the door in front of you in the mess you are quested to clean.\nDid you bring your mop with you? (Type `!stuff` to check your inventory.) If so, turn to 17. Otherwise, turn to 18.",
16 => "You fall down the crevasse. Fortunately, it is not deep and you live. However, you gain a lasting injury. Decrease your maximum and current stamina by 2. (Type `!stam max -2; !stam -2`)\nYou painfully climb back up. Turn to 14.",
17 => "You pull out you trusty mop and open the door. Urgh! This is going to be a lot of work. Test your stamina. (Type `!test stamina`) If you are strong enough, turn to 20. If you are not, turn to 19.",
18 => "You lean on the door and weep as you realize that you will have to explore the mountain once more in order to find the your mop.\nYour quest ends here.",
19 => "The mess is just too much. You give up.\nYour quest ends here.",
20 => "Congratulations! The mess is cleared. You have completed your quest and are at peace.",
);
