=== MailPress ===
Contributors: andre renaut
Donate link: http://www.mailpress.org/wiki
Tags: mail, mails, comments, comment, subscribe, newsletters, newsletter, Wordpress, Plugin, swiftmailer, post, posts, smtp, sendmail, phpmail, notification, notifications, mail, mails, contact form
Requires at least: 2.9
Tested up to: 3.0
Stable tag: 5.0

The WordPress mailing platform

== Description ==

1. Style your html and plain text mails with dedicated themes and templates.
1. Double opt-in subscription.
1. Subscriptions to Comments, Newsletters/Post notifications and even to Mailing lists.
1. Newsletters/Post notifications on a per post, daily, weekly, monthly basis.
1. Optional : full control on all mails sent by WordPress.

**Follow Installation or Upgrade guidelines**

**Never use WordPress automatic upgrade :** you will loose the customized content of mailpress/mp-content and mailpress/tmp folders !

ver 5.0 brings plenty of new features :

Add-ons :

1. Dedicated 'MailPress Add-ons' admin page for add-ons under Plugins menu (see new Newsletter and Comment autonomous add-ons).
1. Newsletter and Comment are autonomous add-ons now. If you previously used them, do not forget to activate them.

Mails & Newsletters :

1. Newsletter (or Comment) is an autonomous add-on now. Activate it under Plugins > MailPress Add-ons .
1. Newsletters are now declared in xml files where theme, template, schedule and much more can be customized (mailpress/mp-content/advanced/newsletters).
1. Mail Api reviewed :
 * $this->the_content() supports the 'more' link
 * $this->the_image() extracts first image of current post (html only!)
 * html to plaintext post content convertion reviewed
 * ...

Admin :

1. New ! Synchronize button to generate plaintext from html in Add mail.
1. User admin pages : New design for list, filters, edit.
1. Mail admin pages : New design for edit.

1. New ! add-on Newsletter : replaces the previous built-in MailPress Newsletter subscription/schedule/process facilities.
1. New ! add-on Comment    : replaces the previous built-in MailPress Comment subscription/process facilities.
1. New ! add-on Post       : to add wp post(s) to a standard mailpress mail. requires a specific template 'manual' (see sample in twentyten mp theme).
1. New ! add-on Tracking_rewrite_url : Rewrite tracking urls : See .htaccess in mp-content/xtras/mp_tracking_rewrite_url + Requires Tracking add-on.
1. New ! add-on Tracking_ga : Track mails activity to your site with google analytics : Not compatible with Tracking add-on.
1. New ! add-on Wp_Cron    : to see what is scheduled in wp_cron.

1. Add-on Tracking  : New design for mail tracking & settings + new tracking modules + new user tracking page.
1. Add-on Roles & Capabilities : New design for settings.
1. Add-on Form : New reCaptcha field [http://recaptcha.net/].

Themes :

1. All themes reviewed.
1. New theme 'twentyten'.

Some technical stuff :

1. Switmailer 4.0.6
1. jQuery 1.4 supported.
1. Optional : possibility to externalize and/or rename the mp-content folder using an optional mailpress-config.php file.

Some bugs fixed of course (see changelog)

Please report any bug in the mailpress google group http://groups.google.com/group/mailpress
starting your subject title with : "(MailPress 5.0)".

Thank you

== Installation ==

1. Make sure you have already installed WordPress 2.9 or above.
1. Unzip the MailPress package.
1. Upload the mailpress folder into wp-content/plugins.
1. Make sure the wp-content/plugins/mailpress/tmp folder is writable.
1. Log in to your WordPress admin and point your browser to "Plugins > installed" page.
1. Activate MailPress plugin first.
1. Point your browser to "Settings > MailPress", fill and save the settings for each tab (General, SMTP, Test).
1. Activate MailPress add-ons (Plugins > MailPress Add-ons) if required and update the settings if necessary.
1. Once everything is installed, use the Test tab in "Settings > MailPress" to validate your settings.

**Upgrading**

**Never use WordPress automatic upgrade** : you will loose the content of mailpress/mp-content and mailpress/tmp folders !

1. Point your browser to "Plugins > installed" page and deactivate all add ons and MailPress.
1. Save mailpress/tmp folder + your MP theme and/or form templates if customized.
1. Delete wp-content/plugins/mailpress folder.
1. Unzip the MailPress package.
1. Upload the mailpress folder into wp-content/plugins.
1. Make sure the wp-content/plugins/mailpress/tmp folder is writable.
1. Restore mailpress/tmp folder + your MP theme/form templates if any.
1. If you implemented page or category template, upgrade from mailpress/mp-content/xtras folder.
1. Refresh "Plugins > installed" page and activate MailPress plugin first.
1. Activate MailPress previous add-ons (Plugins > MailPress Add-ons) + new ones such as Newsletter or Comment if previously used.

MailPress themes and templates do not need to be changed if customized in a previous MailPress release. 

== Upgrade Notice ==

**Never use WordPress automatic upgrade** : you will loose the content of mailpress/mp-content and mailpress/tmp folders !

1. Point your browser to "Plugins > installed" page and deactivate all add ons and MailPress.
1. Save mailpress/tmp folder + your MP theme and/or form templates if customized.
1. Delete wp-content/plugins/mailpress folder.
1. Unzip the MailPress package.
1. Upload the mailpress folder into wp-content/plugins.
1. Make sure the wp-content/plugins/mailpress/tmp folder is writable.
1. Restore mailpress/tmp folder + your MP theme/form templates if any.
1. If you implemented page or category template, upgrade from mailpress/mp-content/xtras folder.
1. Refresh "Plugins > installed" page and activate MailPress plugin first.
1. Activate MailPress previous add-ons (Plugins > MailPress Add-ons) + new ones such as Newsletter or Comment if previously used.

MailPress themes and templates do not need to be changed if customized in a previous MailPress release. 

== Frequently Asked Questions ==

* see wiki page http://www.mailpress.org/wiki

== Screenshots ==

1. none

== Changelog ==

** 5.0 ** 06/13/2010

Changes & Enhancements :

1. Add-ons specific admin page (Plugins > MailPress Add-ons) 

* for developpers, more info in mp-content/add-ons/readme.txt

1. Comment

* becomes an autonomous add-on
* Settings > MailPress > subscriptions shows a disabled checked option as a reminder
* Subscriber to comments to a post now have a link to manage their subscriptions instead of a checked box.

1. Newsletter

* becomes an autonomous add-on
* newsletter declarations are now stored in xml files (mp-content/newsletters).

1. Mailing lists

* list code review.

fixes several bugs since 4.0.2 released Nov 17, 2009 :

1. bounces : 

* (bug fix) code sequence changed for connect/disconnect to db

1. pluggable :  

* (bug fix) password reset was not working : invalid link

1. tracking : 

* (bug fix) better detections of links to track.
* (bug fix) mp-admin/includes/settings/tracking.php : php syntax error.
* (bug fix) changing '&amp;amp;' to '&' before storing original link.
* (bug fix) tallying opened + clicked per day.

1. mail links

* (review) mp-includes/class/MP_Mail_links.class.php

1. Dashboard widgets :

* (bug fix) subscriber activity.
* code review for some widgets using google charts.



**Please Donate** https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=andre%2erenaut%40gmail%2ecom&lc=US&item_name=MailPress&item_number=gg&amount=5%2e00&currency_code=EUR&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHostedGuest
