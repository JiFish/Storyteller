# Storyteller
## A Slack / Discord* Bot for playing GameBooks (v2.0)

## Introduction
Storyteller is a bot for use with Slack which helps a group of people play
through a gamebook co-operatively. It was designed for _Fighting Fantasy_, but
can now play a number of different gamebook types. It should work with any
choose-your-own adventure book.

Storyteller adds commands to read the story, manage your character and roll
dice for fights and other challenges. It will assist you in playing the game
but does not strictly enforce rules - replicating the experience of playing
with a real book and dice.

Storyteller is programmed in PHP, requires no database and is state-based. This
means it can be installed on an ordinary web-server, without the need to setup
a complicated chat-bot.

* Discord is also supported, with some caveats, see *Using Discord* below.

## Book Support

Anything without a character sheet is supported, this includes the Choose Your
Own Adventure books.

The following Fighting Fantasy books currently work very well: Battleblade
Warrior, Black Vein Prophecy, Bloodbones, Caverns of the Snow Witch, The
Citadel of Chaos, City of Thieves, Creature of Havoc, Crypt of the Sorcerer,
Deathmoor, Deathtrap Dungeon, Demons of the Deep, Eye of the Dragon, The Forest
of Doom, House of Hell, Island of the Lizard King, Legend of Zagor, Masks of
Mayhem, Portal of Evil, Rebel Planet, Return to Firetop Mountain, Scorpion
Swamp, Seas of Blood, Starship Traveller, Stealer of Souls, Talisman of Death,
Temple of Terror, Trial of Champions, The Warlock of Firetop Mountain. Most
other can still be played.

The Sonic The Hedgehog Gamebooks are also supported as are The Narnia Solo
Games books.

## Set-Up
Download and unzip. Place on a webserver running PHP. PHP7 is recommended.

### 1. Set-Up Slack
First you must create an incoming and outgoing webhook. In Slack, go to
**Administration > Manage Apps > Custom Integrations**.

#### Incoming Webhook
![Incoming hook example](../master/extras/slack_incoming_hook_example.jpg)

- Set the channel to where you want the story to be told. You will probably
want to dedicate a channel to the story.
- Give the bot a good name. I suggest Storyteller or StorytellerBot.
- **Make a note of the Webhook URL.** Open `config.php` and set `SLACK_HOOK` to
this URL.

#### Outgoing Webhook
![Outgoing hook example](../master/extras/slack_outgoing_hook_example.jpg)

- Set the channel to the same channel as the incoming hook.
- Set the trigger word. I suggest using `!` to keep things simple. The trigger
word must be prefixed to every command.
- Set the URL to where your installation will be located.
- **Make a note of the Webhook Token.** Open `config.php` and set `SLACK_TOKEN`
to this value.

### 2. Get the story
By default Storyteller ships with a very short and simple sample book. It isn't
very fun, so you'll likely want to replace it. You have 3 options.

#### Option 1: Download pre-prepared book
Importing from an official source is painful since many are not available in an
eBook format. So I am reluctantly providing a link to some books that have been
pre-prepared for use. Download the file and replace the contents of `book.php`
with it.

