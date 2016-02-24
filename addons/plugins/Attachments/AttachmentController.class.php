<?php
// Copyright 2013 Toby Zerner, Simon Zerner
// This file is part of esoTalk. Please see the included license file for usage information.

if (!defined("IN_ESOTALK")) exit;

class AttachmentController extends ETController {

    static $file_key = "qqfile";
    
	protected function getAttachment($attachmentId)
	{
		// Find the attachment in the database.
		$model = ET::getInstance("attachmentModel");
		$attachment = $model->getById($attachmentId);

		// Does this attachment exist?
		if (!$attachment or empty($attachment["postId"])) {
			$this->render404(T("message.attachmentNotFound"), true);
			return false;
		}

		// Get the post/conversation that this attachment is in, and make sure the user has permission to view it.
		$post = ET::postModel()->getById($attachment["postId"]);
		$conversation = ET::conversationModel()->getById($post["conversationId"]);

		if (!$conversation) {
			$this->render404(T("message.attachmentNotFound"), true);
			return false;
		}

		return $attachment;
	}

	// View an attachment.
	public function action_index($attachmentId = false)
	{
		$attachmentId = explode("_", $attachmentId);
		$attachmentId = $attachmentId[0];

		if (!($attachment = $this->getAttachment($attachmentId))) return;

		$model = ET::getInstance("attachmentModel");

                // 2016/01 DBの画像ファイルデータ取得し、出力するように修正対応
                $img = $attachment['content'];
                if ($img) {
                    $size = strlen($img);                      
                    
                    header('Pragma: public');
                    header('Cache-Control: public, no-cache');
                    header('Content-Type: '.$model->mime($attachment["filename"]));
                    if ($size) header('Content-Length: ' . $size);
                    header('Content-Disposition: inline; filename="' . basename($attachment["filename"]) . '"');
                    header('Content-Transfer-Encoding: binary');
                    echo $img;
                    exit;
                }
		else {
			$this->render404(T("message.attachmentNotFound"), true);
			return false;
		}
	}

	// Generate/view a thumbnail of an image attachment.
	public function action_thumb($attachmentId = false)
	{
            if (!($attachment = $this->getAttachment($attachmentId))) return;

            $img = "";
            // 画像IDでサムネイル検索
            $model = $this->getAttachmentModel();
            $thumb = $model->getThumb($attachment["attachmentId"]);
            if (is_array($thumb) && $thumb["content"]) {
                // サムネイル画像有り
                $img = $thumb["content"];
            }
            // TODO: メインだがサムネイル画像なしの場合
            if ($img) {
                $size = strlen($img);
                header('Content-Type: '.$model->mime($attachment["filename"]));
                if ($size) header('Content-Length: ' . $size);
                echo file_get_contents($img);
                exit;
            } else {
                $this->render404(T("message.attachmentNotFound"), true);
                return false;
            }
	}
        
        /**
         * XXX: 追加
         * 一覧表示用のテーマメインサムネイル画像を取得
         * @param type $postId
         * @return boolean
         */
	public function action_menu($id = false)
	{
            if ($id) {
                $model = $this->getAttachmentModel();
                $attachment = $model->getById($id);
                $thumb = $model->getThumb($id);
                
                // デフォルトのメイン画像設定
                // NOイメージ用ファイル
                $img = "";
                $noimagePath = SwcUtils::getNoImageFilePath();
                $filename = pathinfo($noimagePath, PATHINFO_BASENAME);
                
                // TODO: メイン画像はあるが、サムネ画像がない場合の処理
                if (is_array($thumb) 
                    && $thumb["content"]
                    && is_array($attachment)) {
                    // サムネイル画像有り
                    $img = $thumb["content"];
                    $filename=$attachment["filename"];
                } else {
                    // 画像がない投稿の場合 ノーイメージ読込
                    $img = file_get_contents($noimagePath);
                }
                if ($img) {
                    $size = strlen($img);
                    header('Content-Type: '.$model->mime($filename));
                    if ($size) header('Content-Length: ' . $size);
                    header('Content-Transfer-Encoding: binary');
                    echo $img;
                    exit;
                } else {
                    $this->render404(T("message.attachmentNotFound"), false);
                    return false;
                }
            }
	}

