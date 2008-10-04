<?php
/*
Template Name: new_user
*/
?>
<?php $this->get_header() ?>
			<table style='margin:0;padding:0;border:none;width:100%;'>
				<tr>
					<td style='margin:0;padding:0pt 0pt 20px 45px;border:none;width:450px;float:left;color:#333333;text-align:left;font-family:Verdana,Sans-Serif;'>
						<div style='margin:0pt 0pt 40px;padding:0;border:none;text-align:justify;'>
							<h2 style='margin:30px 0pt 0pt;padding:0;border:none;color:#333;font-size:1.4em;font-weight:bold;font-family:Verdana,Sans-Serif;'>
							</h2>
							<small style='margin:0;padding:0;border:none;line-height:2em;color:#777;font-size:0.7em;font-family:Arial,Sans-Serif;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
							</small>
							<div style='margin:0;padding:0;border:none;'>
								<p style='margin:0;padding:0;border:none;line-height:1.4em;font-size:0.85em;'>
<?php
printf(__('Username: %s'), $this->args->u->login );
echo "<br/>";
if (isset($this->args->admin))	printf(__('E-mail: %s'),   $this->args->u->email );
else 						printf(__('Password: %s'), $this->args->u->pwd );
echo "<br/><br/>";
echo ("<a style='color:#6D8C82;font-family: verdana,geneva;font-weight:bold;' href='" . get_option('siteurl') . "/wp-login.php'>" . __('Log in') . "</a>.<br/><br/>");
?>
								</p>
							</div>
						</div>
					</td>
				</tr>
			</table>
<?php $this->get_footer() ?>