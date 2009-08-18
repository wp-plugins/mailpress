<?php
/*
Template Name: comments
*/
?>
<?php $this->get_header() ?>

<table <?php $this->classes('nopmb ctable'); ?>>
	<tr>
		<td <?php $this->classes('nopmb ctd'); ?>>
			<div <?php $this->classes('cdiv'); ?>>
				<h2 <?php $this->classes('ch2'); ?>>
Comment # {{c[id]}} in "{{the_title}}"
				</h2>
				<small <?php $this->classes('nopmb cdate'); ?>>
<?php the_time('F j, Y') ?>
				</small>
				<div <?php $this->classes('nopmb'); ?>>
					<p <?php $this->classes('nopmb cp'); ?>>
<?php $this->the_content(); ?>
					</p>
					<p <?php $this->classes('nopmb cp'); ?>>
<a <?php $this->classes('clink2'); ?> href='<?php $post = get_post($this->args->p->id); echo $post->guid; ?>#comment-<?php echo $this->args->c->id; ?>'>Reply to this comment</a>
					</p>
				</div>
			</div>
		</td>
	</tr>
</table>

<?php $this->get_footer() ?>