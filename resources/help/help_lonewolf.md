# Storyteller Commands Help
## Quick Guide / Common Commands
Mostly everything you'll need to know.

### Reading

- `!<number>` Read page . e.g. `!42`. `!look` Re-read last page.
- `!info` Show character sheet. !stats just shows stats, !stuff just shows inventory.
- `!map` Send the map image, if available.

### Random Numbers

- `!fight [name] <skill> <endurance>` Fight a monster with combat skill <skill> and endurance <endurance>. Name is optional and can contain spaces. e.g. !fight Giant Spider 4 5
- `!rand [amount]` Pick [amount] of numbers from the random number table. If amount is missing, picks one.

### Character Management

- `!newgame [name] [m/f] [emoji]` Rolls a new character and resets the game. Optionally set name, gender and emoji. e.g. `!newgame Jill f`
- `!<stat> [+/-]<amount>` Set <stat> to <amount>. Use + or - to alter by <amount>. e.g. `!endurance -3` or `!gold +2` values are: endurance, skill, and gold.
- `!endurance max [+/-]<amount>` As above, but alters the endurance stat's maximum. e.g. `!endurance max -1`
- `!eat` Eats one meal, if there is none lose 3 endurance.
- `!get <item>` Adds <item> to your inventory.
- `!get special [item]` Adds [item] to your special items inventory. e.g. `!get special key`
- `!lose <item>` Removes <item> from your inventory. You can also use !drop and !use for different descriptions. This will try to match partial names.
- `!wield [weapon]` and `!unwield [weapon]` Wield (or unwield) a weapon called [weapon].
- `!undo` When dead, restore the game to the last page you turned to. You cannot undo fights, tests and some other actions!

You can chain multiple commands together in one go with semicolons e.g. `!newgame; !1`

Still awake? Below is an exhaustive list of commands

##Complete Command List
For the nerds

### Reading

- `!<number>` or `!page <number>` Read page <number>. e.g. `!42`
- `!look` Re-read last page.
- `!map` Send the map image, if available.

### Character Information

- `!info` or `!status` Show character stats and inventory.
- `!stats` or `!s` Show character stats only.
- `!stuff` or `!i` Show character inventory.
- `!newgame [name] [gender] [emoji] [race] [adjective]` or `!ng` Rolls a new character and resets the game. Optionally customise the new character. Use `?` to randomise a field.
- `!undo` When dead, restore the game to the last page you turned to. You cannot undo fights, tests and some other actions!

### Inventory Management

- `!get [special/sp] <item>` or `!take <item>` Adds to your inventory. If [special] is given, will add to your special items list.
- `!lose <item>` Removes to your inventory. You don't have to provide a full match. e.g. Drop 'leather armor' with `!drop armor`. Will attempt to manage gold and provisions as above.
- `!drop <item>` or `!use <item>` As above, but with thematic descriptions.
- `!wield [weapon]` wield [weapon]. If you already have two, the second will be `!unwield`ed. If [weapon] matches an item in the backpack that item will be removed and wielded.
- `!unwield [weapon]` Unwield a weapon called [weapon]. Will try to match like `!lose` above. If you have backpack space, it will be placed in the backpack.
- `!eat` Eats one meal, if there is none lose 3 endurance.


### Stats Management

`!<stat> [max/temp] [+/-]<amount>`

Set the stat called to <amount>.

Valid values are: endurance, skill, and gold.

If max is used, the stat's maximum is changed instead.

If temp is used, you set a bonus that will be applied to the stat for the next `!fight` only.

If starts with a - or +, will be subtracted or added from the total. Otherwise the value is replaced with <amount>. Only the weapon stat and temp bonuses can be reduced below 0.

Examples:

- `!endurance -3` Take 3 stamina loss.
- `!skill 20` Set skill to 20.
- `!endurance max +1` Add 1 to maximum endurance.
- `!skill temp -2; !fight 2 2` Do a skill test at a -2 penalty to skill.

### Random Numbers

