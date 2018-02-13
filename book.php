<?php

// This is only an example adventure. See readme.md

$book = array(

0 => "> _*The Janitor of Firetop Mountain*_
> _You are the weary janitor to The Warlock of Firetop Mountain. Your task today is to clean up the corpse of an unfortunate adventurer who is making a mess in one of the rooms. Assuming that you can survive that long..._
> _Now turn to 1_
> _(THIS IS AN EXAMPLE ADVENTURE. SEE README.MD FOR INFO ON CHANGING THE ADVENTURE.)_
",

1 => "> *1*
> _You are in your own room. There is a small cot here with your mop to one side._
> _You may take the mop. (Type `!get mop`)_
> _Will you exit the room (Turn to 2 by typing `!2`) or get some sleep (Turn to 3 by typing `!3`)?_
",

2 => "> *2*
> _You exit your room. There's no point returning until your task is done. In front of you are two doors, one labeled \"Brains\" and the other \"Brawn\"._
> _Will you take the Brains door (Turn to 4) or take the Brawn door (Turn to 5)_
",

3 => "> *3*
> _\"Foolish slave, how dare you sleep on the job?\"_
> _The voice is of your boss, The Warlock. You must fight._
> _WARLOCK SKILL 14 STAMINA 18_
> _(Type `!fight Warlock 14 18`)_
> _If you win, turn to 6. Otherwise, your quest ends here._
",

4 => "> *4*
> _Entering the room, the door locks behind you. There are three exits, one shaped like a square, another a circle and the last a triangle. Carved in to the wall is a message:_
> _\"May I have a large container of coffee?\"_
> _Which door will you take? The triangular door (Turn to 7), The circular door (Turn to 8) or The square door (Turn to 9)?_
",

5 => "> *5*
> _Entering the room, you see a sleeping orc. Do you wish to sneak past him? If so, test your luck. (Type `!test luck`) If you are lucky, turn to 10. If you are unlucky, turn to 11. If you would prefer to attack the creature in it's sleep turn to 12._
",

6 => "> *6*
> _Amazing. You are now the new Warlock of firetop mountain. You pick up the Warlock's staff (Type `!get staff`) which has a weapon bonus of 6. (Type `!weapon 6`)_
> _Even so, that adventurer's corpse is bothering you. You decide you go deal with it. You can have the new janitor clear this one up._
> _Turn to 2._
",

7 => "> *7*
> _Turn to 13_
",

8 => "> *8*
> _Weather through luck or smarts, you make it to the other side of the room and out the door._
> _Turn to 14_
",

9 => "> *9*
> _Turn to 13_
",

10 => "> *10*
> _You sneak past the snoozing orc and out of the room._
> _Turn to 14_
",

11 => "> *11*
> _You awaken the orc. You must fight!_
> _ORC SKILL 6 STAMINA 5_
> _(Type `!fight Orc 6 5`)_
> _If you win the fight, you may exit the room (Turn to 14). Otherwise, your quest ends here._
",

12 => "> *12*
> _You attack the orc before he wakes, weakening him. But you must still fight!_
> _ORC (Weakened) SKILL 5 STAMINA 4_
> _(Type `!fight Orc 5 4`)_
> _If you win the fight, you may exit the room (Turn to 14). Otherwise, your quest ends here._
",

13 => "> *13*
> _Before you can get the door open, the ceiling of the room collapses burying you alive._
> _Your quest ends here._
",

14 => "> *14*
> _Almost there! Your final challenge is an unstable looking bridge. Test your SKILL. (Type `!test skill`) If you are skillful, turn to 15. If you are not skillful, turn to 16._
",

15 => "> *15*
> _You cross the bridge with dexterity and make it to the other side. You are here. On the other side of the door in front of you in the mess you are quested to clean._
> _Did you bring your mop with you? (Type `!stuff` to check your inventory.) If so, turn to 17. Otherwise, turn to 18._
",

16 => "> *16*
> _You fall down the crevasse. Fortunately, it is not deep and you live. However, you gain a lasting injury. Decrease your maximum and current stamina by 2. (Type `!stam max -2; !stam -2`)_
> _You painfully climb back up. Turn to 14._
",

17 => "> *17*
> _You pull out you trusty mop and open the door. Urgh! This is going to be a lot of work. Test your stamina. (Type `!test stam`) If you are strong enough, turn to 20. If you are not, turn to 19._
",

18 => "> *18*
> _You lean on the door and weep as you realize that you will have to explore the mountain once more in order to find your mop._
> _Your quest ends here._
",

19 => "> *19*
> _The mess is just too much. You give up._
> _Your quest ends here._
",

20 => "> *20*
> _Congratulations! The mess is cleared. You have completed your quest and are at peace._
",

);

