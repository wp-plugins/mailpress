=== MailPress ===
Contributors: andre renaut
Donate link: http://www.mailpress.org/wiki
Tags: mail, mails, comments, comment, subscribe, newsletters, newsletter, Wordpress, Plugin, swiftmailer, post, posts, smtp, sendmail, phpmail, notification, notifications, mail, mails
Requires at least: 2.8
Tested up to: 2.8
Stable tag: 4.0.2

The WordPress mailing platform

== Description ==

**Allows you** to send beautiful and styled html and plain text mails based on dedicated themes and templates.

Since last 3.0 version, 
* Code has been deeeeeply and completely reviewed, 
* name field available
* new swiftmailer 4.0.3 (needs php 5.3 but tested with 5.2.5 locally without bugs)
* new api's for mailpress mails, ip, newsletter, import, autoresponder
* optimized size for toemail field for multi recipients mails.
* some previous add ons are integrated to MailPress 4.0

== Installation ==

1. Make sure you have already installed WordPress 2.8 or above.
1. Unzip the MailPress package.
1. Upload the mailpress folder into wp-content/plugins.
1. Make sure the wp-content/plugins/mailpress/tmp folder is writable.
1. Log in to your WordPress admin and point your browser to "Plugins > installed" page.
1. Activate MailPress plugin first.
1. Point your browser to "Settings > MailPress", fill and save the settings for each tab.
1. Activate MailPress add ons if required and update the settings if necessary.
1. Once everything is installed, use the Test tab in "Settings > MailPress" to validate your settings.

Upgrading

Do not use WordPress automatic upgrade : you will loose the content of mailpress/mp-content and mailpress/tmp folders !

1. Deactivate all add ons.
1. Deactivate MailPress.
1. Delete all add ons folders
1. Move mailpress/mp-includes/languages to mailpress/mp-content/languages
1. Delete mailpress/mp-admin, mailpress/mp-includes, mailpress/xtras folders.
1. Follow installation procedure
1. If you implemented page or category template, upgrade from mailpress/mp-content/xtras folder.

MailPress themes and templates do not need to be changed if customized in a previous MailPress release. 

Please report any bug in the mailpress google group http://groups.google.com/group/mailpress
starting your subject title with : "(MailPress 4.0)".

Thank you


== Frequently Asked Questions ==

* see wiki page http://www.mailpress.org/wiki

== Screenshots ==

1. none

== Changelog ==

** 4.0.2 ** 17/11/2009

fixes several bugs :

* bounces : (bug fix) swiftmailer (decorator plugin not working for some configs when using smtp)
* bounces : (bug fix) MP_Bounce.class.php reviewed (now includes mail header Received to detect bounces)

* themes : (bug fix) MP_Themes.class.php reviewed (replacing global wp variables by mp variables)

* mail : (bug fix) MP_Mail.class.php (not include into mail any img src file not having the following extension (jpg, jpeg, gif, png, bmp, tif))

* install files : (bug fix) replacing some tabs by spaces to comply to dbDelta format.

some changes and/or enhancements :

* write mail : now it is possible to select subscribers to a newsletter as recipients
* settings : general tab, changing "Manage subscriptions from" radio buttons by a select
* new user status : unsubscribed + corresponding event in tracking.

* text domain a php constant now (MP_TXTDOM)!
* using __CLASS__ instead of string whenever possible.

** 4.0.1 ** 09/10/2009

fixes several bugs since Oct 17, 2009 :

* form add on : (bug fix) field types settings, structure of xml file changed
* form add on : (bug fix) for captcha field types instead of session_start, now have @session_start 

* newsletter_categories add on : (bug fix) in have_post function, change $mp_general by $mp_subscriptions

* import add on : (bug fix) mailing list support in csv import.
* import add on : (bug fix) wrong csv parsing when email has digit 0 (zero) in it

* autoresponder add on : changed architecture to 'options' architecture

* Bounce class : (bug fix) if return path email is recipient email, not a bounce.
* Log class : minor updates
* Mail class : (bug fix) instead of file_get_contents now use $this->load_template to load template files => better logging when errors
* Themes class : minor updates

* mp-admin/mail.php : (bug fix) name of capability changed (previously referring to a tracking one)
* mp-admin/mails.php : (bug fix) subscriber name now included in search.
* mp-admin/user.php : (bug fix) wrong html generated in IP info meta box when ip informations not found.

** 4.0 ** 17/09/2009

== Next features ==

** available, new wiki ! http://www.mailpress.org/wiki

Please donate !
