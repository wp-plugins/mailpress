<?php
/*
Template Name: default
Subject: [<?php bloginfo('name');?>] Lorem ipsum ... 
*/
?>
<div id='body' style='margin:0 0 20px;padding:0;color:#333;font-family:Verdana,Sans-Serif;font-size:62.5%;text-align:center;'>
	<table style='width:766px;border:none;table-layout:automatic; margin:0; padding:0;'>
		<tbody style='margin:0; padding:0; border:0; width:766px;'>
			<tr style='margin:0; padding:0; border:0;'>
				<td  colspan='3' style='margin:0; padding:0; border:0;height:200px;'>
					<div style='border:0;'>
						<table style='width:100%;border:0;table-layout:fixed;'>
							<tbody style='margin:0;padding:0;border:0;width=100%;'>
								<tr style='margin:0;padding:0;border:0;width=100%;'>
									<td style='text-align:center;margin:0;padding:0;border:0;width:1px;'>
										<img src='images/kubrickheader.jpg' style='margin:0;padding:0;border:0;' alt='' align=''/>
									</td>
									<td style='text-align:center;margin:0;padding:0;border:0;width:100%;heigth:100%;'>
										<h1 style="margin:0;padding:0px;font-size:2.5em;font-family:'Trebuchet MS','Lucida Grande',Verdana,Arial,Sans-Serif; font-weight:bold;">
											<a style='text-decoration:none; color:white;' href="<?php echo get_option('home'); ?>">
<?php bloginfo('name'); ?>
											</a>
										</h1>
										<span style="color:white; font-size:0.75em; font-family:Verdana,Sans-Serif;'">
<?php bloginfo('description'); ?>
										</span>
									</td>
								</tr>
							</tbody>
						</table>

					</div>
				</td>
			</tr>
			<tr style='margin:0; padding:0; border:0;'>
				<td style='width:11px; margin:0; padding:0; border:0; background-color:#E7E7E7;'>
					<img src='images/left.jpg' style='margin:0;padding:0;border:0;height:100%;width:10px;' alt='' align=''/>
				</td>
				<td style='margin:0; padding:0; border:0; background-color:#F9F9F9;width:740px'>
<!-- end header -->

					<table width=100% border=0 cellspacing=0 cellpadding=0>
						<tr>
							<td style='float:left;margin:0;padding:0pt 0pt 20px 45px;width:450px;text-align:left;color:#333333;font-family:Verdana,Sans-Serif;'>
								<div style='margin:0pt 0pt 40px;text-align:justify;'>
									<h2 style='{margin:30px 0pt 0pt;text-decoration:none;color:#333;font-size:1.6em;font-family:Verdana,Sans-Serif;font-weight:bold;}'>
										Lorem ipsum ...
									</h2>
									<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.7em;line-height:2em;'>
										<?php echo mysql2date('F j, Y', current_time('mysql')); ?>
									</small>
									<div style='font-size:1.2em;'>
										<p style='line-height:1.4em;'>
											Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed nisi pede, tincidunt non, vehicula ac, auctor pulvinar, augue. Donec diam neque, hendrerit rhoncus, pellentesque non, ultrices quis, lacus. Nam quis leo nec magna rutrum vehicula. Aenean volutpat. Duis pharetra purus mattis arcu. Donec interdum orci eget sem vestibulum consectetuer. Maecenas bibendum erat id libero. Morbi congue. Donec sodales interdum nulla. Curabitur eu velit et orci euismod convallis. In lobortis posuere nisi. Pellentesque nec libero eu ligula accumsan adipiscing. Aenean vel mauris. Aliquam rutrum turpis nec augue. Duis massa magna, faucibus sed, lobortis quis, suscipit quis, eros. Curabitur urna. Suspendisse iaculis nibh sed sem. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.
										</p>
									</div>
								</div>
							</td>
							<td>
								<?php $this->get_sidebar() ?>
							</td>
						</tr>
					</table>

<!-- start footer -->
				</td>
				<td style='width:11px; margin:0; padding:0; border:0; background-color:#E7E7E7;'>
					<img src='images/right.jpg' style='margin:0;padding:0;border:0;height:100%;width:10px;' alt='' align=''/>
				</td>
			</tr>
			<tr style='margin:0; padding:0; border:0;width:100%'>
				<td  colspan='3' style='margin:0; padding:0; border:0;height:100%;'>
					<div style='border:0;width:100%;' >
						<table style='width:100%;border:none;table-layout: fixed;'>
							<tr>
								<td style='text-align:center; margin:0; padding:0; border:0;width:1px;'>
									<img src='images/kubrickfooter.jpg' alt='' align=''/>
								</td>
								<td style='text-align:center; margin:0; padding:0; border:0;'>
									<p style="margin:0; padding:10px 0; text-align:center; color:#333; font-family:'Lucida Grande,Verdana,Arial,Sans-Serif'; font-size:.7em;">
										<span>									
											This mail is brought to you by 
											<a style='{color:#06C; text-decoration:none;}' href="http://www.nogent94/page_id=70" onmouseover="this.style.color='#f00';" onmouseout="this.style.color='#06C';">
												Mailpress
											</a>
										</span>
									</p>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>