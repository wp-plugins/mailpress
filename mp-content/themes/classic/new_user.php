<?php
/*
Template Name: new_user
*/
?>
<?php $this->get_header() ?>
								<h2 style="-x-system-font:none;border-bottom:1px dotted #CCCCCC;font-family:'Times New Roman',Times,serif;font-size:95%;font-size-adjust:none;font-stretch:normal;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:0.2em;line-height:normal;margin:15px 0 2px;padding-bottom:2px;">
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
								</h2>
								<h3 style="color:#342;border-bottom:1px dotted #EEEEEE;font-family:'Times New Roman',Times,serif;margin-top:0;">
								</h3>
								<div style="-x-system-font:none;font-family:'Lucida Grande','Lucida Sans Unicode',Verdana,sans-serif;font-size:90%;font-size-adjust:none;font-stretch:normal;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:-1px;line-height:175%;">
									<p>
<?php
printf(__('Username: %s'), $this->args->u->login );
echo "<br/>\n";

if (isset($this->args->admin))	printf(__('E-mail: %s'),   $this->args->u->email );
else 						printf(__('Password: %s'), $this->args->u->pwd );

echo "<br/><br/>\n\n";
echo ("<a href='" . get_option('siteurl') . "/wp-login.php'>" . __('Log in') . "</a><br><br>\n\n");
?>
									<br/><br/>
									</p>
								</div>
<?php $this->get_footer() ?>