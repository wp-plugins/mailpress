<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title></title>
	</head>
	<body>
			<small style='color:#6D8C82;'>
<?php if (isset($this->args->viewhtml)) { ?>
				Having trouble reading this email? View it on our 
				<a href='{{viewhtml}}' style='color: rgb(153, 153, 153);font-family: verdana,geneva;font-weight:bold;'>website</a>.
				<br />
				<br />
<?php } ?>
			</small>
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

