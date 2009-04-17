<?php
/*
Template Name: new_subscriber
Subject: [<?php bloginfo('name');?>] <?php printf( __('Waiting for : %s','MailPress'), $this->args->toemail ); ?>
*/
?>
<?php $this->get_header() ?>
			<table style='margin:0;padding:0;border:none;width:100%;'>
				<tr>
					<td style='margin:0;padding:0pt 0pt 20px 45px;border:none;width:450px;float:left;color:#333333;text-align:left;font-family:Verdana,Sans-Serif;'>
						<div style='margin:0pt 0pt 40px;padding:0;border:none;text-align:justify;'>
							<h2 style='margin:30px 0pt 0pt;padding:0;border:none;color:#333;font-size:1.4em;font-weight:bold;font-family:Verdana,Sans-Serif;'>
Email validation.
							</h2>
							<small style='margin:0;padding:0;border:none;line-height:2em;color:#777;font-size:0.7em;font-family:Arial,Sans-Serif;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
							</small>
							<div style='margin:0;padding:0;border:none;'>
								<p style='margin:0;padding:0;border:none;line-height:1.4em;font-size:0.85em;'>
Please confirm your email by clicking the following <a style='color:#6D8C82;font-family: verdana,geneva;font-weight:bold;' href='{{subscribe}}'>link</a>. 
								</p>
							</div>
						</div>
					</td>
				</tr>
			</table>
<?php unset($this->args->unsubscribe); ?>
<?php $this->get_footer() ?>