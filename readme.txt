=== Loggedin ===
Contributors: joelcj91,foxe
Tags: active logins, loggedin, login, logout, limit active logins, login limit
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XUVWY8HUBUXY4
Requires at least: 3.0
Tested up to: 4.5.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Super light weight plugin to limit number of active logins for a user account.

== Description ==

By default in WordPress users can login using one account from **unlimited** devices/browsers at a time. There is no option to limit this. But using this plugin, it is possible to limit the maximum number of active logins for a user account.
You can configure the maximum number of active logins and if that limit reached, user will not be able to login from another device without logging out from at-least one previous device.


> #### LoggedIn - Features
>
> - **Limit the maximum no. of active logins for a single user**.<br />
> - **Block new logins to the same account, if maximum active logins found.**<br />
> - You can set the limit.<br />
> - No complex settings. Just one optional field.<br />
> - Super Light weight.<br />
> - Completely free to use with lifetime updates.<br />
> - Follows best WordPress coding standards.<br />
>
> [Installation](https://wordpress.org/plugins/loggedin/installation/) | [Support](http://wordpress.org/support/plugin/loggedin/) | [Screenshots](https://wordpress.org/plugins/loggedin/screenshots/)


**Important Notice**

Active logins count is calculated while user login, and count will be reduced when user logout. Closing browser without logout will not reduce the active login count. We will implement this feature soon.

**Bug Reports**

Bug reports for are always welcome. [Report here](https://thefoxe.com/bug-report/).

**WordPress Post Revision - More Details**

If you are confused with what is post revision, [refer this page](https://codex.wordpress.org/Revisions) to know more about it.


== Installation ==


= Installing the plugin - Simple =
1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for **LoggedIn** and click "*Install now*"
2. Alternatively, download the plugin and upload the contents of `loggedin.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
3. Activate the plugin
4. Go to General tab under WordPress Settings menu.
5. Find the "Maximum Active Logins" option and select the maximum number of active logins for a user account.


= Missing something? =
If you would like to have an additional feature for this plugin, [let me know](http://thefoxe.com/support/)

== Frequently Asked Questions ==

= What is the use of this plugin? =

Using this plugin you can limit your users with a maximum number of active logins for a single account.

= How does it work? =

Using [WordPress Transients API](https://codex.wordpress.org/Transients_API), we will log the count of users login. We will decrease the count if user logged out.


== Other Notes ==

= Bug Reports =

Bug reports are always welcome. [Report here](https://thefoxe.com/bug-report/).


== Screenshots ==

1. **Settings** - Set maximum no. of active logins for a user account.


== Changelog ==

= 1.0.0 (16/06/2016) =

- Initial version release.


== Upgrade Notice ==

= 1.0.0 (16/06/2016) =

- Initial version release.