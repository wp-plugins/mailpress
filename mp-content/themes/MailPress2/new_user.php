<?php
/*
Template Name: new_user
*/
?>
<?php $this->get_header() ?>
			<table <?php mp_classes('nopmb ctable'); ?>>
				<tr>
					<td <?php mp_classes('nopmb ctd'); ?>>
						<div <?php mp_classes('cdiv'); ?>>
							<h2 <?php mp_classes('ch2'); ?>>
							</h2>
							<small <?php mp_classes('nopmb cdate'); ?>>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
							</small>
							<div <?php mp_classes('nopmb'); ?>>
								<p <?php mp_classes('nopmb cp'); ?>>
<?php printf(__('Username: %s'), $this->args->u->login ); ?>
									<br/>
<?php 
if (isset($this->args->admin))	printf(__('E-mail: %s'),   $this->args->u->email );
else 						printf(__('Password: %s'), $this->args->u->pwd );
?>
									<br/><br/>
									<a <?php mp_classes('clink2'); ?> href='<?php echo get_option('siteurl'); ?>/wp-login.php'>
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