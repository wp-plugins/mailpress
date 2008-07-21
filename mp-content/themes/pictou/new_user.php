<?php
/*
Template Name: new_user
*/
?>
<?php $this->get_header() ?>
				<div id="content" style="margin:0;float:left;padding:10px;width:470px;">
					<div id="post-<?php the_ID(); ?>" style="margin:0;padding:0;margin-bottom:3em;">
						<p style="background:#EFEFEF;border:1px solid #CCC;color:#567;float:right;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-size:0.8em;font-weight:bold;margin:5px 0 0 5px;text-align:center;line-height:1.8em;">
							<span style='display:block;margin:0;padding:0;color:#567;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-size:0.8em;font-weight:bold;text-align:center;line-height:1.8em;padding:0 10px;'>
<?php echo date('M'); ?>
							</span>
							<span style='color:#567;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-weight:bold;text-align:center;display:block;margin:0;padding:0;font-size:1.6em;line-height:1.8em;padding:0 10px;'>
<?php echo date('d'); ?>
							</span>
							<span style='display:block;margin:0;padding:0;color:#567;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-size:0.8em;font-weight:bold;text-align:center;line-height:1.8em;padding:0 10px;'>
<?php echo date('Y'); ?>
							</span>
						</p>
						<h2 style="color:#333;font-family:Georgia,Tahoma,Verdana,Arial,Serif;font-weight:bold;margin:0;border-bottom:1px solid #DDD;font-size:1.6em;line-height:1.2em;padding:4px;">
							<span style="border:0pt none;color:#585D8B;">
New user !
							</span>
						</h2>
						<div style="margin:0;padding:0;color:#999;font-size:0.9em;margin-bottom:10px;padding-left:5px;">
							<p style="margin:0;padding:0;margin-bottom:0.5em;line-height:1.8em;">
&nbsp;
							</p>
					      </div>
					      <div style="margin:0;clear:both;padding:10px 5px;">
printf(__('Username: %s'), $this->args->u->login );
echo "<br/>";
if (isset($this->args->admin))	printf(__('E-mail: %s'),   $this->args->u->email );
else 						printf(__('Password: %s'), $this->args->u->pwd );
echo "<br/><br/>";
echo ("<a style='color:#585D8B;font-family:Georgia,Tahoma,Verdana,Arial,Serif;font-weight:bold;' href='" . get_option('siteurl') . "/wp-login.php'>" . __('Log in') . "</a>.<br/><br/>");
						</div>
				      </div>
				</div><!-- end of div content -->
				<!-- sidebars-->
<?php $this->get_footer();?>