<?php
// Copyright 2011 Toby Zerner, Simon Zerner
// This file is part of esoTalk. Please see the included license file for usage information.

if (!defined("IN_ESOTALK")) exit;

/**
 * Displays the conversation list, including the filter area (search form, gambits, and channel breadcrumb.)
 *
 * @package esoTalk
 */
$isTagSearch = $data["isTagSearch"];
?>

<div id='conversationsFilter' class='bodyHeader'>

<form class='search big' id='search' action='<?php echo URL("conversations/".$data["channelSlug"]); ?>' method='get'>
<fieldset>
<i class='icon-search'></i>
<input name='search' type='text' class='text' value='<?php echo sanitizeHTML($data["searchString"]); ?>' spellcheck='false' placeholder='<?php echo T("Search conversations..."); ?>' maxlength="200"/>
<a class='control-reset' href='<?php echo URL("conversations/".$data["channelSlug"]); ?>'><i class='icon-remove'></i></a>
</fieldset>
</form>

<?php
// タグリスト
if ($isTagSearch): 
$this->renderView("conversation/tagsPath", $data);
else: 
?>
<ul id='channels' class='channels tabs'>
<li class='channelListItem'><a href='<?php echo URL("channels"); ?>' class='channel-list' data-channel='list' title='<?php echo T("Channel List"); ?>'><i class="icon-list"></i></a></li>
<?php $this->renderView("channels/tabs", $data); ?>
</ul>
<?php endif; ?>

<?php
// Controls
if ($data["controlsMenu"]->count()): ?>
<ul id='searchControls' class='controls'>
<?php echo $data["controlsMenu"]->getContents(); ?>
</ul>
<?php endif; ?>

<?php
// Gambits
if ($data["gambitsMenu"]->count()): ?>
<div id='gambits'>
<ul class='popupMenu'>
<?php echo $data["gambitsMenu"]->getContents(); ?>
</ul></div>
<?php endif; ?>

</div>

<div id='conversations'>
<?php $this->renderView("conversations/results", $data); ?>
</div>
