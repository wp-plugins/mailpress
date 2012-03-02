<!-- start footer -->
						</div>
<?php //$this->get_sidebar(); ?>
					</div>
				</div><!-- #main -->

				<footer <?php $this->classes('* cb colophon'); ?> role="contentinfo">
					<div <?php $this->classes('* cb'); ?>>
						<a <?php $this->classes('* cb site-info_a'); ?> href="<?php echo esc_url( 'http://blog.mailpress.org/' ); ?>" title="<?php esc_attr_e( 'The WordPress Mailing Plugin' ); ?>"><?php printf( 'Proudly mailed by %s', 'MailPress' ); ?></a>
					</div>
				</footer><!-- #colophon -->
			</div><!-- #page -->
<?php if (isset($this->args->unsubscribe)) { ?>
			<div <?php $this->classes('mail_link'); ?>>
				<a href='{{unsubscribe}}'  <?php $this->classes('mail_link_a a'); ?>>Manage your subscriptions</a>
			</div>
<?php } ?>
		</div><!-- #body -->
	</body>
</html><?php 
remove_filter( 'comments_popup_link_attributes',array('MP_theme_2012_html', 'comments_popup_link_attributes') );
remove_filter( 'the_category', 				array('MP_theme_2012_html', 'the_category') );
remove_filter( 'term_links-post_tag', 		array('MP_theme_2012_html', 'term_links_post_tag') );