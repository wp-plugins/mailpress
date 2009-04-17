<?php
// Mails - mini dashboard
wp_add_dashboard_widget( 	'mp_mini_dashboard',					
					__( "MailPress - 'Right Now'", 'MailPress' ), 
					'mp_mini_dashboard', 
					$control_callback = null 
				);
// Posts - most subscribed
wp_add_dashboard_widget( 	'mp_posts_most_subscribed',					
					__( 'MailPress - Most subscribed', 'MailPress' ), 
					'mp_posts_most_subscribed',
					$control_callback = null 
				);
// Subscribers Map
wp_add_dashboard_widget( 	'mp_map',					
					__( 'MailPress - Subscribers Map', 'MailPress' ),
					'mp_map',
					'mp_map_control'
				);
// Mails - send
wp_add_dashboard_widget( 	'mp_mails_sent',					
					__( 'MailPress - Mails sent', 'MailPress' ),
					'mp_mails_sent',
					$control_callback = null 
				);
// Subscribers - Activity
wp_add_dashboard_widget( 	'mp_subscribers_activity',					
					__( 'MailPress - Subscribers activity', 'MailPress' ), 
					'mp_subscribers_activity',
					$control_callback = null 
				);
// Mails - Activity
wp_add_dashboard_widget( 	'mp_mails_activity',					
					__( 'MailPress - Mails activity', 'MailPress' ), 
					'mp_mails_activity',
					$control_callback = null 
				);
// Posts - Activity
wp_add_dashboard_widget( 	'mp_comment_subscribers_per_post',					
					__( 'MailPress - Comments subscribers per post', 'MailPress' ),
					'mp_comment_subscribers_per_post',
					$control_callback = null 
				);

/////

function mp_map() {

	global $wpdb, $wp_locale;

	if ( !$options = get_option( 'MailPress_dashboard_mp_map' ) )
	{
		$options['code'] = 'world';
		$options['title'] = __( 'Subscribers - World', 'MailPress' );
	}

	$chdW = $chldW = '';

	if ('usa' == $options['code'])
	{
		$countalls = $wpdb->get_var("SELECT count(*) FROM $wpdb->mp_users WHERE created_country = 'US' and created_US_state <> 'ZZ'  ;");
		$query = "SELECT created_US_state as toto, count(*) as count FROM $wpdb->mp_users WHERE created_country = 'US' and created_US_state <> 'ZZ' GROUP BY created_US_state;";
	}
	else
	{
		$countalls = $wpdb->get_var("SELECT count(*) FROM $wpdb->mp_users WHERE created_country <> 'ZZ' ;");
		$query = "SELECT created_country as toto, count(*) as count FROM $wpdb->mp_users WHERE created_country <> 'ZZ' GROUP BY created_country;";
	}
	$users = $wpdb->get_results( $query );
	foreach($users as $user)
	{
		if (!empty($chdW)) $chdW .= ',';
		$chldW .= ('UK' == $user->toto) ? 'GB' : $user->toto;
		$chdW .= round(100 * $user->count/$countalls);
	}
?>
<div style='text-align:center;'>
<img src="http://chart.apis.google.com/chart?chs=440x200<?php if ('' == $chdW) echo '&amp;chd=s:_'; else echo '&amp;chd=t:' . $chdW; ?>&amp;chco=ffffff,B5F8C2,294D30<?php if ('' != $chldW) echo '&amp;chld=' . $chldW; ?>&amp;chf=bg,s,EAF7FE&amp;cht=t&amp;chtm=<?php echo $options['code']; ?>" alt="<?php echo $options['title']; ?>" />
</div>
<?php
}

function mp_map_control() {

	$c= array (	'africa' 		=> __('Africa','MailPress'),
			'asia'		=> __('Asia','MailPress'),
			'europe'		=> __('Europe','MailPress'),
			'middle_east'	=> __('Middle East','MailPress'),
			'south_america'	=> __('South America','MailPress'),
			'usa'			=> __('USA','MailPress'),
			'world'		=> __('World','MailPress'));

	if ( !$options = get_option( 'MailPress_dashboard_mp_map' ) )
	{
		$options['code'] = 'world';
		$options['title'] = $c[$options['code']];
	}
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['code']) ) 
	{	
		update_option( 'MailPress_dashboard_mp_map', array('code' => $_POST['code'] , 'title' => $c[$_POST['code']]) );
		return;
	}
