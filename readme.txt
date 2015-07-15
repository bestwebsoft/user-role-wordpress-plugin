=== User Role by BestWebSoft ===
Contributors: bestwebsoft
Donate link: http://bestwebsoft.com/donate/
Tags: access, button, capability, change capabilities, confirmation dialogue, editor, groups, permission, recover, recover button, recover capabilites button, recover role capabilities, reset, reset settings, reset role capabilites, restore role capabilties, restore settings, role, role capabilitites, role capabilities groups, security, settings, user, uzer, user role, user rol, uzer rol, user role plugin
Requires at least: 3.6
Tested up to: 4.2.2
Stable tag: 1.4.8
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

User Role plugin allows to change wordpress user role capabilities.

== Description ==

The User Role plugin allows you to change wordpress role capabilities. It is a very useful tool when your site has a bunch of visitors or subscribers. The plugin has intuitive and convenient interface so all the site visitors can be easily sorted by roles with a couple of clicks.

http://www.youtube.com/watch?v=O7OBFHn0_wU

<a href="http://www.youtube.com/watch?v=gz9BkouavtU" target="_blank">Video instruction on Installation</a>

<a href="http://wordpress.org/plugins/user-role/faq/" target="_blank">FAQ</a>

<a href="http://support.bestwebsoft.com" target="_blank">Support</a>

<a href="http://bestwebsoft.com/products/user-role/?k=dabe729fc0e7bef82e30dcb21a6cefc3" target="_blank">Upgrade to Pro Version</a>

= Features =

* Actions: You can recover wordpress role capabilities.
* Interface: Recover button has confirmation dialogue, so that you won't reset your settings ocasionaly.
* Display: All role capabilities are separated into groups.

= Translation =

* Russian (ru_RU)
* Ukrainian (uk)

If you would like to create your own language pack or update the existing one, you can send <a href="http://codex.wordpress.org/Translating_WordPress" target="_blank">the text of PO and MO files</a> for <a href="http://support.bestwebsoft.com" target="_blank">BestWebSoft</a> and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO files  <a href="http://www.poedit.net/download.php" target="_blank">Poedit</a>.

= Technical support =

Dear users, our plugins are available for free download. If you have any questions or recommendations regarding the functionality of our plugins (existing options, new options, current issues), please feel free to contact us. Please note that we accept requests in English only. All messages in another languages won't be accepted.

If you notice any bugs in the plugin's work, you can notify us about it and we'll investigate and fix the issue then. Your request should contain URL of the website, issues description and WordPress admin panel credentials.
Moreover we can customize the plugin according to your requirements. It's a paid service (as a rule it costs $40, but the price can vary depending on the amount of the necessary changes and their complexity). Please note that we could also include this or that feature (developed for you) in the next release and share with the other users then.
We can fix some things for free for the users who provide translation of our plugin into their native language (this should be a new translation of a certain plugin, you can check available translations on the official plugin page).

== Installation ==

1. Deactivate the plugin if you have the previous version installed.
2. Extract "user-role.zip" archive content to the "/wp-content/plugins/user-role" directory.
3. Activate "user-role" plugin via 'Plugins' menu in WordPress admin menu.

<a href="https://docs.google.com/document/d/1uEr5TxEhupny3p6luzUNF1a6rNXes24359HkEGREtV4/edit" target="_blank">View a Step-by-step Instruction on User Role Installation</a>.

http://www.youtube.com/watch?v=gz9BkouavtU

== Frequently Asked Questions ==

= What default settings will be restored if I click on the Recover capabilities button? =

When the User Role plugin is activated it creates a backup of current roles and capabilities for your main blog and it will be restored if you click on the Recover capabilities button.

= When I delete the User Role plugin will the settings of privileges be changed back? =

No. They will stay changed unless you click on the Recover capabilities button.

= How can I use the other language files with User Role? =

Here is an example for Russian language files.

1. In order to use another language for WordPress it is necessary to set a WordPress version to the required language and in the configuration wp file - `wp-config.php` in the line `define('WPLANG', '');` you should enter `define('WPLANG', 'ru_RU');`. If everything is done properly the admin panel will be in Russian.
2. Make sure the files `ru_RU.po` and `ru_RU.mo` are present in the plugin (the folder "Languages" in the plugin root).
3. If there are no such files you should copy the other files from this folder (for example, for German or Italian) and rename them (you should write `ru_RU` instead of `de_DE` in both files).
4. The files can be edited with the help of the program Poedit - http://www.poedit.net/download.php - please download this program, install it, open the file using this program (the required language file) and for each line in English you should write translation in Russian.
5. If everything is done properly all lines will be in Russian in the admin panel and in the front-end.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<a href="http://support.bestwebsoft.com" target="_blank">http://support.bestwebsoft.com</a>). If no, please provide the following data along with your problem's description:

