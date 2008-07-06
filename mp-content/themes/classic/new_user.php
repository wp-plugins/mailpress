<?php
/*
Template Name: new_user
*/
?>
<?php $this->get_header() ?>

					<table style='padding:0;margin:0;border:none;width:100%;'>
						<tr style='padding:0;margin:0;border:none;'>
							<td style='padding:0pt 0pt 20px 45px;margin:0;border:none;width:450px;float:left;color:#333;text-align:left;font-family:Verdana,Sans-Serif;'>
								<div style='padding:0;margin:0pt 0pt 40px;border:none;text-align:justify;'>
									<h2 style='padding:0;margin:30px 0pt 0pt;border:none;color:#333;font-size:1.6em;font-weight:bold;font-family:Verdana,Sans-Serif;'>
									</h2>
									<small style='padding:0;margin:0;border:none;color:#777;line-height:2em;font-size:0.7em;font-family:Arial,Sans-Serif;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
									</small>
									<div style='padding:0;margin:0;border:none;font-size:1.2em;'>
										<p style='padding:0;margin:0;border:none;line-height:1.4em;'>
<?php
printf(__('Username: %s'), $this->args->u->login );
echo "<br/>\n";

if (isset($this->args->admin))	printf(__('E-mail: %s'),   $this->args->u->email );
else 						printf(__('Password: %s'), $this->args->u->pwd );

echo "<br/><br/>\n\n";
echo ("<a href='" . get_option('siteurl') . "/wp-login.php'>" . __('Log in') . "</a><br><br>\n\n");
?>
										</p>
									</div>
								</div>
							</td>
						</tr>
					</table>

<?php $this->get_footer() ?>