Pre-Prepared book.php:
- [Warlock of Firetop Mountain](https://pastebin.com/raw/vWWTeMFj)
- [Return to Firetop Mountain](https://pastebin.com/raw/7gFq1WTW)
- [Seas of Blood](https://pastebin.com/raw/Y4t3V1kq)
- [Starship Traveller](https://pastebin.com/raw/wxi722M5)

These links will be removed hastily if anyone ever objects. Don't be a jerk. If
use them, make sure you own the books. [Amazon
link](http://www.amazon.com/s?url=search-alias%3Daps&field-keywords=fighting+fantasy)

#### Option 2: Import a book you own
Unless you are lucky enough to find the book you want in an eBook format, your
only option is to scan and OCR the text yourself from a paper book. One you've
converted the book to raw text, you can use can use the `bookconvert.php`
script in the *extras* directory to help you convert raw text to the correct
format. e.g. `php bookconvert.php mybook.txt > book.php`. See `book.php` for an
example book.

#### Option 3: Write your own adventure
Storyteller doesn't have to play the official books. Perhaps one of the best
ways to get a story in to the bot is to write your own. Choose a set of
supported rules you like and come up with your own adventure. If anyone does do
this, please consider submitting your story back here. I'd love to include
longer stories with this distribution.

### 3. Set the booktype / adjust config.php
Open `config.php` and set `BOOK_TYPE` to the correct value for the book you are
playing. This controls which stats are available, the character sheet and
ensures character generation matches the book's rules. You can look up the
correct book type in `book_support.html` found in the *extras* directory.

Use `none` for books with no character sheet.

You may wish to adjust other settings in config.php at this point. You might
like to remove `save` and `load` from the disabled commands list. This will
allow players to use save-points. Alternatively, you could add the `undo`
command to this list if you want to prevent players from undoing their
mistakes.

### 4. Get the code online
- Upload the installation to your PHP enabled web-server. No database is
needed. The uploaded directory must be writeable.
- Make sure it's location matches what you step up for the outgoing hook in
step 1. If it's different go back and alter the hook.
- If you are _not_ using apache, you must replicate the rules in `.htaccess` to
ensure the installation is secure.

### 5. Ready to play!
Type `!newgame` in the channel you chose for the webhook to get started. Type
`!help` to see the basic commands and get a link to the complete list.

## Using Discord

### 1. Outgoing hook replacement
The bot can also be used for Discord. The biggest limitation is Discord does
not support outgoing hooks. You can work around this one of two ways:
1. Run a discord bot to emulate outgoing hooks. There's a working python
example `discord_oghook_bot.py` in the *extras* directory. Setting up a discord
app account and getting tokens ready etc. is left as an exercise for the
reader.
2. Expose `input.php` by editing `.htaccess`. This will allow entering commands
by submitting a URL like
`http:\\yourdomain.com\storyteller\input.php?c=!echo%20Hello%20World!` You
could submit this with a form (see `example-form.html` in the *extras*
directory.) Or use your imagination!
If you come up with a creative input method, please consider submitting it back
here!

### 2. Other complications
- Don't forget to add `/slack` to the end of your discord incoming hook to make
it slack compatible. It will look like
`https://discordapp.com/api/webhooks/0123456789/YOURCODEHERE/slack`
- Set `DISCORD_MODE` to true in `config.php`. This will ensure messages are
formatted correctly for Discord.
- Ensure `images/emoji_cache` is writeable. Discord does not support emoji
avatars, so emoji PNGs are cached and sent instead. This will cause a short
delay when using an emoji for the first time.

## Usage Tips and Hints
- Remember, you have to enforce the rules!
- `!help` to see some basic commands. Or see commands.md or commands.html for
the full list
- If the book asks you to do something there isn't a command for, you can
always roll dice with `!roll` and apply any effects manually.
- `!use` doesn't do anything on it's own. You must still apply the item's
effects manually. e.g. `!use Potion of Skill;!skill 99`
- If a fighting fantasy book asks you to do something after a certain number of
rounds in a fight, you can make the fight end early by putting the number of
rounds end the end of the command. e.g. To stop after 3 rounds: `!fight
Squirrel 5 5 3`

## Technical Information
**Do you accept bug reports?**

Yes. Particularly security issues should be reported. Please provide a test
case and post it on the github project's issue page.

**What about support requests and feature ideas?**

Sure. You can also post them on the issues page. No promises though.

**Will you support other books?**

I'd like to. Please make requests on the issues page.

**What about pull requests / patches etc.?**

Gladly.

**What about a proper bot that isn't state-based / support for other chat
software?**

I'd like to do this, but I'm not sure how to best achieve this with PHP. This
is why the discord outgoing hook bot is written in python.
