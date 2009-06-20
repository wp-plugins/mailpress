<?php
/*
Template Name: moderate
*/
?>
<?php $this->get_header() ?>
			<div>
				<table style='width:100%;border:none;'>
					<tr>
						<td style='float:left;margin:0 45px;padding:0;width:auto;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
							<div style='margin:0pt 0pt 40px;text-align:justify;'>
								<h2 style='margin:30px 0pt 0pt;text-decoration:none;color:#333333;font-size:1.1em;font-family:Verdana,Sans-Serif;font-weight:bold;'>
<?php printf( __('New comment on your post #%1$s "%2$s"'), $this->args->c->post_ID , $this->args->p->title  ); ?>
								</h2>
<!--
								<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.7em;line-height:1.5em;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
								</small>
-->
								<div style='font-size:.85em;'>
									<p style='line-height:1.2em;'>
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
echo "<br/>\n" . $this->args->c->content . "<br/>\n<br/>\n";
echo ("<a href='" . get_permalink($this->args->c->post_ID ) . "#comments'>" . __('You can see all comments on this post here: ')          . "</a><br/>\n<br/>\n");
echo ("<a href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&c=" . $this->args->c->id         . "'>" . __('Delete') . "</a><br/>\n<br/>\n");
echo ("<a href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&dt=spam&c=" . $this->args->c->id . "'>" . __('Spam')   . "</a><br/>\n");
?>
									</p>
								</div>
							</div>
						</td>
					</tr>
				</table>
			</div>
<?php $this->get_footer() ?>