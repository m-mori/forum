<?php
// Copyright 2011 Toby Zerner, Simon Zerner
// This file is part of esoTalk. Please see the included license file for usage information.

if (!defined("IN_ESOTALK")) exit;

/**
 * Shows a page to edit a single post.
 *
 * @package esoTalk
 */

$form = $data["form"];
$post = $data["post"];
// 会話開始フラグ
$isStartFlg = $this->data["isStartFlg"];
$mainFlg = $post["mainPostFlg"];
?>
<div class='standalone'>
<?php echo $form->open(); ?>

<?php

// Using the provided form object, construct a textarea and buttons.
$body = $form->input("content", "textarea", array("cols" => "200", "rows" => "20"))."
	<div id='p".$post["postId"]."-preview' class='preview'></div>";

// タグ入力
$footer = $this->getTagsArea($form);

// TW,FB 投稿チェックボタン
$footer .= $this->getCheckBoxArea();

$footer .= "<div class='editButtons'>".
	$form->saveButton()." ".
	$form->cancelButton()."</div>";

// Construct an array for use in the conversation/post view.
$formatted = array(
	"id" => "p".$post["postId"],
	"title" => name($post["username"]),
	"controls" => $data["controls"],
	"class" => "edit",
	"body" => $body,
	"avatar" => avatar($post),
	"footer" => array($footer)
);

$this->trigger("renderEditBox", array(&$formatted, $post));

$this->renderView("conversation/post", array("post" => $formatted));

?>

<?php echo $form->close(); ?>
</div>
