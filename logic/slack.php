<?php

// Send a direct message to a user or channel on slack
function senddirmsg($message, $user = false) {
    if (!$user) {
        $user = $_POST['user_id'];
    }
    return sendmsg($message, true, ':open_book:', '@'.$user);
}


// Send a quick and basic message to slack
function sendqmsg($message, $icon = ':open_book:') {
    return sendmsg($message, true, $icon);
}


// Send an image to slack
function sendimgmsg($message, $imgurl, $icon = ':open_book:') {
    $attachments = array([
            'image_url'    => $imgurl
        ]);
    return sendmsg($message, $attachments, $icon);
}


// Full whistles and bells send message to slack
// Normally use one of the convenience functions above
function sendmsg($message, $attachments = false, $icon = ':open_book:', $chan = false) {
    // Split long messages for discord
    if (DISCORD_MODE && strlen($message) > 1975) {
        $m = str_replace(' ', '¥', $message);
        $m = str_replace("\n", " ", $m);
        $m = wordwrap($m, 1950, "[BREAK]", true);
        $m = str_replace(' ', "\n", $m);
        $m = str_replace('¥', ' ', $m);
        $m = explode("[BREAK]", $m);
        $lastm = count($m)-1;
        foreach ($m as $key => $val) {
            if ($key != $lastm) {
                sendmsg($val, false, $icon, $chan);
            } else {
                sendmsg($val, $attachments, $icon, $chan);
            }
        }
        return;
    }

    // Respect any rate limit
    global $limittime;
    if ($limittime) {
        time_sleep_until($limittime);
        $limittime = false;
    }

    $data['text'] = $message;
    if (is_array($attachments)) {
        $data['attachments'] = $attachments;
    }
    if ($chan) {
        $data['channel'] = $chan;
    }
    if (strpos($icon, 'https://') === false) {
        $data['icon_emoji'] = $icon;
    } else {
        $data['icon_url'] = str_replace(['<', '>'], '', $icon);
    }
    // Undocumented hook to allow the config file to alter output
    if (function_exists('hook_alter_output')) {
        hook_alter_output($data);
    }
    if (DISCORD_MODE) {
        discordize($data);
    }
    $data_string = json_encode($data);
    $ch = curl_init(SLACK_HOOK);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
    );
    //Execute CURL
    $result = get_headers_from_curl_response(curl_exec($ch));

    // Look for discord rate limit header
    if (isset($result['x-ratelimit-remaining']) && $result['x-ratelimit-remaining'] == 0) {
        $limittime = $result['x-ratelimit-reset'];
    }

    return true;
}


function get_headers_from_curl_response($response) {
    $headers = array();

    $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

    foreach (explode("\r\n", $header_text) as $i => $line)
        if ($i === 0)
            $headers['http_code'] = $line;
        else {
            list ($key, $value) = explode(': ', $line);

            $headers[strtolower($key)] = $value;
        }

    return $headers;
}


function discordize(&$data) {
    $data['text'] = str_replace(['*', '~'], ['**', '~~'], $data['text']);
    $data['text'] = str_replace('> ', '', $data['text']);
    if (isset($data['attachments'])) {
        foreach ($data['attachments'] as $akey => $aval) {
            if (isset($aval['fields'])) {
                foreach ($aval['fields'] as $fkey => $fval) {
                    if ($fval['title']) {
                        $data['attachments'][$akey]['fields'][$fkey]['title'] = '**'.$data['attachments'][$akey]['fields'][$fkey]['title'].'**';
                    }
                    $data['attachments'][$akey]['fields'][$fkey]['value'] = str_replace(['*', '~'], ['**', '~~'], $data['attachments'][$akey]['fields'][$fkey]['value']);
                }
            }
        }
    }

    if (isset($data['icon_emoji'])) {
        $data['icon_url'] = discordize_emoji($data['icon_emoji']);
        unset($data['icon_emoji']);
    }
}


function discordize_emoji($e) {
    if (!isset($_SERVER['HTTP_HOST'])) {
        // Must be running from command line, can't send emoji
        return '';
    }

    // Check for emoji in cache and send url if found
    $clean = str_replace(':', '', $e);
    $path = 'images'.DIRECTORY_SEPARATOR.'emoji_cache'.DIRECTORY_SEPARATOR.$clean.'.png';
    $url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'images/emoji_cache/'.$clean.'.png';
    if (file_exists($path)) {
        return $url;
    }

    $remoteurl = get_emoji_remote_url($e);
    // Check URL works
    list($width, $height) = getimagesize($remoteurl);
    if (!$width) {
        return '';
    }
    // Download, resize 90x90 and save emoji to cache
    $src = imagecreatefrompng($remoteurl);
    $dst = imagecreatetruecolor(90, 90);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, 90, 90, $width, $height);
    imagepng($dst, $path);

    return $url;
}


function get_emoji_remote_url($e) {
    $emojis = explode(':', $e);
    $emoji2html = json_decode(file_get_contents('resources/slack_emoticons_to_html_unicode.json'), 1);
    $url = "";
    foreach ($emojis as $e) {
        if (!$e) continue;
        $e = $emoji2html[$e];
        $e = html_entity_decode($e);
        $url .= $e;
    }
    // Special cases, bleh
    if ($emojis[1] == 'male_elf' || $emojis[1] == 'blond-haired-man' || $emojis[1] == 'male_mage') {
        $url .= html_entity_decode('&#x200D;&#x2642;');
    } else if ($emojis[1] == 'female_elf' || $emojis[1] == 'blond-haired-woman' || $emojis[1] == 'female_mage') {
        $url .= html_entity_decode('&#x200D;&#x2640;');
    }
    $url = "https://xn--i-7iq.ws/emoji-image/".urlencode($url).".png";//?format=emojione&ar=1x1";

    return $url;
}