?>
			<select id='code' name='code'>
<?php
			MP_Admin::select_option($c, $options['code']);
?>
			</select>
<?php
}




// Mails - mini dashboard
function mp_mini_dashboard() {

	global $wpdb, $wp_locale;

	$countm = $wpdb->get_var("SELECT sum(scount) FROM $wpdb->mp_stats WHERE stype='t';");
	$counts = $wpdb->get_var("SELECT count(*)    FROM $wpdb->mp_users WHERE status='active';");
	if (!$countm) $countm = 0;
	if (!$counts) $counts = 0;

	$plugin_data = get_plugin_data( '../' . MP_PATH . 'MailPress.php' );

	$th = new MP_Themes();
	$themes = $th->themes; 
	$ct = $th->current_theme_info(); 
?>
<div id="dashboard_right_now">
<div class="inside">
	<p class='sub'><?php _e('At a Glance'); ?> </p>
	<div class='table'>
		<table>
			<tr class='first'>
				<td class="first b b-posts">
<?php if (current_user_can('MailPress_edit_mails')) : ?>
					<a href="<?php echo MailPress_mails; ?>"><?php echo $countm; ?></a>
<?php else : ?>
					<?php echo $countm; ?>
<?php endif; ?>
				</td>
				<td class="t posts"><?php echo( __ngettext( __('Mail sent', 'MailPress'), __('Mails sent', 'MailPress'), $countm )); ?></td>
				<td class="b b-comments">
<?php if (current_user_can('MailPress_edit_users')) : ?>
					<a href="<?php echo MailPress_users; ?>"><?php echo $counts; ?></a>
<?php else : ?>
					<?php echo $counts; ?>
<?php endif; ?>
				</td>
				<td class="last t approved"><?php echo(__ngettext( __('Active subscriber', 'MailPress'), __('Active subscribers', 'MailPress'), $counts )); ?></td>
			</tr>
		</table>
	</div>
	<div class="versions">
		<p>
<?php if (current_user_can('MailPress_switch_themes')) : ?>
			<a class="button rbutton" href="<?php echo MailPress_design; ?>"><?php _e('Change Theme','MailPress'); ?></a>
<?php endif; ?>
		<?php printf(__('Current theme is : %s','MailPress'),"<span class='b'>".$ct->title."</span>"); ?>
		</p>
		<span id="wp-version-message">
			<?php printf(__('and using <span class=\'b\'>MailPress version %1$s</span>.','MailPress'), $plugin_data['Version']) ; ?>
		</span>
	</div>
</div>
</div>
<?php
}

// Mails - send

