<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title></title>
	</head>
	<body>
		<div align='center' <?php mp_classes('globdiv'); ?>>
			<small style='color:#6D8C82;'>
<?php if (isset($this->args->viewhtml)) { ?>
				Having trouble reading this email? View it on our 
				<a href='{{viewhtml}}' <?php mp_classes('globlink'); ?>>website</a>.
				<br />
				<br />
<?php } ?>
			</small>
			<table <?php mp_classes('nopmb htable'); ?>>	
				<tbody <?php mp_classes('nopmb'); ?>>
					<tr <?php mp_classes('nopmb htr'); ?>>
						<td <?php mp_classes('nopmb txtleft'); ?>>
							<img src='MailPresslogo.gif' <?php mp_classes('logo'); ?> align='' alt=''/>
						</td>
						<td style='width:50px;'></td>
						<td <?php mp_classes('nopmb'); ?>></td>
					</tr>
					<tr <?php mp_classes('nopmb'); ?>>
						<td <?php mp_classes('nopmb'); ?>></td>
						<td <?php mp_classes('nopmb'); ?>></td>
						<td <?php mp_classes('nopmb'); ?>></td>
					</tr>
				</tbody>
			</table>
			<table <?php mp_classes('htdate'); ?>>
				<tr <?php mp_classes('nopmb'); ?>>
					<td <?php mp_classes('hdate'); ?>>
						<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
					</td>
				</tr>
			</table>
			<div  <?php mp_classes('contentdiv txtleft'); ?>>
<!-- end header -->