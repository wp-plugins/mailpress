<?php while (have_posts()) : the_post(); ?>
<?php the_time(get_option('date_format')) ?>

<?php $this->the_title(); ?> [<?php the_permalink() ?>]
<?php $this->the_content( __( '(more...)' ) ); ?>

<?php endwhile; ?>