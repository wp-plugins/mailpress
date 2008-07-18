<?php
/*
Template Name: monthly
Subject: [<?php bloginfo('name');?>]  Les articles du mois
*/
?>
<?php $this->get_header() ?>
			<div>
				<table style='width:100%;border:none;'>
<?php while (have_posts()) : the_post(); ?>
					<tr>
						<td style='float:left;margin:0 45px;padding:0;width:auto;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
							<div style='margin:0pt 0pt 40px;text-align:justify;'>
								<h2 style='margin:30px 0pt 0pt;text-decoration:none;color:#333333;font-size:1.1em;font-family:Verdana,Sans-Serif;font-weight:bold;'>
<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
								</h2>
								<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.7em;line-height:1.5em;'>
<?php the_time('F j, Y') ?>
								</small>
								<div style='font-size:.85em;'>
									<p style='line-height:1.2em;'>
<?php the_excerpt_rss(); ?>
									</p>
								</div>
							</div>
						</td>
					</tr>
<?php endwhile; ?>
				</table>
			</div>
<?php $this->get_footer() ?>