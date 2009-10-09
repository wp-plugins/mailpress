<?php
/*
Template Name: confirmed
Subject: [<?php bloginfo('name');?>] <?php printf('%s confirmed', '{{toemail}}'); ?>
*/
?>
<?php $this->get_header() ?>

<table <?php $this->classes('nopmb ctable'); ?>>
	<tr>
		<td <?php $this->classes('nopmb ctd'); ?>>
			<div <?php $this->classes('cdiv'); ?>>
				<h2 <?php $this->classes('ch2'); ?>>
Congratulations !
				</h2>
				<small <?php $this->classes('nopmb cdate'); ?>>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
				</small>
				<div <?php $this->classes('nopmb'); ?>>
					<p <?php $this->classes('nopmb cp'); ?>>

You are now a subscriber of : 
						<a <?php $this->classes('clink2'); ?> href='<?php echo get_option('siteurl'); ?>'>
							<?php echo get_option('blogname'); ?>
						</a>
					</p>
				</div>
			</div>
		</td>
	</tr>
</table>

<?php $this->get_footer() ?>