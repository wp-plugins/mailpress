<?php
/*
Template Name: weekly
Subject: [<?php bloginfo('name');?>] Last week post(s)
*/
?>
<?php $this->get_header() ?>
					<table width=100% border=0 cellspacing=0 cellpadding=0>
<?php while (have_posts()) : the_post(); ?>

						<tr>
							<td style='float:left;margin:0;padding:0pt 0pt 20px 45px;width:450px;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
								<div style='margin:0pt 0pt 40px;text-align:justify;'>
									<h2 style="margin:30px 0pt 0pt;text-decoration:none;color:#333;font-size:2em;font-weight:bold;font-family:'Trebuchet MS','Lucida Grande',Verdana,Arial,Sans-Serif;">
										<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
									</h2>
									<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.9em;line-height:1.5em;'>
										<?php the_time('F jS, Y') ?>
									</small>
									<div style='font-size:1.2em;'>
										<p style='line-height:1.4em;'>
											<?php the_excerpt_rss(); ?>
										</p>
									</div>
								</div>
							</td>
						</tr>
<?php endwhile; ?>
					</table>
<?php $this->get_footer() ?>