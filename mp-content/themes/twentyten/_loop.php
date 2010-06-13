<?php
add_filter( 'comments_popup_link_attributes', 	array('MP_theme_2010', 'comments_popup_link_attributes'), 8, 1 );
add_filter( 'the_category', 				array('MP_theme_2010', 'the_category'), 8, 3 );
add_filter( 'term_links-post_tag', 			array('MP_theme_2010', 'term_links_post_tag'), 8, 1 );

while (have_posts()) : the_post(); 
?>

<div id="post-<?php the_ID(); ?>"  <?php $this->classes('entry'); ?>>
	<h2 <?php $this->classes('entry-title'); ?>>
		<a href="<?php the_permalink(); ?>" title="<?php printf( __('Permalink to %s', 'twentyten'), the_title_attribute('echo=0') ); ?>" rel="bookmark"  <?php $this->classes('entry-title_a'); ?>>
<?php $this->the_title(); ?>
		</a>
	</h2>
			
	<div <?php $this->classes('entry-meta'); ?>>
		<span <?php $this->classes('meta-prep'); ?>><?php _e('Posted on ', 'twentyten'); ?></span>
		<a href="<?php the_permalink(); ?>" title="<?php the_time('Y-m-d\TH:i:sO') ?>" rel="bookmark" <?php $this->classes('meta-prep_a'); ?>>
			<span <?php $this->classes('entry-sep'); ?>>
<?php the_time( get_option( 'date_format' ) ); ?>
			</span>
		</a>
		<span <?php $this->classes('entry-sep'); ?>> 
<?php _e('by ', 'twentyten'); ?> 
		</span>
		<span <?php $this->classes('entry-sep'); ?>>
			<a <?php $this->classes('meta-prep_a'); ?> href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" title="<?php printf( __( 'View all posts by %s', 'twentyten' ), get_the_author() ); ?>">
<?php the_author(); ?>
			</a>
		</span>					
	</div><!-- .entry-meta -->
								
	<div <?php $this->classes('entry-content'); ?>>	
<?php $this->the_content( __( '(more...)' )  ); ?>
<?php wp_link_pages('before=<div ' . $this->classes('page-link', false) . '>' . __( 'Pages:', 'twentyten' ) . '&after=</div>') ?>
	</div><!-- .entry-content -->

	<div <?php $this->classes('entry-utility'); ?>>
		<span <?php $this->classes('entry-sep'); ?>>
			<span  <?php $this->classes('entry-sep'); ?>>
				<?php _e( 'Posted in ', 'twentyten' ); ?>
			</span>
			<?php echo get_the_category_list(', '); ?>
		</span>
		<span <?php $this->classes('entry-sep'); ?>> | </span>
		<?php the_tags( '<span ' . $this->classes('entry-sep', false) . '><span ' . $this->classes('entry-sep', false) . '>' . __('Tagged ', 'twentyten' ) . '</span>', ", ", '</span> <span ' . $this->classes('entry-sep', false) . '>|</span>' ) ?>
		<span <?php $this->classes('entry-sep'); ?>><?php comments_popup_link( __( 'Leave a comment', 'twentyten' ), __( '1 Comment', 'twentyten' ), __( '% Comments', 'twentyten' ) ) ?></span>
	</div><!-- #entry-utility -->	
</div><!-- #post-<?php the_ID(); ?> -->

<?php 
endwhile;

remove_filter( 'comments_popup_link_attributes', 	array('MP_theme_2010', 'comments_popup_link_attributes') );
remove_filter( 'the_category', 				array('MP_theme_2010', 'the_category') );
remove_filter( 'term_links-post_tag', 			array('MP_theme_2010', 'term_links_post_tag') );