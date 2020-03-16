<?php
use CRM_OauthSync_ExtensionUtil as E;

/**
 * OauthSyncRemoteGroup.Syncall API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_oauth_sync_remote_group_Syncall_spec(&$spec) {
  $spec['prefix']['api.required'] = 0;
}

/**
 * OauthSyncRemoteGroup.Syncall API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_oauth_sync_remote_group_Syncall($params) {
  $helpers = null;
  if (array_key_exists('prefix', $params)) {
    $helpers = array(CRM_OauthSync_OAuthHelper::getHelper($params['prefix']));
  } else {
    $helpers = CRM_OauthSync_OAuthHelper::getHelperArray();
  }

  $returnValues = array();
  foreach ($helpers as $helper) {
    $syncHelper = CRM_OauthSync_SyncHelper::getInstance($helper->settingsPrefix);
    $syncHelper->triggerUpdateGroupsListHook();
    $params = array();
    $groups = CRM_Contact_BAO_Group::getGroupList($params);

    $remoteGroupFieldId = CRM_Core_BAO_CustomField::getCustomFieldID($helper->settingsPrefix . "_sync_settings");
    $returnValues[$helper->settingsPrefix] = array();
    $returnValues[$helper->settingsPrefix]["custom_field_id"] = $remoteGroupFieldId;
    foreach (array_keys($groups) as $groupId) {
      $customFields = CRM_Core_BAO_CustomValueTable::getEntityValues($groupId, 'Group', NULL, TRUE);
      $returnValues[$helper->settingsPrefix][$groupId] = array();
      if(key_exists($remoteGroupFieldId, $customFields) && $customFields[$remoteGroupFieldId] != null) {
        $returnValues[$helper->settingsPrefix][$groupId]["remote_group"] = $customFields[$remoteGroupFieldId];
        $remoteGroup = $customFields[$remoteGroupFieldId];
        $returnValues[$helper->settingsPrefix][$groupId]["results"] = $syncHelper->syncGroup($groupId, $remoteGroup, true);
        print($groupId);
        print("synced");
      }
    }
  }
  return civicrm_api3_create_success($returnValues, $params, 'RemoteGroup', 'SyncAll');
}
