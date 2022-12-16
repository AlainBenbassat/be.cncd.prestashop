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

  public function importOrdersByDeliveryNumber($limit) {
    $fromDeliveryNumber = $this->getLastImportedDeliveryNumber();
    $this->importOrders($fromDeliveryNumber, $limit);
  }

  public function fixMissedOrders() {
    $gapListDao = $this->getDeliveryNumberGaps();
    $countBefore = $gapListDao->N;

    while ($gapListDao->fetch()) {
      $this->importOrders($gapListDao->missing_delivery_number - 1, 1);
    }

    $gapListDao = $this->getDeliveryNumberGaps();
    $countAfter = $gapListDao->N;

    return [$countBefore, $countAfter];
  }

  public function importOrders($fromDeliveryNumber, $limit) {
    $orders = $this->api->getDeliveredOrdersByDeliveryNumber($fromDeliveryNumber, $limit);
    if ($orders) {
      foreach ($orders as $order) {
        try {
          $this->importOrder($order->id);
        }
        catch (Exception $e) {
          watchdog('Prestashop', 'Order ' . $order->id . ': ' . $e->getMessage());
        }
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

  private function getLastImportedDeliveryNumber() {
    $customField = $this->config->getCustomField_deliveryNumber();
    $table = $customField['table_name'];
    $field = $customField['column_name'];
    $sql = "select ifnull(max($field), 0) from $table";
    return CRM_Core_DAO::singleValueQuery($sql);
  }

  private function getDeliveryNumberGaps() {
    $customField = $this->config->getCustomField_deliveryNumber();
    $table = $customField['table_name'];
    $field = $customField['column_name'];

    $sql = "
      SELECT
        mo.$field + 1 missing_delivery_number
      FROM
        $table mo
      WHERE
        NOT EXISTS (
          SELECT
            NULL
          FROM
            $table mi
          WHERE
            mi.$field = mo.$field + 1
        )
      and
        mo.$field  > 2000
      ORDER BY
        mo.$field
      LIMIT
        100
    ";

    $dao = CRM_Core_DAO::executeQuery($sql);

    return $dao;
  }
}
