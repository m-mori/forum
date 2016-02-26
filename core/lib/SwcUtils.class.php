<?php
// Copyright 2016 

if (!defined("IN_ESOTALK")) exit;

/**
 * SWCユーティリティ
 * @package swc.forum
 */
class SwcUtils {
    // SWCのセッション名
    const SWC_SESS_NAME ="SubarucommunitySESSID";
    // SWCのプロフィール画像取得url
    const SWC_USER_IMG_URL ="/forum/img/image.php?id=";
    
    // 戻り値クラス名
    static $swc_rel_classname = "ForumResult";
    
    //const FMT_TIME = 'Y年m月d日 H時i分';
    const FMT_TIME = 'Y/m/d H:i';

    function __construct() {}
    
    /**
     * SWC側へのリンクurl取得
     * @param type $name
     * @return string
     */
    public static function getSwcUrl($name) {
        if ($name=='login') {
            return SWC_MEMBERS_LOGIN;
        } else if ($name=='entry') {
            return SWC_MEMBERS_ENTRY;
        } else if ($name =='logout') {
            return URL("user/logout?token=".ET::$session->token);
        }
        return "";
    }

    /**
     * 日付型の文字列フォーマット変換
     * @param type $time
     * @param type $fmt
     * @return type
     */
    public static function getStrfTime($time=null, $fmt=null) {
        $t = $time ? $time : time();
        $fmt = $fmt ? $fmt : self::FMT_TIME;
        $str = date($fmt, $t);
        return $str;
    }

    /**
     * アバター画像url取得用
     * SWCプロフィール画像を出力するurlを取得
     * @param type $userId SWCユーザID
     * @return type
     */
    public static function getUserImgUrl($userId) {
        return self::SWC_USER_IMG_URL .$userId;
    }
    
    /**
     * SWCログインチェック処理
     * @return type
     */
    public static function isSwcLogin() {
        // Ethna側の処理を呼び出しログイン中かどうかチェック
        include_once(SWC_PROJECT_PATH . 'app/Subarucommunity_ForumController.php');
        $rtn = Subarucommunity_ForumController::main('Subarucommunity_ForumController', "forum_index");
        return $rtn;
    }
    
    /**
     * SWCユーザ名（handle_name）取得
     * ユーザ名は、SWCから毎回取得する
     * @return type
     */
    public static function getUserName($userId) {
        $userInfo = self::getUserDetail($userId);
        $rtn = "";
        if ($userInfo) {
            $rtn = $userInfo["handle_name"];
        }
        return $rtn;
    }
    
    /**
     * SWCユーザ情報取得
     * @return type
     */
    public static function getUserDetail($userId) {
        if (ET::$session) {
            // session開始済の場合
            //  session から取得
            $rtn = ET::$session->getUserInfo($userId);
        }
        if (!$rtn && $userId) {
            // session にはない場合（通常ない）
            // Ethna側の処理を呼び出しユーザ情報取得
            include_once(SWC_PROJECT_PATH . 'app/Subarucommunity_ForumController.php');
            $params = array("id"=> $userId);
            $info = Subarucommunity_ForumController::main('Subarucommunity_ForumController', "forum_user", "", $params);
            if ($info instanceof self::$swc_rel_classname
                && $info->userDetail) {
                // ユーザ情報がある場合
                ET::$session->addUsersList($userId, $info->userDetail);
                $rtn = $info->userDetail;
            }
        }
        return $rtn;
    }
    
    /**
     * 前後の半角、全角スペース除去
     * @param type $str
     * @return type
     */
    public static function trimSpace ($str) {
        // 先頭の半角、全角スペースを、空文字に置き換える
        $str = preg_replace('/^[ 　]+/u', '', $str);
        // 最後の半角、全角スペースを、空文字に置き換える
        $str = preg_replace('/[ 　]+$/u', '', $str);
        return $str;
    }
    
    /**
     * 文字列がマルチバイト（を含む）かどうか
     * @param type $s
     * @return type
     */
    public static function isMB($s) {
        $s = mb_convert_encoding($s, "utf-8", "auto");
        return strlen($s) != mb_strlen($s);
    }
    
    /**
     * 一意キー取得.
     * 引数の桁数の半角数値・英小文字 [0-9.a-z]を返す
     */
    public static function createUniqKey($len) {
        $key = md5(uniqid(mt_rand(),1));
        return substr($key, -($len));
    }
    
    /**
     * noimage 画像ファイルパスを取得
     */
    public static function getNoImageFilePath() {
        return PATH_ROOT ."/" .SWC_NOIMG_THUMB_PATH;
    }
    
    /**
     * noimage 画像url を取得
     */
    public static function getNoImageFileUrl() {
        // ドメイン名付加する
        $url = C("esoTalk.baseURL")."/" .SWC_NOIMG_THUMB_PATH;
        return $url;
    }

}
