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
    
    function __construct() {}
    
    /**
     * アバター画像url取得用
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
//        include_once(SWC_PROJECT_PATH . 'app/Subarucommunity_Controller.php');
        include_once(SWC_PROJECT_PATH . 'app/Subarucommunity_ForumController.php');
        $rtn = Subarucommunity_ForumController::main('Subarucommunity_ForumController', "forum_index");
        return $rtn;
    }
    
    /**
     * SWCユーザ情報取得
     * @return type
     */
    public static function getUserDetail($userId) {
        // Ethna側の処理を呼び出しユーザ情報取得
        include_once(SWC_PROJECT_PATH . 'app/Subarucommunity_ForumController.php');
        $params = array("id"=> $userId);
        $info = Subarucommunity_ForumController::main('Subarucommunity_ForumController', "forum_user", "", $params);
        $rtn = "";
        if ($info instanceof self::$swc_rel_classname
            && $info->userDetail) {
            // ユーザ情報がある場合
            $rtn = $info->userDetail;
        }
        return $rtn;
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
    
    
    
}
