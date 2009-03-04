<?php
/*
Template Name: new_subscriber
Subject: [<?php bloginfo('name');?>] <?php printf( __('Waiting for : %s'), $this->args->toemail ); ?>
*/
?>
<?php $this->get_header() ?>
								<h2 style="-x-system-font:none;border-bottom:1px dotted #CCCCCC;font-family:'Times New Roman',Times,serif;font-size:95%;font-size-adjust:none;font-stretch:normal;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:0.2em;line-height:normal;margin:15px 0 2px;padding-bottom:2px;">
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
								</h2>
								<h3 style="color:#342;border-bottom:1px dotted #EEEEEE;font-family:'Times New Roman',Times,serif;margin-top:0;">
<?php _e('Please confirm your subscription'); ?>
								</h3>
								<div style="-x-system-font:none;font-family:'Lucida Grande','Lucida Sans Unicode',Verdana,sans-serif;font-size:90%;font-size-adjust:none;font-stretch:normal;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:-1px;line-height:175%;">
									<p>
<?php _e('Please confirm your subscription by clicking the link below : '); ?>
										<br/>
											<span style='text-align:center;'>
												<a style='color:#342;' href='{{subscribe}}'>
<?php _e('Confirm'); ?>
												</a>
										<br/><br/>
									</p>
								</div>
<?php unset($this->args->unsubscribe); ?>
<?php $this->get_footer() ?>