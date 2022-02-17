<?php

class CRM_Prestashop_Contribution {
  const FINANCIAL_TYPE_ACHAT = 11;
  const FINANCIAL_TYPE_DON = 1;
  private $config;

  public function __construct($config) {
    $this->config = $config;
  }

  public function create($contactId, $order) {
    if (!$this->exists($contactId, $order->id)) {
      $donationAmount = $this->getDonationAmount($order);
      if ($donationAmount > 0) {
        $contrib = $this->createDonationContribution($contactId, $order, $donationAmount);
        $this->updateDeliveryNumber($contrib['id'], $order);
      }

      $contrib = $this->createPurchaseContribution($contactId, $order, $donationAmount);
      if ($contrib) {
        $this->updateProducts($contrib['id'], $order, $donationAmount);
        $this->updateDeliveryNumber($contrib['id'], $order);
      }
    }
  }

  private function exists($contactId, $orderId) {
    $sql = "select count(id) from civicrm_contribution where source = 'boutique_o$orderId' and financial_type_id in (" . self::FINANCIAL_TYPE_ACHAT . ',' . self::FINANCIAL_TYPE_DON . ')';
    $id = CRM_Core_DAO::singleValueQuery($sql);
    if ($id) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private function getDonationAmount($order) {
    $amount = 0;

    foreach ($order->associations->order_rows as $row) {
      if ($this->isDonationProduct($row->product_name)) {
        $amount += $this->removeExtraZeroes($row->unit_price_tax_incl * $row->product_quantity);
      }
    }

    return $amount;
  }

  private function isDonationProduct($productName) {
    if ($productName == "Don à l'Opération 11.11.11") {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private function createPurchaseContribution($contactId, $order, $donationAmount) {
    $totalAmount = $this->removeExtraZeroes($order->total_paid);
    if ($totalAmount > $donationAmount) {
      $params = [
        'sequential' => 1,
        'contact_id' => $contactId,
        'receive_date' => $order->delivery_date,
        'total_amount' => $totalAmount - $donationAmount,
        'currency' => 'EUR',
        'financial_type_id' => self::FINANCIAL_TYPE_ACHAT,
        'payment_instrument_id' => 1, // credit card
        'source' => 'boutique_o' . $order->id,
      ];

      $result = civicrm_api3('Contribution', 'create', $params);
      return $result;
    }
    else {
      return FALSE;
    }
  }

  private function createDonationContribution($contactId, $order, $donationAmount) {
    $params = [
      'sequential' => 1,
      'contact_id' => $contactId,
      'receive_date' => $order->delivery_date,
      'total_amount' => $donationAmount,
      'currency' => 'EUR',
      'financial_type_id' => self::FINANCIAL_TYPE_DON,
      'payment_instrument_id' => 1, // credit card
      'source' => 'boutique_o' . $order->id,
    ];

    $result = civicrm_api3('Contribution', 'create', $params);
    return $result;
  }

  private function updateProducts($contribId, $order, $donationAmount) {
    $orderContainsDonations = ($donationAmount > 0) ? TRUE : FALSE;

    $products = [];
    foreach ($order->associations->order_rows as $row) {
      if ($orderContainsDonations == FALSE || !$this->isDonationProduct($row->product_name)) {
        $products[] = $row->product_id;
        $this->config->addPrestashopProduct($row->product_id, $row->product_name);
      }
    }

    if (count($products) > 0) {
     civicrm_api3('CustomValue','create', [
       'entity_id' => $contribId,
       'entity_table' => $this->config->getCustomGroup_ShoppingCartDetail()['table_name'],
       'custom_' . $this->config->getCustomField_orderedProducts()['id'] => $products,
     ]);
    }
  }

  private function updateDeliveryNumber($contribId, $order) {
    civicrm_api3('CustomValue','create', [
      'entity_id' => $contribId,
      'entity_table' => $this->config->getCustomGroup_ShoppingCartDetail()['table_name'],
      'custom_' . $this->config->getCustomField_deliveryNumber()['id'] => $order->delivery_number,
    ]);
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

    return (float)$newAmount;
  }
}
