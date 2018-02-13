# WarlockBot
## A Slack Bot for playing Fighting Fantasy Gamebooks

### Introduction
WarlockBot is a bot for use with Slack which helps a group of people play through a Fighting Fantasy gamebook co-operatively. It was designed for _Warlock on Firetop Mountain_, but should work fine with any other book using compatible rules.

WarlockBot adds commands to read the story, manage your character and roll dice for fights and other challenges. Warlock Bot is designed to assist your game, but does not strictly enforce rules - replicating the experience of playing with a real book and dice. 

### Set-Up
Download and extract the bot to start.
#### 1. Set-Up Slack
First you must create an incoming and outgoing webhook. In Slack, go to **Administration > Manage Apps > Custom Integrations**.
##### Incoming Webhook
Set the channel to where you want the story to be told. You will probably want to dedicate a channel to the story.
Give the bot a good name. I suggest Storyteller or WarlockBot.
**Make a note of the Webhook URL.** Open `config.php` and set `SLACK_HOOK` to this URL.
##### Outgoing Webhook
Set the channel to the same channel as the incoming hook.
Set the trigger word. I suggest using `!` to keep things simple. The trigger word must be prefixed to every command.
Set the URL to where your installation will be located.
**Make a note of the Webhook Token.** Open `config.php` and set `SLACK_TOKEN` to this value.

#### 2. Get the story
By default WarlockBot ships with a very short and simple sample book. It isn't very fun, so you'll likely want to replace it. You have 3 options.
##### Option 1: Download pre-prepared _Warlock on Firetop Mountain_
Importing from an official source is painful (see the next section.) So I am reluctantly providing a link to _Warlock on Firetop Mountain_ that has been pre-prepared for use. Download this file and replace the contents of `book.php` with it.
http://link.me/dddd
This link will be removed hastily if anyone ever objects. Don't be a jerk. If use this, make sure you own the book. [It's still in print.](https://www.amazon.co.uk/Fighting-Fantasy-Warlock-Firetop-Mountain/dp/1407181300)
##### Option 2: Import a book you own
Unfortunately, the _Fighting Fantasy_ books can no longer be bought in an eBook format. If they were, I'd provide a script to automate extraction. Since they aren't your only option is to scan, OCR and input the text yourself from a paper book. If you're mad enough to attempt this, see `book.php` for details.
##### Option 3: Write your own adventure
WarlockBot doesn't have to play the official books. Perhaps one of the best ways to get a story in to the bot is to write your own. So long as you are compatible with the _Fighting Fantasy_ ruleset, this will work. If anyone does do this, please consider submitting your story back here. I'd love to include longer stories with this distribution.
#### 3. Get the code online
Upload the installation to your PHP enabled web-server. No database is needed. The uploaded directory must be writable.
If you are _not_ using apache, you must replicate the rules in `.htaccess` to ensure the installation is secure.
#### 4. Ready to play!
Type `!newgame` in the channel you chose for the webhook to get started. Type `!help` to see the basic commands or `!helpmore` for the complete list.