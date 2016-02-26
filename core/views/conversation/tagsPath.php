<?php
// Copyright 2016

if (!defined("IN_ESOTALK")) exit;

/**
 * 会話IDに対応するタグ情報をリスト出力する
 *  #tagList
 */
$tags = $data["tags"];
// タグ一覧ページかどうか（false: 詳細ページ）
$isTagSearch = $data["isTagSearch"];
?>

<ul id="tagsList" class='tags tabs'>
<?php if ($isTagSearch): ?>
<li>
<?php else: ?>
<li class='pathItem selected'>
<?php endif; ?>
<span>タグ：</span></li>    
<?php foreach ($tags as $tag) :
$key = $tag["tagsId"];
$val = $tag["tagText"];
if (!$val) continue;
?>
<?php if ($isTagSearch): ?>
<li class='selected'>
<?php else: ?>
<li class='pathItem selected'>
<?php endif; ?>
    <a href='<?php echo URL("conversations/tags/".$val); ?>' data-tag-key='<?php echo $key; ?>' title='<?php echo $val; ?>' class='tag tag-<?php echo $key; ?>'><?php echo $val; ?></a>
</li>
<?php endforeach; ?>
</ul>
