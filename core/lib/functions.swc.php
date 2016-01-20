<?php
// Copyright 2016 

if (!defined("IN_ESOTALK")) exit;

//const FMT_TIME = 'Y年m月d日 H時i分';
const FMT_TIME = 'Y/m/d H:i';

function getStrfTime($time=null, $fmt=null) {
    $t = $time ? $time : time();
    $fmt = $fmt ? $fmt : FMT_TIME;
    $str = date($fmt, $t);
    return $str;
}
