<?php
// Copyright 2013 Toby Zerner, Simon Zerner
// This file is part of esoTalk. Please see the included license file for usage information.

if (!defined("IN_ESOTALK")) exit;

class AttachmentModel extends ETModel {

	public static $mimes = array(

		'hqx'   => 'application/mac-binhex40',
		'cpt'   => 'application/mac-compactpro',
		'csv'   => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream'),
		'bin'   => 'application/macbinary',
		'dms'   => 'application/octet-stream',
		'lha'   => 'application/octet-stream',
		'lzh'   => 'application/octet-stream',
		'exe'   => array('application/octet-stream', 'application/x-msdownload'),
		'class' => 'application/octet-stream',
		'psd'   => 'application/x-photoshop',
		'so'    => 'application/octet-stream',
		'sea'   => 'application/octet-stream',
		'dll'   => 'application/octet-stream',
		'oda'   => 'application/oda',
		'pdf'   => array('application/pdf', 'application/x-download'),
		'ai'    => 'application/postscript',
		'eps'   => 'application/postscript',
		'ps'    => 'application/postscript',
		'smi'   => 'application/smil',
		'smil'  => 'application/smil',
		'mif'   => 'application/vnd.mif',
		'xls'   => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
		'ppt'   => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
		'wbxml' => 'application/wbxml',
		'wmlc'  => 'application/wmlc',
		'dcr'   => 'application/x-director',
		'dir'   => 'application/x-director',
		'dxr'   => 'application/x-director',
		'dvi'   => 'application/x-dvi',
		'gtar'  => 'application/x-gtar',
		'gz'    => 'application/x-gzip',
		'php'   => array('application/x-httpd-php', 'text/x-php'),
		'php4'  => 'application/x-httpd-php',
		'php3'  => 'application/x-httpd-php',
		'phtml' => 'application/x-httpd-php',
		'phps'  => 'application/x-httpd-php-source',
		'js'    => 'application/x-javascript',
		'swf'   => 'application/x-shockwave-flash',
		'sit'   => 'application/x-stuffit',
		'tar'   => 'application/x-tar',
		'tgz'   => array('application/x-tar', 'application/x-gzip-compressed'),
		'xhtml' => 'application/xhtml+xml',
		'xht'   => 'application/xhtml+xml',
		'zip'   => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
		'mid'   => 'audio/midi',
		'midi'  => 'audio/midi',
		'mpga'  => 'audio/mpeg',
		'mp2'   => 'audio/mpeg',
		'mp3'   => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
		'aif'   => 'audio/x-aiff',
		'aiff'  => 'audio/x-aiff',
		'aifc'  => 'audio/x-aiff',
		'ram'   => 'audio/x-pn-realaudio',
		'rm'    => 'audio/x-pn-realaudio',
		'rpm'   => 'audio/x-pn-realaudio-plugin',
		'ra'    => 'audio/x-realaudio',
		'rv'    => 'video/vnd.rn-realvideo',
		'wav'   => 'audio/x-wav',
		'bmp'   => 'image/bmp',
		'gif'   => 'image/gif',
		'jpeg'  => array('image/jpeg', 'image/pjpeg'),
		'jpg'   => array('image/jpeg', 'image/pjpeg'),
		'jpe'   => array('image/jpeg', 'image/pjpeg'),
		'png'   => 'image/png',
		'tiff'  => 'image/tiff',
		'tif'   => 'image/tiff',
		'css'   => 'text/css',
		'html'  => 'text/html',
		'htm'   => 'text/html',
		'shtml' => 'text/html',
		'txt'   => 'text/plain',
		'text'  => 'text/plain',
		'log'   => array('text/plain', 'text/x-log'),
		'rtx'   => 'text/richtext',
		'rtf'   => 'text/rtf',
		'xml'   => 'text/xml',
		'xsl'   => 'text/xml',
		'mpeg'  => 'video/mpeg',
		'mpg'   => 'video/mpeg',
		'mpe'   => 'video/mpeg',
		'qt'    => 'video/quicktime',
		'mov'   => 'video/quicktime',
		'avi'   => 'video/x-msvideo',
		'movie' => 'video/x-sgi-movie',
		'doc'   => 'application/msword',
		'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'word'  => array('application/msword', 'application/octet-stream'),
		'xl'    => 'application/excel',
		'eml'   => 'message/rfc822',
		'json'  => array('application/json', 'text/json'),

	);

