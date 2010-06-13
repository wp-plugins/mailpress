<?php while (have_posts()) : the_post(); ?>

	<h2 <?php $this->classes('ch2'); ?>>
<?php the_time(get_option('date_format')) ?>
	</h2>
	<h3 <?php $this->classes('ch3'); ?>>
		<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"  <?php $this->classes('ch3a'); ?> onmouseover="this.style.color='#9a8';" onmouseout="this.style.color='#342';">
<?php the_title(); ?>
		</a>
	</h3>
	<div <?php $this->classes('cdiv'); ?>>
		<p>
<?php $this->the_content( __( '(more...)' ) ); ?>
		</p>
	</div>

<?php endwhile; ?>