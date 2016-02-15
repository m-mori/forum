<?php
// Copyright 2016 

if (!defined("IN_ESOTALK")) exit;

//const FMT_TIME = 'Y年m月d日 H時i分';
const FMT_TIME = 'Y/m/d H:i';

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

