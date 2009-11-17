<?php
/*
Template Name: new_user
*/
?>
<?php $this->get_header() ?>

<table <?php $this->classes('nopmb ctable'); ?>>
	<tr>
		<td <?php $this->classes('nopmb ctd'); ?>>
			<div <?php $this->classes('cdiv'); ?>>
				<h2 <?php $this->classes('ch2'); ?>>
Congratulations !
				</h2>
				<small <?php $this->classes('nopmb cdate'); ?>>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
				</small>
				<div <?php $this->classes('nopmb'); ?>>
					<p <?php $this->classes('nopmb cp'); ?>>
<?php printf(__('Username: %s'), $this->args->u->login ); ?>
									<br/>
<?php 
if (isset($this->args->admin))	printf(__('E-mail: %s'),   $this->args->u->email );
else 						printf(__('Password: %s'), $this->args->u->pwd );
?>
						<br/><br/>
						<a <?php $this->classes('clink2'); ?> href='<?php echo get_option('siteurl'); ?>/wp-login.php'>
							<?php _e('Log in'); ?>
						</a>.
						<br/><br/>
					</p>
				</div>
			</div>
		</td>
	</tr>
</table>

<?php $this->get_footer() ?>