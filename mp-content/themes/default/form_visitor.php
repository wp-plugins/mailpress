<?php
/*
Template Name: form_visitor
Subject: [<?php bloginfo('name');?>] Copy of your submission
*/
?>

<?php $this->get_header() ?>

<table width=100% border=0 cellspacing=0 cellpadding=0>

	<tr>
		<td style='float:left;margin:0;padding:0pt 0pt 20px 45px;width:450px;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
			<div style='margin:0pt 0pt 40px;text-align:justify;'>
				<h2 style="margin:30px 0pt 0pt;text-decoration:none;color:#333;font-size:2em;font-weight:bold;font-family:'Trebuchet MS','Lucida Grande',Verdana,Arial,Sans-Serif;">
Copy of your submission
				</h2>
				<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.9em;line-height:1.5em;'>
<?php the_time('F jS, Y') ?>
				</small>
				<div style='font-size:1.2em;'>
					<p style='line-height:1.4em;'>

<?php $this->the_content(); ?>

					</p>
				</div>
			</div>
		</td>
	</tr>
</table>

<?php $this->get_footer() ?>