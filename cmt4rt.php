<?php
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Exception\ClientException;

function get($client, $url, $option) {
    $response = NULL;
    try {
        $response = $client->get($url, $option);
    } catch (ClientException $e) {
        $req = $e->getRequest();
        $res = $e->getResponse();
        $code = $res->getStatusCode();
        $reason = $res->getReasonPhrase();
        echo 'Error: ' . $code . ' ' . $reason . ' for ' . $url . PHP_EOL;
        if ($code === 429) {
            $time_utc = $e->getResponse()->getHeader('x-rate-limit-reset');
            echo 'The limit will be reset at ' . gmdate('Y/m/d H:i:s', $time_utc + 9 * 60 * 60) . PHP_EOL;
        }
        exit;
    }
    return $response;
}

function oldest_original_tweet($client, $screen_name, $rt_id) {
    $tweet = NULL;
    $query = [
        'include_rts' => 'false',
        'screen_name' => $screen_name,
        'since_id' => $rt_id,
        'count' => 100
    ];
    for ($page = 0; $page < 20; $page++) {
        $query['page'] = $page;
        if (isset($tweet)) {
            $query['max_id'] = $tweet['id_str'];
        }
        $statuses = get($client, 'statuses/user_timeline.json', [
            'query' => $query,
            'auth' => 'oauth'
        ])->json();
        $length = count($statuses);
        if ($length > 0) {
            $tweet = $statuses[$length - 1];
        } else {
            return $tweet;
        }
    }
    return $tweet;
}

function comments_for_rt($client, $target_id) {
    $comments = array();
    $retweets = get($client, 'statuses/retweets/' . $target_id . '.json', [
        'query' => ['count' => 100],
        'auth' => 'oauth'
    ])->json();

    foreach($retweets as $rt) {
        $screen_name = $rt['user']['screen_name'];
        $rt_id = $rt['id_str'];
        $tweet = oldest_original_tweet($client, $screen_name, $rt_id);
        $tweet['retweeted_at'] = $rt['created_at'];
        $tweet['comment_url'] = 'https://twitter.com/' . $screen_name . '/status/' . $tweet['id_str'];
        $comments[] = $tweet;
    }
    return $comments;
}

function client() {
    $keys = parse_ini_file('keys.ini');

    $client = new Client(['base_url' => 'https://api.twitter.com/1.1/']);

    $oauth = new Oauth1([
        'consumer_key'    => $keys['consumer_key'],
        'consumer_secret' => $keys['consumer_secret'],
        'token'           => $keys['access_token'],
        'token_secret'    => $keys['access_token_secret']
    ]);

    $client->getEmitter()->attach($oauth);

    return $client;
}

date_default_timezone_set('Asia/Tokyo');

$target_id = $argv[1];

if (empty($target_id)) {
    echo 'Usage: php cmt4rt.php <id or url of the retweeted tweet' . PHP_EOL;
    exit;
}

if (preg_match('/\/status\/(\d+)/', $argv[1], $matches)) {
    $target_id = $matches[1];
};

$client = client();

$comments = comments_for_rt($client, $target_id);
foreach($comments as $comment) {
    echo $comment['comment_url'] . PHP_EOL;
    echo $comment['text'] . PHP_EOL;
    $rt_time = strtotime($comment['retweeted_at']);
    $comment_time = strtotime($comment['created_at']);
    echo '(commented after ' . gmdate('H:i:s', $comment_time - $rt_time) . ')' . PHP_EOL;
    echo PHP_EOL;
}