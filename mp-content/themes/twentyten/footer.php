
<!-- start footer -->
						</div>
					</div>
				</div>
				<div <?php $this->classes('footer'); ?>>
					<div <?php $this->classes('colophon'); ?>>
						<div <?php $this->classes('site-generator'); ?>>
Proudly mailed by 
							<img src='images/mailpress.png' style='border:none;margin:0;padding:0;' />
							<span id="generator-link">
								<a style='color:#888;text-decoration:none;' href="http://mailpress.org/" title="<?php _e( 'The WordPress Mailing plugin', 'twentyten' ) ?>" rel="generator">
									<?php _e( 'MailPress', 'twentyten' ) ?>
								</a>.
							</span>
						</div>
						<div <?php $this->classes('site-info'); ?>>
<a <?php $this->classes('site-info_a'); ?> href="<?php bloginfo( 'url' ) ?>/" title="<?php bloginfo( 'name' ) ?>" rel="home"><?php bloginfo( 'name' ) ?></a>
						</div>
					</div>
				</div>
<?php if (isset($this->args->unsubscribe)) { ?>
				<small style='color:#6D8C82;'>
					<br />
					Wish to unsubscribe <a href='{{unsubscribe}}' style='color: rgb(153, 153, 153);font-family: verdana,geneva;font-weight:bold;'>?</a>
				</small>
<?php } ?>
			</div>
		</div>
	</body>
</html>