function mp_mails_sent() {

	global $wpdb, $wp_locale;

	$dend	= date('Y-m-d');
	$y 	= substr($dend,0,4);
	$m 	= substr($dend,5,2);
	$d 	= substr($dend,8,2);
	$dbeg = date('Y-m-d',mktime(0, 0, 0, $m, $d-50, $y));

	$y 	= substr($dbeg,0,4);
	$m 	= substr($dbeg,5,2);
	$d 	= substr($dbeg,8,2);
	$time = $dbeg;

	$chxlM0 = $chxlM1 = $chxlM2 = $chxlM3 = '';
	$wtime = $time; $wy = $y; $wm = $m; $wd = $d;
	do 
	{
		if 		(empty($chxlM3)) 			$chxlM3 .= '|' . $wy;
		elseif 	('0101' == substr($wtime,5,2) . substr($wtime,8,2)) $chxlM3 .= '|' . substr($wtime,0,4);
		else 							$chxlM3 .= '|';

		$chxlM2 .= ('15' == substr($wtime,8,2)) ? '|' . $wp_locale->get_month_abbrev($wp_locale->get_month(substr($wtime,5,2))) : '|';

		if 		('01' == substr($wtime,8,2)) 	$chxlM0 .= '|01';
		elseif 	('15' == substr($wtime,8,2)) 	$chxlM0 .= '|15';
		else 							$chxlM0 .= '|';

		$wtime = date('Y-m-d',mktime(0, 0, 0, $wm, ++$wd, $wy));
	} while ($wtime <= $dend);

	$chdM = '';
	$query = "SELECT sdate, sum(scount) AS count FROM $wpdb->mp_stats WHERE stype = 't' AND sdate BETWEEN '$dbeg' AND '$dend' GROUP BY sdate ORDER BY sdate;";
	$mails = $wpdb->get_results( $query );
	foreach($mails as $mail)
	{
		if (!empty($chdM))	$chdM .= ',';

		if ($mail->sdate > $time)
		{
			do 
			{
				$chdM .= '-1,';
				$time = date('Y-m-d',mktime(0, 0, 0, $m, ++$d, $y));
			} while ($mail->sdate > $time);
		}
		$chdM .= $mail->count;
		if ($chxlM1 < $mail->count) $chxlM1 = $mail->count;

		$time = date('Y-m-d',mktime(0, 0, 0, $m, ++$d, $y));
	}
	if ($time <= $dend)
	{
		do 
		{
			$chdM .= ',-1';

			$time = date('Y-m-d',mktime(0, 0, 0, $m, ++$d, $y));
		} while ($time <= $dend);
	}
	if ($mails) 
	{ 
?>
<div style='text-align:center;'>
<img src="http://chart.apis.google.com/chart?cht=bvg&amp;chs=400x200&amp;chxt=x,y,x,x&amp;chxl=0:<?php echo $chxlM0; ?>|1:||<?php echo $chxlM1; ?>|2:<?php echo $chxlM2; ?>|3:<?php echo $chxlM3; ?>&amp;chds=0,<?php echo $chxlM1; ?>&amp;chbh=5,1,1&amp;chco=4d89f9&amp;chd=<?php if ('' == $chdM) echo 's:_'; else echo 't:' . $chdM; ?>" alt="<?php _e( 'Mails - send', 'MailPress' ); ?>" />
</div>
<?php
	} 
	echo $after_widget;
}

// Subscribers - Activity

