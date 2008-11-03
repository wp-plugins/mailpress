=== MailPress ===
Contributors: andre renaut
Donate link: http://andrerenaut.ovh.org/wp/?page_id=70
Tags: mail, mails, comments, comment, subscribe, newsletters, newsletter, Wordpress, Plugin, swiftmailer, post, posts, smtp, sendmail
Requires at least: 2.6
Tested up to: 2.6
Stable tag: 1.9.2

Allows you to send beautiful emails with style !

== Description ==

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**

**Allows you** to send beautiful and styled html and plain text mails based on dedicated themes and templates.

Several add-ons are available as zip files in the mailpress folder.
They are in fact WordPress plugins interacting with MailPress.
The basic idea is to keep the core of MailPress as stable as possible
and to use the WordPress API to propose some add-ons, 
making MailPress completely scalable to your needs.

* MailPress_batch_send is an add-on for MailPress to send mails in batch mode.
* MailPress_bulk_import is an add-on for MailPress to import users.
* MailPress_connection_sendmail is an add-on for MailPress to replace default SMTP connection by SendMail connection.
* MailPress_filter_img is an add-on for MailPress to filter ALL html img tags before mailing or preview them.
* MailPress_mailing_lists is an add-on for MailPress to manage mailing lists.
* MailPress_newsletter_categories is an add-on for MailPress to manage newsletter for main categories.
* MailPress_sync_wordpress_user is an add-on for MailPress to synchronise MailPress users with WordPress users.
* MailPress_upload_media is an add-on for MailPress to use upload media wordpress facilities for images.

* NEW ! MailPress_connection_phpmail is an add-on for MailPress to replace default SMTP connection by native php mail connection.
* NEW ! MailPress_import is an add-on for MailPress providing an import API for external importers.
* NEW ! MailPress_roles_and_capabilities is an add-on for MailPress to manage capabilities.
* NEW ! MailPress_extra_form_mail_new is an add-on for MailPress to temporarily change the MailPress theme on MailPress Write page.
* NEW ! MailPress_remove_prototype is an add-on for MailPress to remove prototype.js from MailPress Write and avoid potential plugin conflicts.

* NEW Setting ! "Newsletters show at most" equivalent to "Blog pages show at most".

Supported languages : English, French	(.pot provided)

Tested with Firefox3, Internet Explorer 7, Safari 3.1 (Windows XP)

== Installation ==

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**

**See** plugin page at http://www.nogent94.com?page_id=70

== Frequently Asked Questions ==

**Parse error: syntax error, unexpected T_CONST, expecting T_OLD_FUNCTION or T_FUNCTION or T_VAR or '}' in .../wp-content/plugins/mailpress/mp-includes/class/swift/Swift.php**

Your server is running under a version of PHP that does not comply the requirements (see above).

**Fatal error: Uncaught exception 'Swift_FileException' with message 'Disk Caching failed ...'.**

The folder wp-content/plugins/mailpress/tmp is not writable

**Cannot connect with smtp, my test or send always fails**

Some web host limit the use of SMTP. First thing to do is to ask to your local technical support.
Do they provide full access to smtp and if so are there specific settings ?
If not, do they provide access to SENDMAIL, so use the addon MailPress_connection_sendmail (see zip files in the mailpress folder)
If not, do they provide access to native PHP Mail, so use the addon mailpress_connection_phpmail (see zip files in the mailpress folder)
If not, few chances you can successfully use MailPress.

**404 on MailPress Write page or cannot display MailPress Write !**

BEFORE REPORTING !

Plugin conflicts may occur with plugins spreading their code throughout the whole admin pages ...

The weirdest conflict i saw (prototype + jquery) only occured in specific browsers.

A) So try to test the admin with other browsers such as Safari or Google Chrome

B) Identify the 'conflict plugin'
    1) deactivate all your plugins except MailPress.
    2) Do the test.
    3) If ko, report a bug with full description and list of plugins used
    4) if ok, Iterate as following
      4.1) Activate one plugin
      4.2) Do the test
      4.3) If ok, go to 4.1
      4.4) If ko, report to both plugin authors
      4.5) Deactivate the 'conflict plugin'
      4.6) go to 4.1

C) if the conflict comes from the js library prototype.js that should not be in the html page of MailPress Write, 
you can tweak it by using the addon mailpress_remove_prototype (see zip files in the mailpress folder)

 **Since i use MailPress_batch_send addon, mails with more than one recipient are not saved and sent**

Check that your MailPress mails table complies with the installation of MailPress_batch_send (see mailpress_batch_send/mp-admin/includes/install.php). 
There is a new value ('unsent') for column status.

If Deactivating, Activating doesnot add the 'unsent' possible value for the status column, then you have to do it manually (with phpmyadmin for example).

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

**1.9.2**	2008/11/04

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**

** addon folder mailpress_connexion_sendmail has been renamed mailpress_connection_sendmail **

* Minor bugs fixed.
* Theme management review (theme page, theme preview before activation, mailpress themes : default, classic, ...).
* New meta box in user page to display data if any from MailPress usermeta table.
* New general setting equivalent to WordPress "Blog pages show at most".
* Introducing a new add-on "mailpress_connection_phpmail"  to replace default SMTP connection by native php mail connection.
* Introducing a new add-on "mailpress_import" providing an import API for external importers.
* Introducing a new add-on "mailpress_roles_and_capabilities" to manage capabilities.
* Introducing a new add-on "mailpress_extra_form_mail_new" to temporarily change the MailPress theme on MailPress Write page.
* Introducing a new add-on "mailpress_remove_prototype" to remove prototype.js from MailPress Write and avoid potential plugin conflicts.

1.9.1		2008/10/04

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

* With the new (optional) add-on 'MailPress_newsletter_categories' you can allow MailPress users to subscribe to such or such category/periodicity. 
Well ! this add-on is not so light and might overload your server, **so be cautious**. However, for PHP coders, it is a good example of how to generate your owns !

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
 and to use the WordPress API to propose some add-ons, making MailPress completely scalable to your needs.

1.6	  		2008/07/25

* process_img code review for BETTER SUPPORT for POST IMAGES						(mp-includes/MP_Mail.class.php)
* Display bug in mp-admin/includes/mail.php									(mp-admin/includes/mail.php)
* Adding some do_actions on Daniel's request 									(mp-admin/users.php)
For Bulk user insert. 
add-on is downloadable at http://groups.google.com/group/mailpress in the FILE section (file name 'mailpress_bulk_import.rar')
* Adding some do_actions and apply_filters on Daniel and Arthur's request 				(mp-admin/settings.php, mp-includes/MP_Mail.class.php, mp-includes/MP_Admin.class.php)
For specific settings applied to **ALL** images in the mail. 
add-on is downloadable at http://groups.google.com/group/mailpress in the FILE section (file name 'mailpress_filter_img.rar')

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





