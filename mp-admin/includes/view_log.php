<?php

$h2 = __('View Log','MailPress');

$f = $_GET['id'];
$view_url 	=  get_option('siteurl') . '/' . self::get_path() . '/' . $f;
?>
<div class='wrap'>
	<div id="icon-mailpress-tools" class="icon32"><br /></div>
	<div id='mp_message'></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) self::message($message); ?>
		<div><p>&nbsp;</p></div>
	</div>
	<iframe id='mp' name='mp' style='width:100%;' src='<?php echo $view_url; ?>' ></iframe>
</div>