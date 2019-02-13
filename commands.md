Storyteller Commands Help
Quick Guide / Common Commands

Mostly everything you'll need to know.
Reading

    !<number> Read page . e.g. !42. !look Re-read last page.
    !info Show character sheet. !stats just shows stats, !stuff just shows inventory.
    !map Send the map image, if available.

Rolling Dice

    !fight [name] <skill> <stamina> Fight a monster with skill and stamina . Name is optional and can contain spaces. e.g. !fight Giant Spider 4 5
    !roll [dienumber] Roll [dienumber] six-sided dice and sum the result. If dienumber is missing, rolls one die.
    !test luck or !test skill or !test stam Test the stat against two dice.
    !randpage <page 1> [page 2] [page 3] [...] Turn randomly to one of the listed pages.

Character Management

    !newgame [name] [m/f] [emoji] Rolls a new character and resets the game. Optionally set name, gender and emoji. e.g. !newgame Jill f
    !<stat> [+/-]<amount> Set to . Use + or - to alter by . e.g. !skill 3 or !gold +2
    !<stat> max [+/-]<amount> Set or alter the MAX of with . e.g. !stam max -1 values are: skill, stam, luck, weapon, gold and prov
    !eat Eats one provision for 4 stamina.
    !get <item> Adds to your inventory.
    !lose <item> Removes to your inventory. You can also use !drop and !use for different descriptions.
    !buy <item> [cost] Add to your inventory and subtracts [cost] gold. If cost is missing, 2 gold will be taken.
    !shield <on/off> Equips or removes the special shield item. When on gives a 1 in 6 chance to reduce damage by 1 when using !fight.
    !dead Reduce stamina to 0.
    !undo When dead, restore the game to the last page you turned to. You cannot undo fights, tests and some other actions!

You can chain multiple commands together in one go with semicolons e.g. !newgame; !1

Still awake? Below is an exhaustive list of commands
Complete Command List

For the nerds
Reading

    !<number> or !page <number> Read page . e.g. !42
    !look Re-read last page.
    !map Send the map image, if available.

Character Information

    !info or !status Show character stats and inventory.
    !stats or !s Show character stats only.
    !stuff or !i Show character inventory.
    !newgame [name] [gender] [emoji] [race] [adjective] [seed] or !ng Rolls a new character and resets the game. Optionally customise the new character. Use ? to randomise a field. A numeric [seed] may be given in case you want to generate the same character again and again.
    !undo When dead, restore the game to the last page you turned to. You cannot undo fights, tests and some other actions!

Some fun character ideas:

    !ng ? m :male_mage::skin-tone-5: Human Wizard
    !ng ? ? :robot_face: Robot
    !ng Vaarsuvius Androgynous :elf::skin-tone-2: Elf

Inventory Management

    !get <item> or !take <item> Adds to your inventory. Attempts to automatically manage gold and provisions stats if used like "!get 5 gold"
    !lose <item> Removes to your inventory. You don't have to provide a full match. e.g. Drop 'leather armor' with !drop armor. Will attempt to manage gold and provisions as above.
    !drop <item> or !use <item> As above, but with thematic descriptions.
    !eat Eats one provision for 4 stamina.
    !pay <amount> or !spend <amount> Subtracts of gold. See stats below.
    !buy <item> [cost] Add to your inventory and subtracts [cost] gold. If cost is missing, 2 gold will be taken.
    !shield <on/off> Equips or removes the special shield item. When on gives a 1 in 6 chance to reduce damage by 1 when using !fight (and variants.)

Stats Management

!<stat> [max/temp] [+/-]<amount>

    Set the stat called to .
    Valid values are: skill, stam, luck, weapon, gold and prov. (Depending which booktype you are playing, additional stats may be available.)
    If max is used, the stat's maximum is changed instead.
    If temp is used, you set a bonus that will be applied to the stat for the next !test or !fight only.
    If starts with a - or +, will be subtracted or added from the total. Otherwise the value is replaced with . Only the weapon stat and temp bonuses can be reduced below 0.

Examples:

    !stam -3 Take 3 stamina loss.
    !weapon 2 Set weapon bonus to 2.
    !luck max +1 Add 1 to maximum luck.
    !skill temp -2; !test skill Do a skill test at a -2 penalty to skill.

Roll Automation

    !test <stat> [successpage] [failpage] Roll test for . Valid stats are: luck, skill and stam. Turn to [successpage] if successful, [failpage] otherwise (optional.)
    !roll [dienumber] Roll [dienumber] six-sided dice and sum the result. If dienumber is missing, rolls one die.
    !luckyescape or !le Test luck to try to negate damage. Lose 3 stamina on a failure and 1 stamina on a success.
    !randpage <page 1> [page 2] [page 3] [...] Turn randomly to one of the listed pages.

Fight Automation

    !fight [name] <skill> <stamina> [stopafter] Fight a monster named [name] (optional) with skill and stamina . Spaces are accepted in the name. Stop after [stopafter] rounds (optional.) You can use 3 special phrases for [stopafter]: hitme, hitthem and hitany to stop the fight in those situations.
    !attack <skill> [damage] or !a <skill> [damage] Perform a single attack roll versus a monster with skill . [damage] is taken from stamina on a fail (Default: 0) This is for manually running combat with special rules.

