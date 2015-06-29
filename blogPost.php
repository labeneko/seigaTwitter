<?php

session_start();

require_once 'twitter/common.php';
require_once 'twitter/twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

//OAuthトークンとシークレットも使って TwitterOAuth をインスタンス化
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

$time = time();
$cronInterval = 3600;
date_default_timezone_set('Asia/Tokyo');
$articles = getArticle();

$tweetArticles = array();
foreach($articles as $article) {
    if($time - $cronInterval > $article["pubDate"]) {
        break;
    }
    $tweetArticles[] = $article;
}

// tweet
foreach($tweetArticles as $article) {
    $tweetText = "";
    foreach($article["category"] as $category) {
        $tweetText .= "[" . $category . "]";
    }
    $tweetText .= " " . $article["title"] . " " . $article["link"];
    echo $tweetText . PHP_EOL;
    $res = $connection->post("statuses/update", array("status" => $tweetText));
    echo $res;
}


function getArticle() {
    $context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
    $url = "http://blog.nicovideo.jp/seiga/index.xml";
    $xml = file_get_contents($url, false, $context);
    $xml = simplexml_load_string($xml);
    $articles = array();
    foreach($xml->channel->item as $article) {
        $categories = array();
        $category = json_decode(json_encode($article->category), true);
        for($i = 0; $i < $article->category->count(); $i++) {
            $categories[] = $category[$i];
        }
        $articles[] = array(
            "title" => (String)$article->title,
            "link" => (String)$article->link,
            "pubDate" => strtotime((String)$article->pubDate),
            "category" => $categories
        );
    }

    return $articles;
}

