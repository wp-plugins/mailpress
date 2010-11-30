	<h2 <?php $this->classes('ch2'); ?>>
<?php echo mysql2date( get_option('date_format'), current_time('mysql')); ?>
	</h2>
	<h3 <?php $this->classes('ch3'); ?>>
		<span <?php $this->classes('ch3a'); ?>>
<?php if (isset($_the_title)) echo $_the_title; else $this->the_title(); ?>
		</span>
	</h3>
	<div <?php $this->classes('cdiv'); ?>>
		<p>
<?php if (isset($_the_content)) echo $_the_content; else $this->the_content(); ?>
		</p>
		<p>
<?php echo (isset($_the_actions)) ? $_the_actions : '&nbsp;'; ?>
		</p>
	</div>
