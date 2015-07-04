<?php

session_start();

require_once 'twitter/dog_common.php';
require_once 'twitter/twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

set_time_limit(3600);

//OAuthトークンとシークレットも使って TwitterOAuth をインスタンス化
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

$pictures = getTumblrPictures();

foreach($pictures as $picture) {

    $post = array(
        "status" => $picture["tweet_text"]
    );

    if(isset($picture["media_url"])) {
        //認証コードの下に追加
        $media_id = $connection->upload("media/upload", array("media" => $picture["media_url"]));
        $post["media_ids"] = $media_id->media_id_string;
    }


    // tweet
    $res = $connection->post("statuses/update", $post);
    sleep(180);
}


function getTumblrPictures() {
    $queries = array(
        "dogphoto",
        "dogpicture",
        "dogsofig",
        "dogphotography",
        "dogstagram"
    );
    shuffle($queries);
    $url = "https://www.tumblr.com/search/" . $queries[0] . "/recent";
    $html = file_get_contents($url);
    $htmlArray = explode("\n", $html);
    $mongonArray = array(
        "かわいいと思ったらRTワン",
        "かわいいワン #dog",
        "カワイイ写真だワン",
        "かわいいワンちゃんの写真だワン♪ #dog",
        "かわいいワンちゃんの写真だワン",
        "かわいい犬の写真だワン #dog",
    );
    $pictures = array();
    foreach($htmlArray as $htmlLine) {
        if(strpos($htmlLine, "src") !== false) {
            if(preg_match("/src=\"(.*media.tumblr.*jpg)/", $htmlLine, $matches)) {
                if(isset($matches[1])) {
                    $randKeys = array_rand($mongonArray, 1);
                    $pictures[] = array(
                        "tweet_text" => $mongonArray[$randKeys],
                        "media_url" => $matches[1],
                    );
                }
            }
        }
    }
    // dummy
    $tweetTexts = array(
        "ごろごろ〜",
        "わんわん",
        "わんわん♪",
        "がるるる〜",
        "わおーん",
        "わおーん",
        "ワン",
    );
    foreach($tweetTexts as $tweetText) {
        $pictures[] = array(
            "tweet_text" => $tweetText,
        );
    }

    // aff
    $pictures[] = array(
        "tweet_text" => "かわいいワンちゃんの写真集だワン♪ http://www.amazon.co.jp/dp/4904845110/?tag=labeinu-22",
        "media_url" => "http://ecx.images-amazon.com/images/I/41th4rJHMgL.jpg",
    );
    $pictures[] = array(
        "tweet_text" => "かわいいワンちゃんの写真集だワン http://www.amazon.co.jp/dp/4040667638/?tag=labeinu-22",
        "media_url" => "http://ecx.images-amazon.com/images/I/81tifdRG69L.jpg",
    );
    shuffle($pictures);
    return $pictures;
}