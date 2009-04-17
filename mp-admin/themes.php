<?php
if (!current_user_can('MailPress_switch_themes')) wp_die(__('You do not have sufficient permissions to access this page.'));

$th = new MP_Themes();

$mp_general = get_option('MailPress_general');
$url = (isset($mp_general['menu'])) ? 'admin.php' : 'themes.php';
$page = MailPress_page_design;

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

ksort( $themes );
$theme_total = count( $themes );
$per_page = 15;

if ( isset( $_GET['pagenum'] ) )
	$page = absint( $_GET['pagenum'] );

if ( empty($page) )
	$page = 1;

$start = $offset = ( $page - 1 ) * $per_page;

$page_links = paginate_links( array(
	'base' => add_query_arg( 'pagenum', '%#%' ) . '#themenav',
	'format' => '',
	'total' => ceil($theme_total / $per_page),
	'current' => $page
));

$themes = array_slice( $themes, $start, $per_page );
?>
<div class='wrap'>
	<div id="icon-mailpress-themes" class="icon32"><br /></div>
	<h2><?php _e('MailPress Themes','MailPress'); ?></h2>

	<h3><?php _e('Current Theme'); ?></h3>
	<div id="current-theme">
<?php if ( $ct->screenshot ) : ?>
		<img src='<?php echo get_option('siteurl') . '/' . $ct->stylesheet_dir . '/' . $ct->screenshot; ?>' alt='<?php _e('Current MailPress theme preview','MailPress'); ?>' />
<?php endif; ?>
		<h4><?php printf(_c('%1$s %2$s by %3$s|1: theme title, 2: theme version, 3: theme author'), $ct->title, $ct->version, $ct->author) ; ?></h4>
		<p class="description"><?php echo $ct->description; ?></p>
<?php if ($ct->parent_theme) { ?>
		<p><?php printf(__('The template files are located in <code>%2$s</code>.  The stylesheet files are located in <code>%3$s</code>.  <strong>%4$s</strong> uses templates from <strong>%5$s</strong>.  Changes made to the templates will affect both MailPress themes.','MailPress'), $ct->title, $ct->template_dir, $ct->stylesheet_dir, $ct->title, $ct->parent_theme); ?></p>
<?php } else { ?>
		<p><?php printf(__('All theme&#8217;s files in : <code>%2$s</code>.','MailPress'), $ct->title, $ct->template_dir, $ct->stylesheet_dir); ?></p>
<?php } ?>
<?php if ( $ct->tags ) : ?>
		<p><?php _e('Tags:'); ?> <?php echo join(', ', $ct->tags); ?></p>
<?php endif; ?>
	</div>
	<div class="clear"></div>
	<h3><?php _e('Available Themes'); ?></h3>
	<div class="clear"></div>

<?php if ( $page_links ) : ?>
	<div class="tablenav">
<?php echo "	<div class='tablenav-pages'>$page_links</div>"; ?>
		<br class="clear" />
	</div>
	<br class="clear" />
<?php endif; ?>

