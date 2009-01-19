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
						<td style='padding:0;margin:0;border:none;text-align:left;'>
							<img src='MailPresslogo.gif' style='border:none;padding:20px 10px 20px 50px' align='' alt=''/>
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
			<div  style='margin:0;padding:20px;border:0;text-align:left;'>
<!-- end header -->