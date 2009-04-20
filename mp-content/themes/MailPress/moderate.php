<?php
/*
Template Name: moderate
*/
?>
<?php $this->get_header() ?>
			<table style='margin:0;padding:0;border:none;width:100%;'>
				<tr>
					<td style='margin:0;padding:0pt 0pt 20px 45px;border:none;width:450px;float:left;color:#333333;text-align:left;font-family:Verdana,Sans-Serif;'>
						<div style='margin:0pt 0pt 40px;padding:0;border:none;text-align:justify;'>
							<h2 style='margin:30px 0pt 0pt;padding:0;border:none;color:#333;font-size:1.4em;font-weight:bold;font-family:Verdana,Sans-Serif;'>
<?php printf( __('New comment on your post #%1$s "%2$s"'), $this->args->c->post_ID , $this->args->p->title  ); ?>
							</h2>
							<small style='margin:0;padding:0;border:none;line-height:2em;color:#777;font-size:0.7em;font-family:Arial,Sans-Serif;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
							</small>
							<div style='margin:0;padding:0;border:none;'>
								<p style='margin:0;padding:0;border:none;line-height:1.4em;font-size:0.85em;'>
<?php
printf( __('Author : %1$s (IP: %2$s , %3$s)'), $this->args->c->author, $this->args->c->author_IP, $this->args->c->domain);
echo "<br/>\n";
printf( __('E-mail : %s'), $this->args->c->email );
echo "<br/>\n";
printf( __('URL    : %s'), $this->args->c->url);
echo "<br/>\n";
printf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $this->args->c->author_IP );
echo "<br/>\n";
echo "<br/>\n";
_e('Comment: ');
echo "<br>\n" . $this->args->c->content . "<br><br>\n\n";
echo ("<a style='color:#6D8C82;font-family: verdana,geneva;font-weight:bold;' href='" . get_permalink($this->args->c->post_ID ) . "#comments'>" . __('You can see all comments on this post here: ')          . "</a><br><br>");
echo ("<a style='color:#6D8C82;font-family: verdana,geneva;font-weight:bold;' href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&c=" . $this->args->c->id         . "'>" . __('Delete') . "</a><br><br>");
echo ("<a style='color:#6D8C82;font-family: verdana,geneva;font-weight:bold;' href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&dt=spam&c=" . $this->args->c->id . "'>" . __('Spam')   . "</a><br>");
?>
								</p>
							</div>
						</div>
					</td>
				</tr>
			</table>
<?php $this->get_footer() ?>