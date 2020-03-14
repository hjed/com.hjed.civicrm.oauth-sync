<?php

/**
 * Helper class for handling operations on users and groups
 */
class CRM_OauthSync_SyncHelper {
  
  public static $SYNC_MODE_TWO_WAY = "two-way";
  public static $SYNC_MODE_CIVICRM_MASTER = "civicrm-master";
  public static $SYNC_MODE_REMOTE_MASTER = "remote-master";

  private $prefix;

  private static $singletons;

  /**
   * @var bool used to indicate that the system is performing a delete operation on a
   * group contact and this operation should not be replicated to the remote api.
   */
  public $protectedDeleteInProgress = false;

  /**
   * Gets an instance of a Sync Helper for the given prefix
   * @param $prefix the settings prefix
   * @return CRM_OauthSync_SyncHelper the helper
   */
  public static function getInstance($prefix) {
    if(self::$singletons == null) {
      self::$singletons = array();
    }

    if(self::$singletons[$prefix] == null) {
      self::$singletons[$prefix] = new CRM_OauthSync_SyncHelper($prefix);
    }
    return self::$singletons[$prefix];
  }

  /**
   * CRM_OauthSync_SyncHelper constructor.
   * @param $prefix the prefix to use
   *
   * Precondition: the option group `$prefix . '_sync_group_options'` must exist.
   */
  public function __construct($prefix) {
    $this->prefix = $prefix;
  }

  /**
   * Does a lookup to get the option group id for the groups list
   * @return int the id
   */
  private function getGroupsOptionGroupId() {
    $params = array('name' => $this->prefix . '_sync_groups_options');
    $defaults = array();
    $option_group = CRM_Core_BAO_OptionGroup::retrieve(
      $params,
      $defaults
    );
    return $option_group->id;
  }

  /**
   * Plugins should call this when one or more new group is added in the remote api
   * @param array $groups list of group names
   */
  public function addRemoteGroups($groups) {

    $group_id = $this->getGroupsOptionGroupId();

    foreach ($groups as $group) {
      // based on the code in OptionGroup.php
      $value = new CRM_Core_DAO_OptionValue();
      $value->option_group_id = $group_id;
      $value->label = $group;
      $value->value = $group;
      $value->name = $group;
      $value->is_default = false;
      $value->is_active = false;
      $value->weight = 1;
      $value->is_reserved = true;
      $value->is_active = true;
      $value->save();
    }

  }

  /**
   * Plugins should call this when a one or more group is removed in the remote api.
   * @param array $groups list of group names
   */
  public function removeRemoteGroups($groups) {

    $groupId = $this->getGroupsOptionGroupId();
    foreach($groups as $group) {
      $searchParams = array(
        'option_group_id' => $groupId,
        'name' => $group
      );
      $defaults = array();
      $optionValue = CRM_Core_BAO_OptionValue::retrieve($searchParams, $defaults);
      CRM_Core_BAO_OptionValue::del($optionValue->id);
    }
  }

  /**
   * Retrieves the list of remote groups as stored in prefix's option group.
   * This does not retrieve anything from a remote api, it uses cached data only.
   *
   * @return array the list of remote groups
   */
  public function getCachedRemoteGroups() {
    //TODO: implement this
    return array();
  }

  /**
   * Records that the given user has been removed from the remote group and removes said user from all synced groups
   * @param string $group the group name
   * @param string $userId the user name
   */
  public function remoteUserRemovedFromGroup($group, $userId) {

  }


  /**
   * Records that the given user has been added tp the remote group and adds said user from all synced groups
   * @param string $group the group name
   * @param string $userId the user name
   */
  public function remoteUserAddedToGroup($group, $userId) {

  }

  /**
   * Helper function to translate local and remote groups
   * @param string $remoteGroup the remote group
   * @return array the list of local group ids
   */
  public function getLocalGroups($remoteGroup) {

  }

  /**
   * Helper function to translate local and remote groups
   * @param int $localGroup the local group id
   * @param array $customFields optionally the custom fields for the group (to improve efficiency)
   * @return string the remote group
   */
  public function getRemoteGroup($localGroup, $customFields = null) {
    if($customFields == null) {
      $customFields = CRM_Core_BAO_CustomValueTable::getEntityValues($localGroup, 'Group', NULL, TRUE);
    }

    $groupsId = CRM_Core_BAO_CustomField::getCustomFieldID($this->prefix . "_sync_settings");
    return $customFields[$groupsId];
  }

