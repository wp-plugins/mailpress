=== MailPress ===
Contributors: andre renaut
Donate link: Donate a few seconds to rate this plugin (http://wordpress.org/extend/plugins/mailpress/stats/ AND http://weblogtoolscollection.com/pluginblog/2008/07/10/last-release-of-mailpress-ver-12). thanks !
Tags: mail, mails, comments, comment, subscribe, newsletters, newsletter, Wordpress, Plugin, swiftmailer, post, posts, smtp, sendmail
Requires at least: 2.6
Stable tag: 1.9.1

Allows you to send beautiful emails with style !

== Description ==

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**

**Allows you** to send beautiful and styled html and plain text mails based on dedicated themes and templates.

Several add-ons are available as zip files in the mailpress folder.
They are in fact WordPress plugins interacting with MailPress.
The basic idea is to keep the core of MailPress as stable as possible
and to use the WordPress API to propose some add ons, 
making MailPress completely scalable to your needs.

* MailPress_bulk_import is an add-on for MailPress to import users.
* MailPress_filter_img is an add-on for MailPress to filter ALL html img tags before mailing or preview them.
* MailPress_sync_wordpress_user is an add-on for MailPress to synchronise MailPress users with WordPress users.
* MailPress_mailing_lists is an add-on for MailPress to manage mailing lists.
* MailPress_newsletter_categories is an add-on for MailPress to manage newsletter for main categories.
* MailPress_upload_media is an add-on for MailPress to use upload media wordpress facilities for images.
* MailPress_connection_sendmail is an add-on for MailPress to replace default SMTP connection by SendMail connection.
* NEW ! MailPress_batch_send is an add-on for MailPress to send mails in batch mode.

Supported languages : English, French	(.pot provided)

Tested with Firefox3, Internet Explorer 7, Safari 3.1 (Windows XP)

== Installation ==

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**

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
13. Managing subscribers
14. Managing subscriptions
15. Mail sample
16. another Mail sample
17. another Mail sample with a post with a gallery

== Log ==

**1.9.1**	  2008/10/04

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**

* Now preview or view mails using thickbox.
* Introducing a new add-on "mailpress_batch_send" to send mails in batch mode.

1.9	  	2008/09/04

* html tag id's in MailPress form have been modified so be carefull : if necessary modify your specific css accordingly.
* get_bloginfo('siteurl') replaced by get_option('siteurl'). Thanks Greg !
* bug "Cannot load mailpress/mp-admin/users.php" fixed. thanks Javi !

* Better log if Mail not saved.
* Now skip SMTP connection authentication when user/pwd are empty.
* Preview plaintext mail with wrapped text now.

* Most of the MailPress Write process has been reviewed and re-coded :
* line breaks converted in <p></p> for html content
* autosave and mail revisions (one revision per wp user) now available.
* preview mail/revision in a thickbox now available.

* Introducing a new add-on "mailpress_upload_media" for images only. 
Please notice the followings for this 'quick & dirty' plugin : when the media UI buttons display 'Insert into post' ... read 'Insert
into mail', gallery shortcode '[gallery]' is not supported in mails ... 

* Introducing a new add-on "mailpress_connexion_sendmail" for sendmail connexions overriding SMTP. 


1.8  		2008/08/08

* Write panel with Tinymce
* New MP_Newsletter class.
* New user panel.
* New MySql tables mp_user_meta & mp_mail_meta with its basic functions in MP_User/MP_Mail class
* Able to display the post title in mail subject using MailPress mail decoration (see samples in mp-content in single.php file)

* Now subscribers can manage their subscriptions to the basic newsletters (read below).
* By default, all active MailPress users receive the basic newsletters (post/daily/weekly/monthly) if you decided so  (General settings thru admin!).
By activating the subscription management link (if available in their mails), they can now unsubscribe to such or such newsletter (as for comments for such or such post they subscribed before.).

* With the new (optional) add on 'MailPress_newsletter_categories' you can allow MailPress users to subscribe to such or such category/periodicity. 
Well ! this add on is not so light and might overload your server, **so be cautious**. However, for PHP coders, it is a good example of how to generate your owns !

1.7	  		2008/07/31 

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





