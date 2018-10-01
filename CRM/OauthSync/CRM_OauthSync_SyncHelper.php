<?php

/**
 * Helper class for handling operations on users and groups
 */
class CRM_OauthSync_SyncHelper {

  private $prefix;

  private static $singletons;

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