# com.hjed.civicrm.oauth-sync

This plugin provides templates, php classes, and other tools to support other
CiviCRM plugins that connect to oauth based apis to sync users and groups.

By itself the plugin does not provide any user facing functionality.

**This plugin is not complete - do not use it yet**

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 5.4

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.hjed.civicrm.oauth-sync@https://github.com/hjed/com.hjed.civicrm.oauth-sync/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/hjed/com.hjed.civicrm.oauth-sync.git
cv en oauth_sync
```

## Usage

This plugin is provides an interface for other plugins. By itself it does nothing.

Below is the required information for plugins that use this plugin:

### The Prefix

This plugin uses a `prefix` for each connection it is managing (E.g. `jira`, `github`, `facebook`). Connection plugins 
should suply this.

### Settings

The plugin expects certain settings to be defined for each connection, this can be done by getting your plugin to include 
the value of `CRM_OAuthSync_Settings::generateSettings` in your plugin's settings array. For the most part the plugin will then manage these seetings.

### Templates

The plugin provides a number of template pages that can be used to manage connections:

#### CRM_OauthSync_Form_ConnectionSettings

The `CRM_OauthSync_Form_ConnectionSettings` class provides a template for setting the client_id and client_secret for your oauth plugin.
Simply extend this class and implement the abstract methods.

If you do not implement this class you will need to set the `(prefix)_client_id` and `(prefix)_secret` settings, before 
attempting an ouath connection.

### OAuth Consent

The plugin provides a set of helper functions for Three Legged OAuth Consent. The following code snippet demonstrates their usage. 

      // get a new state key, including the settings prefix
      $state = $oauthHelper->newStateKey();
      // generate the redirect url
      $redirect_url= CRM_OauthSync_OAuthHelper::generateRedirectUrlEncoded();
      // use inbuilt page variables to get the path to the current page (assuming $this is a page)
      CRM_JiraConnect_JiraApiHelper::oauthHelper()->setOauthCallbackReturnPath(
        join('/', $this->urlPath)
      );
      // generate the url
      $oauth_url = 'https://oauth_url.example.com/authorize?audience=api.atlassian.com&client_id=' . $client_id . '&scope=manage:jira-configuration%20offline_access&redirect_uri=' . $redirect_url . '&state=' . $state . '&response_type=code&prompt=consent'
(`$oauth_helper` is an instance of `CRM_OauthSync_OAuthHelper`)

`$oauth_url` then becomes a link you can provide to the user to go through the consent flow. The redirect will occur through 
the plugin allowing it to complete the final step in the oauth flow and retrieve the token.

### Hooks
The plugin provides a number of additional CiviCRM hooks that can be used to manage your connection. 

(This is inspired by [nz.co.fuzion.accountsync](https://github.com/eileenmcnaughton/nz.co.fuzion.accountsync/))
#### civicrm_oauthsync_consent_success

Called on the successful completion of the consent flow. Plugins may want to handle this hook to provide functionality
like selecting the correct site to sync with (if the api you are using supports multiple sites/accounts/installations/etc).

### Option Groups

Plugins must define the following option groups. 

#### {prefix}_sync_group_options

Defines the available groups in the remote system that can be synced too.

The `data_type` should be `String`. E.g. if implemented in auto_install.xml

      <OptionGroups>
        <OptionGroup>
          <name>my_prefix_sync_groups_options</name>
          <title>My Connection Groups</title>
          <data_type>String</data_type>
          <is_reserved>1</is_reserved>
          <is_active>1</is_active>
        </OptionGroup>
      </OptionGroups>
      




## Known Issues

(* FIXME *)
