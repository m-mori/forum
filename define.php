<?php
// SWC ethna/subarucommunity パス設定
define('SWC_PROJECT_PATH', '/hostdev/swc/osaka_var_opt/ethna/subarucommunity/');
//define('SWC_PROJECT_PATH', '/var/opt/ethna/subarucommunity/');
// TODO: SWC ドメイン名 ※プロトコルリラティブで設定
define("SWC_MEMBERS", '//members.swc.dev/');
//define("SWC_MEMBERS", '//members.subaru.dev/');

// ログインurl
define("SWC_MEMBERS_LOGIN", SWC_MEMBERS .'login/');
// 新規登録url
define("SWC_MEMBERS_ENTRY", SWC_MEMBERS .'entry/');

// TODO: SNSボタン設定
define("SWC_TW_BTN", 1);        // Twitter ボタン
define("SWC_FB_BTN", 1);        // Fb ボタン
define("SWC_LIKE_BTN", 1);      // SWCいいね ボタン

// 画像サイズ
define("SWC_IMG_MAX_H", 1024);  // MAX値 縦
define("SWC_IMG_MAX_W", 1024);  // MAX値 横
// サムネイル画像サイズ
define("SWC_IMG_THUMB_H", 150);  // サムネイル 縦
define("SWC_IMG_THUMB_W", 200);  // サムネイル 横

// 一覧メイン画像サムネイル表示設定 1: 表示
define("SWC_MAIN_THUMB_DISPLAY", 1);

// デフォルト画像 サムネイルパス
define("SWC_NOIMG_THUMB_PATH", "img/noimage.jpg");

// 新着記事一覧件数 デフォルト: 30
define("SWC_LIST_CNT_DEFAULT", 30);

