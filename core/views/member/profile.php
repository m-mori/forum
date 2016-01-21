<?php
// Copyright 2011 Toby Zerner, Simon Zerner
// This file is part of esoTalk. Please see the included license file for usage information.

if (!defined("IN_ESOTALK")) exit;

/**
 * Member profile view. Displays the header area on a member's profile, pane tabs, and the current pane.
 *
 * @package esoTalk
 */

$member = $data["member"];
?>

<div class='bodyHeader clearfix' id='memberProfile'>

<?php echo avatar($member); ?>

<div id='memberInfo'>

<h1 id='memberName'><?php echo name($member["username"]); ?></h1>

<?php
// Output the email if the viewer is an admin.
if (ET::$session->isAdmin()): ?><p class='subText'><?php echo sanitizeHTML($member["email"]); ?></p><?php endif; ?>

</div>

</div>

<ul id='memberPanes' class='tabs big'>
<?php echo $data["panes"]->getContents(); ?>
</ul>

<div id='memberContent' class='area'>
<?php $this->renderView($data["view"], $data); ?>
</div>
