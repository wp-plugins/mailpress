=== MailPress ===
Contributors: andre renaut
Donate link: http://www.mailpress.org
Tags: mail, mails, comments, comment, subscribe, newsletters, newsletter, Wordpress, Plugin, swiftmailer, post, posts, smtp, sendmail, phpmail, notification, notifications
Requires at least: 2.7
Tested up to: 2.7
Stable tag: 3.0.1

The WordPress mailing platform

== Description ==

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**
**FOR THIS VERSION, READ THE 'Other Notes' TAG or LOG thanks!**

**Allows you** to send beautiful and styled html and plain text mails based on dedicated themes and templates.

** FULLY COMPLIANT WITH LOOK AND FEEL OF WP 2.7 ADMIN UI **

Several add-ons are available as zip files in the mailpress folder.
They are in fact WordPress plugins interacting with MailPress.
The basic idea is to keep the core of MailPress as stable as possible
and to use the WordPress API to propose some add ons, 
making MailPress completely scalable to your needs.

* MailPress_autoresponder is an addon for MailPress to manage autoresponders (based on wp-cron).
* MailPress_batch_send is an add-on for MailPress to send mails in batch mode.
* MailPress_bulk_import is an add-on for MailPress to import users.
* MailPress_connection_phpmail is an add-on for MailPress to replace default SMTP connection by native php mail connection.
* MailPress_connection_sendmail is an add-on for MailPress to replace default SMTP connection by SendMail connection.
* MailPress_deregister_scripts is an add-on for MailPress to remove scripts from MailPress Pages and avoid potential plugin conflicts at javascript level.
* MailPress_extra_form_mail_new is an add-on for MailPress to temporarily change the MailPress theme on MailPress Write page.
* MailPress_filter_img is an add-on for MailPress to filter ALL html img tags before mailing or preview them.
* MailPress_import is an add-on for MailPress providing an import API for external importers.
* MailPress_mail_custom_fields is an add-on for MailPress to edit MailPress mail custom fields.
* MailPress_mailing_lists is an add-on for MailPress to manage mailing lists.
* MailPress_newsletter_categories is an add-on for MailPress to manage newsletter for main categories.
* MailPress_roles_and_capabilities is an add-on for MailPress to manage capabilities.
* MailPress_sync_wordpress_user is an add-on for MailPress to synchronise MailPress users with WordPress users.
* MailPress_tracking is an addon for MailPress to track the mails/users activity.
* MailPress_upload_media is an add-on for MailPress to use upload media wordpress facilities for images.
* MailPress_user_custom_fields is an add-on for MailPress to edit MailPress user custom fields.
* MailPress_view_logs is an add-on for MailPress to view logs.

* !! MailPress_reset : read carefully the readme.txt in the add-on folder.

Supported languages : English, French	(.pot provided)

Tested with Firefox3, Internet Explorer 7, Safari 3.1, Google Chrome (Windows XP)

== Installation ==

**See** plugin page at http://www.mailpress.org

== Frequently Asked Questions ==

* on plugin page at http://www.mailpress.org
* on welcome page at http://groups.google.com/group/mailpress/web/welcome-page

== Screenshots ==

1. Subscription form
2. MailPress dashboard widgets
3. MailPress widget
4. Write
5. Preview Mail (html)
6. Manage mails
7. View Mail (html)
8. Themes
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

** 3.0.1 ** 2009/04/20

* Minor changes :
 - some changes about w3c recommendation requiring a space before /> for empty elements
 - some text changes accordingly
 - addon files renamed for consistency
 - preparing wp2.8
 - mails involved in autoresponders have a specific icon in mails list (if using mailpress_autoresponder).
 - new error message in MailPress form when subscriber cannot be inserted in the db.
 - PHP errors removed in mp logs.
 - bug fixed : Losing attachements when restoring a revision.
 - bug fixed : inappropriate set_error_handler callback function in MP_Log.
 - bug fixed : safe mode tested before set_time_limit(0).
 - bug fixed : attachements not included in preview when a revision is active.

3.0	  	2009/04/12

* NEW ! introducing MailPress_autoresponder is just an addon for MailPress to manage autoresponders (based on wp-cron).
* NEW ! introducing MailPress_user_custom_fields is just an add-on for MailPress to edit MailPress user custom fields. (replacing former MailPress_custom_fields addon)
* NEW ! introducing MailPress_mail_custom_fields is just an add-on for MailPress to edit MailPress mail custom fields.
* NEW ! introducing MailPress_tracking is just an addon for MailPress to track the mails/users activity.

* NEW ! introducing MailPress_reset : read carefully the readme.txt in the add-on folder.

* bugs :
  - better support for arabic characters, umlaut etc ...
  - replacing $url <--> $args->subscribe for confirmation subscription default mail (thanks chonder!)
  - MP_User update_meta bug in updating a metadata (thanks john!)
  - a tiny bug when updating the widget fields in Appearance>widgets
  - better support of mail and user meta data when only one recipient in Write page.
  - replacing 'Confirm Subscription' to 'Subscription confirmed'.

* big changes :
 + Mail page :
	- Mails can have attachements now ! (swfuploader (wp standard) and my homemade uploader available).
	- Big code review on the javascript of the page (autosave.js rewritten).

 + Themes :
	- Mail page with optional template now (call it default.php) : Thanks to Joe the coder (ah ah!).
	- Specific optional plaintext folder for your mailpress theme.
	see default.php samples in default theme folder and plaintext subfolder with the new $this->the_content() function call.

 + Newsletters :
	- You can decide which newsletters are default ones
		* general settings page has been changed accordingly
		* in order to keep users settings, be aware that changing the status of a newsletter can lead to big updates in the mailpress users tables.
	- Some sites are facing issues with duplicate newsletters
		* Building newsletters (and sending if not using MailPress_batch_send) relies now on a new wp cron job : mp_build_newsletters .

 + Widget / Subscription form :
	- Submit button id has been renamed to 'mp_submit'.
	- new class MP_Widget.class.php
		contains all code related to the widget and subscription form
	- new behavior on email field when on focus and on blur (thanks Daniel!)

 + Published posts prior to install or last activation
	- can be modified, they will not be mailed.

 + MailPress meta datas :
	- new class MP_Mailmeta.class.php
	- new class MP_Usermeta.class.php

 + MP_Mail.class.php code review
	- function 'build_mail_content' has been rewritten.
		as well as all related 'build_mail_content' functions (get_header, get_footer, get_stylesheet, get_sidebar).
	- permalinks for subscription management page/category when used (thanks Daniel!)

== Next features ==

**Any new idea** or **code improvement** can be posted at : contact@nogent94.com

Please donate !
