<?php
/*
from an original idea of David Potter http://dpotter.net/Technical/2008/11/mailpress-review-introduction/

the file name must be style.php
the array variable must be $_classes

using 
	<td <?php $this->classes('nopmb ctd'); ?>>
will retrieve
	<td style="padding:0;margin:0;border:none;margin:0;padding:0pt 0pt 20px 45px;border:none;width:550px;float:left;color:#333333;text-align:left;font-family:Verdana,Sans-Serif;">
*/

$_classes = array(

'nopmb' 		=> "	padding:0;
				margin:0;
				border:none;",

'txtleft'		=> "	text-align:left;",

// header

'globdiv'		=> "	color:#000;
				font-family: verdana,geneva;",

'globlink'		=> "	color: rgb(153, 153, 153);
				font-family: verdana,geneva;
				font-weight:bold;",

'htable'		=> "	background:#6D8C82;
				width:100%;",

'htr'			=> "	height:60px;",

'logo'		=> "	border:none;
				padding:20px 10px 20px 50px",

'htdate'		=> "	width:100%;
				height:70px;
				padding:0;
				margin:0;
				border-bottom:1px solid #C6D9E9;
				background:#DCFAF1;",

'hdate'		=> "	padding:0 10px 0 0;
				margin:0;border:none;
				background:#DCFAF1;
				font-family:Georgia,Times,serif;
				color#555;
				font-size:30px;
				text-align:right;",

// content

'contentdiv'	=> "	margin:0;
				padding:20px;
				border:0;",

'ctable'		=> "	width:100%;",

'ctd'			=> "	margin:0;
				padding:0pt 0pt 20px 45px;
				border:none;
				width:550px;
				float:left;
				color:#333333;
				text-align:left;
				font-family:Verdana,Sans-Serif;",

'cdiv'		=> "	margin:0pt 0pt 40px;
				padding:0;
				border:none;
				text-align:justify;",

'ch2'			=> "	margin:30px 0pt 0pt;
				padding:0;
				border:none;
				color:#333;
				font-size:1.4em;
				font-weight:bold;
				font-family:Verdana,Sans-Serif;",

'cdate'		=> "	line-height:2em;
				color:#777;
				font-size:0.7em;
				font-family:Arial,Sans-Serif;",

'cp'			=> "	line-height:1.4em;
				font-size:0.85em;",

'clink'		=> "	text-decoration:none;
				color:#333;",

'clink2'		=> "	min-width:80px;
				text-align:center;
				text-shadow:0 -1px 0 rgba(0, 0, 0, 0.3);
				font-weight:bold;
				background-color:#6D8C82;
				border-color:#6D8C82;
				color:#FFFFFF;
				font-weight:bold;
				-moz-border-radius: 11px;
				-webkit-border-top-left-radius: 11px;
				-khtml-border-top-left-radius: 11px;
				border-top-left-radius: 11px;
				border-style:solid;
				border-width:1px;
				cursor:pointer;
				font-size:11px !important;
				line-height:16px;
				padding:2px 8px;
				text-decoration:none;
				margin:1px;
				font-family:'Lucida Grande',Verdana,Arial,'Bitstream Vera Sans',sans-serif;",

// footer

'ftable'		=> "	margin:0;
				padding:10px 20px;
				border-top:1px solid #DEDEDE;
				width:100%;",

'fltd'		=> "	font-family:Verdana,sans-serif;
				color:#2583AD;
				font-size:10px;",

'frtd'		=> "	font-family:Georgia,Times,serif;
				color:#8c8c8c;
				font-size:14px;text-align:right;"

);
?>