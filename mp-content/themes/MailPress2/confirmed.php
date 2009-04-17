<?php
/*
Template Name: confirmed
Subject: [<?php bloginfo('name');?>] <?php printf('%s confirmed', $this->args->toemail); ?>
*/
?>
<?php $this->get_header() ?>
			<table <?php mp_classes('nopmb ctable'); ?>>
				<tr>
					<td <?php mp_classes('nopmb ctd'); ?>>
						<div <?php mp_classes('cdiv'); ?>>
							<h2 <?php mp_classes('ch2'); ?>>
Congratulations !
							</h2>
							<small <?php mp_classes('nopmb cdate'); ?>>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
							</small>
							<div <?php mp_classes('nopmb'); ?>>
								<p <?php mp_classes('nopmb cp'); ?>>

You are now a subscriber of : 
									<a <?php mp_classes('clink2'); ?> href='<?php echo get_option('siteurl'); ?>'>
										<?php echo get_option('blogname'); ?>
									</a>
								</p>
							</div>
						</div>
					</td>
				</tr>
			</table>
<?php $this->get_footer() ?>