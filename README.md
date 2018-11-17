# Storyteller
## A Slack Bot for playing Fighting Fantasy Gamebooks (v1.01)

## Introduction
Storyteller is a bot for use with Slack which helps a group of people play through a Fighting Fantasy gamebook co-operatively. It was designed for _Warlock on Firetop Mountain_, but should work fine with any other book using compatible rules.

Storyteller adds commands to read the story, manage your character and roll dice for fights and other challenges. It will assist you in playing the game but does not strictly enforce rules - replicating the experience of playing with a real book and dice.

Storyteller is programmed in PHP, requires no database and is state-based. This means it can be installed on an ordinary web-server, without the need to setup a complicated chat-bot.

## Set-Up
Download and extract the bot to start.

### 1. Set-Up Slack
First you must create an incoming and outgoing webhook. In Slack, go to **Administration > Manage Apps > Custom Integrations**.

#### Incoming Webhook
![Incoming hook example](../master/example-slack-webhooks/slack_incoming_hook_example.jpg)

- Set the channel to where you want the story to be told. You will probably want to dedicate a channel to the story.
- Give the bot a good name. I suggest Storyteller or StorytellerBot.
- **Make a note of the Webhook URL.** Open `config.php` and set `SLACK_HOOK` to this URL.

#### Outgoing Webhook
![Outgoing hook example](../master/example-slack-webhooks/slack_outgoing_hook_example.jpg)

- Set the channel to the same channel as the incoming hook.
- Set the trigger word. I suggest using `!` to keep things simple. The trigger word must be prefixed to every command.
- Set the URL to where your installation will be located.
- **Make a note of the Webhook Token.** Open `config.php` and set `SLACK_TOKEN` to this value.

### 2. Get the story
By default Storyteller ships with a very short and simple sample book. It isn't very fun, so you'll likely want to replace it. You have 3 options.

#### Option 1: Download pre-prepared _Warlock on Firetop Mountain_ or _Return to Firetop Mountain_
Importing from an official source is painful (see the next section.) So I am reluctantly providing a link to _Warlock on Firetop Mountain_ and _Return to Firetop Mountain_ that has been pre-prepared for use. Download this file and replace the contents of `book.php` with it.

- [Pre-prepared WoFM book.php](https://pastebin.com/raw/rwbfuT6L)
- [Pre-prepared RtFM book.php](https://pastebin.com/raw/GSL5sY7B)

This link will be removed hastily if anyone ever objects. Don't be a jerk. If use this, make sure you own the book. [It's still in print.](https://www.amazon.co.uk/Fighting-Fantasy-Warlock-Firetop-Mountain/dp/1407181300)

#### Option 2: Import a book you own
Unfortunately, the _Fighting Fantasy_ books can no longer be bought in an eBook format. If they were, I'd provide a script to automate extraction. Since they aren't your only option is to scan, OCR and input the text yourself from a paper book. If you're mad enough to attempt this, see `book.php` for details.

#### Option 3: Write your own adventure
Storyteller doesn't have to play the official books. Perhaps one of the best ways to get a story in to the bot is to write your own. So long as you are compatible with the _Fighting Fantasy_ ruleset, this will work. If anyone does do this, please consider submitting your story back here. I'd love to include longer stories with this distribution.

### 3. Get the code online
- Upload the installation to your PHP enabled web-server. No database is needed. The uploaded directory must be writeable.
- Make sure it's location matches what you step up for the outgoing hook in step 1. If it's different go back and alter the hook.
- If you are _not_ using apache, you must replicate the rules in `.htaccess` to ensure the installation is secure.

### 4. Ready to play!
Type `!newgame` in the channel you chose for the webhook to get started. Type `!help` to see the basic commands or `!helpmore` for the complete list.

## Usage Tips and Hints
- Remember, you have to enforce the rules!
- If the book asks you to do something there isn't a command for, you can always roll dice with `!roll` and apply any effects manually.
- To easily restore a stat to max, just set it to 99 and it'll get set to the highest possible value.
- `!use` doesn't do anything on it's own. You must still apply the item's effects manually. e.g. `!use Potion of Skill;!skill 99`
- If the book asks you to do something after a certain number of rounds in a fight, you can make the fight end early by putting the number of rounds end the end of the command. e.g. To stop after 3 rounds: `!fight Squirrel 5 5 3`
- If the book says you get a bonus to your attack for just one fight, you can apply it to your weapon bonus and remove it at the end. e.g. `!weapon +1;!fight Squirrel 5 5;!weapon -1`

## Technical Information
**Do you accept bug reports?**

Yes. Particularly security issues should be reported. Please provide a test case and post it on the github project's issue page.

**What about support requests and feature ideas?**

Sure. You can also post them on the issues page. No promises though.

**What about pull requests / patches etc.?**

Gladly.

**Where's the object-orientation?**

There isn't any. This was pretty much a single use script that I've genericised just enough that it can be installed elsewhere. I've tried to clean the code up enough to allow easy hackablity, but drastic expansions will likely require a refactor.

**What about support for other chat software (Discord, IRC etc.) or other books/systems?**

All these would be nice. But I'll have to refactor the code before considering them.
