<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title></title>
	</head>
	<body>
		<div align='center' <?php $this->classes('globdiv'); ?>>
			<small <?php $this->classes('small'); ?>>
<?php if (isset($this->args->viewhtml)) { ?>
				Having trouble reading this email? View it on our 
				<a href='{{viewhtml}}'  <?php $this->classes('small'); ?>>website</a>.
				<br />
				<br />
<?php } ?>
			</small>
			<div>
				<table cellspacing='0' cellpadding='0' <?php $this->classes('htable'); ?>>
					<tbody <?php $this->classes('nopmb'); ?>>
						<tr <?php $this->classes('nopmb'); ?>>
							<td <?php $this->classes('htd_name'); ?>>
								<a href='<?php bloginfo('url');?>'  <?php $this->classes('htd_namea'); ?> onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
<?php bloginfo('name');?>
								</a>
							</td>
							<td rowspan='4' <?php $this->classes('htd_sidebar'); ?>>
<?php $this->get_sidebar() ?>
							</td>
						</tr>
						<tr <?php $this->classes('nopmb'); ?>>
							<td <?php $this->classes('htd_content'); ?>>
								<div <?php $this->classes('nopmb txtleft'); ?>>
<!-- end header -->