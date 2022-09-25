<?php

function checkRemoteFile($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    // don't download content
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    var_dump(curl_getinfo($ch));
    curl_close($ch);
    return curl_exec($ch) !== FALSE;
}

$url = 'https://www.avc.edu/sites/default/files/studentservices/lc/Pictures/blog1.jpg';
checkRemoteFile($url);

$headers = get_headers($url,1);
print_r($headers);