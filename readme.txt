=== MailPress ===
Contributors: andre renaut
Donate link: http://www.mailpress.org/wiki
Tags: mail, mails, comments, comment, subscribe, newsletters, newsletter, Wordpress, Plugin, swiftmailer, post, posts, smtp, sendmail, phpmail, notification, notifications
Requires at least: 2.8
Tested up to: 2.8
Stable tag: 4.0

The WordPress mailing platform

== Description ==

This is the official version of MailPress 4.0

* Code has been deeeeeply and completely reviewed, 
* name field available
* new swiftmailer 4.0.3 (needs php 5.3 but tested with 5.2.5 locally without bugs)
* new api's for mailpress, ip, newsletter, import
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

** 4.0 ** 17/09/2009

== Next features ==

** available, new wiki ! http://www.mailpress.org/wiki

Please donate !