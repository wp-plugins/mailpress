<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title></title>
	</head>
	<body>
<?php $this->get_stylesheet(); ?>
		<div <?php $this->classes('body'); ?>>
			<div <?php $this->classes('wrapper'); ?>>
			<small style='color:#6D8C82;'>
<?php if (isset($this->args->viewhtml)) { ?>
				Having trouble reading this email? View it on our 
				<a href='{{viewhtml}}' style='color: rgb(153, 153, 153);font-family: verdana,geneva;font-weight:bold;'>website</a>.
				<br />
				<br />
<?php } ?>
			</small>
				<div <?php $this->classes('header'); ?>>
					<div <?php $this->classes('masthead'); ?>>
						<div <?php $this->classes('branding'); ?>>
							<div <?php $this->classes('site-title'); ?>>
								<span <?php $this->classes('site-title_span'); ?>>
									<a <?php $this->classes('site-title_a'); ?> href="<?php bloginfo( 'url' ) ?>/" title="<?php bloginfo( 'name' ) ?>" rel="home">
<?php bloginfo( 'name' ) ?>
									</a>
								</span>
							</div>
							<div <?php $this->classes('site-description'); ?>>
<?php bloginfo( 'description' ) ?>
							</div>
<?php
	$post_id = false;
	if (isset($this->args->newsletter['query_posts']['p'])) $post_id = $this->args->newsletter['query_posts']['p'];
	if (!is_numeric($post_id)) $post_id = false;
	if ( 	$post_id &&
		has_post_thumbnail( $post_id ) &&
		($image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'post-thumbnail' ) ) &&
		$image[1] >= MP_theme_2010::HEADER_IMAGE_WIDTH )
	{
?>
		<img src="<?php echo $image[0]; ?>" width="<?php echo MP_theme_2010::HEADER_IMAGE_WIDTH; ?>" height="<?php echo MP_theme_2010::HEADER_IMAGE_HEIGHT; ?>" alt="" <?php $this->classes('header_img'); ?> />
<?php
	}
	else
	{ 
?>
		<img src="<?php header_image(); ?>" width="<?php echo MP_theme_2010::HEADER_IMAGE_WIDTH; ?>" height="<?php echo MP_theme_2010::HEADER_IMAGE_HEIGHT; ?>" alt="" <?php $this->classes('header_img'); ?> />
<?php 
	} 
?>
						</div>
						<div <?php $this->classes('access'); ?>>
							<div <?php $this->classes('menu'); ?>>
								<ul>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div <?php $this->classes('main'); ?>>
					<div <?php $this->classes('container'); ?>>
						<div <?php $this->classes('content'); ?>>
<!-- end header -->