<?php
/*
Template Name: new_subscriber
*/
?>
<?php $this->get_header() ?>

			Subscription to <?php echo get_option('blogname'); ?>


Confirm your subscription by activating the following link :
{{subscribe}}


<?php unset($this->args->unsubscribe); ?>
<?php $this->get_footer() ?>