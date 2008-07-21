<?php
/*
Template Name: monthly
Subject: [<?php bloginfo('name');?>] Last month posts
*/
?>
<?php $this->get_header() ?>
				<div id="content" style="margin:0;float:left;padding:10px;width:470px;">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					<div id="post-<?php the_ID(); ?>" style="margin:0;padding:0;margin-bottom:3em;">
						<p style="background:#EFEFEF;border:1px solid #CCC;color:#567;float:right;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-size:0.8em;font-weight:bold;margin:5px 0 0 5px;text-align:center;line-height:1.8em;">
							<span style='display:block;margin:0;padding:0;color:#567;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-size:0.8em;font-weight:bold;text-align:center;line-height:1.8em;padding:0 10px;'>
<?php the_time('M') ?>
							</span>
							<span style='color:#567;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-weight:bold;text-align:center;display:block;margin:0;padding:0;font-size:1.6em;line-height:1.8em;padding:0 10px;'>
<?php the_time('d') ?>
							</span>
							<span style='display:block;margin:0;padding:0;color:#567;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-size:0.8em;font-weight:bold;text-align:center;line-height:1.8em;padding:0 10px;'>
<?php the_time('Y') ?>
							</span>
						</p>
						<h2 style="color:#333;font-family:Georgia,Tahoma,Verdana,Arial,Serif;font-weight:bold;margin:0;border-bottom:1px solid #DDD;font-size:1.6em;line-height:1.2em;padding:4px;">
							<a style="border:0pt none;color:#585D8B;" href="<?php the_permalink() ?>" rel="bookmark">
<?php the_title(); ?>
							</a>
						</h2>
						<div style="margin:0;padding:0;color:#999;font-size:0.9em;margin-bottom:10px;padding-left:5px;">
							<p style="margin:0;padding:0;margin-bottom:0.5em;line-height:1.8em;">
								Published by <?php the_author_posts_link() ?> at <?php the_time();?> under <?php the_category(',') ?> <?php edit_post_link(); ?>
							</p>
					      </div>
					      <div style="margin:0;clear:both;padding:10px 5px;">
<?php the_excerpt_rss(); ?>
						</div>
				      </div>
<?php endwhile; ?>
				</div><!-- end of div content -->
				<!-- sidebars-->
<?php $this->get_footer();?>