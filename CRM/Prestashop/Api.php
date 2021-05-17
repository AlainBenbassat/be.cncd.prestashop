<?php

class CRM_Prestashop_Api {
  const STATUS_OK = 200;

  private $settings;
  private $authorizationKey;
  private $endpoint;

  public function __construct($settings) {
    $this->settings = $settings;
    $this->prepareCurlParams();
  }

  public function test() {
    list($status, $data) = $this->sendRequest('', []);
    if ($status != SELF::STATUS_OK) {
      throw new Exception("Status = $status (should be 200)");
    }
  }

  public function getModifiedCustomers($since) {
    list($status, $data) = $this->sendRequest('customers/6532', []);
    return $data;
  }

  private function sendRequest($apiFunc, $apiParams) {
    $ch = curl_init();

    $url = $this->getUrlWithParams($apiFunc, $apiParams);
    $header = $this->getCurlHeader();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
      // error, get status code
      $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
    else {
      $status = SELF::STATUS_OK;
    }

    curl_close($ch);

    return [$status, $data];
  }

  private function prepareCurlParams() {
    $this->extractAuthorizationKey();
    $this->extractEndPoint();
  }

  private function extractEndPoint() {
    $this->endpoint = $this->settings->getUri();
  }

  private function extractAuthorizationKey() {
    // compute the user:password couple for the authorization header
    // (password is empty for PrestaShop, and user name = token)
    $token = $this->settings->getToken();
    $this->authorizationKey = base64_encode($token . ':');
  }

  private function getCurlHeader() {
    $header = [];
    $header[] = 'Content-length: 0';
    $header[] = 'Content-type: application/json';
    $header[] = 'Authorization: Basic ' . $this->authorizationKey;

    return $header;
  }

  private function getUrlWithParams($apiFunc, $apiParams) {
    $url = $this->endpoint . "/api/$apiFunc";

    // add params to the url (if needed)
    if (count($apiParams)) {
      $url .= '?' . implode('&', $apiParams);
    }

    return $url;
  }

}
