<?php

class CRM_Prestashop_Contribution {
  const FINANCIAL_TYPE_ACHAT = 11;

  public static function create($contactId, $order) {
    if (!self::exists($contactId, $order->id)) {
      $params = [
        'sequential' => 1,
        'contact_id' => $contactId,
        'receive_date' => $order->invoice_date,
        'total_amount' => self::removeExtraZeroes($order->total_paid),
        'currency' => 'EUR',
        'financial_type_id' => self::FINANCIAL_TYPE_ACHAT,
        'payment_instrument_id' => 1, // credit card
        'source' => 'boutique_o' . $order->id,
      ];

      $result = civicrm_api3('Contribution', 'create', $params);
    }
  }

  private static function exists($contactId, $orderId) {
    $sql = "select max(id) from civicrm_contribution where contact_id = $contactId and source = 'boutique_o$orderId' and financial_type_id = " . self::FINANCIAL_TYPE_ACHAT;
    $id = CRM_Core_DAO::singleValueQuery($sql);
    if ($id) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private static function removeExtraZeroes($amount) {
    // take only two digits after the decimal separator

    $parts = explode('.', $amount);
    $newAmount = $parts[0];
    if (count($newAmount) > 1) {
      $newAmount .= substr($newAmount[1], 0, 2);
    }
    else {
      $newAmount .= '.00';
    }

    return $newAmount;
  }
}
