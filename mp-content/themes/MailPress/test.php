<?php
/*
Template Name: test
Subject: [<?php bloginfo('name');?>] Lorem ipsum ... é à ù ç €
*/
?>
<?php $this->get_header() ?>
			<table style='margin:0;padding:0;border:none;width:100%;'>
				<tr>
					<td style='margin:0;padding:0pt 0pt 20px 45px;border:none;width:450px;float:left;color:#333333;text-align:left;font-family:Verdana,Sans-Serif;'>
						<div style='margin:0pt 0pt 40px;padding:0;border:none;text-align:justify;'>
							<h2 style='margin:30px 0pt 0pt;padding:0;border:none;color:#333;font-size:1.4em;font-weight:bold;font-family:Verdana,Sans-Serif;'>
Lorem ipsum ...
							</h2>
							<small style='margin:0;padding:0;border:none;line-height:2em;color:#777;font-size:0.7em;font-family:Arial,Sans-Serif;'>
<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
							</small>
							<div style='margin:0;padding:0;border:none;'>
								<p style='margin:0;padding:0;border:none;line-height:1.4em;font-size:0.85em;'>
é à ù ç €
 Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed nisi pede, tincidunt non, vehicula ac, auctor pulvinar, augue. Donec diam neque, hendrerit rhoncus, pellentesque non, ultrices quis, lacus. Nam quis leo nec magna rutrum vehicula. Aenean volutpat. Duis pharetra purus mattis arcu. Donec interdum orci eget sem vestibulum consectetuer. Maecenas bibendum erat id libero. Morbi congue. Donec sodales interdum nulla. Curabitur eu velit et orci euismod convallis. In lobortis posuere nisi. Pellentesque nec libero eu ligula accumsan adipiscing. Aenean vel mauris. Aliquam rutrum turpis nec augue. Duis massa magna, faucibus sed, lobortis quis, suscipit quis, eros. Curabitur urna. Suspendisse iaculis nibh sed sem. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.
								</p>
							</div>
						</div>
					</td>
				</tr>
			</table>
<?php $this->get_footer() ?>
