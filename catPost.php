<?php

session_start();

require_once 'twitter/cat_common.php';
require_once 'twitter/twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

//OAuthトークンとシークレットも使って TwitterOAuth をインスタンス化
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

$pictures = getTumblrPictures();

foreach($pictures as $picture) {
    if(mt_rand(0, 1) == 1) {
        $tweetTexts = array(
            "ごろごろ〜",
            "にゃーん",
            "にゃおーん",
            "にゃんにゃん"
        );
        shuffle($tweetTexts);
        $res = $connection->post("statuses/update", array(
            "status" => $tweetTexts[0],
        ));
        sleep(180);
    }
    //認証コードの下に追加
    $media_id = $connection->upload("media/upload", array("media" => $picture));

    // tweet
    $tweetText = "かわいい猫の写真";
    $res = $connection->post("statuses/update", array(
        "status" => $tweetText,
        'media_ids' => $media_id->media_id_string,
    ));
    sleep(180);
}


function getTumblrPictures() {
    $url = "https://www.tumblr.com/search/catphoto/recent";
    $html = file_get_contents($url);
    $htmlArray = explode("\n", $html);
    $pictures = array();
    foreach($htmlArray as $htmlLine) {
        if(strpos($htmlLine, "src") !== false) {
            if(preg_match("/src=\"(.*media.tumblr.*jpg)/", $htmlLine, $matches)) {
                if(isset($matches[1])) {
                    $pictures[] = $matches[1];
                }
            }
        }
    }
    shuffle($pictures);
    return $pictures;
}