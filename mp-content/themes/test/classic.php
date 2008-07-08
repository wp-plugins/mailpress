<?php
/*
Template Name: classic
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
		<div align='center' style='color:#000;font-family: verdana,geneva;'>
			<small style='color:#c0c0c0;font-family:Times,serif;font-size:80%;font-size-adjust:none;font-stretch:normal;font-style:italic;font-variant:normal;font-weight:normal;letter-spacing:0.1em;line-height:normal;'>
<?php if (isset($this->args->viewhtml)) { ?>
				Having trouble reading this email? View it on our 
				<a href='{{viewhtml}}' style='color:#c0c0c0;font-family:Times,serif;font-size:80%;font-size-adjust:none;font-stretch:normal;font-style:italic;font-variant:normal;font-weight:bold;letter-spacing:0.1em;line-height:normal;'>website</a>.
				<br />
				<br />
<?php } ?>
			</small>
<!--border-color:#565;border-style:solid;border-width:3px 2px 1px-->
			<table cellspacing='0' cellpadding='0' style='margin:0;padding:0;background:#fff none repeat scroll 0%;;color:#000;font-family:Verdana,sans-serif;'>
				<tbody style='margin:0;padding:0;'>
					<tr style='margin:0;padding:0;'>
						<td style='margin:0pt;padding:15px 10px 15px 60px;height:75px;background:#90A090 none repeat scroll 0%;border-color:#99AA99 rgb(85, 102, 85) rgb(170, 187, 170) rgb(153, 170, 153);border-style:solid solid double;border-width:1px 1px 3px;letter-spacing:0.2em;line-height:normal;font-stretch:normal;font-style:italic;font-variant:normal;font-weight:normal;font-size-adjust:none;font-family:Times,serif;font-size:230%;'>
							<a href='<?php bloginfo('url');?>' style='color:#fff;text-decoration:none;'>
<?php bloginfo('name');?>
							</a>
						</td>
						<td rowspan='2' style='padding:20px 0pt 10px 30px;margin:0 2px 0 0;vertical-align:top;width:11em;background:#fff none repeat scroll 0%;border-left:1px dotted #ccc;border-top:3px solid #E0E6E0;'>
<?php $this->get_sidebar() ?>
						</td>
					</tr>
					<tr style='margin:0;padding:0;'>
						<td style='margin:0;padding:30px 60px 0pt 3em;border:none;vertical-align:top;'>
<!-- end header -->

					<table style='padding:0;margin:0;border:none;width:100%;'>
						<tr style='padding:0;margin:0;border:none;'>
							<td style='padding:0pt 0pt 20px 45px;margin:0;border:none;width:450px;float:left;color:#333;text-align:left;font-family:Verdana,Sans-Serif;'>
								<div style='padding:0;margin:0pt 0pt 40px;border:none;text-align:justify;'>
									<h2 style='padding:0;margin:30px 0pt 0pt;border:none;color:#333;font-size:1.6em;font-weight:bold;font-family:Verdana,Sans-Serif;'>
Lorem ipsum ...
									</h2>
									<small style='padding:0;margin:0;border:none;color:#777;line-height:2em;font-size:0.7em;font-family:Arial,Sans-Serif;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
									</small>
									<div style='padding:0;margin:0;border:none;font-size:1.2em;'>
										<p style='padding:0;margin:0;border:none;line-height:1.4em;'>
Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed nisi pede, tincidunt non, vehicula ac, auctor pulvinar, augue. Donec diam neque, hendrerit rhoncus, pellentesque non, ultrices quis, lacus. Nam quis leo nec magna rutrum vehicula. Aenean volutpat. Duis pharetra purus mattis arcu. Donec interdum orci eget sem vestibulum consectetuer. Maecenas bibendum erat id libero. Morbi congue. Donec sodales interdum nulla. Curabitur eu velit et orci euismod convallis. In lobortis posuere nisi. Pellentesque nec libero eu ligula accumsan adipiscing. Aenean vel mauris. Aliquam rutrum turpis nec augue. Duis massa magna, faucibus sed, lobortis quis, suscipit quis, eros. Curabitur urna. Suspendisse iaculis nibh sed sem. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.
										</p>
									</div>
								</div>
							</td>
						</tr>
					</table>

<!-- start footer -->
								</td>
								<td></td>
							</tr>
							<tr style='margin:0;padding:0;'>
								<td style='margin:10px 0pt 0pt;padding:3px;background:#90A090 none repeat scroll 0%;border-top:3px double #AABBAA;letter-spacing:-1px;line-height:175%;text-align:center;color:#fff;font-size:11px;font-size-adjust:none;font-stretch:normal;font-style:normal;font-variant:normal;font-weight:normal;font-family:Verdana,sans-serif;'>
									<cite style='margin:0;padding:0;font-size:90%;font-style:normal;'>
										This mail is brought to you by 
										<a style='margin:0;padding:0;color:#fff;' href='http://www.nogent94/page_id=70'>
											<strong style='margin:0;padding:0;border:none;'>
												MailPress
											</strong>
										</a>
									</cite>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
<?php if (isset($this->args->unsubscribe)) { ?>
		<small style='color:#c0c0c0;font-family:Times,serif;font-size:80%;font-size-adjust:none;font-stretch:normal;font-style:italic;font-variant:normal;font-weight:normal;letter-spacing:0.1em;line-height:normal;'>
			<br/>
			<br/>
			Wish to unsubscribe <a href='{{unsubscribe}}' style='color:#c0c0c0;font-family:Times,serif;font-size:110%;font-size-adjust:none;font-stretch:normal;font-style:italic;font-variant:normal;font-weight:bold;letter-spacing:0.1em;line-height:normal;'>?</a>
		</small>
<?php } ?>
	</body>
</html>