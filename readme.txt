=== Loggedin - Limit Active Logins ===
Contributors: joelcj91,duckdev
Tags: active logins, loggedin, login, logout, limit active logins, login limit, concurrent logins
Donate link: https://paypal.me/JoelCJ
Requires at least: 4.0
Tested up to: 5.2
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Light weight plugin to limit number of active logins from an account. Set maximum number of concurrent logins a user can have from multiple places.

== Description ==

By default in WordPress users can login using one account from **unlimited** devices/browsers at a time. This is not good for everyone, seriously! With this plugin you can easily set a limit for no. of active logins a user can have.


> #### Loggedin 🔒 Features and Advantages
>
> - **Set maximum no. of active logins for a user**.<br />
> - **Block new logins to the same account, if maximum active logins found.**<br />
> - Prevent users from sharing their account.<br />
> - Useful for membership sites (for others too).<br />
> - No complex settings. Just one optional field to set the limit.<br />
> - Super Light weight.<br />
> - Filter to bypass login limit for certain users or roles.<br />
> - Completely free to use with lifetime updates.<br />
> - Follows best WordPress coding standards.<br />
>
> [Installation](https://wordpress.org/plugins/loggedin/installation/) | [Support](http://wordpress.org/support/plugin/loggedin/) | [Screenshots](https://wordpress.org/plugins/loggedin/screenshots/)

Please contribute to the plugin development in [GitHub](https://github.com/joel-james/LoggedIn).

**🔐 Important Notice**

Even if the user is closing the browser without logging out, their login session exists for period of time. So this will also considered as an active login.

== Installation ==


= Installing the plugin - Simple =
1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for **LoggedIn** and click "*Install now*"
2. Alternatively, download the plugin and upload the contents of `loggedin.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
3. Activate the plugin
4. Go to General tab under WordPress Settings menu.
5. Find the "Maximum Active Logins" option and select the maximum number of active logins for a user account.


= Missing something? =
If you would like to have an additional feature for this plugin, [let me know](https://duckdev.com/support/)

== Frequently Asked Questions ==

= How can I set the limit, and where? =

This plugin does not have a seperate settings page. But we have one configural settings to let you set the login limit.

1. Go to `Settings` page in admin dashboard.
2. Scroll down to see the option `Maximum Active Logins`.
3. Set the maximum number of active logins a user can have.

= Can I bypass this limit for certain users or roles? =

Yes, of course. But this time you are going to add few lines of code. Don't worry. Just copy+paste this code in your theme's `functions.php` file or in custom plugin:

<pre lang="php">
function f_loggedin_bypass_users( $bypass, $user_id ) {
    
    // Enter the user IDs to bypass.
    $allowed_users = array( 1, 2, 3, 4, 5 );

    return in_array( $user_id, $allowed_users );

}

add_filter( 'loggedin_bypass', 'f_loggedin_bypass_users', 10, 2 );
</pre>

Or if you want to bypass this for certain roles:

<pre lang="php">
function f_loggedin_bypass_roles( $prevent, $user_id ) {

    // Array of roles to bypass.
    $allowed_roles = array( 'administrator', 'editor' );

    $user = get_user_by( 'id', $user_id );

    $roles = ! empty( $user->roles ) ? $user->roles : array();

    return ! empty( array_intersect( $roles, $whitelist ) );

}

add_filter( 'loggedin_bypass', 'f_loggedin_bypass_roles', 10, 2 );
</pre>


== Other Notes ==

= Bug Reports =

Bug reports are always welcome - [report here](https://duckdev.com/support/).


== Screenshots ==

1. **Settings** - Set maximum no. of active logins for a user account.


== Changelog ==

= 1.1.0 (06/06/2019) =

- Code improvements.
- Added cleanup on plugin uninstall.
- Added review notice.

= 1.0.1 (02/07/2016) =

- Fixing misspelled variable.

= 1.0.0 (16/06/2016) =

- Initial version release.


== Upgrade Notice ==

= 1.1.0 (06/06/2019) =

- Code improvements.
- Added cleanup on plugin uninstall.
- Added review notice.