<?php 
if ( 1 < $theme_total ) { 

?>
	<table id="availablethemes" cellspacing="0" cellpadding="0">
<?php
	unset($themes['plaintext']);

	$style = '';

	$theme_names = array_keys($themes);
	natcasesort($theme_names);
	$rows = ceil(count($theme_names) / 3);
	for ( $row = 1; $row <= $rows; $row++ )
		for ( $col = 1; $col <= 3; $col++ )
			$table[$row][$col] = array_shift($theme_names);



	foreach ( $table as $row => $cols ) {
?>
		<tr>
<?php
		foreach ( $cols as $col => $theme_name ) {

			$class = array('available-theme');
			if ( $row == 1 ) $class[] = 'top';
			if ( $col == 1 ) $class[] = 'left';
			if ( $row == $rows ) $class[] = 'bottom';
			if ( $col == 3 ) $class[] = 'right';

?>
			<td class="<?php echo join(' ', $class); ?>">
<?php
	 		if ( !empty($theme_name) ) :
				$template 		= $themes[$theme_name]['Template'];
				$stylesheet 	= $themes[$theme_name]['Stylesheet'];
				$title 		= $themes[$theme_name]['Title'];
				$version 		= $themes[$theme_name]['Version'];
				$description 	= $themes[$theme_name]['Description'];
				$author 		= $themes[$theme_name]['Author'];
				$screenshot 	= $themes[$theme_name]['Screenshot'];
				$stylesheet_dir 	= $themes[$theme_name]['Stylesheet Dir'];

				$preview_link 	= get_option('siteurl') . '/' . MP_PATH . 'mp-includes/action.php';
				$preview_link 	= clean_url(add_query_arg( array('action' => 'theme-preview', 'template' => $template, 'stylesheet' => $stylesheet, 'TB_iframe' => 'true', 'width' => 600, 'height' => 400 ), $preview_link ));
				$preview_text 	= attribute_escape( sprintf( __('Preview of "%s"'), $title ) );
				$tags 		= $themes[$theme_name]['Tags'];
				$thickbox_class 	= 'thickbox';

				$page .= '&action=activate&template=';
				$activate_link 	=  clean_url(wp_nonce_url($url . '?page=' . $page . $template . '&stylesheet=' . $stylesheet, 'switch-theme_' . $template));

				$activate_text 	= attribute_escape( sprintf( __('Activate "%s"'), $title ) );
?>
				<a href="<?php echo $activate_link; ?>" class="<?php echo $thickbox_class; ?> screenshot">
<?php if ( $screenshot ) : ?>
					<img src='<?php echo get_option('siteurl') . '/' . $stylesheet_dir . '/' . $screenshot; ?>' alt="" />
<?php endif; ?>
				</a>
				<h3><a class="<?php echo $thickbox_class; ?>" href="<?php echo $activate_link; ?>"><?php echo $title; ?></a></h3>
<?php if ( $description ) : ?>
				<p><?php echo $description; ?></p>
<?php endif; ?>
<?php if ( $tags ) : ?>
				<p><?php _e('Tags:'); ?> <?php echo join(', ', $tags); ?></p>
<?php endif; ?>
				<noscript><p class="themeactions"><a href="<?php echo $preview_link; ?>" title="<?php echo $preview_text; ?>"><?php _e('Preview'); ?></a> <a href="<?php echo $activate_link; ?>" title="<?php echo $activate_text; ?>"><?php _e('Activate'); ?></a></p></noscript>
				<div style="display:none;"><a class="previewlink" href="<?php echo $preview_link; ?>"><?php echo $preview_text; ?></a> <a class="activatelink" href="<?php echo $activate_link; ?>"><?php echo $activate_text; ?></a></div>
<?php endif; // end if not empty theme_name ?>
			</td>
<?php } // end foreach $cols ?>
		</tr>
<?php } // end foreach $table ?>
	</table>
<?php } ?>
	<br class="clear" />
<?php if ( $page_links ) : ?>
	<div class="tablenav">
<?php echo "	<div class='tablenav-pages'>$page_links</div>"; ?>
		<br class="clear" />
	</div>
<?php endif; ?>

	<br class="clear" />

<?php
// List broken themes, if any.
$broken_themes = $th->get_broken_themes();
if ( count($broken_themes) ) {
?>
	<h2><?php _e('Broken Themes'); ?></h2>
	<p><?php _e('The following themes are installed but incomplete.  Themes must have a stylesheet and a template.'); ?></p>

	<table class='widefat' width="100%" cellpadding="3" cellspacing="3">
		<thead>
			<tr>
				<th><?php _e('Folder','MailPress'); ?></th>
				<th><?php _e('Name','MailPress'); ?></th>
				<th><?php _e('Description','MailPress'); ?></th>
			</tr>
		</thead>
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
		<tbody>
			<tr <?php echo $theme;?>>
				 <td><?php echo $folder;?></td>
				 <td><?php echo $title;?></td>
				 <td><?php echo $description;?></td>
			</tr>
		</tbody>
<?php
	}
?>
	</table>
<?php
}
?>
</div>