<?php
require_once 'curl.php';
define('BITLY_API_URL', 'http://api.bitly.com');
define('BITLY_API_KEY', '');
define('BITLY_USER',    '');

function shortenURL($long_url) {
    $url = BITLY_API_URL.'/v3/shorten?login='.BITLY_USER.'&apiKey='.BITLY_API_KEY.'&longUrl='.urlencode($long_url).'&format=xml';
    $response = getWebAPI($url);
    if (empty($response)) return false;

    $xml = simplexml_load_string($response);
    $short_url = $xml->data->url;

    return $short_url;
}
