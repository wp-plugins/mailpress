<?php
/*
Template Name: single
*/
?>
<?php $this->get_header() ?>
<?php while (have_posts()) : the_post(); ?>

<?php the_title(); ?> (<?php the_permalink() ?>) <?php echo "\n"; ?>
<?php the_time('F j, Y') ?>

<?php the_excerpt_rss(); ?>

<?php endwhile; ?>
<?php $this->get_footer() ?>