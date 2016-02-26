<?php
// Copyright 2016

if (!defined("IN_ESOTALK")) exit;

/**
 * The channel model provides functions for retrieving and managing channel data.
 *
 * @package esoTalk
 */
class ETTagsModel extends ETModel {

/** conversation_tags */    
public $conversationTagsModel;

/**
 * Class constructor; sets up the base model functions to use the channel table.
 *
 * @return void
 */
public function __construct()
{
	parent::__construct("tags");
        $this->conversationTagsModel = new ETConversationTagsModel();
}

/**
 * 会話IDに対応するタグ配列（k,v）を取得
 * @param type $conversationId
 * @return type
 */
public function getTagsInfo($conversationId) {
    $row = $this->getConversationTagsRow($conversationId);
    if ($row) {
        $tagIdList = array();
        // 検索条件のタグIDリスト設定
        foreach ($row as $key => $value) {
            // tag0-9 を取得
            if (preg_match('/^tag[0-9]+$/', $key) && $value) {
                $tagIdList[] = $value;
            }
        }
        // タグID,タグテキストの配列を取得
        $result = ET::SQL()
            ->select("*") 
            ->from("tags")
            ->where("tagsId IN (:tagIdList)")
            ->bind(":tagIdList", $tagIdList)
            ->exec()
            ->allRows("");
        return $result;
    }
}

/**
 * タグ情報更新
 *  タグマスタ、会話-タグ情報を更新する
 *  ※投稿の更新時に呼び出しされる
 * @param type $post
 * @param type $tags
 * @return boolean
 */
public function editPost($post, $tags)
{
    $mainPostFlg = $post["mainPostFlg"];
    if ($mainPostFlg 
        && is_array($tags) && count($tags)>0) {
        // テーマの開始記事で、タグ入力値がある場合
	// Validate the post content.
//        foreach ($tags as $key => $value) {
//            $name = "tag" .$key;
//            $this->validate($name, $value, array($this, "validateTag"));
//        }
	if ($this->errorCount()) return false;
        
        $conversationId = $post["conversationId"];
        
        $tagIdList = array();
        // タグマスタの登録更新
        foreach ($tags as $tagText) {
            // マスタに一致するタグがあるかチェック
            $id = $this->getIdByTagText($tagText);
            if (!$id) {
                // 存在しな場合
                // マスタにタグを新規追加する
                $id = $this->create(array("tagText"=>$tagText));
            }
            // 登録するタグIDリストに追加
            if (!in_array($id, $tagIdList)) {
                $tagIdList[] = $id;
            }
        }
        
        // 会話-タグ情報を更新
        $this->updateConversationTgas($conversationId, $tagIdList);
    }
}

public function getConversationTagsRow($conversationId) {
    $row = null;
    if ($conversationId) {
        $row = $this->conversationTagsModel->getById($conversationId);
    }
    return $row;
}

/**
 * ft_conversation_tags を更新する
 *  会話IDに対応するタグ情報（10個まで）を保持する
 *  基本的にdelete/insert で登録する
 * @param type $conversationId
 * @param type $tagIdList
 */
public function updateConversationTgas($conversationId, $tagIdList) {
    // 既存行取得
    $row = $this->getConversationTagsRow($conversationId);
    if (is_array($row)) {
        // 既存行がある場合 削除
        $this->conversationTagsModel->deleteById($conversationId);
    }
    if (count($tagIdList)>0) {
        // 登録するタグIDがある場合
        $entity = array("conversationId"=>$conversationId);
        foreach ($tagIdList as $key => $value) {
            $col = "tag" .$key;
            $entity[$col]=$value;
        }
        // 登録
        $this->conversationTagsModel->create($entity, false);
    }
}

/**
 * タグ文字列でタグマスタ検索（完全一致検索）
 *  存在する場合、タグIDを返却する
 * @param type $text
 */
public function getIdByTagText($text) {
    $row = $this->get(array("tagText"=>$text));
    $id = "";
    if (is_array($row)) {
        $id=$row[0]["tagsId"];
    }
    return $id;
}

/**
 * タグの入力内容チェック
 * Validate a post's content.
 *
 * @param string $content The post content.
 * @return bool|string Returns an error string or false if there are no errors.
 */
public function validateTag($tag)
{
// ※必要がある場合実装
//    // Make sure it's not too long but has at least one character.
//    if (strlen($content) > C("esoTalk.conversation.maxCharsPerPost")) return sprintf(T("message.postTooLong"), C("esoTalk.conversation.maxCharsPerPost"));
//    if (!strlen($content)) return "emptyPost";
}
}

class ETConversationTagsModel extends ETModel {
    public function __construct($table = "", $primaryKey = "") {
        parent::__construct("conversation_tags", "conversationId");
    }
}