        /**
         * 添付画像ファイルアップロード処理
         *  ※DBへ保存するように処理修正
         */
	// Upload an attachment.
	public function action_upload()
	{
//		require_once 'qqFileUploader.php';
//		$uploader = new qqFileUploader();
//		$uploader->inputName = 'qqfile';
                
                // 管理画面で設定されたファイル拡張子（画像のみ）
		// Set the allowed file types based on config.
		$allowedFileTypes = C("plugin.Attachments.allowedFileTypes");
//		if (!empty($allowedFileTypes))
//			$uploader->allowedExtensions = $allowedFileTypes;

                // 管理画面で設定されたMAXサイズ（デフォルト: 25MB）
		// Set the max file size based on config.
                $maxSize = C("plugin.Attachments.maxFileSize");
//		if ($size = C("plugin.Attachments.maxFileSize"))
//			$uploader->sizeLimit = $size;

                $model = $this->getAttachmentModel();
                $id = "";
                try {
                    // アップロード処理
                    $attachmentId="";
                    $uploader = ET::uploader();
                    $filepath = $uploader->getUploadedFile(self::$file_key, $allowedFileTypes);
                    if (file_exists($filepath)) {
                        // ファイルサイズチェック
                        $size = filesize($filepath);
                        if ($size>$maxSize) {
                            // TODO: サイズ超過 エラー時
                        }
                        // 元ファイル名
                        $name = $_FILES[self::$file_key]["name"];
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        if ($ext != "gif") {
                            // gif 以外はjpg で保存
                            $ext = "jpg";
                        }
                        // ファイル名
                        $baseName = pathinfo($name, PATHINFO_BASENAME);
//                        $baseName = pathinfo($filepath, PATHINFO_FILENAME) .$ext;
                        $attachmentId = SwcUtils::createUniqKey(13);
                        // ランダム文字列
                        $secret = generateRandomString(13, "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890");
                        // 保存先ファイルパス
                        $destPath = $model->path() .$attachmentId .$secret ."." .$ext;
                        // MAX値超過する場合 リサイズする
                        $outputFile = $uploader->saveAsImage($filepath, $destPath, SWC_IMG_MAX_W, SWC_IMG_MAX_H);
                        
                        // 画像データ
                        $imgFile = file_get_contents($outputFile);
                        
                        // エンティティ設定
                        $entity = array(
                            "attachmentId"=>$attachmentId,
                            "filename"=>$baseName,
                            "secret"=>$secret,
                            "content"=>$imgFile,
                        );
                        $paramPostId = R("postId");
                        if (preg_match('/^p[0-9]+$/', $paramPostId)) {
                            // postID がある場合 "p"から始まる
                            // "p"を除去して登録
                            $entity["postId"] = substr($paramPostId, 1);
                        } else if (preg_match('/^c[0-9]+$/', $paramPostId)) {
                            // 会話ID を設定 "c"から始まる
                            // 新規の場合、"c0"が設定されている
                            // アップロード時は"c" を付けたまま一時登録する
                            $entity["conversationId"]= $paramPostId;
                        }
                        // sessId取得
                        $sessId = ET::$session->getSessId();
                        $entity["sessId"] = $sessId;
                        
                        $model->create($entity, false);
                    }
                        
                } catch (Exception $e) {
                    // TODO:
                    return;
                }
                 
                $result = array();
		if ($attachmentId) {
                    // OK の場合
                    $result["success"] = 1;
                    $result["uploadName"] = $baseName;
                    $result["attachmentId"] = $attachmentId;
		} else {
                    // NG
                    $result["success"] = 0;
                }

		header("Content-Type: text/plain");
		echo json_encode($result);
	}

	// Remove an attachment.
	public function action_remove($attachmentId)
	{
		if (!$this->validateToken()) return;

//		$session = (array)ET::$session->get("attachments");
		$model = ET::getInstance("attachmentModel");

                $attachment = $model->getById($attachmentId);
                $sessId = ET::$session->getSessId();
                if (is_array($attachment) && count($attachment)>0) {
                    // Make sure the user has permission to edit this post.
                    $permission = false;
                    if (!empty($attachment["postId"])) {
                        $post = ET::postModel()->getById($attachment["postId"]);
                        $conversation = ET::conversationModel()->getById($post["conversationId"]);
                        $permission = ET::postModel()->canEditPost($post, $conversation);
                    } else {
                        if ($attachment["sessId"]==$sessId) {
                            $permission = true;
                        } else {
                            $permission = ET::$session->userId == $attachment["draftMemberId"];
                        }
                    }

                    if (!$permission) {
                            $this->renderMessage(T("Error"), T("message.noPermission"));
                            return false;
                    }

                    // Remove the attachment from the database.
                    $model->deleteById($attachmentId);

                    // Remove the attachment from the filesystem.
                    $model->removeFile($attachment);
                
                }
	}

	// Remove attachments stored in the session for a specific post.
	public function action_removeSession($postId)
	{
		if (!$this->validateToken()) return;

		$model = ET::getInstance("attachmentModel");
		$attachments = $model->extractFromTemporary("p".$postId);
		foreach ($attachments as $attachment) {
			$model->removeFile($attachment);
		}
	}
        
        private function getAttachmentModel() {
            return ET::getInstance("attachmentModel");
        }

}
