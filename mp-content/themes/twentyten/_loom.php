<div <?php $this->classes('entry'); ?>>
	<h2 <?php $this->classes('entry-title'); ?>>
<?php if (isset($_the_title)) echo $_the_title; else $this->the_title(); ?>
	</h2>
			
	<div <?php $this->classes('entry-meta'); ?>>
		<span <?php $this->classes('meta-prep'); ?>>
<?php _e('Generated on ', 'twentyten'); ?>
<?php echo mysql2date(get_option( 'date_format' ), current_time('mysql')); ?>
		</span>
	</div>
								
	<div <?php $this->classes('entry-content'); ?>>	
<?php if (isset($_the_content)) echo $_the_content; else $this->the_content(); ?>
	</div>

<?php if (isset($_the_actions)) : ?>
	<div <?php $this->classes('entry-utility'); ?>>
		<span <?php $this->classes('entry-sep'); ?>>
			<span  <?php $this->classes('entry-sep'); ?>>
<?php echo $_the_actions; ?>
			</span>
		</span>
	</div>
<?php endif; ?>
</div>