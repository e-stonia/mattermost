<?php
# Later check if <16383 characters as Mattermost doesn't allow message to be longer. If longer - send multiple messages.
# https://developers.mattermost.com/integrate/incoming-webhooks/ - documentation
# "channel" and "attachments" not working (also probably not so important "icon_url" also not working)

// Get the key for the incoming webhook
include 'conf.php';
$url = 'https://messages.crewnew.com/hooks/' . $key;

// Get cURL resource
$curl = curl_init();

// Set some options - we are passing in a useragent too here
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $url,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => [
       'username' => 'crewnew', //username from where the message is sent
       'text' => '-- skype -- integration test here sent via webhook' //message that is sent
    ]
]);

// Send the request & save response to $resp
$resp = curl_exec($curl);

// Close request to clear up some resources
curl_close($curl);

echo 'Result: ' . $resp; //if works then the result is "ok"
