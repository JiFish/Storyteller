# Storyteller Commands Help
## Quick Guide / Common Commands
Mostly everything you'll need to know.

### Reading
- `!<number>` Read page <number>. e.g. !42. `!look` Re-read last page.
- `!info` Show character sheet. `!stats` just shows stats, `!stuff` just shows inventory.
- `!map` Send the map image, if available.

### Rolling Dice
- `!fight [name] <skill> <stamina>` Fight a monster with skill <skill> and stamina <stamina>. Name is optional and can contain spaces. e.g. `!fight Giant Spider 4 5`
- `!roll [dienumber]` Roll [dienumber] six-sided dice and sum the result. If dienumber is missing, rolls one die.
- `!test luck` or `!test skill` or `!test stam` Test the stat against two dice.
- `!randpage <page 1> [page 2] [page 3] [...]` Turn randomly to one of the listed pages.

### Character Management
- `!newgame [name] [m/f] [emoji]` Rolls a new character and resets the game. Optionally set name, gender and emoji. e.g. `!newgame Jill f`
- `!<stat> [+/-]<amount>` Set <stat> to <amount>. Use + or - to *alter* <stat> by <amount>. e.g. `!skill 3` or `!gold +2`
- `!<stat> max [+/-]<amount>` Set or alter the MAX of <stat> with <amount>. e.g. `!stam max -1`
<stat> values are: `skill`, `stam`, `luck`, `weapon`, `gold` and `prov`
- `!eat` Eats one provision for 4 stamina.
- `!get <item>` Adds <item> to your inventory.
- `!lose <item>` Removes <item> to your inventory. You can also use `!drop` and `!use` for different descriptions.
- `!buy <item> [cost]` Add <item> to your inventory and subtracts [cost] gold. If cost is missing, 2 gold will be taken.
- `!shield <on/off>` Equips or removes the special shield item. When on gives a 1 in 6 chance to reduce damage by 1 when using !fight.
- `!dead` Reduce stamina to 0.
- `!undo` When dead, restore the game to the last page you turned to. You cannot undo fights, tests and some other actions!

You can chain multiple commands together in one go with semicolons e.g. `!newgame; !1`

**Still awake? Below is an exhaustive list of commands**

## Complete Command List
For the nerds

### Reading
- `!<number>` or `!page <number>` Read page <number>. e.g. `!42`
- `!look` Re-read last page.
- `!map` Send the map image, if available.

### Character Information
- `!info` or `!status` Show character stats and inventory.
- `!stats` or `!s` Show character stats only.
- `!stuff` or `!i` Show character inventory.
- `!newgame [name] [gender] [emoji] [race] [adjective] [seed]` or `!ng` Rolls a new character and resets the game. Optionally customise the new character. Use `?` to randomise a field. A numeric [seed] may be given in case you want to generate the same character again and again.
- `!undo` When dead, restore the game to the last page you turned to. You cannot undo fights, tests and some other actions!

##### Some fun character ideas:
- `!ng ? m :male_mage::skin-tone-5: Human Wizard`
- `!ng ? ? :robot_face: Robot`
- `!ng Vaarsuvius Androgynous :elf::skin-tone-2: Elf`

### Inventory Management
- `!get <item>` or `!take <item>` Adds <item> to your inventory. Attempts to automatically manage gold and provisions stats if used like "!get 5 gold"
- `!lose <item>` Removes <item> to your inventory. You don't have to provide a full match. e.g. Drop 'leather armor' with `!drop armor`. Will attempt to manage gold and provisions as above.
- `!drop <item>` or `!use <item>` As above, but with thematic descriptions.
- `!eat` Eats one provision for 4 stamina.
- `!pay <amount>` or `!spend <amount>` Subtracts <amount> of gold. See stats below.
- `!buy <item> [cost]` Add <item> to your inventory and subtracts [cost] gold. If cost is missing, 2 gold will be taken.
- `!shield <on/off>` Equips or removes the special shield item. When on gives a 1 in 6 chance to reduce damage by 1 when using !fight (and variants.)

### Stats Management

`!<stat> [max/temp] [+/-]<amount>`

- Set the stat called <stat> to <amount>.
- Valid <stat> values are: `skill`, `stam`, `luck`, `weapon`, `gold` and `prov`. (Depending which booktype you are playing, additional stats may be available.)
- If `max` is used, the stat's maximum is changed instead.
- If `temp` is used, you set a bonus that will be applied to the stat for the next !test or !fight only.
- If <amount> starts with a - or +, <amount> will be subtracted or added from the total. Otherwise the value is *replaced* with <amount>. Only the weapon stat and temp bonuses can be reduced below 0. 

##### Examples:
- `!stam -3` Take 3 stamina loss.
- `!weapon 2` Set weapon bonus to 2.
- `!luck max +1` Add 1 to maximum luck.
- `!skill temp -2; !test skill` Do a skill test at a -2 penalty to skill.

