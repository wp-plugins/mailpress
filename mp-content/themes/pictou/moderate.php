<?php
/*
Template Name: moderate
*/
?>
<?php $this->get_header() ?>
				<div id="content" style="margin:0;float:left;padding:10px;width:470px;">
					<div id="post-<?php the_ID(); ?>" style="margin:0;padding:0;margin-bottom:3em;">
						<p style="background:#EFEFEF;border:1px solid #CCC;color:#567;float:right;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-size:0.8em;font-weight:bold;margin:5px 0 0 5px;text-align:center;line-height:1.8em;">
							<span style='display:block;margin:0;padding:0;color:#567;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-size:0.8em;font-weight:bold;text-align:center;line-height:1.8em;padding:0 10px;'>
<?php echo date('M'); ?>
							</span>
							<span style='color:#567;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-weight:bold;text-align:center;display:block;margin:0;padding:0;font-size:1.6em;line-height:1.8em;padding:0 10px;'>
<?php echo date('d'); ?>
							</span>
							<span style='display:block;margin:0;padding:0;color:#567;font-family:"Lucida Sans","Trebuchet MS",Verdana,Arial,Serif;font-size:0.8em;font-weight:bold;text-align:center;line-height:1.8em;padding:0 10px;'>
<?php echo date('Y'); ?>
							</span>
						</p>
						<h2 style="color:#333;font-family:Georgia,Tahoma,Verdana,Arial,Serif;font-weight:bold;margin:0;border-bottom:1px solid #DDD;font-size:1.6em;line-height:1.2em;padding:4px;">
							<span style="border:0pt none;color:#585D8B;">
<?php printf( __('New comment on your post #%1$s "%2$s"'), $this->args->c->post_ID , $this->args->p->title  ); ?>
							</span>
						</h2>
						<div style="margin:0;padding:0;color:#999;font-size:0.9em;margin-bottom:10px;padding-left:5px;">
							<p style="margin:0;padding:0;margin-bottom:0.5em;line-height:1.8em;">
&nbsp;
							</p>
					      </div>
					      <div style="margin:0;clear:both;padding:10px 5px;">
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
echo ("<a style='color:#585D8B;font-family:Georgia,Tahoma,Verdana,Arial,Serif;font-weight:bold;' href='" . get_permalink($this->args->c->post_ID ) . "'>" . __('You can see all comments on this post here: ')          . "</a><br><br>");
echo ("<a style='color:#585D8B;font-family:Georgia,Tahoma,Verdana,Arial,Serif;font-weight:bold;' href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&c=" . $this->args->c->id         . "'>" . __('Delete') . "</a><br><br>");
echo ("<a style='color:#585D8B;font-family:Georgia,Tahoma,Verdana,Arial,Serif;font-weight:bold;' href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&dt=spam&c=" . $this->args->c->id . "'>" . __('Spam')   . "</a><br>");
?>
				        	</div>
					</div>
				</div><!-- end of div content -->
				<!-- sidebars-->
<?php unset($this->args->unsubscribe); ?>
<?php $this->get_footer();?>