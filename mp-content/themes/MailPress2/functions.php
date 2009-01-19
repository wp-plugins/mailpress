<?php
/*
functions.php

from an original idea of David Potter http://dpotter.net/Technical/2008/11/mailpress-review-introduction/

*/

if ( !function_exists( 'mp_classes' ) ) :
function mp_classes($classes)
{
	include ('styles.php');

	$count = 1;

	while ($count) $classes = str_replace('  ',' ',$classes,$count);
	$a_classes = explode(' ',trim($classes));

	$style = '';

	foreach($a_classes as $class) if (isset($mailpress2_classes[$class])) $style .=  mp_clean_style($mailpress2_classes[$class]);

	if ('' != $style) echo "style=\"" . $style . "\"";
}
endif;

if ( !function_exists( 'mp_clean_style' ) ) :
function mp_clean_style($style)
{
	$style = trim($style);
	$style = str_replace("\t",'',$style);
	$style = str_replace("\n",'',$style);
	$style = str_replace("\r",'',$style);
	if (strlen($style)) if ($style[strlen($style) -1] != ';') $style .=';';
	return $style;
}
endif;
?>