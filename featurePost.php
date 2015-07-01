<?php

session_start();

require_once 'twitter/common.php';
require_once 'twitter/twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

//OAuthトークンとシークレットも使って TwitterOAuth をインスタンス化
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

$featureString = getFeatureString();
echo $featureString;
$res = $connection->post("statuses/update", array("status" => $featureString));


function getFeatureString() {
    $url = "http://seiga.nicovideo.jp/manga";
    $html = file_get_contents($url);
    $htmlArray = explode("\n", $html);
    $features = array();
    $featureStart = false;
    $isTitle = false;
    $isDescription = false;
    $featureTitle = "";
    $comicTitle = "";
    $comicDescription = "";
    $comicId = "";
    foreach($htmlArray as $htmlLine) {
        if($featureStart) {
            if(strpos($htmlLine, "<h3>") !== false) {
                $featureTitle = strip_tags(trim($htmlLine));
            }
            if($isTitle) {
                $comicTitle = trim($htmlLine);
                $isTitle = false;
            }
            if($isDescription) {
                if(strpos($htmlLine, "</p>") !== false) {
                    $isDescription = false;
                } else {
                    $comicDescription .= trim($htmlLine);
                }
            }
            if(strpos($htmlLine, "/comic/") !== false) {
                preg_match("/\/comic\/([0-9]+)/", $htmlLine, $matches);
                $comicId = $matches[1];
                $isTitle = true;
            }
            if(strpos($htmlLine, "</strong>") !== false) {
                $isDescription = true;
            }
        }
        if(strpos($htmlLine, "mg_feature_header") !== false) {
            $featureStart = true;
        }
        if(strpos($htmlLine, "mg_thumb_img_wrapper") !== false) {
            $featureStart = false;
        }
    }

    $str = "【" . $featureTitle . "】" . PHP_EOL . "『" . $comicTitle . "』" . PHP_EOL .
            $comicDescription . PHP_EOL . "http://seiga.nicovideo.jp/comic/" . $comicId . "?track=twitter";

    return $str;
}

