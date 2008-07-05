<?php
/*
Category-xx
*/
?>
<?php include(MP_PATH . 'mp-includes/mp-manage-user.php'); ?>

<?php $results = mp_manage_subscriber(); ?>

<?php get_header(); ?>

	<div id='content' class='narrowcolumn'>
		<div class='post' id='post-MailPress'>
		<h2><?php echo $results ['title']; ?></h2>
			<div class='entry'>
				<?php echo $results ['content']; ?>
			</div>
		</div>
	</div>

<?php get_footer(); ?>