<div>
	<table style='width:100%;border:none;'>
		<tr>
			<td style='float:left;margin:0 45px;padding:0;width:auto;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
				<div style='margin:0pt 0pt 40px;text-align:justify;'>
					<h2 style='margin:30px 0pt 0pt;text-decoration:none;color:#333333;font-size:1.1em;font-family:Verdana,Sans-Serif;font-weight:bold;'>
<?php if (isset($_the_title)) echo $_the_title; else $this->the_title(); ?>
					</h2>
					<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.7em;line-height:1.5em;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
					</small>
					<div style='font-size:.85em;'>
						<p style='line-height:1.2em;'>
<?php if (isset($_the_content)) echo $_the_content; else $this->the_content(); ?>
						</p>
						<p style='line-height:1.2em;'>
<?php echo (isset($_the_actions)) ? $_the_actions : '&nbsp;'; ?>
						</p>
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>