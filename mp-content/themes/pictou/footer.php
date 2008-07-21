<!-- start footer -->
				<div style="clear: both;"/>
				</div>
			</div><!-- end of div main -->
			<div id="footer" style="background:#333 none repeat scroll 0% 0%;border:3px solid #CCC;margin:5px auto 0;padding:0 15px;width:870px;overflow:hidden;">
				<p style="line-height:1.8em;color:#696;font-size:0.9em;margin:0;padding:7px;">
					<span style="margin:0;padding:0;float:right;">
						<a style="margin:0;padding:0;border:0 none;color:#696;" href="http://wpthemeshop.com/free-themes/" title="Free WordPress Themes by WP Theme Shop">
							WordPress Theme Shop
						</a>
						 | 
						<a style="margin:0;padding:0;border:0 none;color:#696;" href="http://webhostinggeeks.com" title="Web Hosting Geeks" target="_blank">
							Web Hosting Geeks
						</a>
					</span>
					<strong style="border:0 none;color:#EEE;">
						<?php bloginfo('name');?>
					</strong>
					 Copyright &copy; 
					<?php echo date('Y');?>
					 All Rights Reserved .
				</p>
			</div>
		</div>
<?php if (isset($this->args->unsubscribe)) { ?>
		<small style='color:#696;font-family:Verdana,Tahoma,Arial,Serif;line-height:1.8em;font-size:0.9em;'>
			<br/>
			<br/>
			Wish to unsubscribe <a href='{{unsubscribe}}' style='color:#696;font-family:Verdana,Tahoma,Arial,Serif;font-weight:bold;'>?</a>
		</small>
<?php } ?>
	</body>
</html>