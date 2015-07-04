<?php

session_start();

require_once 'twitter/cat_common.php';
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
        "catphoto",
        "catpicture",
        "catsofig",
        "catphotography",
        "catstagram"
    );
    shuffle($queries);
    $url = "https://www.tumblr.com/search/" + $queries[0] + "/recent";
    $html = file_get_contents($url);
    $htmlArray = explode("\n", $html);
    $mongonArray = array(
        "かわいいと思ったらRTするにゃん",
        "かわいいと思ったらRTするニャ",
        "カワイイと思ったらRTするニャ",
        "かわいい猫ちゃんの写真だにゃん♪",
        "かわいい猫ちゃんの写真だニャ",
        "かわいいネコちゃんの写真だニャン",
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
        "にゃーん",
        "にゃおーん♪",
        "にゃんにゃん",
        "ごろ〜ん",
        "ごろにゃ〜ご",
        "ごろごろ〜",
        "ヽ(=^・ω・^=)丿"
    );
    foreach($tweetTexts as $tweetText) {
        $pictures[] = array(
            "tweet_text" => $tweetText,
        );
    }

    // aff
    $pictures[] = array(
        "tweet_text" => "かわいい猫の写真集にゃん♪ http://www.amazon.co.jp/%E5%B2%A9%E5%90%88%E5%85%89%E6%98%AD%E5%86%99%E7%9C%9F%E9%9B%86-%E3%81%AD%E3%81%93%E8%BC%9D%E3%81%8F-%E5%B2%A9%E5%90%88-%E5%",
        "media_url" => "https://pbs.twimg.com/media/CJEa60EUcAEoMt6.jpg",
    );
    shuffle($pictures);
    return $pictures;
}