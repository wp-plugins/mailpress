<?php while (have_posts()) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php $this->classes('article'); ?>>
		<header class="entry-header">
			<h1 class="entry-title">
				<a rel="bookmark" href="<?php the_permalink() ?>">
					<?php the_title(); ?>
				</a>
			</h1>
			<div class="entry-meta">
				<span class="date">
					<a rel="bookmark" title="permalink to <?php the_permalink() ?>">
						<time class="entry-date" datetime="<?php the_time('j. F Y') ?>">
							<?php the_time('F, j Y') ?>
						</time>
					</a>
				</span>
				<span class="author vcard">
					<a class="url fn n" rel="author" title="Show all posts by <?php the_author(); ?>"><?php the_author(); ?></a>
				</span>
			</div>
		</header>
		<div class="entry-content">
			<?php $this->the_content( __( 'More →' )); ?>
		</div>
		<footer class="entry-meta">
			<div class="comments-link">
				<a title="comment <?php the_title(); ?>" href="<?php the_permalink(); ?>#respond">
					<span class="leave-reply">
						Leave a comment
					</span>
				</a>
			</div>
		</footer>
	</article>
<?php endwhile; ?>
