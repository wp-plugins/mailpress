<?php
/*
Template Name: daily
Subject: [<?php bloginfo('name');?>] Posts published yesterday
*/
?>
<?php $this->get_header() ?>
					<table style='padding:0;margin:0;border:none;width:100%;'>
<?php while (have_posts()) : the_post(); ?>
						<tr style='padding:0;margin:0;border:none;'>
							<td style='padding:0pt 0pt 20px 45px;margin:0;border:none;width:450px;float:left;color:#333;text-align:left;font-family:Verdana,Sans-Serif;'>
								<div style='padding:0;margin:0pt 0pt 40px;border:none;text-align:justify;'>
									<h2 style='padding:0;margin:30px 0pt 0pt;border:none;color:#333;font-size:1.6em;font-weight:bold;font-family:Verdana,Sans-Serif;'>
<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
									</h2>
									<small style='padding:0;margin:0;border:none;color:#777;line-height:2em;font-size:0.7em;font-family:Arial,Sans-Serif;'>
<?php the_time('F j, Y') ?>
									</small>
									<div style='padding:0;margin:0;border:none;font-size:1.2em;'>
										<p style='padding:0;margin:0;border:none;line-height:1.4em;'>
<?php the_excerpt_rss(); ?>
										</p>
									</div>
								</div>
							</td>
						</tr>
<?php endwhile; ?>
					</table>
<?php $this->get_footer() ?>