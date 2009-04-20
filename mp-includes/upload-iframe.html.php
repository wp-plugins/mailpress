<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
		<title></title>
<?php
wp_enqueue_script('jquery');
do_action('admin_print_scripts' );
?>
<script type="text/javascript">
jQuery(document).ready( function() {
	jQuery('input#mp_fileupload_file_<?php echo $id; ?>').change(function(){
		var file = jQuery('input#mp_fileupload_file_<?php echo $id; ?>').val(); 
		parent.mp_fileupload.changed(<?php echo $id; ?>,file);

		jQuery('#upload_form_<?php echo $id; ?>').submit(function(){
			jQuery('label#mp_fileupload_file').hide();
			var filea = jQuery('input#mp_fileupload_file_<?php echo $id; ?>').val();
			var fileb = filea.match(/(.*)[\/\\]([^\/\\]+\.\w+)$/)
            	var file  = (fileb === null) ? jQuery('input#mp_fileupload_file_<?php echo $id; ?>').val() : fileb[2];

			var i = document.createElement('input');
			i.setAttribute('type', 'hidden');
			i.setAttribute('name', 'file');
			i.setAttribute('value', file);
			var f = document.getElementById('upload_form_<?php echo $id; ?>');
			f.appendChild(i);

			parent.mp_fileupload.submitted(<?php echo $id; ?>,file); 
			return true;
		});
		jQuery('#upload_form_<?php echo $id; ?>').submit();
	});
});
</script>
	</head>
	<body style='margin:0;padding:0;overflow:hidden;background:transparent;'>
		<form id='upload_form_<?php echo $id; ?>' action='<?php echo $url ?>' method='POST' enctype='multipart/form-data' style='margin:0;padding:0;overflow:hidden;'>
			<input type="hidden" name="action" 		value="html_mail_attachement" />
			<input type="hidden" name="draft_id" 	value="<?php echo $draft_id; ?>" />
			<input type="hidden" name="id" 		value="<?php echo $id; ?>" />
			<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
			<label class='mp_fileupload_file' id='mp_fileupload_file' style='	height:24px;width:132px;display:block;overflow:hidden;background:transparent url(images/upload.png) repeat;cursor:pointer;'>
				<input type='file' id='mp_fileupload_file_<?php echo $id; ?>' name='async-upload' style='height:24px;width:132px;margin:0;opacity:0;-moz-opacity:0;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);position:relative;' />
			</label>
			<input type='submit' id='upload_iframe_submit_<?php echo $id; ?>' style='display:none;' />
		</form>
	</body>
</html>
