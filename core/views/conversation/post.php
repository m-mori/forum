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
?>

<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/ja_JP/sdk.js#xfbml=1&version=v2.5";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

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
<iframe allowtransparency="true" frameborder="0" scrolling="no" style="margin-top:5px;margin-left: 15px;vertical-align: middle;float:left;width:90px;height:20px;" src="http://platform.twitter.com/widgets/tweet_button.html?url=http%3a%2f%2fsample%2durl%2f&amp;lang=ja&amp;count=horizontal"></iframe>

<div class="fb-like" style="float:left;margin-top: 5px;margin-right: 15px;" data-href="window.location.href;" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>

<?php if (!empty($post["footer"])): ?>
    <?php foreach ((array)$post["footer"] as $footer) echo $footer, "\n"; ?>
<?php endif; ?>

</div>
<?php endif; ?>

    
</div>

</div>
<br clear="all">