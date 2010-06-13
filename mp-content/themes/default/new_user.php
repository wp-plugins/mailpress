<?php
/*
Template Name: new_user
*/
?>
<?php $this->get_header() ?>


					<table width=100% border=0 cellspacing=0 cellpadding=0>
						<tr>
							<td style='float:left;margin:0;padding:0pt 0pt 20px 45px;width:450px;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
								<div style='margin:0pt 0pt 40px;text-align:justify;'>
									<h2 style="margin:30px 0pt 0pt;text-decoration:none;color:#333;font-size:2em;font-weight:bold;font-family:'Trebuchet MS','Lucida Grande',Verdana,Arial,Sans-Serif;">
									</h2>
									<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.9em;line-height:1.5em;'>
										<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
									</small>
									<div style='font-size:1.2em;'>
										<p style='line-height:1.4em;'>
<?php
printf(__('Username: %s'), $this->args->u->login );
echo "<br/>";

if (isset($this->args->admin))	printf(__('E-mail: %s'),   $this->args->u->email );
else 						printf(__('Password: %s'), $this->args->u->pwd );

echo "<br/><br/>";
echo ("<a href='" . get_option('siteurl') . "/wp-login.php'>" . __('Log in') . "</a><br><br>");
?>										</p>
									</div>
								</div>
							</td>
							<td>
								<?php $this->get_sidebar() ?>
							</td>
						</tr>
					</table>


<?php $this->get_footer() ?>