	public function __construct()
	{
		parent::__construct("attachment");
	}

	public function path()
	{
		return PATH_UPLOADS.'/attachments/';
	}

	public function mime($path)
	{
		$extension = pathinfo($path, PATHINFO_EXTENSION);

		if ( ! array_key_exists($extension, self::$mimes)) return "application/octet-stream";

		return (is_array(self::$mimes[$extension])) ? self::$mimes[$extension][0] : self::$mimes[$extension];
	}

	// Find attachments for a specific post or conversation that are being stored in the session, remove
	// them from the session, and return them.
        /**
         * 一時画像ファイル情報を取得
         * @param type $postId 
         * @param type $isDelete (デフォルト: true) 
         *      true: 取得後、レコード削除
         *      false: 削除しない
         * @return type
         */
	public function extractFromTemporary($postId, $isDelete=true)
	{
            // DBから取得するように変更
            $attachments = array();
            // ログインユーザの sessId
            $sessId = ET::$session->getSessId();
            if ($sessId) {
                if (preg_match('/^p[0-9]+$/', $postId)) {
                    // "p"から始まる場合 postId
                    // postId, sessId から一時画像ファイル検索
                    $where = array(
                        "postId" => substr($postId, 1), 
                        "sessId"=>$sessId
                    );
                } else if (preg_match('/^c[0-9]+$/', $postId)) {
                    // "c"から始まる場合 conversationId
                    // conversationId, sessId から一時画像ファイル検索
                    $where = array(
                        "conversationId" => $postId, 
                        "sessId"=>$sessId
                    );
                }
                $result = $this->get($where);
                
                if (is_array($result) && count($result)>0) {
                    // 一時画像ファイルデータがある場合
                    foreach ($result as $attachment) {
                        $id = $attachment["attachmentId"];
                        $attachments[$id] = $attachment;
                    }
                    if ($isDelete) {
                        // DBから削除
                        $this->delete($where);
                    }
                }
            }
            return $attachments;
	}

        /**
         * 添付画像ファイル 本登録処理
         *  ※画像ファイルはDBへ保存するように修正
         * @param type $attachments
         * @param type $keys: 本登録時に以下のキー情報を設定する
         *  ["postId"]
         *  ["conversationId"]
         *  ["channelId"]
         * 
         */
	public function insertAttachments($attachments, $keys, $isDraft=false, $mainPostFlg=false)
	{
		$values = array();
                $mainFlg = false;
		foreach ($attachments as $id => $attachment) {
                    if (!$isDraft) {
                        // 本登録処理 sessId= null にする
                        $values = array(
                            "postId" => $keys["postId"],
                            "conversationId" => $keys["conversationId"],
                            "sessId" => null,
                            "channelId" => $keys["channelId"],
                        );
                        if (!$mainFlg) {
                            // メイン画像存在チェック
                            $mainFlg = $this->getCountByPostId($keys["postId"], $keys["conversationId"]);
                            if ($mainFlg===0) {
                                // 0件の場合 main= 1を設定する
                                $values["main"]=1;
                                $mainFlg = 1;
                                
                                if ($mainPostFlg) {
                                    // 新規会話作成の場合
                                    // サムネイル画像登録 
                                    $this->createThumb($attachment, $keys["postId"], 1);
                                }
                            }
                        }
                        
                    } else {
                        // 下書き登録の場合
                        $values = array(
                            "postId" => null,
                            "conversationId" => null,
                            "draftConversationId" => $keys["draftConversationId"],
                            "draftMemberId" => $keys["draftMemberId"],
                            "sessId" => null,
                        );
                        
                    }
                    // PKで更新
                    $this->updateById($id, $values);
                }
                
                // DB登録後 uploadsフォルダ内の一時ファイル削除
		foreach ($attachments as $id => $attachment) {
                    $this->removeFile($attachment);
                }
	}

        /**
         * サムネイル情報取得
         * @param type $id
         * @return type
         */
        public function getThumb($id) {
            $result = "";
            if ($id) {
                $result = ET::SQL()
                    ->select("*")
                    ->from("thumb")
                    ->where("thumbId", $id)
                    ->exec()
                    ->firstRow();
            }
            return $result;
        }

