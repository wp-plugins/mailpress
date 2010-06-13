<?php 
$title = (isset($_the_title)) ? $_the_title : $this->get_the_title();
$title = trim($title);
$box   = str_repeat( '~', strlen(utf8_decode($title)) );
echo "* $box *\n! $title !\n* $box *\n";
?>
<?php echo mysql2date(get_option( 'date_format' ), current_time('mysql')); ?>


<?php if (isset($_the_content)) echo $_the_content; else $this->the_content(); ?>

<?php if (isset($_the_actions)) echo "$_the_actions"; ?>

