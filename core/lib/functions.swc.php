<?php
// Copyright 2016 

if (!defined("IN_ESOTALK")) exit;

// SWC definitions ---------------------------------//
//if (!defined("SWC_MEMBERS")) define("SWC_MEMBERS", '//members.swc.dev/');    // TODO: domain name
if (!defined("SWC_MEMBERS")) define("SWC_MEMBERS", '//members.subaru.dev/');    // TODO: domain name
if (!defined("SWC_MEMBERS_LOGIN")) define("SWC_MEMBERS_LOGIN", SWC_MEMBERS .'login/');    // TODO: domain name
if (!defined("SWC_MEMBERS_ENTRY")) define("SWC_MEMBERS_ENTRY", SWC_MEMBERS .'entry/');    // TODO: domain name

// TODO: SNSボタン
if (!defined("SWC_TW_BTN")) define("SWC_TW_BTN", 1);        // Twitter ボタン
if (!defined("SWC_FB_BTN")) define("SWC_FB_BTN", 1);        // Fb ボタン
if (!defined("SWC_LIKE_BTN")) define("SWC_LIKE_BTN", 1);    // SWCいいね ボタン

//const FMT_TIME = 'Y年m月d日 H時i分';
const FMT_TIME = 'Y/m/d H:i';

// -------------------------------------------------//


/**
 * SWC側へのリンクurl取得
 * @param type $name
 * @return string
 */
function getSwcUrl($name) {
    if ($name=='login') {
        return SWC_MEMBERS_LOGIN;
    } else if ($name=='entry') {
        return SWC_MEMBERS_ENTRY;
    }
    return "";
}

/**
 * 日付型の文字列フォーマット変換
 * @param type $time
 * @param type $fmt
 * @return type
 */
function getStrfTime($time=null, $fmt=null) {
    $t = $time ? $time : time();
    $fmt = $fmt ? $fmt : FMT_TIME;
    $str = date($fmt, $t);
    return $str;
}

