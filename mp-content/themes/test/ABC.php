<?php
/*
Template Name: ABC
Subject: [<?php bloginfo('name');?>] Lorem ipsum ... 
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title></title>
<style type="text/css" media="screen">
	
	/* common
	--------------------------------------------------*/
	
	body {
		margin: 0px;
		padding: 0px;
	}
	#BodyImposter {
		color: #666;
		background: #fff;
		width: 100%;
		margin: 0px;
		padding: 0px;
	}
	#BodyImposter dd,
	#BodyImposter dt,
	#BodyImposter li,
	#BodyImposter p,
	#BodyImposter td {
		font-size: 12px;
		line-height: 1.5em;
		font-family: Helvetica, Arial, sans-serif;
	}
	#BodyImposter dl,
	#BodyImposter p,
	#BodyImposter ol,
	#BodyImposter ul {
		margin: 0em 0em 1em 0em;
	}
	#BodyImposter img {
		border: 0px;
	}
	#BodyImposter small {
		font-size: 10px;
		line-height: 1.3em;
	}
	#BodyImposter ol {
		padding-left: 1em;
	}
	#Primary ul {
		padding-left: 1em;
		padding-right: 1em;
	}
	#Secondary ul {
		padding: 0px;
	}
	#BodyImposter ol li {
		margin: 0px;
		padding: 0px;
	}
	#BodyImposter ul li {
		background: url("images/bullet.gif") no-repeat 0em 0.5em;
		padding: 0px 0px 0px 8px;
		margin: 0px;
		list-style: none;
	}
	
	/* links
	--------------------------------------------------*/
	
	#BodyImposter a {
		text-decoration: underline;
	}
	#Primary a:link,
	#Primary a:visited,
	#Secondary a:link,
	#Secondary a:visited {
		color: #000;
		background: #fff;
	}
	#BodyImposter h2 a:link,
	#BodyImposter h2 a:visited {
		color: #5397b9;
		background: #fff;
		text-decoration: none;
	}
	#Helpful a:link,
	#Helpful a:visited {
		color: #c2c2c2;
		background: #fff;
	}
	#Legal a:link,
	#Legal a:visited {
		color: #999;
		background: #ccc;
	}
	#BodyImposter a:hover {
		text-decoration: none;
	}
	#BodyImposter h2 a:hover {
		text-decoration: underline;
	}
	#BodyImposter a:active {
		color: #000;
		background: #fff;
		text-decoration: none;
	}
	#Legal a:active {
		color: #000;
		background: #ccc;
		text-decoration: none;
	}
	
	/* heads
	--------------------------------------------------*/
	
	#BodyImposter h1,
	#BodyImposter h2 {
		color: #5397b9;
		background: #fff;
		font-family: Helvetica, Arial, sans-serif;
		font-weight: bold;
		line-height: normal;
		margin: 0.75em 0em;
		padding: 0px;
	}
	#Masthead h1 {
		font-size: 28px;
		color: #fff;
		background: #333;
	}
	#Primary h2 {
		font-size: 18px;
	}
	#Secondary h2,
	#Tertiary h2 {
		font-size: 14px;
	}
	
	/* boxes
	--------------------------------------------------*/
	
	#Box {
		width: 100%;
		text-align: center;
	}
	#Content {
		margin: 0px auto;
		text-align: left;
	}
	#Content td#Primary {
		width: 360px;
	}
	#Content td#Primary img {
		border: 6px solid #e0e0e0;
		float: right;
		margin: 5px 0px 10px 15px;
	}
	#Helpful table {
		color: #c2c2c2;
		background: #fff;
		margin: 0px auto;
	}
	#Helpful p {
		width: 550px;
		margin: 0px auto;
		padding: 0px 20px;
		text-align: left;
	}
	#Legal {
		padding: 20px 0px;
	}
	#Legal table {
		color: #999;
		background: #ccc;
		margin: 0px auto;
	}
	#Legal p {
		width: 550px;
		margin: 0px auto;
		padding: 0px 20px;
		text-align: left;
	}
	#Masthead {
		padding: 20px 0px;
	}
	#Masthead table {
		color: #fff;
		background: #333;
		margin: 0px auto;
	}
	#Masthead h1,
	#Masthead p {
		width: 550px;
		margin: 0px auto;
		padding: 0px 20px;
		text-align: left;
	}
	#Masthead p {
		font-size: 14px;
	}
	#Masthead p #Date {
		float: right;
	}
	#Tertiary {
		padding: 20px 0px;
	}
	#Tertiary table {
		color: #d69898;
		background: #c33;
		margin: 0px auto;
	}
	#Tertiary h2 {
		color: #fff;
		background: #c33;
		padding: 0em 0em 0.5em 0em;
	}
	#Tertiary h2,
	#Tertiary p {
		width: 550px;
		margin: 0px auto;
		padding: 0px 20px;
		text-align: left;
	}
	#Helpful,
	#Legal,
	#Masthead,
	#Tertiary {
		width: 100%;
		text-align: center;
	}

	
