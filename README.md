# php-cmt4rt (comments for RT)

コンソールから使います。
指定したリツイートに対するコメント（エアリプ）っぽいものを探して表示します。


## 事前準備

https://apps.twitter.com で以下を作ります。

* consumer_key
* consumer_secret
* access_token
* access_token_secret

[Create New App]でアプリを作った後、Keys and Access Tokens タブの Token Actions で access_token, access_token_secret を作ります。

*_secret は安全なところに隠しましょう。

## インストール

```
$ git clone https://github.com/takatama/php-cmt4rt.git
$ cd php-cmt4rt
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar update
```
key.ini.template を key.ini にリネームして、取得した情報を書き込みます。

```keys.ini
consumer_key=ここに書く
consumer_secret=ここに書く
access_token=ここに書く
access_token_secret=ここに書く
```

## 使い方

調査したいツイートのURLか、IDを指定します。
例えば、これの場合
<blockquote class="twitter-tweet" lang="ja"><p>悪い知らせを受けた時に、まず「報告してくれてありがとう」と言える、大きな人物になりたいなー。早い段階で報告されなくなったら、おしまいだもの。「だから言ったのに！」って今言われても凹むし、次は黙ってようと思っちゃうよ…</p>&mdash; 高玉広和 (@takatama_jp) <a href="https://twitter.com/takatama_jp/status/547687296988770304">2014, 12月 24</a></blockquote>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

```
$ php cmt4rt.php https://twitter.com/takatama_jp/status/547687296988770304
```

または

```
$ php cmt4rt.php 547687296988770304
```

## 難しいところ

* エアリプが気になるくらいなら、ツイートを控え(ry
* エアリプかどうかは判断が難しいですね。参考のため、RTの後、コメントされるまでの時間も表示してます。
* Twitter API の制限がきつくて、たくさんRTされたものは調べきれないです。Web アプリにするのは難しそうですね。
* Guzzle の非同期 API 使ったら、体感速度はもうちょい上がると思います。ばりばりの手続き型で書いちゃってます。

## 参考にした情報
* RtRTはAPI廃止でダメになった→すまん、ありゃウソだった - esuji5's diary
http://esuji5.hateblo.jp/entry/2014/04/01/233633

* PHP - Guzzle で Twitter REST API を叩く - Qiita
http://qiita.com/kawanamiyuu/items/2cab57e3d3932a2c5e4e

* API Rate Limits | Twitter Developers
https://dev.twitter.com/rest/public/rate-limiting

* GET statuses/retweets/:id | Twitter Developers
https://dev.twitter.com/rest/reference/get/statuses/retweets/%3Aid

* GET statuses/user_timeline | Twitter Developers
https://dev.twitter.com/rest/reference/get/statuses/user_timeline

