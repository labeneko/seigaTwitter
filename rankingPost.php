<?php

session_start();

require_once 'twitter/common.php';
require_once 'twitter/twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

set_time_limit(6000);
date_default_timezone_set('Asia/Tokyo');

//OAuthトークンとシークレットも使って TwitterOAuth をインスタンス化
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

$limit = 10;
$rankingArray = getRankingArray($limit);
$rankingList = convertRankingList($rankingArray);
$rankingInfo = $rankingArray["ranking"]["info"];

$infoString = getDateString($rankingInfo["created"]) . " 総合" . getSpanString($rankingInfo["span"]) . "ランキング" . PHP_EOL;

$rankingList = array_reverse($rankingList);
foreach($rankingList as $content) {
    $rankingString = $infoString . "第" . $content["rank"] . "位 『" . $content["title"] . "』" . PHP_EOL;
    $rankingString .= "作者: " . $content["author"] . PHP_EOL;
    $rankingString .= "http://seiga.nicovideo.jp/comic/" . $content["id"] . "?track=twitter";
    echo $rankingString;
    $res = $connection->post("statuses/update", array("status" => $rankingString));
    sleep(180);
}

function getRankingArray($limit) {
    $context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
    $url = "http://seiga.nicovideo.jp/api/manga/ranking?limit=" . $limit;
    $xml = file_get_contents($url, false, $context);
    $xmlObject = simplexml_load_string($xml);
    return json_decode(json_encode($xmlObject),true);
}
function convertRankingList($rankingInfo) {
    $result = array();
    foreach($rankingInfo["ranking"]["episode_list"]["episode"] as $content) {
        $_content = array(
            "rank" => $content["rank"],
            "id" => $content["content_id"],
            "title" => $content["content_title"],
            "author" => $content["author"]
        );
        $result[$content["rank"]] = $_content;
    }
    return $result;
}

function getSpanString($span) {
    switch($span) {
        case "daily":
            return "デイリー";
        case "hourly":
            return "毎時";
        default:
            return null;
    }
    return null;
}

function getDateString($date) {
    return date("n月j日", strtotime($date));
}