1. the link to the page where the problem occurs
2. the name of the plugin and its version. If you are using a pro version - your order number.
3. the version of your WordPress installation
4. copy and paste into the message your system status report. Please read more here: <a href="https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/edit" target="_blank">Instuction on System Status</a>

== Screenshots ==

1. Plugin interface on a single blog wordpress setup.
2. Plugin flat view in WordPress Multisite network admin panel.
3. Plugin table view in WordPress Multisite network admin panel.

== Changelog ==

= V1.4.8 - 15.07.2015 =
* Update : Input maxlength is added.
* Update : BWS plugins section is updated.

= V1.4.7 - 22.05.2015 =
* Bugfix : We fixed plugins work with Table view on the Network. 
* Bugfix : We fixed the selection of current role when switching between view modes on the Network.
* Update : We updated all functionality for wordpress 4.2.2.

= V1.4.6 - 27.03.2015 =
* Update : BWS plugins section is updated.
* Update : We updated all functionality for wordpress 4.1.1.

= V1.4.5 - 09.01.2015 =
* Update : We updated all functionality for wordpress 4.1.

= V1.4.4 - 13.11.2014 =
* Bugfix : Plugin optimization is done.
* Update : BWS plugins section is updated.

= V1.4.3 - 17.09.2014 =
* Bugfix : Bug with disappearing of capabilities from the list was fixed.
* Bugfix : When plugin is deleted the roles are restored to the capabilities that were before the plugin was installed.
* Update : We updated all functionality for wordpress 4.0.

= V1.4.2 - 08.08.2014 =
* Update : We updated all functionality for wordpress 4.0-beta3.
* Budfix : The bug with breaking words is fixed.
* Budfix : Security Exploit was fixed.

= V1.4.1 - 22.05.2014 =
* Bugfix : Problem with Masonry in WordPress versions below 3.9 on the plugin settings page is fixed.

= V1.4 - 01.04.2014 =
* Bugfix : Repair roles and save roles functions were refactored.
* Bugfix : Small css and js bugs were fixed.
* New : Screenshots were remade.

= V1.3 - 26.03.2014 =
* Bugfix : Code refactored.
* NEW : Essential interface remade.

= V1.2 - 20.03.2014 =
* Bugfix : Code was refactored.
* NEW : Screenshots were remade.
* NEW : Plugin name was changed.

= V1.1 - 18.03.2014 =
* Bugfix : Capabilities print bug was fixed when new settings saved.
* NEW : Screenshots were remade.
* NEW : Status message was added if multiple roles recovered.

= V1.0 - 13.03.2014 =
* Bugfix : Interface version 2 tab was deleted from single blog Wordpress installation.
* NEW : Css-style was added for Internet Explorer 10.

== Upgrade Notice ==

= V1.4.8 =
Input maxlength is added. BWS plugins section is updated.

= V1.4.7 =
We fixed plugins work with Table view on the Network.  We fixed the selection of current role when switching between view modes on the Network. We updated all functionality for wordpress 4.2.2.

= V1.4.6 =
We updated all functionality for wordpress 4.1.1. BWS plugins section is updated.


= V1.4.5 =
We updated all functionality for wordpress 4.1.

= V1.4.4 =
Plugin optimization is done. BWS plugins section is updated.

= V1.4.3 =
Bug with disappearing of capabilities from the list was fixed. When plugin is deleted the roles are restored to the capabilities that were before the plugin was installed. We updated all functionality for wordpress 4.0.

= V1.4.2 =
We updated all functionality for wordpress 4.0-beta3. The bug with breaking words is fixed. Security Exploit was fixed.

= V1.4.1 =
Problem with Masonry in WordPress versions below 3.9 on the plugin settings page is fixed.

= V1.4 =
Repair roles and save roles functions were refactored. Small css and js bugs were fixed. Screenshots were remade.

= V1.3 =
Code was refactored. Essential interface was remade.

= V1.2 =
Code was refactored. Screenshots were remade. Plugin name was changed.

= V1.1 =
Capabilities print bug was fixed when new settings saved. Screenshots were remade. Status message was added if multiple roles recovered.

= V1.0 =
The code refactoring. Css-style was added.
