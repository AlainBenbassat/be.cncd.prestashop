<?php

class CRM_Prestashop_Importer {
  private $settings;
  private $api;

  public $throwErrorWhenNotFound = FALSE;

  public function __construct() {
    $this->settings = new CRM_Prestashop_Settings();
    $this->api = new CRM_Prestashop_Api($this->settings);
  }

  public function importOrdersSince() {

  }

  public function importOrder($orderId) {
    // get the order from prestashop
    $order = $this->api->getOrder($orderId);
    if ($order) {
      // get the customer from prestashop
      $customer = $this->api->getCustomer($order->id_customer);

      // get the civicrm contact from the customer info
      $contact = new CRM_Prestashop_Contact($customer);

      // create the contribution from the order info
      CRM_Prestashop_Contribution::create($contact->contactId, $order);
    }
    else {
      if ($this->throwErrorWhenNotFound) {
        throw new Exception("Commande $orderId non trouvée.");
      }
    }
  }
}