        /**
         * 投稿IDでメイン画像サムネイル取得
         *  基本的には投稿IDでユニーク
         * @param type $postId
         * @return type
         */
        public function getThumbByPostId($postId) {
            $result = "";
            if ($postId) {
                $result = ET::SQL()
                    ->select("*")
                    ->from("thumb")
                    ->where("postId", $postId)
                    ->where("type", 1)
                    ->exec()
                    ->firstRow();
            }
            return $result;
        }
        
        /**
         * サムネイル画像登録
         * @param type $attachment
         * @param type $type
         */
        public function createThumb($attachment, $postId, $type) {
            $uploader = ET::uploader();
            // 一時ファイルパス取得
            $srcPath = $this->getTempFileName($attachment);
            $thumbPath = $this->getThumbFileName($srcPath);
            
            // サムネイル画像生成
            $thumb = $uploader->saveAsImage($srcPath, $thumbPath, SWC_IMG_THUMB_W, SWC_IMG_THUMB_H, "crop");
            $imgFile = file_get_contents($thumb);
            
            // type= 1: メイン画像
            $entity = array(
                "thumbId"=>$attachment["attachmentId"],
                "postId"=>$postId,
                "type"=>$type,
                "attachmentId"=>$attachment["attachmentId"],
                "content"=>$imgFile,
            );
            
            ET::SQL()->insert("thumb")
                    ->set("thumbId", $attachment["attachmentId"])
                    ->set("postId", $postId)
                    ->set("type", $type)
                    ->set("attachmentId", $attachment["attachmentId"])
                    ->set("content", $imgFile)
                    ->exec();
        }
        
        public function getTempFileName($attachment) {
            $ext = strtolower(pathinfo($attachment["filename"], PATHINFO_EXTENSION));
            if ($ext != "gif") {
                $ext = "jpg";
            }
            return $this->path().$attachment["attachmentId"].$attachment["secret"].".".$ext; 
        }
        
        /**
         * サムネイル用ファイル名取得
         * @param type $srcPath
         * @return string
         */
        public function getThumbFileName($srcPath) {
            $pathData = pathinfo($srcPath);
            $thumbPath = $pathData["dirname"]."/".$pathData["filename"] ."_thumb." .$pathData["extension"];
            return $thumbPath; 
        }
        
        /**
         * upload/attachments 配下の一時ファイル削除
         * @param type $attachment
         */
	public function removeFile($attachment)
	{
            $path = $this->getTempFileName($attachment);
            if (file_exists($path)) {
		@unlink($path);
            }
            // サムネイルファイル名
            $thumbPath= $this->getThumbFileName($path);
            if (file_exists($thumbPath)) {
		@unlink($thumbPath);
            }
	}
        
        /**
         * 投稿ID,会話IDでメイン画像存在するかどうか取得
         * @param type $conversationId
         * @param type $postId
         */
        public function getCountByPostId($postId, $conversationId) {
            if ($conversationId && $postId) {
            // conversationId, sessId から一時画像ファイル検索
                $where = array(
                    "conversationId" => $conversationId,
                    "postId" => $postId, 
                    "main"=>1
                );
                $cnt = intval($this->count($where));
            }
            return $cnt;
        }

        /**
         * 会話IDから対応するテーマ（スレッド）のチャンネルIDを取得
         * @param type $conversationId
         * @return type
         */
        public function getChannelId($conversationId) {
            $channelId = "";
            if ($conversationId) {
                $conModel = ET::conversationModel();
                $result = ET::SQL()
                        ->select("channelId")
                        ->from("conversation")
                        ->where("conversationId", $conversationId)
                        ->exec()
                        ->firstRow();
                if ($result) {
                    $channelId = $result["channelId"];
                }
            }
            return $channelId;
        }

    /**
     * 会話IDからメイン画像が存在する投稿IDを取得する
     *  下書き投稿ではない、会話IDを指定すること
     * @param type $conversationId
     */
    public function getMainAttachmentId($conversationId) {
        $attachmentId = "";
        if ($conversationId) {
            $result = ET::SQL()
                ->select("attachmentId") 
                ->from("attachment a")
                ->from("post p", "p.postId=a.postId AND p.mainPostFlg=1 AND a.main=1 AND p.conversationId=:conversationId", "inner")
                ->bind(":conversationId", intval($conversationId))
                ->exec()
                ->firstRow();
            if ($result) {
                $attachmentId = $result["attachmentId"];
            }
        }
        return $attachmentId;
    }
        
}
