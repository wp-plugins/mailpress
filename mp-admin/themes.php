<?php

$th = new MP_Themes();

$mp_general = get_option('MailPress_general');
$url = (isset($mp_general['menu'])) ? 'admin.php' : 'themes.php';
$page = MP_FOLDER . '/mp-admin/themes.php';

if ( ! $th->validate_current_theme() ) 
{
?>
<div id='message1' class='updated fade'><p><?php _e('The active MailPress theme is broken.  Reverting to the default MailPress theme.','MailPress'); ?></p></div>
<?php 
}
elseif ( isset($_GET['activated']) ) 
{
?>
<div id='message2' class='updated fade'><p><?php printf(__('New MailPress theme activated.','MailPress'), get_bloginfo('url') . '/'); ?></p></div>
<?php 
}


$themes = $th->themes; 
$ct = $th->current_theme_info(); 
?>
<div class='wrap'>
	<h2>
		<?php _e('Current MailPress Theme','MailPress'); ?>
	</h2>
	<div id='rightcol'>
		<div id='rightcol2'>

<?php if ( 1 < count($themes) ) { ?>

<?php
		$style = '';

		$theme_names = array_keys($themes);
		natcasesort($theme_names);
		$x = true;

		foreach ($theme_names as $theme_name) 
		{
			if ( $theme_name == $ct->name ) 	continue;
			if ( 'plaintext' == $theme_name ) 	continue;
			$template 		= $themes[$theme_name]['Template'];
			$stylesheet 	= $themes[$theme_name]['Stylesheet'];
			$title 		= $themes[$theme_name]['Title'];
			$version 		= $themes[$theme_name]['Version'];
			$description 	= $themes[$theme_name]['Description'];
			$author 		= $themes[$theme_name]['Author'];
			$screenshot 	= $themes[$theme_name]['Screenshot'];
			$stylesheet_dir 	= $themes[$theme_name]['Stylesheet Dir'];
			$tags 		= $themes[$theme_name]['Tags'];

			$page .= '&amp;action=activate&amp;amp;template=';
			$activate_link 	= wp_nonce_url($url . '?page=' . $page . urlencode($template) . '&amp;stylesheet=' . urlencode($stylesheet), 'switch-theme_' . $template);
?>
			<div class='righttheme<?php if (!$x) echo ' alternate'; $x = !$x; ?>'>
				<a href='<?php echo $activate_link; ?>' >
					<?php echo $title; ?>
				</a>
				<br/><br/>
				<a href='<?php echo $activate_link; ?>' class='screenshot'>
<?php if ( $screenshot ) : ?>
					<img src='<?php echo get_option('siteurl') . '/' . $stylesheet_dir . '/' . $screenshot; ?>' alt=''/>
<?php endif; ?>
				</a>
				<br/><br/>
			</div>
<?php 	} // end foreach theme_names ?>
		</div>
	</div>

	<div id='leftcol'>
<?php if ( $ct->screenshot ) : ?>
		<img src='<?php echo get_option('siteurl') . '/' . $ct->stylesheet_dir . '/' . $ct->screenshot; ?>' alt='<?php _e('Current MailPress theme preview','MailPress'); ?>'/>
<?php endif; ?>
		<h3>
			<?php printf(_c('%1$s %2$s by %3$s|1: theme title, 2: theme version, 3: theme author'), $ct->title, $ct->version, $ct->author) ; ?>
		</h3>
		<?php echo $ct->description; ?>
		<br/>
<?php if ($ct->parent_theme) { ?>
		<?php printf(__('The template files are located in <code>%2$s</code>.  The stylesheet files are located in <code>%3$s</code>.  <strong>%4$s</strong> uses templates from <strong>%5$s</strong>.  Changes made to the templates will affect both MailPress themes.','MailPress'), $ct->title, $ct->template_dir, $ct->stylesheet_dir, $ct->title, $ct->parent_theme); ?>
<?php } else { ?>
		<?php printf(__('All theme&#8217;s files in : <code>%2$s</code>.','MailPress'), $ct->title, $ct->template_dir, $ct->stylesheet_dir); ?>
<?php } ?>
<?php if ( $ct->tags ) : ?>
		<br/>
		<?php _e('Tags:'); ?> <?php echo join(', ', $ct->tags); ?>
<?php endif; ?>
	</div>

<?php } ?>

<?php
// List broken themes, if any.
$broken_themes = $th->get_broken_themes();
if ( count($broken_themes) ) {
?>
	<h2>
		<?php _e('Incomplete Themes'); ?>
	</h2>
	<table class='widefat' width='100%' cellpadding='3' cellspacing='3'>
		<thead>
			<tr>
				<th><?php _e('Folder','MailPress'); ?></th>
				<th><?php _e('Name','MailPress'); ?></th>
				<th><?php _e('Description','MailPress'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$theme = '';

	$theme_names = array_keys($broken_themes);
	natcasesort($theme_names);

	foreach ($theme_names as $theme_name) 
	{
		$title = $broken_themes[$theme_name]['Title'];
		$description = $broken_themes[$theme_name]['Description'];
		$folder = $broken_themes[$theme_name]['Folder'];

		$theme = ("class='alternate'" == $theme) ? '' : "class='alternate'";
?>
			<tr <?php echo $theme;?>>
				 <td><?php echo $folder;?></td>
				 <td><?php echo $title;?></td>
				 <td><?php echo $description;?></td>
			</tr>
<?php
	}
?>
		</tbody>
	</table>
<?php
}
?>
</div>