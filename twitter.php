<?php
require_once 'twitter/twitteroauth.php';

function postTwitter($status) {
    $to = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET,
                           TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_TOKEN_SECRET);
    $req = $to->OAuthRequest(TWITTER_UPDATE_URL, "POST", array("status"=>"$status"));
}