</style>
</head>

<body>
<div id="BodyImposter">
	<table cellspacing="0" cellpadding="15" width="100%">
		<tr>
			<td id="Helpful">
				<table cellspacing="0" cellpadding="0" width="550">
					<tr>
						<td style="font-family: Helvetica, Arial, sans-serif;">
							<p>You are receiving this email because the email address [email] was subscribed to our email list. Having trouble reading this email? View it on our <a href="{{viewhtml}}">website</a>.</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<table cellspacing="0" cellpadding="15" width="100%">
		<tr>
			<td id="Masthead" style="color: #fff; background: #333; font-family: Helvetica, Arial, sans-serif;">
				<table cellspacing="0" cellpadding="0" width="550">
					<tr>
						<td>
							<h1>Widget Monthly Newsletter</h1>
							<p><span id="Date">September 27, 2007</span> Brought to you by ABC Widgets</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<div id="Box">
		<table id="Content" cellspacing="0" cellpadding="15" width="580">
			<tr valign="top">
				<td id="Primary" style="font-family: Helvetica, Arial, sans-serif;">
					<h2>Happening this month</h2>
<img src="images/phFPO.jpg" width="159" height="100" alt="" />
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin molestie est in augue. Proin accumsan eleifend odio. Integer mauris mi.</p>
<p>Integer nonummy augue in lectus. Aliquam tristique nibh at dolor venenatis aliquet.</p>

<h2>Latest Events</h2>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin molestie est in augue. Proin accumsan eleifend odio. Integer mauris mi, sollicitudin at, laoreet vitae, lobortis id, justo. Mauris sollicitudin sapien nec quam. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam tristique nibh at dolor venenatis aliquet.</p>
				</td>
				<td id="Secondary" style="font-family: Helvetica, Arial, sans-serif;">
					<h2>In this issue</h2>
					<ul>
																<li><a href="#">Happening this month</a></li>
								<li><a href="#">Latest Events</a></li>
								<li><a href="#">New Web Site</a></li>
							</ul>
					<h2>Also this issue</h2>
					<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin molestie est in augue. Proin accumsan eleifend odio.</p>
					<h2>Pass it on</h2>
					<p>If you know someone who may be interested in receiving this newsletter, you can easily <forwardtoafriend>forward up to five copies</forwardtoafriend> at once.</p>
				</td>
			</tr>
		</table>
	</div>
	<table cellspacing="0" cellpadding="15" width="100%">
		<tr>
			<td id="Tertiary" style="color: #d69898; background: #c33;">
				<table cellspacing="0" cellpadding="0" width="550">
					<tr>
						<td style="color: #d69898; background: #c33; font-family: Helvetica, Arial, sans-serif;">
							<h2 style="color: #fff; background: #c33;">New Web Site</h2>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin molestie est in augue. Proin accumsan eleifend odio. Integer mauris mi, sollicitudin at, laoreet vitae, lobortis id, justo.</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<table cellspacing="0" cellpadding="15" width="100%">
		<tr>
			<td id="Legal" style="color: #999; background: #ccc;">
				<table cellspacing="0" cellpadding="0" width="550">
					<tr>
						<td style="color: #999; background: #ccc; font-family: Helvetica, Arial, sans-serif;">
							<p>&#169;2007 ABC Widgets, 123 Some Street, City, ST 99999. <unsubscribe>Unsubscribe</unsubscribe> from this newsletter.</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>
</body>
</html>