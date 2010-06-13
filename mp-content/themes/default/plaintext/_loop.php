<?php while (have_posts()) : the_post(); ?>

<?php $this->the_title(); ?> [<?php the_permalink() ?>]
<?php the_time('F jS, Y'); ?>
<?php $this->the_content( __( '(more...)' ) ); ?>

<?php endwhile; ?>