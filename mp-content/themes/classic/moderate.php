<?php
/*
Template Name: moderate
*/
?>
<?php $this->get_header() ?>
								<h2 style="-x-system-font:none;border-bottom:1px dotted #CCCCCC;font-family:'Times New Roman',Times,serif;font-size:95%;font-size-adjust:none;font-stretch:normal;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:0.2em;line-height:normal;margin:15px 0 2px;padding-bottom:2px;">
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
								</h2>
								<h3 style="color:#342;border-bottom:1px dotted #EEEEEE;font-family:'Times New Roman',Times,serif;margin-top:0;">
<?php printf( __('New comment on your post #%1$s "%2$s"'), $this->args->c->post_ID , $this->args->p->title  ); ?>
								</h3>
								<div style="-x-system-font:none;font-family:'Lucida Grande','Lucida Sans Unicode',Verdana,sans-serif;font-size:90%;font-size-adjust:none;font-stretch:normal;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:-1px;line-height:175%;">
									<p>
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
echo ("<a href='" . get_permalink($this->args->c->post_ID ) . "#comments'>" . __('You can see all comments on this post here: ')          . "</a><br/><br/>\n\n");
echo ("<a href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&c=" . $this->args->c->id         . "'>" . __('Delete') . "</a><br/><br/>\n\n");
echo ("<a href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&dt=spam&c=" . $this->args->c->id . "'>" . __('Spam')   . "</a><br/>\n");
?>
									<br/><br/>
									</p>
								</div>
<?php $this->get_footer() ?>