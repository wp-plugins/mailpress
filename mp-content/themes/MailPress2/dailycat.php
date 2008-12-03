<?php
/*
Template Name: dailycat
Subject: [<?php bloginfo('name');?>] Posts published yesterday  in {{newsletter[params][catname]}}
*/
?>
<?php $this->get_header() ?>
			<table <?php mp_classes('nopmb ctable'); ?>>
<?php while (have_posts()) : the_post(); ?>
				<tr>
					<td <?php mp_classes('nopmb ctd'); ?>>
						<div <?php mp_classes('cdiv'); ?>>
							<h2 <?php mp_classes('ch2'); ?>>
								<a <?php mp_classes('clink'); ?> href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
							</h2>
							<small <?php mp_classes('nopmb cdate'); ?>>
<?php the_time('F j, Y') ?>
							</small>
							<div <?php mp_classes('nopmb'); ?>>
								<p <?php mp_classes('nopmb cp'); ?>>
<?php the_content(); ?>
								</p>
							</div>
						</div>
					</td>
				</tr>
<?php endwhile; ?>
			</table>
<?php $this->get_footer() ?>