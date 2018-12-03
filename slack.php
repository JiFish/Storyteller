<?php

// Send a direct message to a user or channel on slack
function senddirmsg($message, $user = false)
{
    if (!$user) {
        $user = $_POST['user_id'];
    }
    return sendmsg($message, true, ':open_book:', '@'.$user);
}

// Send a quick and basic message to slack
function sendqmsg($message, $icon = ':open_book:')
{
    return sendmsg($message, true, $icon);
}

// Send an image to slack
function sendimgmsg($message, $imgurl, $icon = ':open_book:')
{
    $attachments = array([
            'image_url'    => $imgurl
    ]);
    return sendmsg($message, $attachments, $icon);
}

// Full whistles and bells send message to slack
// Normally use one of the convenience functions above
function sendmsg($message, $attachments = array(), $icon = ':open_book:', $chan = false)
{
    $data = array(
        'text'        => $message,
        'attachments' => $attachments
    );
    if ($chan) {
        $data['channel'] = $chan;
    }
    if (strpos($icon,'https://') === false) {
        $data['icon_emoji'] = $icon;
    } else {
        $data['icon_url'] = str_replace(['<','>'],'',$icon);
    }
    $data_string = json_encode($data);
    $ch = curl_init(SLACK_HOOK);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
    //Execute CURL
    $result = curl_exec($ch);
    return $result;
}
