=== MailPress ===
Contributors: andre renaut
Donate link: http://www.mailpress.org
Tags: mail, mails, comments, comment, subscribe, newsletters, newsletter, Wordpress, Plugin, swiftmailer, post, posts, smtp, sendmail, phpmail, notification, notifications
Requires at least: 2.8
Tested up to: 2.8
Stable tag: 3.0.2

The WordPress mailing platform

== Description ==

This is an ALPHA version of what will be MailPress 4.0

So many changes, i cannot list them all, need to rewrite the doc.

	* Code has been deeeeeply and completely reviewed, 

	* name field available

	* less ressources needed if not activated (till 3.0.2 MailPress was loading everything before doing something)
	* new swiftmailer 4.0.3 (needs php 5.3 but tested with 5.2.5 locally without bugs)
	* new ip search management
	* less subscriber specific data stored for each recipients, when sending a multi-recipients mail.
	* some add ons have been completely integrated to MailPress

== Installation ==

Some words about installation. But it is still an ALPHA ! so do not use it in production.

* Deactivate MailPress and add ons

* If you are using a specific page or category for subscription management, see mailpress/xtras templates. there has been some changes here too.
  So make the changes accordingly.
* Delete all add on folders !! Yes, all new add ons will be in the mailpress folder
* Inside 'mailpress' folder, delete folders 'mp-admin', 'mp-includes', 'xtras' .


* Unzip the downloaded mailpress zip file.
* Upload everything in you wordpress plugin folder
* Activate MailPress
* Activate add ons needed.
* Check your settings (MailPress settings are now in the WP Settings menu).
* Check your MailPress theme (Mails > Themes)
* Make a test (or with Settings > MailPress > Test or using new MailPress test feature available in Write Post page !!!

Please report any bug in the mailpress google group http://groups.google.com/group/mailpress
starting your subject title with : "(MailPress 4)".

Thank you


== Frequently Asked Questions ==

* looking for 'mailpress_deregister_scripts' ?
see mailpress/mp-admin/xml

== Screenshots ==

1. none

== Changelog ==

** 4.0 ** one day !

== Next features ==

** a new doc !

Please donate !
