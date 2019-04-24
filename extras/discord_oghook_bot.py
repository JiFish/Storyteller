# Requires Python 3.6
# Use pip to install discord and requests
# Set TOKEN to your Discord OAuth Token
# Set HOOKS to match your outgoing hooks

import discord
import requests

# Discord Token
TOKEN = 'YOURDISCORDOAUTHTOKENHERE'

# Outgoing Hooks to emulate
HOOKS = [
    {
        'url': 'https://mydomain.com/myhook',
        'token': 'secret',
        'trigger_word': '!',
        'channels': ['mychannel']
    },
    {
        'url': 'https://mydomain.com/myotherhook',
        'token': 'anothersecret',
        'trigger_word': '!',
        'channels': ['mychannel','myotherchannel']
    }
]

client = discord.Client()

print("Starting...")

@client.event
async def on_message(message):
    # Skip own messages
    if message.author == client.user:
        return

    # Look for messages that match our hooks
    for h in HOOKS:
        if (message.channel.name in h.channels) and (message.content.startswith(h.trigger_word)):
            r = requests.post(h.url, data={'text': message.content, 'trigger_word': h.trigger_word, 'token': h.token})
            print(r.status_code, r.reason)

@client.event
async def on_ready():
    print('Logged in as'+client.user.name+'#'+client.user.id)

client.run(TOKEN)
