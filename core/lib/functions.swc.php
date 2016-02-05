<?php
// Copyright 2016 

if (!defined("IN_ESOTALK")) exit;

// SWC definitions ---------------------------------//
if (!defined("SWC_MEMBERS")) define("SWC_MEMBERS", '//members.swc.dev/');    // TODO: domain name
if (!defined("SWC_MEMBERS_LOGIN")) define("SWC_MEMBERS_LOGIN", SWC_MEMBERS .'login/');    // TODO: domain name
if (!defined("SWC_MEMBERS_ENTRY")) define("SWC_MEMBERS_ENTRY", SWC_MEMBERS .'entry/');    // TODO: domain name

//const FMT_TIME = 'Y年m月d日 H時i分';
const FMT_TIME = 'Y/m/d H:i';

function getSwcUrl($name) {
    if ($name=='login') {
        return SWC_MEMBERS_LOGIN;
    } else if ($name=='entry') {
        return SWC_MEMBERS_ENTRY;
    }
    return "";
}

function getStrfTime($time=null, $fmt=null) {
    $t = $time ? $time : time();
    $fmt = $fmt ? $fmt : FMT_TIME;
    $str = date($fmt, $t);
    return $str;
}

