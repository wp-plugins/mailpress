=== MailPress ===
Contributors: andre renaut
Donate link: Donate a few seconds to rate this plugin (http://wordpress.org/extend/plugins/mailpress/stats/ AND http://weblogtoolscollection.com/pluginblog/2008/07/10/last-release-of-mailpress-ver-12). thanks !
Tags: mail, comments, comment, subscribe, newsletter, Wordpress, Plugin, swiftmailer, post, posts
Requires at least: 2.5
Stable tag: 1.7

Allows you to send beautiful emails with style !

== Description ==

**Allows you** to send beautiful and styled html and plain text mails based on dedicated themes and templates.

And you can even put some add-ons to fit your needs :
* better management of css style for images in the mail (mailpress_filter_img)
* import emails in an easy way (mailpress_bulk_import)
* synchronize your MailPress users and your WordPress users (mailpress_sync_wordpress_user)
* or NEW !
* organize your users with mailing lists ... (mailpress_mailing_lists)

These add-ons are in fact WordPress plugins interacting with MailPress.

The basic idea is to keep the core of MailPress as stable as possible
and to use the WordPress API to propose some add ons, making MailPress completely scalable to your needs.

Supported languages : English, French	(.pot provided)

Tested with Firefox3, Internet Explorer 7, Safari 3.1 (Windows XP)

== Installation ==

**For upgrade from lower versions, deactivate and reactivate the plugin.**

**See** plugin page at http://www.nogent94.com?page_id=70

== Frequently Asked Questions ==

**See** plugin page at http://www.nogent94.com?page_id=70

== Screenshots ==

1. Subscription form
2. MailPress Dashboard
3. MailPress Dashboard Sidebar
4. Write
5. Manage mails
6. View Mail (html)
7. View Mail (plaintext)
8. Design
9. Settings - General
10. Settings - SMTP
11. Settings - Test
12. Manage subscribers
13. Mail sample
14. another Mail sample
15. another Mail sample with a gallery

== Log ==

**1.7**  		2008/07/31 

* Now Drafts can be used as Templates and remain Drafts even if send (sent mail created)			(mp-includes/MP_Mail.class.php)
* bug on stats for subscribers to comments per post when deleting a MailPress user				(mp-includes/MP_User.class.php)

* Adding some apply_filters 
1. MailPress_title				for admin (page title)
1. MailPress_enqueue_css			for admin
1. MailPress_enqueue_scripts			for admin
1. MailPress_edit_user_url			for admin MP user list
1. MailPress_mailing_lists			give an id and a description to your mailing list 		(available for drafts only)
1. MailPress_mailing_lists_query		return the MySQL query from the id of the mailing list 	(available for drafts only)
1. MailPress_get_recipients			adding some data related to the user for mail decoration  	(available for drafts only)

* Adding some do_actions 
1. MailPress_mp_redirect			for admin
1. MailPress_register_scripts			for admin
1. MailPress_menu					to add a menu to MailPress. (true/false) (true) for plugin page inside Mailpress menu, (false) for standard WP menu.
1. MailPress_insert_user			when inserting a MP user (mp_user_id)
1. MailPress_delete_user			when deleting a MP user  (mp_user_id)
1. MailPress_restrict_manage_users		for admin MP user list
1. MailPress_extra_user_list			for admin MP user list

and made some adjustements to integrate the new 'mailpress_mailing_lists' add-on.

Why all those filters and actions ? The basic idea is to keep the core of MailPress as stable as possible
 and to use the WordPress API to propose some add ons, making MailPress completely scalable to your needs.

1.6	  		2008/07/25

* process_img code review for BETTER SUPPORT for POST IMAGES						(mp-includes/MP_Mail.class.php)
* Display bug in mp-admin/includes/mail.php									(mp-admin/includes/mail.php)
* Adding some do_actions on Daniel's request 									(mp-admin/users.php)
For Bulk user insert. 
Add-on is downloadable at http://groups.google.com/group/mailpress in the FILE section (file name 'mailpress_bulk_import.rar')
* Adding some do_actions and apply_filters on Daniel and Arthur's request 				(mp-admin/settings.php, mp-includes/MP_Mail.class.php, mp-includes/MP_Admin.class.php)
For specific settings applied to **ALL** images in the mail. 
Add-on is downloadable at http://groups.google.com/group/mailpress in the FILE section (file name 'mailpress_filter_img.rar')

1.5.1  		2008/07/21 

* action.php?action= instead of action.php/?action=								(mp-includes/MP_User.class.php)
* Delete mails for level 10 only
* Checkbox management on mails and users list compliant with wordpress 2.6

1.5  			2008/07/18 

* SMTP-AUTH	support (Andrew's request)										(mp-includes/MP_Mail.class.php, mp-admin/includes/settings-smtp.php)
* POP before SMTP support 												(mp-includes/MP_Mail.class.php, mp-admin/includes/settings-smtp.php)
* Title for MailPress widget ++											(mp-includes/MP_User.class.php, MailPress.php)
* MailPress Dashboard widgets : x axis labels									(mp-admin/includes/dashboard.php)
* Screenshot updates
* Plugin page update
* Google group : http://groups.google.com/group/mailpress

1.4	  		2008/07/12 

* bug in IP country Geolocation when ip unknown									(mp-includes/MP_User.class.php)
* unsubscribe link mail allows subscribers to unsubscribe from one or several posts 		(mp-includes/mp-mail-links.php)

1.3	  		2008/07/10 

* code review														(mp-admin/includes/install.php)
* new dashboard widget (comment subscribers per post) 							(mp-admin/includes/dashboard.php)
* review of wp_redirect policy

1.2.1  		2008/07/09 

* code review														(mp-admin/includes/install.php)
* new dashboard widget (comment subscribers per post) 							(mp-admin/includes/dashboard.php)

1.2	  		2008/07/09 

* bug in install.php since 1.1.5

1.1.6  		2008/07/09 

* swift classes ver 3.3.3
* List of registered socket transports 	 									(mp-admin/includes/settings-smtp.php)
* miscellaneous 														(mp-includes/js/iframe.js, mp-includes/mp-mail-links.php)
* code review 														(MailPress.php)

1.1.5  		2008/07/08 

* better IP country Geolocation											(mp-includes/MP_User.class.php)
* flags in MailPress users lists											(mp-includes/MP_User.class.php)
* Weekly and Daily mails now available										(MailPress.php, mp-admin/includes/install.php)
* some useful changes on xtras											(category-xx.php, pt_MailPress.php)

1.1.4 		2008/07/07 minor bugs

* css modified for MailPress form											(MailPress.php)
* empty folder (tmp) not in download										(due to svn !)
* review of comments stats												(MailPress.php, mp-includes/MP_User.class.php)
* code review 														(mp-includes/MP_Mail.class.php)

1.1.3  		2008/07/07 minor bugs

* review of MP_Mail.class.php
* review of subscribe to comments code										(MailPress.php)

1.1.2     		2008/07/06 minor bugs

* review of subscribe to comments code										(MailPress.php, mp-includes/MP_User.class.php)

1.1.1     		2008/07/05 minor bugs

* url in MailPress dashboard footer 										(mp-admin/dashboard.php line 4)
* subject for mailing comments (id of comment) 									(MailPress.php line 314)
* MP_PATH instead of MP_FOLDER 											(mp-includes/MP_Admin.class.php lines 83 & 87)

1.1     			2008/07/02 first release

== Next features ==

**Any new idea** or **code improvement** can be posted at : contact@nogent94.com





