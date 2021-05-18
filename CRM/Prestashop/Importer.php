<?php

class CRM_Prestashop_Importer {
  private $settings;

  public function __construct() {
    $this->settings = new CRM_Prestashop_Settings();
  }

  public function importOrdersSince() {
    $api = new CRM_Prestashop_Api($this->settings);
  }

  public function importOrder($orderId) {
    $api = new CRM_Prestashop_Api($this->settings);

    // get the order from prestashop
    $order = $api->getOrder($orderId);
    if ($order) {
      // get the customer from prestashop
      $customer = $api->getCustomer($order->id_customer);

      // get the civicrm contact from the customer info
      $contact = new CRM_Prestashop_Contact($customer);
    }

  }
}
