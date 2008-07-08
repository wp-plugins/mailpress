<?php
/*
Template Name: moderate
*/
?>
<?php $this->get_header(); ?>

					<table style='padding:0;margin:0;border:none;width:100%;'>
						<tr style='padding:0;margin:0;border:none;'>
							<td style='padding:0pt 0pt 20px 45px;margin:0;border:none;width:450px;float:left;color:#333;text-align:left;font-family:Verdana,Sans-Serif;'>
								<div style='padding:0;margin:0pt 0pt 40px;border:none;text-align:justify;'>
									<h2 style='padding:0;margin:30px 0pt 0pt;border:none;color:#333;font-size:1.6em;font-weight:bold;font-family:Verdana,Sans-Serif;'>
<?php printf( __('New comment on your post #%1$s "%2$s"'), $this->args->c->post_ID , $this->args->p->title  ); ?>
									</h2>
									<small style='padding:0;margin:0;border:none;color:#777;line-height:2em;font-size:0.7em;font-family:Arial,Sans-Serif;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
									</small>
									<div style='padding:0;margin:0;border:none;font-size:1.2em;'>
										<p style='padding:0;margin:0;border:none;line-height:1.4em;'>
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
echo "<br>\n" . $this->args->c->content . "<br/><br/>\n\n";
echo ("<a href='" . get_permalink($this->args->c->post_ID ) . "'>" . __('You can see all comments on this post here: ')          . "</a><br/><br/>\n\n");
echo ("<a href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&c=" . $this->args->c->id         . "'>" . __('Delete') . "</a><br/><br/>\n\n");
echo ("<a href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&dt=spam&c=" . $this->args->c->id . "'>" . __('Spam')   . "</a><br/>\n");
?>
										</p>
									</div>
								</div>
							</td>
						</tr>
					</table>

<?php $this->get_footer(); ?>