<?php while (have_posts()) : the_post(); ?>

<?php 
$title = $this->get_the_title(); 
$title = trim($title);
$box   = str_repeat( '~', strlen(utf8_decode($title)) );
echo "* $box *\n! $title !\n* $box *\n";
?>
<?php the_permalink(); ?>

<?php the_time(get_option( 'date_format' )); ?>
<?php $this->the_content( __( '(more...)' ) ); ?>

<?php endwhile; ?>