- `!rand [amount]` or `!roll` Pick [amount] of numbers from the random number table. If amount is missing, picks one.
- `!randpage <page 1> [page 2] [page 3] [...]` Turn randomly to one of the listed pages. **Not recommend. Often in Lone Wolf the chances of each page are unequal or are modified by other factors. Use !rand instead.**

Examples:

- `!rand 10` Generate 10 random numbers.

### Fight Automation

- `!fight [name] <skill> <endurance> <+/-bonus> [stopafter]` Fight a monster named [name] (optional) with combat skill <skill> and endurance <endurance>. Your skill will be modified by <+/-bonus> (optional.) Stop after [stopafter] rounds (optional.) Spaces are accepted in the name.
- `!attack [name] <skill> <endurance>` or `!a` Single attack, shorthand for a one-round fight
- `!flee [name] <skill> <+/-bonus> [stopafter]` Attempt to flee a monster named [name] (optional) with combat skill <skill>. Your skill will be modified by <+/-bonus> (optional.) Stop running [stopafter] rounds (optional, default: 1.)

Examples:

- `!fight 5 5` Fight generic 5 5 opponent.
- `!fight Giant 10 50 4` Fight a giant and stop after 4 rounds
- `!fight 6 6 +4` Fight with a +4 bonus
- `!fight Green Goo 8 20 -2 3` Fight green goo with -2 penalty and stop after 3 rounds

### Restoring To Earlier

- `!undo` When dead, restore the game to the last page you turned to. You cannot undo fights, tests and some other actions!
- `!save [slot]` Save the current game in slot numbered [slot]. Valid slot numbers are 0 - 10. If you don't specify a slot, 0 will be used. (This command is disabled by default.)
- `!load [slot]` Restore the game from slot [slot]. (This command is disabled by default.)
- `!clearslots [confirm]` Clear all the save slots. Useful after switching books. You are required to type `!clearslots confirm` for safety. (This command is disabled by default.)

Example

- `!save 3; !7; !load 3` Save in slot 3, turn to page 7, reload game

### Command Chaining

You can chain multiple commands together in one go with semicolons `;` e.g. `!newgame; !1` The chain will stop automatically on player death. You can omit the `!` prefix after the first command in the chain.

Examples:

- `!eat;eat;eat` Eat 3 times.
- `!fight Spider 4 5; !42` Fight a spider and turn to page 42 if you win.
- `!gold -5; get Odd Potion` Pay 5 gold and receive an Odd Potion.

### Fancy Stuff & Debugging

Dragons be here. *Advanced users only.*

- `!echo <message>` Simply repeats <message>. Useful to label outputs when chaining commands.
- `!debugset <var> <val>` Set character variable to <var>. Potentially could ruin the character if you are careless. (This command is disabled by default.)
- `!silentset <var> <val>` Set character variable to <var>. As above, but nothing will be outputted. (This command is disabled by default.)
- `!debuglist` Show all character variables and the the current value. (This command is disabled by default.)
- `!macro <line>` or `!m <line>` run line number from macros.txt as a command. Useful if an adventure requires the same sequence of commands to be run again and again.

You can include magic substitutions in any command with curly brackets. There are three types:
- Character variables. Try `!echo Hello {name}`.
- Dice rolls in the form `<dice>d[dicesides][+/-bonus]`. If dicesides is omitted, 6 is assumed. e.g. `{1d}`, `{3d10}`, `{1d8-4}`, `{1d+3}`
- `{sc}` will be replaced with a semicolon ";". Just in case you need one in a string.

Examples:

- `!macro 1` Run the first line in macros.txt. (Set to an example by default.)
- `!echo Jim:; roll 5; echo Bob:; roll 5` Get 5 random numbers each for Jim and Bob.
- `!{1d400}` Turn to a random page between 1 - 400.
- `!skill -{1d10-1}` Roll a 10-sided dice, subtract 1 and subtract the result from skill.
- `!endurance max {endurance}` Set your maximum endurance to your current endurance.
- `!ng {name} {gender} {emoji}` Second Start a new game as the offspring of the last character
- `!silentset name Bob` Set the character's name to Bob silently.
