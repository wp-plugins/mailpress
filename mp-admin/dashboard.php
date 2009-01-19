<?php
global $wpdb, $wp_locale;

$mailpress_url = 'http://www.nogent94.com?page_id=70';

$countm = $wpdb->get_var("SELECT sum(scount) FROM $wpdb->mp_stats WHERE stype='t';");
$counts = $wpdb->get_var("SELECT count(*)    FROM $wpdb->mp_users WHERE status='active';");
if (!$countm) $countm = 0;
if (!$counts) $counts = 0;

$plugin_data = get_plugin_data( '../' . MP_PATH . 'MailPress.php' );

$th = new MP_Themes();
$themes = $th->themes; 
$ct = $th->current_theme_info(); 

?>
<div class="wrap">
	<h2><?php _e('Dashboard'); ?></h2>
	<div id="rightnow">
		<h3 class="reallynow">
			<span>
				<?php _e('Right now','MailPress'); ?>
			</span>
<?php if (current_user_can('MailPress_edit_mails')) : ?>
			<a href="<?php echo MailPress_write; ?>" class="rbutton">
				<strong>
					<?php _e('Write a New Mail','MailPress'); ?>
				</strong>
			</a>
<?php endif; ?>
			<br class="clear" />
		</h3>
		<p class="youhave">
<?php 
				$as = sprintf( __ngettext( __('%s active subscriber', 'MailPress'), __('%s active subscribers', 'MailPress'), $counts ), $counts );
				$sm = sprintf( __ngettext( __('%s mail', 'MailPress'), __('%s mails', 'MailPress'), $countm ), $countm );
				printf( __('You have <a href=\'%1$s\'>%2$s</a> and send <a href=\'%3$s\'>%4$s</a> ... and counting ...','MailPress'), MailPress_users . '&amp;status=active',$as, MailPress_mails,$sm); 
?>
		</p>
		<p class="youare">
			<?php printf(__('You are using MailPress "%1$s" theme.','MailPress'), $ct->title); ?>&nbsp;&nbsp;
<?php if (current_user_can('MailPress_switch_themes')) : ?>
			<a href="<?php echo MailPress_design; ?>" class="rbutton">
				<?php _e('Change Theme','MailPress'); ?> 
			</a>
<?php endif; ?>
			<span id='wp-version-message'>
				&nbsp;&nbsp;<?php printf(__('This is MailPress version %1$s.','MailPress'), $plugin_data['Version']) ; ?>
			</span>
		</p>
	</div><!-- rightnow -->
	<br class="clear" />
	<div id="dashboard-widgets-wrap">
		<div id='dashboard-widgets'>
			<?php dynamic_sidebar('mp_dashboard'); ?>
			<br class='clear' />
			<br class='clear' />
		</div>
	</div>
</div><!-- wrap -->
<div id="mp_footer" style="background:#6D8C82 url(<?php echo '../' . MP_PATH . 'mp-admin/images/m.png'; ?>) no-repeat scroll 20px 10px;">
	<p>
		<?php printf(__('Thank you for mailing with <a href="%1$s ">MailPress</a>','MailPress'), $mailpress_url) ;  ?> | <a href="<?php echo $mailpress_url; ?>"><?php _e('Documentation'); ?></a> | <a href="<?php echo $mailpress_url; ?>"><?php _e('Feedback'); ?></a> | <?php printf(__('Version %1$s','MailPress'), $plugin_data['Version']) ; ?>
	</p>
</div>