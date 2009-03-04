=== MailPress ===
Contributors: andre renaut
Donate link: http:\\www.mailpress.org
Tags: mail, mails, comments, comment, subscribe, newsletters, newsletter, Wordpress, Plugin, swiftmailer, post, posts, smtp, sendmail, phpmail, notification, notifications
Requires at least: 2.7
Tested up to: 2.7
Stable tag: 2.0.1

The WordPress mailing platform

== Description ==

**REQUIRES WORDPRESS 2.7 ** REQUIRES WORDPRESS 2.7 ** REQUIRES WORDPRESS 2.7**

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**
**FOR THIS VERSION, READ THE 'Other Notes' TAG or LOG thanks!**

**Allows you** to send beautiful and styled html and plain text mails based on dedicated themes and templates.

** FULLY COMPLIANT WITH LOOK AND FEEL OF WP 2.7 ADMIN UI **

Several add-ons are available as zip files in the mailpress folder.
They are in fact WordPress plugins interacting with MailPress.
The basic idea is to keep the core of MailPress as stable as possible
and to use the WordPress API to propose some add ons, 
making MailPress completely scalable to your needs.

* MailPress_batch_send is an add-on for MailPress to send mails in batch mode.
* MailPress_bulk_import is an add-on for MailPress to import users.
* MailPress_connection_phpmail is an add-on for MailPress to replace default SMTP connection by native php mail connection.
* MailPress_connection_sendmail is an add-on for MailPress to replace default SMTP connection by SendMail connection.
* MailPress_extra_form_mail_new is an add-on for MailPress to temporarily change the MailPress theme on MailPress Write page.
* MailPress_filter_img is an add-on for MailPress to filter ALL html img tags before mailing or preview them.
* MailPress_import is an add-on for MailPress providing an import API for external importers.
* MailPress_mailing_lists is an add-on for MailPress to manage mailing lists.
* MailPress_newsletter_categories is an add-on for MailPress to manage newsletter for main categories.
* MailPress_roles_and_capabilities is an add-on for MailPress to manage capabilities.
* MailPress_sync_wordpress_user is an add-on for MailPress to synchronise MailPress users with WordPress users.
* MailPress_upload_media is an add-on for MailPress to use upload media wordpress facilities for images.
* MailPress_view_logs is an add-on for MailPress to view logs.

* NEW ! introducing MailPress_deregister_scripts is an add-on for MailPress to remove scripts from MailPress Pages and avoid potential plugin conflicts at javascript level.
* NEW ! introducing MailPress_custom_fields is just an add-on for MailPress to edit MailPress user cunstom fields.

Supported languages : English, French	(.pot provided)

Tested with Firefox3, Internet Explorer 7, Safari 3.1 (Windows XP)

== Installation ==

**REQUIRES WORDPRESS 2.7 ** REQUIRES WORDPRESS 2.7 ** REQUIRES WORDPRESS 2.7**

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**

**See** plugin page at http://www.mailpress.org

== Frequently Asked Questions ==

**REQUIRES WORDPRESS 2.7 ** REQUIRES WORDPRESS 2.7 ** REQUIRES WORDPRESS 2.7**

== Installation ==

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**

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

**REQUIRES WORDPRESS 2.7 ** REQUIRES WORDPRESS 2.7 ** REQUIRES WORDPRESS 2.7**

**2.0.1**  	2009/01/20

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**

* minor bugs
  - css stuff (thanks bigdog!)
  - mod_security HTTP404 & HTTP406 : mail-* files renamed as mail_* (thanks jimmy!)
  - some text with quotes not displaying properly
  - widget options are now used for widget only
  _ Some Google charts when changing year

* MailPress users list ability to list from a letter  

* test of basic system requirements on install.

* MailPress setting test tab now includes a random post for templates such as daily, weekly, monthly ...

* MailPress forms review (To ease customization, all texts are now in MailPress::form_defaults).

* Widget review (larger control on display + more options).

* shortcode [mailpress] now supported. Optional attributes are :
  - jq		values : 1 or 0  	(jQuery already loaded (true/false))
  - urlsubmgt	values : 1 or 0	("Manage your subscription" link ? (true/false))
  - txtbutton     for text of button
  - txtsubmgt     for text of "Manage your subscription" link
  - txtloading    for text "Loading ..."
  - newsletter	values : existing newsletter in $mp_registered_newsletters
			makes an automatic subscription to a specific newsletter.

  - mailinglist	values : id of mailinglist (only works with mailpress_mailing_lists)
			makes an automatic subscription to a specific mailinglist.

sample :   [mailpress jq="0" urlsubmgt="1" txtbutton="Click me !" mailinglist="21"]
		means display MailPress subscription form,
			jQuery library not already loaded, 
			display 'Manage your subscription' link if any,
			button has text : Click me !
			automatic subscription to mailinglist #21.


* NEW ! introducing MailPress_deregister_scripts is an add-on for MailPress to remove scripts from MailPress Pages and avoid potential plugin conflicts at javascript level.
* NEW ! introducing MailPress_custom_fields is just an add-on for MailPress to edit MailPress user custom fields.

* MailPress_deregister_scripts replacing mailpress_remove_prototype. (bug fixed since 2.0.1-RC1)
All scripts in a xml file (scripts.xml) can be "de-registered" for all mailpress pages.
The purpose of this xml file is for MailPress community to easily communicate on potential plugin conflicts 
solved by "deregistering" unwelcomed scripts on MailPress pages.


2.0	  	2008/12/11

* FULLY COMPLIANT WITH LOOK AND FEEL OF WP 2.7 ADMIN UI
* All .mo, .po, .pot add-ons are now merged in MailPress .mo, .po, .pot .

== Next features ==

**Any new idea** or **code improvement** can be posted at : contact@nogent94.com

Please donate !
