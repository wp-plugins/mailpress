<?php include('header.php'); ?>
<style type="text/css">* html { overflow-x: hidden; }</style>
<?php
wp_admin_css_color('classic', __('Classic'), admin_url("css/colors_classic.css"), array('#07273E', '#14568A', '#D54E21', '#2683AE'));
wp_admin_css_color('fresh', __('Fresh'), admin_url("css/colors_fresh.css"), array('#464646', '#CEE1EF', '#D54E21', '#2683AE'));

wp_admin_css( 'css/global' );
wp_admin_css();
wp_admin_css( 'css/colors' );
wp_admin_css( 'css/ie' );

do_action('admin_print_styles' );
?>
<script type="text/javascript">
//<![CDATA[
addLoadEvent = function(func) {if (typeof jQuery != "undefined") jQuery(document).ready(func); else if (typeof wpOnload!='function'){wpOnload=func;} else {var oldonload=wpOnload; wpOnload=function(){oldonload();func();}}};
//]]>
</script>
<?php
wp_enqueue_script('jquery-ui-tabs');
do_action('admin_print_scripts' );
?>
<link rel='stylesheet' href='<?php echo get_option('siteurl') . '/' . MP_PATH; ?>mp-admin/css/mail.css' type='text/css' title='MailPress' media='all' />
	</head>
	<body id="media-upload">
		<div id="wpwrap">
			<div id="wpcontent">
				<div id="wpbody" style='background-color:#fff;margin-left:15px;'>
					<div class='wrap'>
<?php if (isset($view)) : ?>
						<form action=''>
							<div id='post-body'>
								<table class="form-table">
									<tr>
										<th>
											<?php _e('From','MailPress'); ?>
										</th>
										<td>
											<?php echo $from; ?>
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('To','MailPress'); ?>
										</th>
										<td>
											<?php echo $to; ?>
										</td>
									</tr>
									<tr>
										<th>
											<?php _e('Subject','MailPress'); ?>
										</th>
										<td>
											<b><?php echo $subject;?></b> 
										</td>
									</tr>
								</table>
							</div>
						</form>
<?php endif; ?>
						<div id='example'>
							<ul class="tablenav ui-tabs-nav">
								<li><a href='#fragment-2'><span><?php _e('Plaintext View','MailPress'); ?></span></a></li>
								<li class='ui-tabs-selected'><a href='#fragment-1'><span><?php _e('Html View','MailPress'); ?></span></a></li>
							</ul>
							<div id='fragment-1'>
								<div style='margin:0;background:#fff;border:1px solid #c0c0c0;padding:5px;'>
									<?php echo $html; ?>
								</div>
							</div>
							<div id='fragment-2'>
								<div style='margin:0;background:#fff;border:1px solid #c0c0c0;padding:5px;'>
									<?php echo $plaintext; ?>
								</div>
							</div>
						</div>
<?php if (isset($attachements) && (!empty($attachements))) : ?>
						<div id='attachements'>
							<table>
								<tr>
									<td style='vertical-align:top;'>
										<?php _e('Attachements','MailPress'); ?>
									</td>
									<td>
										<table>
											<?php echo $attachements; ?>
										</table>
									</td>
								</tr>
							</table>
						</div>
<?php endif; ?>
					</div>
				</div>
			</div>
			<br />
		</div>
<?php do_action('admin_print_footer_scripts'); ?>
<script type="text/javascript">
	jQuery(document).ready(function(){jQuery('#example').tabs();});
</script>
	</body>
</html>