function mp_subscribers_activity() {

	global $wpdb, $wp_locale;

// Subscriber activity

	$BLOG = $COMM = $WAIT = 0;
	$chdS = $chxlS0 = $chxlS1 = $chxlS2 = $chxlS3 = '';
	$Ss = array();

	$dend	= date('Y-m-d');
	$y 	= substr($dend,0,4);
	$m 	= substr($dend,5,2);
	$d 	= substr($dend,8,2);
	$dbeg = date('Y-m-d',mktime(0, 0, 0, $m, $d-50, $y));
	$y 	= substr($dbeg,0,4);
	$m 	= substr($dbeg,5,2);
	$d 	= substr($dbeg,8,2);
	$time = $dbeg;

	$wtime = $time; $wy = $y; $wm = $m; $wd = $d;
	do 
	{
		if 		(empty($chxlS3)) 			$chxlS3 .= '|' . $wy;
		elseif 	('0101' == substr($wtime,5,2) . substr($wtime,8,2)) $chxlS3 .= '|' . substr($wtime,0,4);
		else 							$chxlS3 .= '|';

		$chxlS2 .= ('15' == substr($wtime,8,2)) ? '|' . $wp_locale->get_month_abbrev($wp_locale->get_month(substr($wtime,5,2))) : '|';

		if 		('01' == substr($wtime,8,2)) 	$chxlS0 .= '|01';
		elseif 	('15' == substr($wtime,8,2)) 	$chxlS0 .= '|15';
		else 							$chxlS0 .= '|';

		$wtime = date('Y-m-d',mktime(0, 0, 0, $wm, ++$wd, $wy));
	} while ($wtime <= $dend);

	$query = "SELECT slib, sum(scount) as scount FROM $wpdb->mp_stats WHERE stype = 'u' AND sdate < '$dbeg' GROUP BY slib;";
	$users = $wpdb->get_results( $query );
	foreach($users as $user) if ('active' == $user->slib) $BLOG = $COMM = $user->scount; elseif ('comment' == $user->slib) $COMM += $user->scount; elseif ('waiting' == $user->slib)  $WAIT = $user->scount;

	$query = "SELECT sdate, slib, scount FROM $wpdb->mp_stats WHERE stype = 'u' AND sdate BETWEEN '$dbeg' AND '$dend' ORDER BY sdate;";
	$users = $wpdb->get_results( $query );
	foreach($users as $user) $Ss [$user->sdate] [$user->slib] = $user->scount;

	foreach ($Ss as $date => $S)
	{
		if (!empty($chdS))
		{
			$chdS['blog'] .= ',';
			$chdS['comm'] .= ',';
			$chdS['wait'] .= ',';
			$chdS['fake'] .= ',';
		}

		if ($date > $time)
		{
			do 
			{
				$chdS['blog'] .= $BLOG . ',';
				$chdS['comm'] .= $COMM . ',';
				$chdS['wait'] .= $WAIT . ',';
				$chdS['fake'] .= '0,';

				$time = date('Y-m-d',mktime(0, 0, 0, $m, ++$d, $y));
			} while ($date > $time);
		}

		$active  = (isset($S['active']))  ? $S['active']  : 0;
		$waiting = (isset($S['waiting'])) ? $S['waiting'] : 0;
		$comment = (isset($S['comment'])) ? $S['comment'] : 0;

		$BLOG += $active;
		$COMM += $comment + $active;
		$WAIT += $waiting;

		$chdS['blog'] .= $BLOG;
		$chdS['comm'] .= $COMM;
		$chdS['wait'] .= $WAIT;
		$chdS['fake'] .= '0';

		if ($chxlS1 < $BLOG) $chxlS1 = $BLOG;
		if ($chxlS1 < $COMM) $chxlS1 = $COMM;
		if ($chxlS1 < $WAIT) $chxlS1 = $WAIT;

		$time = date('Y-m-d',mktime(0, 0, 0, $m, ++$d, $y));
	}
	if ($time <= $dend)
	{
		do
		{
			$chdS['blog'] .= ',' . $BLOG;
			$chdS['comm'] .= ',' . $COMM;
			$chdS['wait'] .= ',' . $WAIT;
			$chdS['fake'] .= ',0';

			$time = date('Y-m-d',mktime(0, 0, 0, $m, ++$d, $y));
		} while ($time <= $dend);
	}
	if ($Ss != array()) 
	{
?>
<div style='text-align:center;'>
<img src="http://chart.apis.google.com/chart?cht=lc&amp;chs=395x230&amp;chd=t:<?php echo $chdS['wait']; ?>|<?php echo $chdS['comm']; ?>|<?php echo $chdS['blog']; ?>|<?php echo $chdS['fake']; ?>&amp;chco=224499,FF0000,80C65A,000000&amp;chm=b,224499,0,1,0|b,FF0000,1,2,0|b,80C65A,2,3,0&amp;chds=0,<?php echo $chxlS1; ?>&amp;chxt=x,y,x,x&amp;chxl=0:<?php echo $chxlS0; ?>|1:||<?php echo $chxlS1; ?>|2:<?php echo $chxlS2; ?>|3:<?php echo $chxlS3; ?>" alt="<?php _e( 'Subscribers - Activity', 'MailPress' ); ?>" />
<br /><small><span style='background:#224499;margin:0 20px 0 20px;'>&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;<?php _e('waiting','MailPress'); ?>&nbsp;<span style='background:#FF0000;margin:0 20px;'>&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;<?php _e('comment','MailPress'); ?>&nbsp;<span style='background:#80C65A;margin:0 20px;'>&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;<?php _e('blog &amp; comment','MailPress'); ?></small>
</div>
<?php
	}
}

// Mails - Activity

