<?php
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
    //print_r(self::getHelperArray());
    //print_r($prefix);
    return self::getHelperArray()[$prefix];
  }

  static $helperArray = array();

  private static function getHelperArray() {
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
//    print_r(self::getHelperArray());
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
    $client_id = $this->getPrefixSetting('client_id');
    $client_secret = $this->getPrefixSetting('secret');
    $redirect_url = self::generateRedirectUrl();

    $requestJsonDict = array(
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'redirect_uri' => $redirect_url,
      'grant_type' => 'authorization_code',
      'code' => $code
    );
    $postBody = json_encode($requestJsonDict, JSON_UNESCAPED_SLASHES);
    print $postBody;

    // make a request
    $ch = curl_init($this->tokenUrl);
//    $ch = curl_init('http://localhost:1500');
    curl_setopt_array($ch, array(
      CURLOPT_POST => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
      // the token endpoint requires a user agent
      CURLOPT_USERAGENT => 'OauthSync Helper',
      CURLOPT_POSTFIELDS => $postBody
    ));
    $response = curl_exec($ch);
    if(curl_errno($ch)) {
      echo 'Request Error:' . curl_error($ch);
      // TODO: handle this better
    } else {
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
    $this->setPrefixSetting("refresh", $response_json["refresh_token"]);
    $this->setPrefixSetting("expiry", $response_json["expiry"]);
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
   * Adds the access token to a curl request
   *
   * refreshes the token if it has expired
   * @param $curl_request
   */
  private function addAccessToken(&$curl_request) {
    // TODO: check expiry and refresh
    curl_setopt(
      $curl_request,
      CURLOPT_HTTPHEADER,
      array(
        'Authorization: Bearer ' . $this->getPrefixSetting('token'),
        'Accept: application/json'
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