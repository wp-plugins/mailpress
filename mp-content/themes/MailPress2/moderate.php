<?php
/*
Template Name: moderate
*/
?>
<?php $this->get_header() ?>
			<table <?php mp_classes('nopmb ctable'); ?>>
				<tr>
					<td <?php mp_classes('nopmb ctd'); ?>>
						<div <?php mp_classes('cdiv'); ?>>
							<h2 <?php mp_classes('ch2'); ?>>
<?php printf( __('New comment on your post #%1$s "%2$s"'), $this->args->c->post_ID , $this->args->p->title  ); ?>
							</h2>
							<small <?php mp_classes('nopmb cdate'); ?>>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
							</small>
							<div <?php mp_classes('nopmb'); ?>>
								<p <?php mp_classes('nopmb cp'); ?>>
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
?>

<a <?php mp_classes('clink2'); ?> href='<?php echo get_permalink($this->args->c->post_ID ); ?>#comments'><?php _e('You can see all comments on this post here: '); ?></a><br><br>
<a <?php mp_classes('clink2'); ?> href='<?php echo get_option('siteurl'); ?>/wp-admin/comment.php?action=cdc&c=<?php echo $this->args->c->id; ?>'><?php _e('Delete'); ?></a><br><br>
<a <?php mp_classes('clink2'); ?> href='<?php echo get_option('siteurl'); ?>/wp-admin/comment.php?action=cdc&dt=spam&c=<?php echo $this->args->c->id ?>'><?php _e('Spam'); ?></a><br><br>
								</p>
							</div>
						</div>
					</td>
				</tr>
			</table>
<?php $this->get_footer() ?>