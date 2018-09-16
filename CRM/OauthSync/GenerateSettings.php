<?php

/**
 * Provides utilities for generating settings for new oauth connections
 */
class CRM_OAuthSync_Settings {

  /**
   * This function is used to generate settings for a given connection
   *
   * @param string $prefix - the prefix for the settings we generate
   * @param string $connection_name - a human readable name for the connection. Used in Title and description strings.
   * @return array the settings array
   */
  public static function generateSettings($prefix, $connection_name) {
    return array(
      /* OAuth Application Credentials */
      $prefix . '_client_id' => array(
        'group_name' => $connection_name . ' Settings',
        'group' => $prefix,
        'name' => $prefix . '_client_id',
        'type' => 'String',
        'add' => '4.4',
        'is_domain' => 1,
        'is_contact' => 0,
        'description' => $connection_name . ' Client ID',
        'title' => $connection_name . ' Client ID',
        'help_text' => '',
        'html_type' => 'Text',
        'html_attributes' => array(
          'size' => 50,
        ),
        'quick_form_type' => 'Element',
      ),
      $prefix . '_secret' => array(
        'group_name' => $connection_name . ' Settings',
        'group' => $prefix,
        'name' => $prefix . '_secret',
        'type' => 'String',
        'add' => '4.4',
        'is_domain' => 1,
        'is_contact' => 0,
        'description' => $connection_name . ' Secret',
        'title' => $connection_name . ' Secret',
        'help_text' => '',
        'html_type' => 'Text',
        'html_attributes' => array(
          'size' => 50,
        ),
        'quick_form_type' => 'Element',
      ),
      /* OAuth Token Related Settings */
      $prefix . '_token' => array(
        'group_name' => $connection_name . ' Token Control',
        'group' => $prefix . '_token',
        'name' => $prefix . '_token',
        'type' => 'String',
        'add' => '4.4',
        'is_domain' => 1,
        'is_contact' => 0,
        'description' => $connection_name . ' Token',
        'title' => $connection_name . ' Token',
        'help_text' => '',
        'html_type' => 'Text',
        'html_attributes' => array(
          'size' => 50,
        ),
        'quick_form_type' => 'Element',
      ),
      $prefix . '_refresh' => array(
        'group_name' => $connection_name . ' Token Control',
        'group' => $prefix . '_token',
        'name' => $prefix . '_refresh',
        'type' => 'String',
        'add' => '4.4',
        'is_domain' => 1,
        'is_contact' => 0,
        'description' => $connection_name . ' Refresh Token',
        'title' => $connection_name . ' Refresh Token',
        'help_text' => '',
        'html_type' => 'Text',
        'html_attributes' => array(
          'size' => 50,
        ),
        'quick_form_type' => 'Element',
      ),
      $prefix . '_expiry' => array(
        'group_name' => $connection_name . ' Token Control',
        'group' => $prefix . '_token',
        'name' => $prefix . '_expiry',
        'type' => 'Integer',
        'add' => '4.4',
        'is_domain' => 1,
        'is_contact' => 0,
        'description' => $connection_name . ' Token Expiry in Seconds since the unix epoch',
        'title' => $connection_name . ' Token Expiry (s)',
        'help_text' => '',
      ),
      $prefix . '_connected' => array(
        'group_name' => $connection_name . ' Token Control',
        'group' => $prefix . '_token',
        'name' => $prefix . '_connected',
        'type' => 'Boolean',
        'add' => '4.4',
        'is_domain' => 1,
        'is_contact' => 0,
        'description' => 'If we have succesfully connected a JIRA instance',
        'title' => $connection_name . ' Connected',
        'help_text' => '',
        'default' => false,
      ),
      $prefix . '_oauth_state' => array(
        'group_name' => $connection_name . ' Token Control',
        'group' => $prefix . '_token',
        'name' => $prefix . '_oauth_state',
        'type' => 'String',
        'add' => '4.4',
        'is_domain' => 1,
        'is_contact' => 0,
        'description' => 'Temporary setting to store unique state code',
        'title' => 'Temporary OAuth State Code',
        'help_text' => '',
        'default' => false,
      ),
      $prefix . '_callback_return_path' => array(
        'group_name' => $connection_name . ' Token Control',
        'group' => $prefix . '_token',
        'name' => $prefix . '_callback_return_path',
        'type' => 'String',
        'add' => '4.4',
        'is_domain' => 1,
        'is_contact' => 0,
        'description' => 'The page to return to once the callback is complete',
        'title' => 'The page to return to once the callback is complete',
        'help_text' => '',
        'default' => false,
      ),
      /* Sync Related Settings */
    );
  }
}

?>