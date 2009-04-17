<?php
/*
Template Name: single
Subject: [<?php bloginfo('name');?>] New Post : {{newsletter[the_title]}}
*/
?>
<?php $this->get_header() ?>
<?php while (have_posts()) : the_post(); ?>
								<h2 style="-x-system-font:none;border-bottom:1px dotted #CCCCCC;font-family:'Times New Roman',Times,serif;font-size:95%;font-size-adjust:none;font-stretch:normal;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:0.2em;line-height:normal;margin:15px 0 2px;padding-bottom:2px;">
<?php the_time('F j, Y') ?>
								</h2>
								<h3 style="color:#000000;border-bottom:1px dotted #EEEEEE;font-family:'Times New Roman',Times,serif;margin-top:0;">
									<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>" style="text-decoration:none;color:#342;" onmouseover="this.style.color='#9a8';" onmouseout="this.style.color='#342';">
<?php the_title(); ?>
									</a>
								</h3>
								<div style="-x-system-font:none;font-family:'Lucida Grande','Lucida Sans Unicode',Verdana,sans-serif;font-size:90%;font-size-adjust:none;font-stretch:normal;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:-1px;line-height:175%;">
									<p>
<?php the_excerpt_rss(); ?>
									<br/><br/>
									</p>
								</div>
<?php endwhile; ?>
<?php $this->get_footer() ?>