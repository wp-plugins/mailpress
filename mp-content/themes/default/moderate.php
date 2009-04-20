<?php
/*
Template Name: moderate
*/
?>
<?php $this->get_header() ?>

					<table width=100% border=0 cellspacing=0 cellpadding=0>
						<tr>
							<td style='float:left;margin:0;padding:0pt 0pt 20px 45px;width:450px;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
								<div style='margin:0pt 0pt 40px;text-align:justify;'>
									<h2 style="margin:30px 0pt 0pt;text-decoration:none;color:#333;font-size:2em;font-weight:bold;font-family:'Trebuchet MS','Lucida Grande',Verdana,Arial,Sans-Serif;">
<?php printf( __('New comment on your post #%1$s "%2$s"'), $this->args->c->post_ID , $this->args->p->title  ); ?>
									</h2>
									<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.9em;line-height:1.5em;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
									</small>
									<div style='font-size:1.2em;'>
										<p style='line-height:1.4em;'>
<?php
printf( __('Author : %1$s (IP: %2$s , %3$s)'), $this->args->c->author, $this->args->c->author_IP, $this->args->c->domain);
echo "<br/>";
printf( __('E-mail : %s'), $this->args->c->email );
echo "<br/>";
printf( __('URL    : %s'), $this->args->c->url);
echo "<br/>";
printf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $this->args->c->author_IP );
echo "<br/>";
echo "<br/>";
_e('Comment: ');
echo "<br>" . $this->args->c->content . "<br><br>";
echo ("<a href='" . get_permalink($this->args->c->post_ID ) . "#comments'>" . __('You can see all comments on this post here: ')          . "</a><br><br>");
echo ("<a href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&c=" . $this->args->c->id         . "'>" . __('Delete') . "</a><br><br>");
echo ("<a href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&dt=spam&c=" . $this->args->c->id . "'>" . __('Spam')   . "</a><br>");
?>
										</p>
									</div>
								</div>
							</td>
							<td>
								<?php $this->get_sidebar() ?>
							</td>
						</tr>
					</table>

<?php $this->get_footer(); ?>