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
			<img src='degrade.jpg' style='width:100%;height:25px;border:none;padding:5px 0;' align='' alt='' />
			<span style='float:left;font-family:verdana,geneva;sans-serif;padding:0;margin:0;'>
				<small>
					<b>
						<a href='<?php echo get_bloginfo('siteurl'); ?>' style='color:#D76716;text-align:left;text-decoration:none;outline-style:none;'>
							<?php echo get_bloginfo('siteurl'); ?>
						</a>
					</b>
				</small>
			</span>
			<span style='float:right;font-family:verdana,geneva;sans-serif;color:#590000'>
				<small>
					<b>
						<?php echo mysql2date('l j F Y', current_time('mysql')); ?>
					</b>
				</small>
			</span>
		</div>
		<br style='clear:both;'/>
		<br />
		<br />
<!-- end header -->