The following covers many custom fight rules:

    !critfight [name] <skill> [who] [critchance] Fight a monster named [name] (optional) with skill with critical strikes doing damage only. [who] is who has to roll the crits, me or both (Default: me). [critchance] is the chance of the crit hitting x in 6. (Default: 2)
    !bonusfight [name] <skill> <stamina> <bonusdmg> [bonusdmgchance] Fight a monster named [name] (optional) with skill and stamina . After each round the monster has a [bonusdmgchance]/6 chance of doing damage. Default 3/6.
    !fighttwo <name 1> <skill 1> <stamina 1> [<name 2> <skill 2> <stamina 2>] Fight two opponents at the same time. If a second monster isn't provided, you'll fight two copies of the first.
    !fightbackup [name] <skill> <stamina> [allyname] <allyskill> Fight an opponent with an ally named [allyname] (optional) and with skill <allyskill> backing you up in the fight.
    !vs <name 1> <skill 1> <stamina 1> <name 2> <skill 2> <stamina 2> Fight two monsters against each other.
    !battle [name] <strike> <strength> [stopafter] Fight a large scale battle with opponent named [name] (optional) with strike <strike> and strength , using your strike and strength. This command is only available for some books.
    !phaser [-/+penalty] [stun/kill] [name] <skill> [stun/kill] [maxrounds] or !gun. Run phaser combat. [Penalty] is added to your dice rolls. Shooting to [stun/kill] (default: stun.) Against a opponent named [name] (optional) with a skill of . The opponent is shooting to [stun/kill] (default: kill.) This command is only available for some books.
    !shipbattle [name] <skill> <stamina> [stopafter] Battle a ship named [name] (optional) with weapons and shields . This command is only available for some books.

Restoring To Earlier

    !undo When dead, restore the game to the last page you turned to. You cannot undo fights, tests and some other actions!
    !save [slot] Save the current game in slot numbered [slot]. Valid slot numbers are 0 - 10. If you don't specify a slot, 0 will be used. (This command is disabled by default.)
    !load [slot] Restore the game from slot [slot]. (This command is disabled by default.)
    !clearslots [confirm] Clear all the save slots. Useful after switching books. You are required to type `!clearslots confirm` for safety. (This command is disabled by default.)

Example

    !save 3; !7; !load 3 Save in slot 3, turn to page 7, reload game

Spellcasting

(Only available for some books.)

    !spellbook [page] Read your spellbook. [Page] can be a number; or it can be one of the four spell types: combat, self, object and utility; or the word all to see every spell. (Warning: using all will result in a long post.)
    !cast <spell name> Cast the spell ! Magic points will be deducted and stats effects applied where appropriate.
    !cast <spell name> [name] <skill> <stamina> Some spell require a combat target. These 3 values work the same as the !fight command.

Examples

    !spellbook 1 Read page 1 of your spellbook
    !spellbook combat Read all combat spells in your spellbook
    !cast Luck Cast Luck and gain 1 Luck
    !cast Fireball Killer Snowman 7 8 Cast Fireball on a Killer Snowman

Ordering crew

(Only available for some books.)

    !<position> <command> Order the crew member in to perform <command>. Command is a valid command including parameters.
    !everyone <command> Shorthand to make every crew member and yourself perform <command>.
    !beam <up/down> [position] [position] [position] Mark crew as in the away team with up, and remove them with down. `!beam up` on it's own beams everybody up.
    !awayteam <command> Shorthand to make every crew member in the away team and yourself perform <command>.
    !recruit <position> <name> <skill> <stam> [gender] [race] Replace one of the crew with a new crew member with the given information.

The following commands are available for ordering: bonusfight, critfight, dead, fight, fighttwo, phaser, skill, stam, test.
Examples

    !medic test skill - Order medic to test skill
    !security phaser kill Salt Monster 7 - Order security officer to phaser fight Salt Monster, Shoot to kill
    !everyone stam +2 - Everyone gain 2 stamina
    !beam down science medic guard - Add your science officer, medic and guard to the away team.

Command Chaining

You can chain multiple commands together in one go with semicolons ; e.g. !newgame; !1 The chain will stop automatically on player death. You can omit the ! prefix after the first command in the chain.
Examples:

    !eat;eat;eat Eat 3 times.
    !fight Spider 4 5; !42 Fight a spider and turn to page 42 if you win.
    !pay 5; get Odd Potion Pay 5 gold and receive an Odd Potion.

Fancy Stuff & Debugging

Dragons be here. Advanced users only.

    !echo <message> Simply repeats <message>. Useful to label outputs when chaining commands.
    !debugset <var> <val> Set character variable to <var>. Potentially could ruin the character if you are careless. (This command is disabled by default.)
    !silentset <var> <val> Set character variable to <var>. As above, but nothing will be outputted. (This command is disabled by default.)
    !debuglist Show all character variables and the the current value. (This command is disabled by default.)
    !macro <line> or !m <line> run line number from macros.txt as a command. Useful if an adventure requires the same sequence of commands to be run again and again.
    You can include magic substitutions in any command with curly brackets. There are three types:
        Character variables. Try !echo Hello {name}.
        Dice rolls in the form <dice>d[dicesides][+/-bonus]. If dicesides is omitted, 6 is assumed. e.g. {1d}, {3d10}, {1d8-4}, {1d+3}
        {sc} will be replaced with a semicolon ";". Just in case you need one in a string.

Examples:

    !macro 1 Run the first line in macros.txt. (Set to an example by default.)
    !echo Jim:; roll 5; echo Bob:; roll 5 Roll 5 dice each for Jim and Bob.
    !{1d400} Turn to a random page between 1 - 400.
    !stam -{1d} Roll a 6-sided dice and subtract the result from stamina.
    !skill max {skill} Set your maximum skill to your current skill.
    !ng {name} {gender} {emoji} {race} Second Start a new game as the offspring of the last character
    !silentset name Bob Set the character's name to Bob silently.
