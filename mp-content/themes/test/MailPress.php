<?php
/*
Template Name: MailPress
Subject: [<?php bloginfo('name');?>] Lorem ipsum ... é à ù ç €
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title></title>
	</head>
	<body>
		<div align='center' style='color:#000;font-family: verdana,geneva;'>
			<small style='color:#6D8C82;'>
<?php if (isset($this->args->viewhtml)) { ?>
				Having trouble reading this email? View it on our 
				<a href='{{viewhtml}}' style='color: rgb(153, 153, 153);font-family: verdana,geneva;font-weight:bold;'>website</a>.
				<br />
				<br />
<?php } ?>
			</small>
			<table style='padding:0;margin:0;border:none;background:#6D8C82;width:100%;'>	
				<tbody style='padding:0;margin:0;border:none;'>
					<tr style='padding:0;margin:0;border:none;height:60px;'>
						<td style='padding:0;margin:0;border:none;'>
							<img src='MailPresslogo.gif' style='padding:20px 10px 20px 50px' alt=''/>
						</td>
						<td style='width:50px;'></td>
						<td style='padding:0;margin:0;border:none;'></td>
					</tr>
					<tr style='padding:0;margin:0;border:none;'>
						<td style='padding:0;margin:0;border:none;'></td>
						<td style='padding:0;margin:0;border:none;'></td>
						<td style='padding:0;margin:0;border:none;'></td>
					</tr>
				</tbody>
			</table>
			<table style='width:100%;height:70px;padding:0;margin:0;border-bottom:1px solid #C6D9E9;background:#DCFAF1;'>
				<tr style='padding:0;margin:0;border:none;'>
					<td style='padding:0 10px 0 0;margin:0;border:none;background:#DCFAF1;font-family:Georgia,Times,serif;color#555;font-size:30px;text-align:right;'>
						<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
					</td>
				</tr>
			</table>
<!-- end header -->
			<table style='margin:0;padding:0;border:none;width:100%;'>
				<tr>
					<td style='margin:0;padding:0pt 0pt 20px 45px;border:none;width:450px;float:left;color:#333333;text-align:left;font-family:Verdana,Sans-Serif;'>
						<div style='margin:0pt 0pt 40px;padding:0;border:none;text-align:justify;'>
							<h2 style='margin:30px 0pt 0pt;padding:0;border:none;color:#333;font-size:1.4em;font-weight:bold;font-family:Verdana,Sans-Serif;'>
Lorem ipsum ...
							</h2>
							<small style='margin:0;padding:0;border:none;line-height:2em;color:#777;font-size:0.7em;font-family:Arial,Sans-Serif;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
							</small>
							<div style='margin:0;padding:0;border:none;'>
								<p style='margin:0;padding:0;border:none;line-height:1.4em;font-size:0.85em;'>
é à ù ç €
 Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed nisi pede, tincidunt non, vehicula ac, auctor pulvinar, augue. Donec diam neque, hendrerit rhoncus, pellentesque non, ultrices quis, lacus. Nam quis leo nec magna rutrum vehicula. Aenean volutpat. Duis pharetra purus mattis arcu. Donec interdum orci eget sem vestibulum consectetuer. Maecenas bibendum erat id libero. Morbi congue. Donec sodales interdum nulla. Curabitur eu velit et orci euismod convallis. In lobortis posuere nisi. Pellentesque nec libero eu ligula accumsan adipiscing. Aenean vel mauris. Aliquam rutrum turpis nec augue. Duis massa magna, faucibus sed, lobortis quis, suscipit quis, eros. Curabitur urna. Suspendisse iaculis nibh sed sem. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.
								</p>
							</div>
						</div>
					</td>
				</tr>
			</table>
<!-- start footer -->
			<table style='margin:0;padding:10px 20px;border-top:1px solid #DEDEDE;width:100%;'>
				<tr>	
					<td style='font-family:Verdana,sans-serif;color:#2583AD;font-size:10px;'>
						<b>
							This mail is brought to you by MailPress.
						</b>
					</td>
					<td style='font-family:Georgia,Times,serif;color:#8c8c8c;font-size:14px;text-align:right;'>
						<b>
							MAIL IS SHARING POETRY
						</b>
					</td>
				</tr>
			</table>
		</div>
<?php if (isset($this->args->unsubscribe)) { ?>
		<small style='color:#6D8C82;'>
			<br/>
			<br/>
			Wish to unsubscribe <a href='{{unsubscribe}}' style='color: rgb(153, 153, 153);font-family: verdana,geneva;font-weight:bold;'>?</a>
		</small>
<?php } ?>
	</body>
</html>
