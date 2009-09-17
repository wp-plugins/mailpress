<?php
/*
Template Name: form_visitor
Subject: [<?php bloginfo('name');?>] Copy of your submission
*/
?>


<?php $this->get_header() ?>

<table <?php $this->classes('nopmb ctable'); ?>>
	<tr>
		<td <?php $this->classes('nopmb ctd'); ?>>
			<div <?php $this->classes('cdiv'); ?>>
				<h2 <?php $this->classes('ch2'); ?>>
					<a <?php $this->classes('clink'); ?> href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
Copy of your submission
					</a>
				</h2>
				<small <?php $this->classes('nopmb cdate'); ?>>
<?php the_time('F j, Y') ?>
				</small>
				<div <?php $this->classes('nopmb'); ?>>
					<p <?php $this->classes('nopmb cp'); ?>>

<?php $this->the_content(); ?>

					</p>
				</div>
			</div>
		</td>
	</tr>
</table>

<?php $this->get_footer() ?>