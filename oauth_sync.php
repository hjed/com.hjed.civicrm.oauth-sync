<?php

require_once 'oauth_sync.civix.php';
use CRM_OauthSync_ExtensionUtil as E;

require_once 'CRM/OauthSync/GenerateSettings.php';
require_once 'CRM/OauthSync/Form/ConnectionSettings.php';
require_once 'CRM/OauthSync/CRM_OauthSync_OAuthHelper.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function oauth_sync_civicrm_config(&$config) {
  _oauth_sync_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function oauth_sync_civicrm_xmlMenu(&$files) {
  _oauth_sync_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function oauth_sync_civicrm_install() {
  _oauth_sync_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function oauth_sync_civicrm_postInstall() {
  _oauth_sync_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function oauth_sync_civicrm_uninstall() {
  _oauth_sync_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function oauth_sync_civicrm_enable() {
  _oauth_sync_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function oauth_sync_civicrm_disable() {
  _oauth_sync_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function oauth_sync_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _oauth_sync_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function oauth_sync_civicrm_managed(&$entities) {
  _oauth_sync_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function oauth_sync_civicrm_caseTypes(&$caseTypes) {
  _oauth_sync_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function oauth_sync_civicrm_angularModules(&$angularModules) {
  _oauth_sync_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function oauth_sync_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _oauth_sync_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function oauth_sync_civicrm_entityTypes(&$entityTypes) {
  _oauth_sync_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_fieldOptions()
 *
 * Used to keep the list of sync groups up to date
 *
 * @param string $entity the name of the entity
 * @param string $field the field we are looking up
 * @param array $options the list of options as a reference to modify
 * @param array $params params sent to the lookup field
 */
function oauth_sync_civicrm_fieldOptions($entity, $field, &$options, $params) {
//  print_r($field);
//  if($entity == 'Group') {
//    print $field;
//    print "\n";
//    print_r($params);
//    print "\n";
//    print_r($options);
//    print "\n";
//    CRM_Core_BAO_CustomField::getOptionGroupDefault()
//    print CRM_Core_BAO_CustomField::getCustomFieldID("JIRA_Groups", "JIRA_Sync_Settings");
//    print "\n";
//  }
//  $field_id = CRM_Core_BAO_CustomField::getCustomFieldID($field);
//  $field = CRM_Core_BAO_CustomField::getFieldObject($field_id);
//  print_r($field);
//  print_r($options);
//  if ($entity == 'Group' && (substr($field,0, 6) == 'custom')) {
//    if($field->option_group_id == 'jira_sync_group_options') {
//      print_r($field);
//      $field_id = CRM_Core_BAO_CustomField::getCustomFieldID($field);
//      $field = CRM_Core_BAO_CustomField::getFieldObject($field_id);
//      print_r($field);
//      print_r($options);
//      print 'aaa';
//    }
//  }
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function oauth_sync_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function oauth_sync_civicrm_navigationMenu(&$menu) {
  _oauth_sync_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _oauth_sync_civix_navigationMenu($menu);
} // */

/**
 * Implementation of hook_civicrm_pre
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pre
 */
function oauth_sync_civicrm_pre( $op, $objectName, $id, &$params ) {
  // TODO: handle user deletion (plugins must handle their own email matching changes)
}

/**
 * Implementation of hook_civicrm_post
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
function oauth_sync_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  // handle change to sync groups
  if ($objectName == 'Group' ) {
    if ($op == 'edit' || $op == 'create') {
      // check if any of our prefixed methods have changed
      // we don't need to check for removals as that just means we don't sync them next time a sync action
      // is performed.
      $customFields = CRM_Core_BAO_CustomValueTable::getEntityValues($objectId, $objectName, NULL, TRUE);
//      print_r($customFields);
      foreach (CRM_OauthSync_OAuthHelper::getHelperArray() as $helper) {
        $prefix = $helper->settingsPrefix;
        $groupsId = CRM_Core_BAO_CustomField::getCustomFieldID($prefix . "_sync_settings");
        $remoteGroup = $customFields[$groupsId];
        print_r($remoteGroup);
        if ($remoteGroup != null) {
          // we have groups
          print_r($remoteGroup);
          // retrieve this each time for consistency
          $groupContacts = CRM_Contact_BAO_Group::getGroupContacts($objectId);
          // the getGroupContacts method returns a list of contact objects containing just their ids
          // their ids are also the keys of the array.
          $groupContacts = array_keys($groupContacts);

          print_r($groupContacts);
          $groupMembers = array();
          CRM_Utils_Hook::singleton()->invoke(
            array('remoteGroupName', 'members'),
            $remoteGroup,
            $groupMembers,
            CRM_Utils_Hook::$_nullObject,
            CRM_Utils_Hook::$_nullObject,
            CRM_Utils_Hook::$_nullObject,
            CRM_Utils_Hook::$_nullObject,
            'civicrm_oauthsync_' . $prefix . '_get_remote_user_list'
          );

          print_r($groupMembers);
          print_r($groupContacts);
          // do a diff and get the groups in sync in a none destructive manner
          $toAddLocal = array_diff($groupMembers, $groupContacts);
          $toAddRemote = array_diff($groupContacts, $groupMembers);
          print_r($toAddRemote);
          print_r($toAddLocal);

          CRM_Contact_BAO_GroupContact::addContactsToGroup($toAddLocal, $objectId);

          # we don't need to remove any users here
          $emptyArray = array();
          // add the remote members
          CRM_Utils_Hook::singleton()->invoke(
            array('remoteGroupName', 'toRemove', 'toAdd'),
            $remoteGroup,
            $emptyArray,
            $toAddRemote,
            CRM_Utils_Hook::$_nullObject,
            CRM_Utils_Hook::$_nullObject,
            CRM_Utils_Hook::$_nullObject,
            'civicrm_oauthsync_' . $prefix . '_update_remote_users'
          );

        }
      }
    }
  } elseif($objectName == 'GroupContact') {
    $groupCustomFields = CRM_Core_BAO_CustomValueTable::getEntityValues(
      CRM_Contact_BAO_GroupContact::getGroupId($objectId),
      "Group",
      NULL,
      TRUE
    );

    if($op == 'create' || $op == 'edit') {
      foreach (CRM_OauthSync_OAuthHelper::getHelperArray() as $helper) {
        $prefix = $helper->settingsPrefix;

        // we only care about groups that have a remote counter part
        $groupsId = CRM_Core_BAO_CustomField::getCustomFieldID($prefix . "_sync_settings");
        $remoteGroup = $groupCustomFields[$groupsId];
        print_r($remoteGroup);
        if($remoteGroup != null) {
          # we don't need to remove any users here
          $emptyArray = array();
          $toAddRemote = $objectRef;
          // add the remote members
          CRM_Utils_Hook::singleton()->invoke(
            array('remoteGroupName', 'toRemove', 'toAdd'),
            $remoteGroup,
            $emptyArray,
            $toAddRemote,
            CRM_Utils_Hook::$_nullObject,
            CRM_Utils_Hook::$_nullObject,
            CRM_Utils_Hook::$_nullObject,
            'civicrm_oauthsync_' . $prefix . '_update_remote_users'
          );
        }
      }
    } elseif ($op == 'delete') {
      foreach (CRM_OauthSync_OAuthHelper::getHelperArray() as $helper) {
        $prefix = $helper->settingsPrefix;

        // we only care about groups that have a remote counter part
        $groupsId = CRM_Core_BAO_CustomField::getCustomFieldID($prefix . "_sync_settings");
        $remoteGroup = $groupCustomFields[$groupsId];
        print_r($remoteGroup);
        if($remoteGroup != null) {
          # we don't need to remove any users here
          $emptyArray = array();
          $toRemoveRemote = $objectRef;
          // add the remote members
          CRM_Utils_Hook::singleton()->invoke(
            array('remoteGroupName', 'toRemove', 'toAdd'),
            $remoteGroup,
            $toRemoveRemote,
            $emptyArray,
            CRM_Utils_Hook::$_nullObject,
            CRM_Utils_Hook::$_nullObject,
            CRM_Utils_Hook::$_nullObject,
            'civicrm_oauthsync_' . $prefix . '_update_remote_users'
          );
        }
      }

    }
  }
  // TODO: handle adding or removing a user from a group in civicrm
}

