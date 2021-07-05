<?php

class CRM_Prestashop_Api {
  const STATUS_OK = 200;
  const ORDER_STATUS_DELIVERED = 5;


  private $settings;
  private $authorizationKey;
  private $endpoint;
  private $accessMethod;

  public function __construct($settings) {
    $this->settings = $settings;
    $this->prepareCurlParams();
  }

  public function test() {
    $data = $this->sendRequest('currencies/1', []);
    if ($this->isExpectedTestData($data)) {
      return 'OK';
    }
    else {
      throw new Exception('Test does not return expected data.');
    }
  }

  private function isExpectedTestData($data) {
    // we expect an object with currency->iso_code == EUR
    if ($data) {
      if (property_exists($data, 'currency')) {
        if (property_exists($data->currency, 'iso_code')) {
          if ($data->currency->iso_code == 'EUR') {
            return TRUE;
          }
        }
      }
    }

    return FALSE;
  }

  public function getDeliveredOrdersSince($dateSince) {
    $data = $this->sendRequest("orders", [
      "filter[delivery_date]=>[$dateSince]",
      'filter[current_state]=' . self::ORDER_STATUS_DELIVERED,
      'filter[valid]=1',
    ]);
    return $data;
  }

  public function getCustomer($customerId) {
    $data = $this->sendRequest("customers/$customerId", []);

    if (is_object($data) && property_exists($data, 'customer')) {
      return $data->customer;
    }
    else {
      return FALSE;
    }
  }

  public function getAddress($addressId) {
    $data = $this->sendRequest("addresses/$addressId", []);

    if (is_object($data) && property_exists($data, 'address')) {
      // add the country iso code before returning the object
      $data->address->country_iso_code = $this->getCountryIsoCode($data->address->id_country);
      return $data->address;
    }
    else {
      return FALSE;
    }
  }

  public function getCountryIsoCode($countryId) {
    // don't call the API for Belgium
    if ($countryId == 3) {
      return 'BE';
    }

    $data = $this->sendRequest("countries/$countryId", []);

    if (is_object($data) && property_exists($data, 'country')) {
      return $data->country->iso_code;
    }
    else {
      return FALSE;
    }
  }

  public function getOrder($orderId) {
    $data = $this->sendRequest("orders/$orderId", []);

    if (is_object($data) && property_exists($data, 'order')) {
      return $data->order;
    }
    else {
      return FALSE;
    }
  }

  private function isBlockedByFirewall($data) {
    // the firewall of the hosting provider blocks access with status code = 200
    // try to find out about the blocking by looking for keywords in the error message
    if (strpos($data, 'blocage') > 0 && strpos($data, 'pare-feu') > 0) {
      return TRUE;
    }

    return FALSE;
  }

  private function sendRequest($apiFunc, $apiParams) {
    $header = $this->getCurlHeader();
    $url = $this->getUrlWithParams($apiFunc, $apiParams);

    // send the curl reauest
    $ch = curl_init();
    $data = $this->getCurlRequestData($ch, $url, $header);
    $status = $this->getCurlRequestStatus($ch);

    curl_close($ch);

    $this->throwErrorIfStatusIsInvalid($status, $data);

    // decode the json result (if we have one)
    if ($data) {
      return json_decode($data);
    }
    else {
      return '';
    }
  }

  private function prepareCurlParams() {
    $this->extractAcessMethod();
    $this->extractAuthorizationKey();
    $this->extractEndPoint();
  }

  private function extractAcessMethod() {
    $this->accessMethod = $this->settings->getAccessMethod();
  }

  private function extractEndPoint() {
    $this->endpoint = $this->settings->getUri();
  }

  private function extractAuthorizationKey() {
    // compute the user:password couple for the authorization header when access method = basic
    // (password is empty for PrestaShop, and user name = token)
    $token = $this->settings->getToken();

    if ($this->accessMethod == 'basic') {
      $this->authorizationKey = base64_encode($token . ':');
    }
    else {
      $this->authorizationKey = $token;
    }
  }

  private function getCurlHeader() {
    $header = [];
    $header[] = 'Content-length: 0';

    if ($this->accessMethod == 'basic') {
      $header[] = 'Authorization: Basic ' . $this->authorizationKey;
    }

    return $header;
  }

  private function getUrlWithParams($apiFunc, $apiParams) {
    $url = $this->endpoint . "/api/$apiFunc?output_format=JSON";

    if ($this->accessMethod == 'ws_key') {
      $url .= '&ws_key=' . $this->authorizationKey;
    }

    // add params to the url (if needed)
    if (count($apiParams)) {
      $url .= '&' . implode('&', $apiParams);
    }

    return $url;
  }

  private function throwErrorIfStatusIsInvalid($status, $data) {
    if ($status != self::STATUS_OK) {
      throw new Exception("Status = $status, but should be " . self::STATUS_OK);
    }

    // sometimes the status is 200 but the data contains an error message
    if ($data == '401 Unauthorized') {
      throw new Exception('401 Unauthorized');
    }

    // sometimes the status is 200 but the firewall blocks te request
    if ($this->isBlockedByFirewall($data)) {
      throw new Exception('Blocked by firewall');
    }
  }

  private function getCurlRequestData($ch, $url, $header) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    return curl_exec($ch);
  }

  private function getCurlRequestStatus($ch) {
    if (curl_errno($ch)) {
      $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
    else {
      $status = self::STATUS_OK;
    }

    return $status;
  }

}
