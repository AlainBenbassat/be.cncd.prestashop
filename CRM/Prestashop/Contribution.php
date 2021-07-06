<?php

class CRM_Prestashop_Contribution {
  const FINANCIAL_TYPE_ACHAT = 11;
  private $config;

  public function __construct($config) {
    $this->config = $config;
  }

  public function create($contactId, $order) {
    $contribId = $this->exists($contactId, $order->id);
    if ($contribId === FALSE) {
      $contribId = $this->createContribution($contactId, $order)['id'];
    }

    $this->updateProducts($contribId, $order);
  }

  private function exists($contactId, $orderId) {
    $sql = "select max(id) from civicrm_contribution where contact_id = $contactId and source = 'boutique_o$orderId' and financial_type_id = " . self::FINANCIAL_TYPE_ACHAT;
    $id = CRM_Core_DAO::singleValueQuery($sql);
    if ($id) {
      return $id;
    }
    else {
      return FALSE;
    }
  }

  private function createContribution($contactId, $order) {
    $params = [
      'sequential' => 1,
      'contact_id' => $contactId,
      'receive_date' => $order->delivery_date,
      'total_amount' => $this->removeExtraZeroes($order->total_paid),
      'currency' => 'EUR',
      'financial_type_id' => self::FINANCIAL_TYPE_ACHAT,
      'payment_instrument_id' => 1, // credit card
      'source' => 'boutique_o' . $order->id,
    ];

    $result = civicrm_api3('Contribution', 'create', $params);
    return $result;
  }

  private function updateProducts($contribId, $order) {
    $products = [];
    foreach ($order->associations->order_rows as $row) {
      $products[] = $row->product_id;
      $this->config->addPrestashopProduct($row->product_id, $row->product_name);
    }

    if (count($products) > 0) {
     civicrm_api3('CustomValue','create', [
       'entity_id' => $contribId,
       'entity_table' => $this->config->getCustomGroup_ShoppingCartDetail()['table_name'],
       'custom_' . $this->config->getCustomField_orderedProducts()['id'] => $products,
     ]);
    }
  }

  private function removeExtraZeroes($amount) {
    // take only two digits after the decimal separator

    $parts = explode('.', $amount);
    $newAmount = $parts[0];
    if (count($parts) > 1) {
      $newAmount .= '.' . substr($parts[1], 0, 2);
    }
    else {
      $newAmount .= '.00';
    }

    return $newAmount;
  }
}