### Roll Automation
- `!test <stat> [successpage] [failpage]` Roll test for <stat>. Valid stats are: `luck`, `skill` and `stam`. Turn to [successpage] if successful, [failpage] otherwise (optional.)
- `!roll [dienumber]` Roll [dienumber] six-sided dice and sum the result. If dienumber is missing, rolls one die.
- `!luckyescape` or `!le` Test luck to try to negate damage. Lose 3 stamina on a failure and 1 stamina on a success.
- `!randpage <page 1> [page 2] [page 3] [...]` Turn randomly to one of the listed pages.

### Fight Automation
- `!fight [name] <skill> <stamina> [stopafter]` Fight a monster named [name] (optional) with skill <skill> and stamina <stamina>. Spaces are accepted in the name. Stop after [stopafter] rounds (optional.) You can use 3 special phrases for [stopafter]: `hitme`, `hitthem` and `hitany` to stop the fight in those situations.
- `!attack <skill> [damage]` or `!a <skill> [damage]` Perform a single attack roll versus a monster with skill <skill>. [damage] is taken from stamina on a fail (Default: 0) This is for manually running combat with special rules.
##### The following covers many custom fight rules:
- `!critfight [name] <skill> [who] [critchance]` Fight a monster named [name] (optional) with skill <skill> with critical strikes doing damage only. [who] is who has to roll the crits, `me` or `both` (Default: me). [critchance] is the chance of the crit hitting x in 6. (Default: 2)
- `!bonusfight [name] <skill> <stamina> <bonusdmg> [bonusdmgchance]` Fight a monster named [name] (optional) with skill <skill> and stamina <stamina>. After each round the monster has a [bonusdmgchance]/6 chance of doing <bonusdmg> damage. Default 3/6.
- `!fighttwo <name 1> <skill 1> <stamina 1> [<name 2> <skill 2> <stamina 2>]` Fight two opponents at the same time. If a second monster isn't provided, you'll fight two copies of the first.
- `!vs <name 1> <skill 1> <stamina 1> <name 2> <skill 2> <stamina 2>` Fight two monsters against each other.
- `!battle [name] <strike> <strength> [stopafter]` Fight a large scale battle with opponent named [name] (optional) with strike <strike> and strength <strength>, using your strike and strength. This command is only available for some books.

### Spellcasting
(Only available for some books.)
- `!spellbook [page]` Read your spellbook. [Page] can be a number; or it can be one of the four spell types: `combat`, `self`, `object` and `utility`; or the word `all` to see every spell. (Warning: using `all` will result in a long post.)
- `!cast <spell name>` Cast the spell <spell name>! Magic points will be deducted and stats effects applied where appropriate.
- `!cast <spell name> [name] <skill> <stamina>` Some spell require a combat target. These 3 values work the same as the !fight command.

##### Examples
- `!spellbook 1` Read page 1 of your spellbook
- `!spellbook combat` Read all combat spells in your spellbook
- `!cast Luck` Cast Luck and gain 1 Luck
- `!cast Fireball Killer Snowman 7 8` Cast Fireball on a Killer Snowman

### Command Chaining
You can chain multiple commands together in one go with semicolons `;` e.g. `!newgame; !1` The chain will stop automatically on player death. You can omit the `!` prefix after the first command in the chain.

##### Examples:
- `!eat;eat;eat` Eat 3 times.
- `!fight Spider 4 5; !42` Fight a spider and turn to page 42 if you win.
- `!pay 5; get Odd Potion` Pay 5 gold and receive an Odd Potion.

### Fancy Stuff & Debugging
Dragons be here. Advanced users only.

- `!echo <message>` Simply repeats <message>. Useful to label outputs when chaining commands.
- `!debugset <var> <val> [silent]` Set character variable <var> to <val>. Potentially could ruin the character if you are careless. Silent is optional, if given nothing will be outputted.
- `!macro <line>` or `!m <line>` run line number <line> from macros.txt as a command. Useful if an adventure requires the same sequence of commands to be run again and again. 
- You can include magic substitutions in any command with curly brackets. There are three types:
-- Character vars. Any of the stats will work, plus a few extra. Try `!echo Hello {name}.` Useful for debugging. Use the special case `{all}` to see everything.
-- Dice rolls in the form <numdice>d[dicesides][+/-bonus]. If dicesides is omitted, 6 is assumed. e.g. `{1d}`, `{3d10}`, `{1d8-4}`, `{1d+3}`
-- `{sc}` will be replaced with a semicolon ";". Just in case you need one in a string.

##### Examples:
- `!macro 1` Run the first line in macros.txt. (Set to an example by default.)
- `!echo Jim:; roll 5; echo Bob:; roll 5` Roll 5 dice each for Jim and Bob.
- `!{1d400}` Turn to a random page between 1 - 400.
- `!stam -{1d}` Roll a 6-sided dice and subtract the result from stamina.
- `!skill max {skill}` Set your maximum skill to your current skill.
- `!ng {name} {gender} {emoji} {race} Second` Start a new game as the offspring of the last character
- `!debugset name Bob silent` Set the character's name to Bob silently.
