		</div>
		</div>
		</div><!-- #main -->
		<footer id="colophon" class="site-footer" role="contentinfo">
			<?php //get_sidebar( 'main' ); ?>
			<div id="secondary" class="sidebar-container" role="complementary">
				<?php if (isset($this->args->unsubscribe)) ?>
				<div <?php $this->classes('mail_link'); ?>>
					<a href='{{unsubscribe}}'  <?php $this->classes('mail_link_a a'); ?>>Manage your subscriptions</a>
				</div>
			</div>

			<div class="site-info">
				<?php do_action( 'twentythirteen_credits' ); ?>
				<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'twentythirteen' ) ); ?>" title="<?php esc_attr_e( 'Semantic Personal Publishing Platform', 'twentythirteen' ); ?>"><?php printf( __( 'Proudly powered by %s', 'twentythirteen' ), 'WordPress and the MailPress Plugin' ); ?></a>
			</div><!-- .site-info -->
		</footer><!-- #colophon -->
	</div><!-- #page -->

	<?php //wp_footer(); ?>

</body>
</html>
