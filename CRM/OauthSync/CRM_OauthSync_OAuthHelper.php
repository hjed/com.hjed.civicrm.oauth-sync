<?php
require_once 'CRM_OauthSync_SyncHelper.php';

/**
 * Class to provide helper utilities for oauth
 */
class CRM_OauthSync_OAuthHelper {


  /**
   * Retrieves the helper with the given prefix
   * @param string $prefix
   * @return CRM_OauthSync_OAuthHelper the helper
   */
  public static function getHelper($prefix) {
    return self::getHelperArray()[$prefix];
  }

  static $helperArray = array();

  public static function getHelperArray() {
    if(self::$helperArray == null) {
      self::$helperArray = array();
    }
    return self::$helperArray;
  }


  /**
   * @var The token url for the given oauth provider
   */
  public $tokenUrl;

  /**
   * @var the settings prefix
   */
  public $settingsPrefix;

  /**
   * CRM_OauthSync_OAuthHelper constructor.
   * @param string $prefix the settings prefix for this oauth provider
   * @param string $tokenUrl the token url for this oauth provider
   */
  function __construct($prefix, $tokenUrl) {
    $this->tokenUrl = $tokenUrl;
    $this->settingsPrefix = $prefix;
    self::$helperArray[$prefix] = $this;
  }

  /**
   * Helper function to return the value of a setting which has been prefixed for a connection
   * @param string $setting the setting
   * @return mixed the setting value
   */
  private function getPrefixSetting($setting) {
    return Civi::settings()->get($this->settingsPrefix . '_' . $setting);
  }

  /**
   * Helper function to set the value of a setting which has been prefixed for a connection
   * @param string $setting the setting
   * @param mixed $value the value to set
   * @return mixed the setting value
   */
  private function setPrefixSetting($setting, $value) {
    return Civi::settings()->set($this->settingsPrefix . '_' . $setting, $value);
  }

  /**
   * Generate a new state key for oauth, store it in the settings
   *
   * @return string
   *  Ex: 1234
   */
  public function newStateKey() {
    $stateKey = uniqid("", true);

    $this->setPrefixSetting('oauth_state', $stateKey);

    return $this->settingsPrefix . '!' . $stateKey;
  }

  /**
   * Check if the state key is valid by comparing it against our stored value
   *
   * @param $stateKey string
   *  Ex: 123123123
   * @return bool
   */
  public function verifyState($stateKey) {
    $actualValue = $this->getPrefixSetting('oauth_state');

    // are they equal
    return $actualValue == $stateKey;
  }

  /**
   * Performs an oauth authorization code grant exchange.
   * Redirects to $return_uri if successful
   *
   * @param string $code the code to use for the exchange
   */
  public function doOAuthCodeExchange($code) {
    $redirect_url = self::generateRedirectUrl();

    $requestJsonDict = array(
      'redirect_uri' => $redirect_url,
      'code' => $code
    );
    $success = $this->doOAuthTokenRequest('authorization_code', $requestJsonDict);

    if($success) {
      CRM_Utils_Hook::singleton()->invoke(
        array('prefix'),
        $this->settingsPrefix,
        CRM_Utils_Hook::$_nullObject,
        CRM_Utils_Hook::$_nullObject,
        CRM_Utils_Hook::$_nullObject,
        CRM_Utils_Hook::$_nullObject,
        CRM_Utils_Hook::$_nullObject,
        'civicrm_oauthsync_consent_success'
      );

      $this->setPrefixSetting('connected', true);

      // load the list of groups
      CRM_OauthSync_SyncHelper::getInstance($this->settingsPrefix)->triggerUpdateGroupsListHook();

      $return_path = CRM_Utils_System::url($this->getPrefixSetting('callback_return_path'), 'reset=1', TRUE, NULL, FALSE, FALSE);
      header("Location: " . $return_path);
      die();
    }

  }

  /**
   * Parse a standard oauth token response and store in settings.
   * Does not handle error conditions.
   * @param $response_json array
   */
  public function parseOAuthTokenResponse($response_json) {
    // for now just store the tokens
    $this->setPrefixSetting("token", $response_json["access_token"]);
    if($response_json["refresh_token"]) {
      $this->setPrefixSetting("refresh", $response_json["refresh_token"]);
    }
    // we subtract 10 to give us an additional saftey margin
    $this->setPrefixSetting("expiry", time() + $response_json["expires_in"] - 10);

  }

  /**
   * Generates a urlencoded oauth redirect url for the app
   *
   * @return string
   */
  public static function generateRedirectUrlEncoded() {
    $redirect_url = urlencode(self::generateRedirectUrl());
    return $redirect_url;
  }

  /**
   * Generates a oauth redirect url for the app
   *
   * @return string
   */
  public static function generateRedirectUrl() {
    $redirect_url = CRM_Utils_System::url('civicrm/oauth-sync/callback', 'reset=1', TRUE, NULL, FALSE, TRUE);
    return $redirect_url;
  }


  /**
   * Perform an oauth token exchange and update our token storage
   *
   * @param string $grant_type the oauth grant type to perform
   * @param array $authParams the additional parameters needed for that specific grant type
   *  Ex. array('redirect_url' => $redirect_url, 'code' => $code)
   * @return bool success
   */
  public function doOAuthTokenRequest($grant_type, $authParams) {

    $client_id = $this->getPrefixSetting('client_id');
    $client_secret = $this->getPrefixSetting('secret');

    $requestJsonDict = array(
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'grant_type' => $grant_type
    ) + $authParams;
    $postBody = json_encode($requestJsonDict, JSON_UNESCAPED_SLASHES);
    // make a request
    $ch = curl_init($this->tokenUrl);
    curl_setopt_array($ch, array(
      CURLOPT_POST => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
      ),
      // the token endpoint requires a user agent
      CURLOPT_USERAGENT => 'OauthSync Helper',
      CURLOPT_POSTFIELDS => $postBody
    ));
    $response = curl_exec($ch);
    if(curl_errno($ch)) {
      echo 'Request Error:' . curl_error($ch);
      return false;
      // TODO: handle this better
    } else {

      $response_json = json_decode($response, true);
      if (in_array("error", $response_json)) {
        // TODO: handle this better
        echo "<br/><br/>Error\n\n";
        echo $response_json["error_description"];
        return false;
      } else {
        $this->parseOAuthTokenResponse($response_json);
        return true;
      }
    }
  }

  /**
   * Refresh the access token
   */
  public function refreshAccessToken() {
    print("refreshing");
    $jsonBody = array(
      'refresh_token' => $this->getPrefixSetting('refresh')
    );
    $this->doOAuthTokenRequest('refresh_token', $jsonBody);

    //TODO: handle failure
  }

  /**
   * Adds the access token to a curl request
   *
   * refreshes the token if it has expired
   * @param $curl_request
   */
  public function addAccessToken(&$curl_request) {
    // TODO: check expiry and refresh
    print "<br/> exp: " . $this->getPrefixSetting('expiry') . ' time ' . time();
    if($this->getPrefixSetting("expiry") <= time()) {
      $this->refreshAccessToken();
    }
    curl_setopt(
      $curl_request,
      CURLOPT_HTTPHEADER,
      array(
        'Authorization: Bearer ' . $this->getPrefixSetting('token'),
        'Accept: application/json',
        'Content-Type: application/json'
      )
    );

  }

  /**
   * Sets the return path for the oauth consent for the given prefix
   * @param $path the civicrm path to return to
   */
  public function setOauthCallbackReturnPath($path) {
    $this->setPrefixSetting('callback_return_path', $path);
  }

}