function mp_mails_activity() {

	global $wpdb, $wp_locale;

	$chdMA = $chlMA = '';

// admin 	(moderate,new_user)
// post	(single,monthly)
// subscri	(new_subscriber,confirmed)
// comments	(comments)

	$out = '';
	$MASS = array (	
				__('Admin','MailPress') 		=> array('moderate','new_user' )		,
				__('Posts','MailPress') 		=> array('single','monthly' )			,
				__('Subscription','MailPress')	=> array('new_subscriber','confirmed')	,
				__('Comments','MailPress')		=> array('comments')				,
				__('Test','MailPress')			=> array('test')
			);

	foreach ($MASS as $k => $MAS)
	{
		$in = implode("','",$MAS);
		$out .= (empty($out)) ? $in : "','" . $in;
		$x  = $wpdb->get_var("SELECT sum(scount) FROM $wpdb->mp_stats WHERE stype = 't' AND slib IN ('$in') ;");
		if ($x) 
		{
			$chdMA .= (empty($chdMA)) ? $x 	: ',' . $x;
			$chlMA .= (empty($chlMA)) ? $k	: '|' . $k;
		}
	}			
	$x  = $wpdb->get_var("SELECT sum(scount) FROM $wpdb->mp_stats WHERE stype = 't' AND slib NOT IN ('$out') ;");
	if ($x) 
	{
		$chdMA .= (empty($chdMA)) ? $x 				: ',' . $x;
		$chlMA .= (empty($chlMA)) ? __('Misc.','MailPress') 	: '|' . __('Misc.','MailPress');
	}

	if ('' != $chdMA) 
	{
?>
<div style='text-align:center;'>
<img src="http://chart.apis.google.com/chart?cht=p3&amp;chs=396x180&amp;chco=0000ff&amp;chd=t:<?php echo $chdMA; ?>&amp;chl=<?php echo $chlMA; ?>" alt="<?php _e( 'Mails - Activity', 'MailPress' ); ?>" />
</div>
<?php
	}
}

// Posts - most subscribed

function mp_posts_most_subscribed() {

	global $wpdb, $wp_locale;

	$wgt_post = '';
	$query = "SELECT count(*) as count, id, post_title, guid, post_modified FROM $wpdb->posts a, $wpdb->postmeta b WHERE meta_key = '_MailPress_subscribe_to_comments_' AND id = post_id AND post_status = 'publish' GROUP BY id, post_title, guid ORDER BY 1;";
	$posts = $wpdb->get_results($query);
	foreach($posts as $post)
	{
		$wgt_post .= "<li>\n";
		$wgt_post .= "($post->count) <a class='rsswidget' title='' href='$post->guid'>$post->post_title</a> \n";
		$wgt_post .= "<span class='rss-date'>" . mysql2date(get_option('date_format'), $post->post_modified) . "</span>\n";
		$wgt_post .= "</li>\n";
	}
	if ('' != $wgt_post) 
	{
?>
<div>
<ul>
<?php echo $wgt_post; ?>
</ul>
</div>
<?php 
	}
}

// Posts - Activity

