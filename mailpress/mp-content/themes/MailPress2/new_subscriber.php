<?php
/*
Template Name: new_subscriber
Subject: [<?php bloginfo('name');?>] <?php printf( __('Waiting for : %s','MailPress'), $this->args->toemail ); ?>
*/
?>
<?php $this->get_header() ?>
			<table <?php mp_classes('nopmb ctable'); ?>>
				<tr>
					<td <?php mp_classes('nopmb ctd'); ?>>
						<div <?php mp_classes('cdiv'); ?>>
							<h2 <?php mp_classes('ch2'); ?>>
Email validation.
							</h2>
							<small <?php mp_classes('nopmb cdate'); ?>>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
							</small>
							<div <?php mp_classes('nopmb'); ?>>
								<p <?php mp_classes('nopmb cp'); ?>>
Please confirm your email by clicking the following <a <?php mp_classes('clink2'); ?> href='{{subscribe}}'>link</a>. 
								</p>
							</div>
						</div>
					</td>
				</tr>
			</table>
<?php unset($this->args->unsubscribe); ?>
<?php $this->get_footer() ?>