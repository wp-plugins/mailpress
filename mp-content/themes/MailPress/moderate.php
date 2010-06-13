<?php
/*
Template Name: moderate
*/
?>
<?php $moderator = (isset($mail->p->title)) ? true : false; ?>
<?php $this->get_header() ?>

<table <?php $this->classes('nopmb ctable'); ?>>
	<tr>
		<td <?php $this->classes('nopmb ctd'); ?>>
			<div <?php $this->classes('cdiv'); ?>>
				<h2 <?php $this->classes('ch2'); ?>>
<?php printf( __('New comment on your post #%1$s "%2$s"'), $this->args->c->post_ID , '{{the_title}}' ); ?>
				</h2>
				<small <?php $this->classes('nopmb cdate'); ?>>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
				</small>
				<div <?php $this->classes('nopmb'); ?>>
					<p <?php $this->classes('nopmb cp'); ?>>
<?php
printf( __('Author : %1$s (IP: %2$s , %3$s)'), $this->args->c->author, $this->args->c->author_IP, $this->args->c->domain);
echo "<br />\n";
printf( __('E-mail : %s'), $this->args->c->email );
echo "<br />\n";
printf( __('URL    : %s'), $this->args->c->url);
echo "<br />\n";
printf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $this->args->c->author_IP );
echo "<br />\n";
echo "<br />\n";
_e('Comment: ');
echo "<br />\n" . $this->args->c->content . "<br /><br />\n\n";
?>
<?php if ($moderator) : ?>
<a <?php $this->classes('clink2'); ?> href='<?php echo get_permalink($this->args->c->post_ID ); ?>#comments'><?php _e('View all'); ?></a>
<?php else : ?>
<a <?php $this->classes('clink2'); ?> href='<?php echo get_option('siteurl'); ?>/wp-admin/comment.php?action=mac&c=<?php echo $this->args->c->id; ?>'><?php _e('Approve'); ?></a>
<?php endif; ?>
<a <?php $this->classes('clink2'); ?> href='<?php echo get_option('siteurl'); ?>/wp-admin/comment.php?action=cdc&c=<?php echo $this->args->c->id; ?>'><?php _e('Delete'); ?></a>
<a <?php $this->classes('clink2'); ?> href='<?php echo get_option('siteurl'); ?>/wp-admin/comment.php?action=cdc&dt=spam&c=<?php echo $this->args->c->id ?>'><?php _e('Spam'); ?></a>
					</p>
				</div>
			</div>
		</td>
	</tr>
</table>

<?php $this->get_footer() ?>