function mp_comment_subscribers_per_post() {

	global $wpdb, $wp_locale;

	$POSTS = $INIT = $chdS = array();
	$chxlS1 = 0;
	$v = $p = $c = '';

	$dend	= date('Y-m-d');
	$y 	= substr($dend,0,4);
	$m 	= substr($dend,5,2);
	$d 	= substr($dend,8,2);
	$dbeg = date('Y-m-d',mktime(0, 0, 0, $m, $d-50, $y));
	$y 	= substr($dbeg,0,4);
	$m 	= substr($dbeg,5,2);
	$d 	= substr($dbeg,8,2);
	$time = $dbeg;

	$chdS = $chxlS0 = $chxlS2 = $chxlS3 = '';
	$wtime = $time; $wy = $y; $wm = $m; $wd = $d;
	do 
	{
		if 		(empty($chxlS3)) 			$chxlS3 .= '|' . $wy;
		elseif 	('0101' == substr($wtime,5,2) . substr($wtime,8,2)) $chxlS3 .= '|' . substr($wtime,0,4);

		else 							$chxlS3 .= '|';

		$chxlS2 .= ('15' == substr($wtime,8,2)) ? '|' . $wp_locale->get_month_abbrev($wp_locale->get_month(substr($wtime,5,2))) : '|';

		if 		('01' == substr($wtime,8,2)) 	$chxlS0 .= '|01';
		elseif 	('15' == substr($wtime,8,2)) 	$chxlS0 .= '|15';
		else 							$chxlS0 .= '|';

		$wtime = date('Y-m-d',mktime(0, 0, 0, $wm, ++$wd, $wy));
	} while ($wtime <= $dend);

	$query = "SELECT slib, sum(scount) as scount FROM $wpdb->mp_stats WHERE stype = 'c' AND sdate < '$dbeg' GROUP BY slib;";
	$posts = $wpdb->get_results( $query );
	foreach($posts as $post) $INIT[$post->slib] = $post->scount;

	$query = "SELECT sdate, slib, scount FROM $wpdb->mp_stats WHERE stype = 'c' AND sdate BETWEEN '$dbeg' AND '$dend' ORDER BY sdate;";
	$posts = $wpdb->get_results( $query );
	foreach($posts as $post) $POSTS[$post->slib][$post->sdate] = $post->scount;

	foreach ($POSTS as $postid => $dates)
	{
		$init = (isset($INIT[$postid])) ? $INIT[$postid] : -1;
		unset ($INIT[$postid]);
		$wy = $y;
		$wm = $m;
		$wd = $d;
		$wtime = $time;

		foreach ($dates as $date => $value)
		{
			if ($date > $wtime)
			{
				do 
				{
					if ($chxlS1 < $init) $chxlS1 = $init;
					if (!empty($chdS[$postid])) $chdS[$postid] .= ',';
					$chdS[$postid] .= $init ;

					$wtime = date('Y-m-d',mktime(0, 0, 0, $wm, ++$wd, $wy));
				} while ($date > $wtime);
			}
			if (-1 == $init) $init = 0;
			$init = $init + $value;
			if ($chxlS1 < $init) $chxlS1 = $init;
			if (!empty($chdS[$postid])) $chdS[$postid] .= ',';
			$chdS[$postid] .= $init;
		}
		if ($wtime <= $dend)
		{
			do
			{
				if ($chxlS1 < $init) $chxlS1 = $init;
				if (!empty($chdS[$postid])) $chdS[$postid] .= ',';
				$chdS[$postid] .= $init ;

				$wtime = date('Y-m-d',mktime(0, 0, 0, $wm, ++$wd, $wy));
			} while ($wtime <= $dend);
		}
	}

	do {
		foreach ($INIT as $postid => $init) 
		{
			if ($chxlS1 < $init) $chxlS1 = $init;
			if (!empty($chdS[$postid])) $chdS[$postid] .= ',';
			$chdS[$postid] .= $init;
		}

		$time = date('Y-m-d',mktime(0, 0, 0, $m, ++$d, $y));

	} while ($time <= $dend);

	if (!empty($chdS)) 
	{
		ksort($chdS);
		foreach ($chdS as $key => $value)
		{
			$v .= ('' == $v) ? $value : '|' . $value ; 
			$p .= ('' == $p) ? $key   : '|' . $key ; 
			$c .= ('' == $c) ? mp_postid_color($key)   : ',' . mp_postid_color($key) ; 
		}
?>
<div style='text-align:center;'>
<img src="http://chart.apis.google.com/chart?cht=lc&amp;chs=395x230&amp;chdlp=b&amp;chd=t:<?php echo $v; ?>&amp;chdl=<?php echo $p; ?>&amp;chco=<?php echo $c; ?>&amp;chds=0,<?php echo $chxlS1; ?>&amp;chxt=x,y,x,x&amp;chxl=0:<?php echo $chxlS0; ?>|1:||<?php echo $chxlS1; ?>|2:<?php echo $chxlS2; ?>|3:<?php echo $chxlS3; ?>" alt="<?php _e( 'Comments subscribers per post', 'MailPress' ); ?>" />
</div>
<?php
	}
}

function mp_postid_color($p){
	$x = pow(3,$p);
	$y = intval($p/3) * 10;
	$p = (355/113)* pow($p,2);
	$c = '';
	$c = sprintf("%02X", mp_my_bcmod($p, '255')) . sprintf("%02X", mp_my_bcmod($y, '255')) . sprintf("%02X", mp_my_bcmod($x, '255'));
    return $c;
}

function mp_my_bcmod( $x, $y )
{
    // how many numbers to take at once? carefull not to exceed (int)
    $take = 5;    
    $mod = '';

    do
    {
        $a = (int)$mod.substr( $x, 0, $take );
        $x = substr( $x, $take );
        $mod = $a % $y;   
    }
    while ( strlen($x) );

    return (int)$mod;
} 



?>