<?php
// Copyright 2011 Toby Zerner, Simon Zerner
// This file is part of esoTalk. Please see the included license file for usage information.

if (!defined("IN_ESOTALK")) exit;

/**
 * Displays a single post.
 *
 * @package esoTalk
 */

$post = $data["post"];
$mainFlg = $post["mainPostFlg"];
$isInit = $this->data["isInit"];
?>

<div class='post hasControls <?php echo implode(" ", (array)$post["class"]); ?>' id='<?php echo $post["id"]; ?>'<?php
if (!empty($post["data"])):
foreach ((array)$post["data"] as $dk => $dv)
	echo " data-$dk='$dv'";
endif; ?>>
    
<?php if (!empty($post["avatar"])): ?>
<div class='avatar'<?php if (!empty($post["hideAvatar"])): ?> style='display:none'<?php endif; ?>><?php echo $post["avatar"]; ?></div>
<?php endif; ?>

<div class='postContent thing'>

<div class='postHeader'>
<div class='info'>
<h3><?php echo $post["title"]; ?></h3>
<?php if (!empty($post["info"])) foreach ((array)$post["info"] as $info) echo $info, "\n"; ?>
</div>
<div class='controls'>
<?php if (!empty($post["controls"])) foreach ((array)$post["controls"] as $control) echo $control, "\n"; ?>
</div>
</div>

<?php if (!empty($post["body"])): ?>
<div class='postBody'>
<?php echo $post["body"]; ?>
</div>
<?php endif; ?>

<?php if (!empty($post["title"])): ?>    
<div class='postFooter'>
<?php if (!empty($post["footer"])): ?>
    <?php foreach ((array)$post["footer"] as $footer) echo $footer, "\n"; ?>
<?php endif; ?>
</div>
    
<?php endif; ?>
    
</div>

</div>

<?php // 初期表示の場合 返信以外の場合 SNSボタン出力
if ($isInit && !empty($post["title"]) && ($post["id"]!="reply")): ?>    
<div class='postSnsArea' style="padding: 5px 15px 0 79px;">

    <?php if (SWC_TW_BTN && $mainFlg): // TW ボタン出力の場合 ?>
    <iframe allowtransparency="true" frameborder="0" scrolling="no" style="vertical-align: middle;float:left;width:90px;height:20px;" src="//platform.twitter.com/widgets/tweet_button.html?url=<?php echo $post['purl']; ?>&AMP;count=horizontal&AMP;lang=ja&AMP;hashtags=%E3%82%B9%E3%83%90%E3%82%B3%E3%83%9F"></iframe>
    <?php endif; ?>

    <?php if (SWC_FB_BTN && $mainFlg): // FB ボタン出力の場合 ?>
    <div class="fb-like" style="float:left;margin-right: 15px;" data-href="<?php echo $post['purl']; ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true"></div>
    <?php endif; ?>

    <?php if (SWC_LIKE_BTN): // LIKE ボタン出力の場合 ?>
    <div class="general-button swc-like-btn" data-cnt="<?php echo $post['likeCnt'];?>" data-liked="<?php echo $post['liked'];?>" data-uids="<?php echo $post['data']['memberid'];?>" data-kind="1" data-cid="<?php echo $post['data']['id'];?>" data-conid="<?php echo $post['conversationId'];?>">
        <div class="button-content">
            <span class="favo-icon-font">twinkle</span>      
            <span class="button-text">スバる！</span></div>
    </div>
    <div style="display: inline-block;">
        <div class="arrow_box"><?php echo $post['likeCnt'];?></div>
    </div>
    <?php endif; ?>

</div>
<br clear="all">
<?php endif; ?>
