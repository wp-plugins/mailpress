<?php
/*
Template Name: monthlycat
Subject: [<?php bloginfo('name');?>] Last month posts  in {{newsletter[params][catname]}}
*/
?>
<?php $this->get_header() ?>
			<table style='margin:0;padding:0;border:none;width:100%;'>
<?php while (have_posts()) : the_post(); ?>
				<tr>
					<td style='margin:0;padding:0pt 0pt 20px 45px;border:none;width:450px;float:left;color:#333333;text-align:left;font-family:Verdana,Sans-Serif;'>
						<div style='margin:0pt 0pt 40px;padding:0;border:none;text-align:justify;'>
							<h2 style='margin:30px 0pt 0pt;padding:0;border:none;color:#333;font-size:1.4em;font-weight:bold;font-family:Verdana,Sans-Serif;'>
								<a style='text-decoration:none;color:#333;' href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
							</h2>
							<small style='margin:0;padding:0;border:none;line-height:2em;color:#777;font-size:0.7em;font-family:Arial,Sans-Serif;'>
<?php the_time('F j, Y') ?>
							</small>
							<div style='margin:0;padding:0;border:none;'>
								<p style='margin:0;padding:0;border:none;line-height:1.4em;font-size:0.85em;'>
<?php the_content(); ?>
								</p>
							</div>
						</div>
					</td>
				</tr>
<?php endwhile; ?>
			</table>
<?php $this->get_footer() ?>