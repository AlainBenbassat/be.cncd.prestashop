<?php

class CRM_Prestashop_Settings {
  private $settingUri = 'prestashop_uri';
  private $settingToken = 'prestashop_token';
  private $settingAccessMethod = 'prestashop_access_method';

  public function getUri() {
    return Civi::settings()->get($this->settingUri);
  }

  public function setUri($value) {
    Civi::settings()->set($this->settingUri, $value);
  }

  public function getToken() {
    return Civi::settings()->get($this->settingToken);
  }

  public function setToken($value) {
    Civi::settings()->set($this->settingToken, $value);
  }

  public function getAccessMethod() {
    $am = Civi::settings()->get($this->settingAccessMethod);
    if (!$am) {
      $am = 'basic';
    }

    return $am;
  }

  public function setAccessMethod($value) {
    Civi::settings()->set($this->settingAccessMethod, $value);
  }

}
