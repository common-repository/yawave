=== Yawave ===
Contributors: @yawaveadmin on WordPress.org
Tags: yawave,content marketing,publizieren,automatisieren,analysieren,kommunikation
Requires at least: 5.1
Tested up to: 6.5.2
Requires PHP: 5.6
Stable tag: 2.9.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
== Description ==
 
Data-Driven Experience Platform

Generate real-time user data and personalise the experience for more relevant interactions, increased engagement, and higher loyalty. More details and use cases on https://yawave.com

Activate this plugin to leverage features of yawave.

== Installation ==

1. Either upload folder from extracted zip file to your plugins directory or install the plugin via WordPress admin panel 'Add New'. 
1. Activate the plugin
1. Navigate to Yawave > Settings > Authentication tab in order to configure the plugin. Enter your IDs and Credentials provided by your yawave Application Webhook Connection configuration. 
1. In order to check the integration create or update a publication in your yawave application. Changes will be reflected in your posts.

== Screenshots ==

1. Information page of the settings and available shortcodes
2. Authentication setup to connect with yawave
3. Import settings

== Changelog ==
1.0
Start of the yawave wordpress plugin

1.0.1
update plugin description

1.0.2
update shortcode output -> show cover description

1.0.3
update versions number for update in wordpress

1.1
add pagination in publication shortcode output

1.1.1
add shortcode information to table in porals, categories and publications. code cleanup and publication update process optimization.

1.1.2
code cleanup

1.1.3
live and development mode fixup

1.1.4
add settings to import as post type post or publication

1.1.5
add shortcode for place action buttons in template, category fix when category already exists in wordpress

2.0
Liveblog integration

2.0.1
liveblog bugfixes, debug method

2.0.2
liveblog bugfixes: show action buttons in publications view

2.0.4
adding video description value to content

2.0.5
adding KPI metrics for publications

2.0.6
add possibility to use the action button shortcode also in the posts loop

2.0.7
add possibility to add first action tool in publications output shortcode, set default language when translation tool is intalled

2.0.8.2
add correct functionality for action button type other, remove some small bugs

2.0.9
bugfix: check if select any category in publication, if not, set default wordpress category

2.0.10
bugfix: portal update now working

2.0.11
bugfix: if more languages, sdk will loaded in the right language now and some other small bugfixes

2.0.12
save header image to publication, save header focus points in custom field, replace landingpage html things with nothing, add support for focuspoint integration

2.1
adding styles support of yawave and some little bugifxes

2.1.1
bugfix with sdk language detection

2.1.2
bugfix with elementor usage

2.1.3
add shortcode klickable featured image (publication image)

2.2
add new feature to set yawave author as wordpress author (yawave settings -> import settings)

2.2.1
add information on the plugin and author page

2.3
add gutenberg block for adding publication in a page or post

2.4 
WPML Support, Multilanguage Support

2.5
limit debug output in admin, fix category sync

2.5.1
add alternativeLocationUrl function

2.5.2
bugfixes and new style for output

2.5.3
add realmname option in yawave settings

2.5.4
performance bugfixes

2.5.5
liveblog bugfixes

2.5.6
sdk publication id in javascript

2.5.7
php 8 update

2.7.0
minor fixes WPML workflow

2.7.0
bugfix

2.7.3
debug insert in whole process

2.7.5
bugfixes

2.7.6
bugfixes

2.7.7
bugfixes

2.7.8
bugfixes

2.7.9
bugfixes

2.8.0
bugfixes

2.8.1
bugfixes

2.8.3
Changes that had to be done due to performance and category hierarchy consistency:

- new/changed categories and tags now consumed from webhook and hence only created or changed for those categories. Using webhook now enabled possibility of deleting categories or tags also in WP if they get deleted in yawave

- on publication adding/changing: if a category or tag reference by publication is missing in WP it will be added on the fly

- If a subcategory gets added or changed for which parent category is missing in WP, parent gets created first, then subcategory

- main category is a separate field

open: 

- adding a parent category in 1 language like DE with 2 subcategories (DE, EN): subcategory EN is not added in hierarchy under parent category

- deleting just a language in a category will no delete that language in WP → not critical, won't be done

Hinweis/Convention: das Kategorie-Handling im WP bedingt, dass der Member bei Kategorien, die im DE und EN gleich lauten (und somit den gleichen localised Slug hätten) wie Arena (Slug 'arena') etc. nicht übersetzt werden, d.h. der Member soll dann EN weglassen. Sonst führt das im WP zu Slug der Art 'arena' (für DE) und 'arena-2' (für EN) wegen Sicherstellung der Slug-Eindeutigkeit. Slugs müssen in WP über die Sprachen hinweg eindeutig sein. Fachlich, technisch und SEO-technisch will man aber für dasselbe keine unterschiedlichen Slugs wie 'arena' und 'arena-2' oder gar 'arena-de' und arena-en'

2.8.4
bugfix

2.8.5
bugfix

2.8.6
- fixed pushing wp perma link to yawave for all publication languages 
- fixed assigning/unassigning portal and order weight to posts in all languages

2.8.7
bugfix

2.8.8
bugfix

2.9.0
shortcode bugfix

2.9.1
sdk bugfix