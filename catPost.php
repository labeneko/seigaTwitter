<?php

session_start();

require_once 'twitter/cat_common.php';
require_once 'twitter/twitteroauth/autoload.php';
require_once '../tesseract-ocr-for-php/TesseractOCR/TesseractOCR.php';

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
    sleep(300);
}


function getTumblrPictures() {
    $queries = array(
        //"catphoto",
        "catpicture",
        "catsofig",
        "catphotography",
        "catstagram",
        "%E3%81%AD%E3%81%93%E9%83%A8"
    );
    shuffle($queries);
    $url = "https://www.tumblr.com/search/" . $queries[0] . "/recent";
    $html = file_get_contents($url);
    $htmlArray = explode("\n", $html);
    $mongonArray = array(
        "かわいいと思ったらRTするにゃん",
        "かわいいにゃーん",
        "カワイイ写真ニャ〜",
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
                    $mediaUrl = $matches[1];
                    $image_path = file_get_contents($mediaUrl, FILE_BINARY); // 画像ファイルを取得
                    file_put_contents("checkimg", $image_path);
                    $tesseract = new TesseractOCR('checkimg');
                    if($tesseract->recognize()) {
                        continue;
                    }
                    $pictures[] = array(
                        "tweet_text" => $mongonArray[$randKeys],
                        "media_url" => $mediaUrl,
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
        "tweet_text" => "かわいい猫の写真集にゃん♪ http://www.amazon.co.jp/dp/4777814696/?tag=labeinu-22",
        "media_url" => "https://pbs.twimg.com/media/CJEa60EUcAEoMt6.jpg",
    );
    $pictures[] = array(
        "tweet_text" => "かわいい猫の写真集にゃ http://www.amazon.co.jp/dp/4047317314/?tag=labeinu-22",
        "media_url" => "http://ecx.images-amazon.com/images/I/91HngifsNpL.jpg",
    );
    $pictures[] = array(
        "tweet_text" => "美しい猫の写真集にゃ http://www.amazon.co.jp/dp/4767817358/?tag=labeinu-22",
        "media_url" => "http://ecx.images-amazon.com/images/I/51sqb8%2B2v0L.jpg",
    );
    shuffle($pictures);
    return $pictures;
}