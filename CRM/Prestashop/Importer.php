<?php

class CRM_Prestashop_Importer {
  private $settings;
  private $api;
  private $config;

  public $throwErrorWhenNotFound = FALSE;

  public function __construct() {
    $this->settings = new CRM_Prestashop_Settings();
    $this->api = new CRM_Prestashop_Api($this->settings);
    $this->config = new CRM_Prestashop_Config();
  }

  public function importOrdersBetween($fromDate, $toDate) {
    $orders = $this->api->getDeliveredOrdersBetween($fromDate, $toDate);
    if ($orders) {
      foreach ($orders as $order) {
        $this->importOrder($order->id);
      }
    }
  }

  public function importOrder($orderId) {
    $order = $this->getOrderFromPrestashop($orderId);
    $customer = $this->getCustomerFromPrestashop($order->id_customer);

    if ($order->id_address_invoice) {
      $address = $this->getAddressFromPrestashop($order->id_address_invoice);
    }
    else {
      $address = FALSE;
    }

    // get or create the civicrm contact from the customer info
    $contact = new CRM_Prestashop_Contact();
    $contact->getOrCreate($customer, $address);

    // create the contribution from the order info
    $contrib = new CRM_Prestashop_Contribution($this->config);
    $contrib->create($contact->contactId, $order);
  }

  private function getOrderFromPrestashop($orderId) {
    $order = $this->api->getOrder($orderId);
    if ($order === FALSE) {
      throw new Exception("Commande $orderId non trouvée.");
    }

    return $order;
  }

  private function getCustomerFromPrestashop($customerId) {
    $customer = $this->api->getCustomer($customerId);
    if ($customer === FALSE) {
      throw new Exception("Client $customerId non trouvé.");
    }

    return $customer;
  }

  private function getAddressFromPrestashop($addressId) {
    $address = $this->api->getAddress($addressId);
    //if ($address === FALSE) {
    //  throw new Exception("Adresse $addressId non trouvée.");
    //}

    return $address;
  }
}
