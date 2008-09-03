<?php
/*
Template Name: nogent94
Subject: [<?php bloginfo('name');?>] Lorem ipsum ... 
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title></title>
	</head>
	<body>
		<div align='center' style='color: rgb(153, 153, 153);font-family: verdana,geneva;'>
			<small>
<?php if (isset($this->args->viewhtml)) { ?>
				<a href='{{viewhtml}}' style='color: rgb(153, 153, 153);font-family: verdana,geneva;'>
					Si ce mail ne s'affiche pas correctement ouvrir ce lien
				</a>.
				<br />
<?php } ?>
			</small>
		</div>
		<div>
			<br/>
			<img src='Nogent94.gif' alt=''  align='' style='border:none;margin:0;padding:0'/>
			<br /><br />
		</div>
		<div style='display:block;height=1.45em;'>
			<img src='degrade.jpg' style='width:100%;height:25px;' alt=''  align='' style='border:none;margin:0;padding:0'/>
			<div style='float:left;font-family:verdana,geneva;sans-serif;'>
				&nbsp;
				<small>
					<b>
						<a href='<?php echo get_bloginfo('siteurl'); ?>' style='color:#D76716;text-align:left;text-decoration:none;outline-style:none;'>
							<?php echo get_bloginfo('siteurl'); ?>
						</a>
					</b>
				</small>
			</div>
			<div style='float:right;font-family:verdana,geneva;sans-serif;color:#590000'>
				<small>
					<b>
						<?php echo mysql2date('l j F Y', current_time('mysql')); ?>
					</b>
				</small>
			</div>
		</div>
		<br />
		<br />
		<br />
<!-- end header -->

					<table style='width:100%;border:none;'>
						<tr>
							<td style='float:left;margin:0;padding:0pt 0pt 20px 45px;width:450px;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
								<div style='margin:0pt 0pt 40px;text-align:justify;'>
									<h2 style='margin:30px 0pt 0pt;text-decoration:none;color:#333333;font-size:1.1em;font-family:Verdana,Sans-Serif;font-weight:bold;'>
Lorem ipsum ...
									</h2>
									<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.7em;line-height:1.5em;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
									</small>
									<div style='font-size:.85em;'>
										<p style='line-height:1.2em;'>
Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed nisi pede, tincidunt non, vehicula ac, auctor pulvinar, augue. Donec diam neque, hendrerit rhoncus, pellentesque non, ultrices quis, lacus. Nam quis leo nec magna rutrum vehicula. Aenean volutpat. Duis pharetra purus mattis arcu. Donec interdum orci eget sem vestibulum consectetuer. Maecenas bibendum erat id libero. Morbi congue. Donec sodales interdum nulla. Curabitur eu velit et orci euismod convallis. In lobortis posuere nisi. Pellentesque nec libero eu ligula accumsan adipiscing. Aenean vel mauris. Aliquam rutrum turpis nec augue. Duis massa magna, faucibus sed, lobortis quis, suscipit quis, eros. Curabitur urna. Suspendisse iaculis nibh sed sem. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.
										</p>
									</div>
								</div>
							</td>
						</tr>
					</table>

<!-- start footer -->
		<div align='center' style='color: rgb(153, 153, 153);font-family: verdana,geneva;'>
			<br />
			<small>
				Vous recevez cet e-mail car vous avez donn&eacute; votre accord pour recevoir des informations sur support &eacute;lectronique de la part de Nogent94.
				<br />
				Conform&eacute;ment aux dispositions de la loi 'informatique et libert&eacute;s' du 6 janvier 1978, vous disposez d'un droit d'acc&egrave;s et de rectification aux donn&eacute;es personnelles vous concernant que vous pouvez exercer en &eacute;crivant &agrave; <a href='mailto:contact@nogent94.com'>contact@nogent94.com</a>
				<br />
				Pour en savoir plus : <a href='www.cnil.fr'>www.cnil.fr</a>
				<br />
				<br />
				<br />
<?php if (isset($this->args->unsubscribe)) { ?>
				Pour se d&eacute;sinscrire :
				<br />
				Il vous suffit d'activer 
				<a href='{{unsubscribe}}'>
					ce lien
				</a>.
				<br />
				<br />
				<br />
<?php } ?>
			</small>
		</div>
	</body>
</html>
