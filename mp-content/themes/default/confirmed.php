<?php
/*
Template Name: confirmed
Subject: [<?php bloginfo('name');?>] <?php printf( __('%s confirmed'), $this->args->toemail); ?>
*/
?>
<?php $this->get_header() ?>

					<table style='border:0;width:100%;'>
						<tr>
							<td style='float:left;margin:0;padding:0pt 0pt 20px 45px;width:400px;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
								<div style='margin:0pt 0pt 40px;text-align:justify;'>
									<h2 style="margin:30px 0pt 0pt;text-decoration:none;color:#333;font-size:2em;font-weight:bold;font-family:'Trebuchet MS','Lucida Grande',Verdana,Arial,Sans-Serif;">
<?php _e('Congratulations !'); ?> 
									</h2>
									<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.9em;line-height:1.5em;'>
										<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
									</small>
									<div style='font-size:1.2em;'>
										<p style='line-height:1.4em;'>
<?php printf( __('You are now a subscriber of : %s'), "<a href='" . get_option('siteurl') . "'>" . get_option('blogname') . "</a>" ); ?>
										</p>
									</div>
								</div>
							</td>
<!--
							<td>
								<?php $this->get_sidebar() ?>
							</td>
-->
						</tr>
					</table>
<?php $this->get_footer() ?>