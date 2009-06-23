<?php
/*
Template Name: new_subscriber
Subject: [<?php bloginfo('name');?>] <?php printf( __('Waiting for : %s','MailPress'), '{{toemail}}'); ?>
*/
?>
<?php $this->get_header() ?>

<table <?php $this->classes('nopmb ctable'); ?>>
	<tr>
		<td <?php $this->classes('nopmb ctd'); ?>>
			<div <?php $this->classes('cdiv'); ?>>
				<h2 <?php $this->classes('ch2'); ?>>
Email validation.
				</h2>
				<small <?php $this->classes('nopmb cdate'); ?>>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
				</small>
				<div <?php $this->classes('nopmb'); ?>>
					<p <?php $this->classes('nopmb cp'); ?>>
Please confirm your email by clicking the following <a <?php $this->classes('clink2'); ?> href='{{subscribe}}'>link</a>. 
					</p>
				</div>
			</div>
		</td>
	</tr>
</table>

<?php unset($this->args->unsubscribe); ?>
<?php $this->get_footer() ?>