<?php
/*
Category-xx
*/
?>
<?php

if (class_exists('MailPress'))
{
	MailPress::require_class('Mail_links');
	$results = MP_Mail_links::process();
}

?>

<?php get_header(); ?>

<script type='text/javascript' src='<?php echo get_option('siteurl') . '/' . MP_PATH . 'mp-includes/js/iframe.js' ?>'></script>

	<div id='content' class='narrowcolumn'>
		<div class='post' id='post-MailPress'>
		<h2><?php echo $results ['title']; ?></h2>
			<div class='entry'>
				<?php echo $results ['content']; ?>
			</div>
		</div>
	</div>

<?php get_footer(); ?>