<?php

/**
 * Helper class for handling operations on users and groups
 */
class CRM_OauthSync_SyncHelper {

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
   * @param string $localGroup the local group id
   * @return array the list of remote group ids
   */
  public function getRemoteGroups($localGroup) {

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
   * @return array the users added to local and the users that weren't on the remote
   */
  public function syncGroup($localGroupId, $remoteGroup, $remoteIsMaster = false) {
    // retrieve this each time for consistency
    $groupContacts = CRM_Contact_BAO_Group::getGroupContacts($localGroupId);
    // the getGroupContacts method returns a list of contact objects containing just their ids
    // their ids are also the keys of the array.
    $groupContacts = array_keys($groupContacts);

    print("group contacts");
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
      'civicrm_oauthsync_' . $this->prefix . '_get_remote_user_list'
    );

    print("\ngroupMembers\n");
    print_r($groupMembers);
    print("\ncontacts\n");
    print_r($groupContacts);
    // do a diff and get the groups in sync in a none destructive manner
    $toAddLocal = array_diff($groupMembers, $groupContacts);
    $usersNotOnRemote = array_diff($groupContacts, $groupMembers);
    print_r($usersNotOnRemote);
    print_r($toAddLocal);

    CRM_Contact_BAO_GroupContact::addContactsToGroup($toAddLocal, $localGroupId);

    if($remoteIsMaster) {
      $this->protectedDeleteInProgress = true;
      try {
        // remove the contacts not in the remote group
        CRM_Contact_BAO_GroupContact::removeContactsFromGroup($usersNotOnRemote, $localGroupId);
      } finally {
        $this->protectedDeleteInProgress = false;
      }
    } else {
      print "remote is not master";
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
    return array('added_to_local' => $toAddLocal, 'users_not_on_remote' => $usersNotOnRemote);
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

}