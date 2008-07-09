<?php
/*
Template Name: new_subscriber
Subject: [<?php bloginfo('name');?>] <?php printf( __('En attente de : %s'), $this->args->toemail ); ?>
*/
?>
<?php $this->get_header() ?>
			<div>
				<table style='width:100%;border:none;'>
					<tr>
						<td style='float:left;margin:0 45px;padding:0;width:auto;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
							<div style='margin:0pt 0pt 40px;text-align:justify;'>
								<h2 style='margin:30px 0pt 0pt;text-decoration:none;color:#333333;font-size:1.1em;font-family:Verdana,Sans-Serif;font-weight:bold;'>
									Confirmation de l'abonnement
								</h2>
<!--
								<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.7em;line-height:1.5em;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
								</small>
-->
								<div style='font-size:.85em;'>
									<p style='line-height:1.2em;'>
										Veuillez confirmer votre abonnement en cliquant sur ce 
										<a href='{{subscribe}}'>
											lien
										</a>. 
									</p>
								</div>
							</div>
						</td>
					</tr>
				</table>
			</div>
<?php unset($this->args->unsubscribe); ?>
<?php $this->get_footer() ?>