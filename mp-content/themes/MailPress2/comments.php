<?php
/*
Template Name: comments
Subject: [<?php bloginfo('name');?>] New comment in "<?php $post = get_post($this->args->p->id); echo $post->post_title; ?>"
*/
?>
<?php $this->get_header() ?>
			<table <?php mp_classes('nopmb ctable'); ?>>
				<tr>
					<td <?php mp_classes('nopmb ctd'); ?>>
						<div <?php mp_classes('cdiv'); ?>>
							<h2 <?php mp_classes('ch2'); ?>>
Comment # <?php echo $this->args->c->id; ?> in Post  "<?php $post = get_post($this->args->p->id); echo $post->post_title; ?>"
							</h2>
							<small <?php mp_classes('nopmb cdate'); ?>>
<?php the_time('F j, Y') ?>
							</small>
							<div <?php mp_classes('nopmb'); ?>>
								<p <?php mp_classes('nopmb cp'); ?>>
<?php $this->the_content(); ?>
								</p>
								<p <?php mp_classes('nopmb cp'); ?>>
<a <?php mp_classes('clink2'); ?> href='<?php $post = get_post($this->args->p->id); echo $post->guid; ?>#comment-<?php echo $this->args->c->id; ?>'>Reply to this comment</a>
								</p>
							</div>
						</div>
					</td>
				</tr>
			</table>
<?php $this->get_footer() ?>