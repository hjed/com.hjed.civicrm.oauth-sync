<?php
/**
 * Helper class for handling operations on users and groups
 */

class CRM_OauthSync_SyncHelper {

  private $prefix;

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
   * Plugins should call this when one or more new group is added in the remote api
   * @param array $groups list of group names
   */
  public function addRemoteGroups($groups) {
    //TODO: implement this
  }

  /**
   * Plugins should call this when a one or more group is removed in the remote api.
   * @param array $groups list of group names
   */
  public function removeRemoteGroups($groups) {
    //TODO: implement this
  }

  /**
   * Retrieves the list of remote groups as stored in prefix's option group.
   * This does not retrieve anything from a remote api, it uses cached data only.
   *
   * @return array the list of remote groups
   */
  public function getCachedRemoteGroups() {
    //TODO: implement this
  }

  /**
   * Records that the given user has been removed from the remote group and removes said user from all synced groups
   * @param string $group the group name
   * @param string $user_id the user name
   */
  public function remoteUserRemovedFromGroup($group, $user_id) {

  }


  /**
   * Records that the given user has been added tp the remote group and adds said user from all synced groups
   * @param string $group the group name
   * @param string $user_id the user name
   */
  public function remoteUserAddedToGroup($group, $user_id) {

  }

  /**
   * Helper function to translate local and remote groups
   * @param string $remote_group the remote group
   * @return array the list of local group ids
   */
  public function getLocalGroups($remote_group) {

  }

  /**
   * Helper function to translate local and remote groups
   * @param string $local_group the local group id
   * @return array the list of remote group ids
   */
  public function getRemoteGroups($local_group) {

  }

}