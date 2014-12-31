=== Plugin Name ===
Contributors: welcher
Donate link: http://www.ryanwelcher.com/donate/
Tags: users, media, posts, pages, admin
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Limits Post/Pages/Media admin screens to only display the current user's content. Media can be shared by providing an ID list.

== Description ==

This is a simple plugin that helps to clean up the admin side of WordPress by only showing the currently logged in user's content the Post/Page/Media pages.

For the Media list, you can provide a list of user ID's who's Media will also be available to logged in users. It is highly recommended to create a user for this purpose.


__Credits__

This plugin was inspired by an article by [Sarah Gooding](http://premium.wpmudev.org/blog/author/pollyplummer/) on [wpmudev](http://premium.wpmudev.org/blog/how-to-limit-the-wordpres-posts-screen-to-only-show-authors-their-own-posts/) and uses portions of that code. Please see the source code for further details.

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add any additional user id's in the settings page.

== Frequently Asked Questions ==

= Why would I want to give users access to other users media? =

This would allow you to create set of standard or default media available to all users. In the case where users are creating content and don't have an image, they can choose one from the "Media Pool".

= Why create a new user just for media? =

If you create all of your site content with one user and allow that user's media to be shared, then other users could get to your users content from the Media Library page and (depending on the role assiged to them) potentially modify the content.

You can use the above to your advantage by creating example content for users and uploading media to it.


== Changelog ==

= 1.0 =
* plugin created

= 1.0.1 =
* bug fix for undefined variable on re-activation because I'm a hack.
* added proper cleanup if plugin is deleted - don't know why you'd want to do something like that, but there it is.

= 1.0.2 =
* minor fixes for the Media Library limitation
