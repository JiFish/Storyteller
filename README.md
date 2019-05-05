# Storyteller
## A Slack / Discord* Bot for playing Gamebooks (v2.0)

## Introduction
Storyteller is a bot for use with Slack which helps a group of people play
through a gamebook co-operatively. It was designed for _Fighting Fantasy_, but
can now play a number of different gamebook types, including _Lone Wolf_.

It should work with almost any gamebook.

Storyteller adds commands to read the story, manage your character and roll
dice for fights and other challenges. It will assist you in playing the game
but does not strictly enforce rules - replicating the experience of playing
with a real book and dice.

Storyteller is programmed in PHP, requires no database and is state-based. This
means it can be installed on an ordinary web-server, without the need to setup
a complicated chat-bot.

* Discord is also supported, with some caveats, see *Using Discord* below.

## Book Support

Anything without a character sheet is supported, this includes the
_Choose Your Own Adventure_ books.

The following _Fighting Fantasy_ books currently work very well: Battleblade
Warrior, Black Vein Prophecy, Bloodbones, Caverns of the Snow Witch, The
Citadel of Chaos, City of Thieves, Creature of Havoc, Crypt of the Sorcerer,
Deathmoor, Deathtrap Dungeon, Demons of the Deep, Eye of the Dragon, The Forest
of Doom, House of Hell, Island of the Lizard King, Legend of Zagor, Masks of
Mayhem, Portal of Evil, Rebel Planet, Return to Firetop Mountain, Scorpion
Swamp, Seas of Blood, Starship Traveller, Stealer of Souls, Talisman of Death,
Temple of Terror, Trial of Champions, The Warlock of Firetop Mountain. Most
other can still be played.

The first 20 _Lone Wolf_ books have good support, as do the 8
_Lone Wolf: New Order_ books.

The following other books / series are supported: Alice's Nightmare in Wonderland,
The Crystal Maze Adventure, The Sonic The Hedgehog Gamebooks, The Narnia Solo
Games books.

The full list of supported books is here:
http://htmlpreview.github.io/?https://github.com/JiFish/Storyteller/master/extras/book_support.html

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
- **Make a note of the Webhook URL.** Open `config.ini` and set `slack_hook` to
this URL.

#### Outgoing Webhook
![Outgoing hook example](../master/extras/slack_outgoing_hook_example.jpg)

- Set the channel to the same channel as the incoming hook.
- Set the trigger word. I suggest using `!` to keep things simple. The trigger
word must be prefixed to every command.
- Set the URL to where your installation will be located.
- **Make a note of the Webhook Token.** Open `config.ini` and set `slack_token`
to this value.

### 2. Get the story
By default Storyteller ships with a very short and simple sample book. It isn't
very fun, so you'll likely want to replace it. You have 3 options.

#### Option 1: Install Lone Wolf books
Joe Dever has generously allowed Project Aon to host copies of the Lone Wolf
gamebooks. I've provided a tool that downloads the books ready for use. If
you go with this option, the config.ini file will be updated for you, and you
can skip section 3. Run the script and follow the on-screen prompts:
```
php tools/install_lonewolf.php
```

Consider making a donation to Project Aon: https://www.projectaon.org/en/Main/HelpUs#donations

#### Option 2: Import a book you own
Storyteller comes with two tools for importing existing texts: one for plain
text and one for htmlz. Use htmlz if your book has images. If you own the
book in a eBook format, you can use the tool calibre (https://calibre-ebook.com/)
to convert it to one of these two formats.

Many books, notably Fighting Fantasy, are not currently available in eBook
formats. Your only option here is to scan and OCR the text yourself!

The text is converted to a php array. See `jofm.php` in *books* for an example
book. The converters aren't perfect and you may need to clean up the output
manually.

##### Import from plain text .txt file
Use the bookconvert.php script in the *tools* directory e.g.
```
php tools/bookconvert.php mybook.txt mybook
```

##### Import from .htmlz file
htmlzconvert.php will attempt to extract images from the book to Storyteller's
images directory at the same time as converting the text.
```
php tools/htmlzconvert.php mybook.htmlz mypicturebook
```

#### Option 3: Write your own adventure
Perhaps one of the best ways to get a story in to the bot is to write your own.
Choose a set of supported rules you like and come up with your own adventure.
If anyone does do this, please consider submitting your story back here. I'd
love to include longer stories with this distribution.

### 3. Add your book to config.ini
Once you have your book php file, put it in the `books` directory. Next add a
new section to `config.ini` for your book.

- The section name will be used as the book id.
- `name` is the full title of the book.
- `file` is the php file from step 2.
- `rules` is the ruleset to use for the book. This controls which stats are
available, the character sheet and ensures character generation matches the
book. You can look up the correct book type in `book_support.html` found in
the *extras* directory. Use `none` for books with no character sheet.

Here is an example section:
```
[wofm]
name = "Warlock of Firetop Mountain"
file = books/wofm.php
rules = ff_wofm
```

Set `default_book` under `[general]` to your book id.

### 3a. Add book images (optional)

Create a directory in `images` with the name of your book id and copy in
illustrations. The images should be named after the page number e.g.
`42.jpg` or `1.png`

### 4. Adjust other config.ini settings (optional)

You may wish to adjust other settings in config.php at this point. You might
like to remove `save` and `load` from the disabled commands list. This will
allow players to use save-points. Alternatively, you could add the `undo`
command to this list if you want to prevent players from undoing their
mistakes.

### 5. Get the code online
- Upload the installation to your PHP enabled web-server. No database is
needed. The uploaded directory must be writeable.
- Make sure it's location matches what you step up for the outgoing hook in
step 1. If it's different go back and alter the hook.
- If you are _not_ using apache, you must replicate the rules in `.htaccess` to
ensure the installation is secure.

### 6. Ready to play!
Type `!library` in the channel you chose for the webhook to see a list of available
books and open one with the `!book` commands shown.

Type `!0` to read the background page and `!1` to start the story. When the
story tells you to turn to a page or section, you can read it in the same way,
e.g. `!42`.

For games with character sheets, like Lone Wolf or Fighting Fantasy, the use
`!newgame` to roll a character.

`!help` will list other commands available for the open gamebook.

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
- Set `discord_mode` to true in `config.ini`. This will ensure messages are
formatted correctly for Discord.
- Ensure `images/emoji_cache` is writeable. Discord does not support emoji
avatars, so emoji PNGs are cached and sent instead. This will cause a short
delay when using an emoji for the first time.

## Usage Tips and Hints
- Remember, you have to enforce the rules!
- `!help` to see some basic commands. Help will be customised depending on the
ruleset.
- If a book adds complications to the normal `!fight` rules. Check `!help`
to see if there is parameter or alternate command that can help you. The
Fighting Fantasy rulesets has several alternate fight rules programmed. Most
others allow you to stop fights after a given number of rounds.
- If the book asks you to do something there isn't a command for, you can
always roll dice with `!roll` and apply any effects manually.

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