  /**
   * Does a sync of a local group and a remote group
   *
   * This method *does not* create a connection between two groups.
   * @param int $localGroupId the local group id
   * @param string $remoteGroup the remote group id
   * @param bool $remoteIsMaster if we should remove contacts that don't exist in the
   *  the remote group. If false this function performs a union of the two groups, if
   *  true this function causes the local group to exactly match the remote group.
   *  ***Provided that the group supports syncing in that direction***
   * @return array the users added to local and the users that weren't on the remote as well as their parents
   */
  public function syncGroup($localGroupId, $remoteGroup, $remoteIsMaster = false) {
    // calculate the sync mode
    $syncModeFieldId = CRM_Core_BAO_CustomField::getCustomFieldID($this->prefix . "_sync_mode");
    $customFields = CRM_Core_BAO_CustomValueTable::getEntityValues($localGroupId, 'Group', NULL, TRUE);
    $syncMode = $customFields[$syncModeFieldId];
    if($syncMode == null) {
      // two way sync is default
      $syncMode = self::$SYNC_MODE_TWO_WAY;
    }


    // retrieve this each time for consistency
    $groupContacts = CRM_Contact_BAO_Group::getMember($localGroupId);
    // the getGroupContacts method returns a list of contact objects containing just their ids
    // their ids are also the keys of the array.
    $groupContacts = array_keys($groupContacts);

    $groupMembers = array();
    CRM_Utils_Hook::singleton()->invoke(
      array('remoteGroupName', 'members'),
      $remoteGroup,
      $groupMembers,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      'civicrm_oauthsync_' . $this->prefix . '_get_remote_user_list'
    );

    // do a diff and get the groups in sync in a none destructive manner
    $toAddLocal = array_diff($groupMembers, $groupContacts);
    $usersNotOnRemote = array_diff($groupContacts, $groupMembers);

    // add contacts to the group if the remote is master or we are doing a two way sync
    if($syncMode == self::$SYNC_MODE_REMOTE_MASTER || $syncMode == self::$SYNC_MODE_TWO_WAY) {
      CRM_Contact_BAO_GroupContact::addContactsToGroup($toAddLocal, $localGroupId);
    }
    $removedLocalUsers = false;

    if($remoteIsMaster) {
      if($syncMode == self::$SYNC_MODE_REMOTE_MASTER || $syncMode == self::$SYNC_MODE_TWO_WAY) {
        $this->protectedDeleteInProgress = true;
        try {
          // remove the contacts not in the local group
          CRM_Contact_BAO_GroupContact::removeContactsFromGroup($usersNotOnRemote, $localGroupId);
          $removedLocalUsers = true;
        } finally {
          $this->protectedDeleteInProgress = false;
        }
      }
    } else if($syncMode == self::$SYNC_MODE_TWO_WAY || $syncMode == self::$SYNC_MODE_CIVICRM_MASTER) {
      # we don't need to remove any users here
      $emptyArray = array();
      // add the remote members
      CRM_Utils_Hook::singleton()->invoke(
        array('remoteGroupName', 'toRemove', 'toAdd'),
        $remoteGroup,
        $emptyArray,
        $usersNotOnRemote,
        CRM_Utils_Hook::$_nullObject,
        CRM_Utils_Hook::$_nullObject,
        CRM_Utils_Hook::$_nullObject,
        'civicrm_oauthsync_' . $this->prefix . '_update_remote_users'
      );
    }
    $output =  array(
      'added_to_local' => $toAddLocal,
      'users_not_on_remote' => $usersNotOnRemote,
      'mode' => $syncMode,
      'removed_locally' => $removedLocalUsers
    );

    if(CRM_Contact_BAO_GroupNesting::hasParentGroups($localGroupId)) {
      $output['parent'] = array();
      $parents = CRM_Contact_BAO_GroupNesting::getParentGroupIds($localGroupId);
      foreach ($parents as $parent) {
        $this->syncGroup($parent, $this->getRemoteGroup($parent) , $remoteIsMaster);
      }
    }

    return $output;
  }

  /**
   * Updates the cached list of remote groups to match the provided list
   * @param array $newGroupsList the new list of remote groups
   */
  public function updateRemoteGroupsList($newGroupsList) {
    $current_list = $this->getCachedRemoteGroups();
    $added = array_diff($newGroupsList, $current_list);
    $removed = array_diff($current_list, $newGroupsList);
    $this->addRemoteGroups($added);
    $this->removeRemoteGroups($removed);
  }

  /**
   * Triggers the civicrm_oauthsync_(prefix)_sync_groups_list hook
   */
  public function triggerUpdateGroupsListHook() {
    $newGroupsList = array();
    CRM_Utils_Hook::singleton()->invoke(
      array('groups'),
      $newGroupsList,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      'civicrm_oauthsync_' . $this->prefix . '_sync_groups_list'
    );

    $this->updateRemoteGroupsList($newGroupsList);
  }

  /**
   * Finds all remote groups
   * @param $localGroupId the local group to check
   * @param $requireSyncToRemote if we should only return groups that can sync to remote
   * @param $requireSyncToLocal if we should only return groups that can sync to local
   * @return array the remote groups reltated to this group (including those related through its parents)
   * @throws CiviCRM_API3_Exception
   */
  public function getRemoteGroupsIncludingParents($localGroupId, $requireSyncToRemote, $requireSyncToLocal) {
    $allLocal = array($localGroupId);
    $next = array($localGroupId);
    $groupsId = CRM_Core_BAO_CustomField::getCustomFieldID($this->prefix . "_sync_settings");
    $modeFieldId = CRM_Core_BAO_CustomField::getCustomFieldID($this->prefix . "_sync_mode");
    while($next = CRM_Contact_BAO_GroupNesting::getParentGroupIds($next)) {
      $allLocal = array_merge($next, $allLocal);
    }
    
    $remoteGroups = array();
    foreach ($allLocal as $groupId) {

      $groupCustomFields = CRM_Core_BAO_CustomValueTable::getEntityValues(
        $groupId,
        "Group",
        NULL,
        FALSE
      );
      
      // we only care about groups that have a remote counter part

      $remoteGroup = $groupCustomFields[$groupsId];
      if($remoteGroup != null) {
        $syncMode = $groupCustomFields[$modeFieldId];
        if(
          ($requireSyncToRemote && ($syncMode == self::$SYNC_MODE_CIVICRM_MASTER || $syncMode == self::$SYNC_MODE_TWO_WAY)) ||
          ($requireSyncToLocal && ($syncMode == self::$SYNC_MODE_REMOTE_MASTER || $syncMode == self::$SYNC_MODE_TWO_WAY))
        ){
            $remoteGroups[] = $remoteGroup;
        }
      }
    }
    return $remoteGroups;
  }

}