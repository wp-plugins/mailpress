<?php
/*
Template Name: form_destinataire
Subject: [<?php bloginfo('name');?>] Mail issu de formulaire
*/
?>

<?php $this->get_header() ?>

<div>
	<table style='width:100%;border:none;'>
		<tr>
			<td style='float:left;margin:0 45px;padding:0;width:auto;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
				<div style='margin:0pt 0pt 40px;text-align:justify;'>
					<h2 style='margin:30px 0pt 0pt;text-decoration:none;color:#333333;font-size:1.1em;font-family:Verdana,Sans-Serif;font-weight:bold;'>
Mail issu de formulaire
					</h2>
					<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.7em;line-height:1.5em;'>
<?php the_time('F j, Y') ?>
					</small>
					<div style='font-size:.85em;'>
						<p style='line-height:1.2em;'>
<?php $this->the_content(); ?>
						</p>
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>

<?php $this->get_footer() ?>