<?php
use CRM_Prestashop_ExtensionUtil as E;

function _civicrm_api3_prestashop_Sync_spec(&$spec) {
  $spec['limit']['api.required'] = 0;
}

function civicrm_api3_prestashop_Sync($params) {
  try {
    civicrm_api3_prestashop_validateParams($params);

    $importer = new CRM_Prestashop_Importer();
    $importer->importOrdersByDeliveryNumber($params['limit']);
  }
  catch (Exception $e)  {
    throw new API_Exception('Error in ' . $e->getFile() . ' on line ' . $e->getLine() . ': ' . $e->getMessage(), $e->getCode());
  }
}

function civicrm_api3_prestashop_validateParams(&$params) {
  $limit = CRM_Utils_Array::value('limit', $params);
  if (!$limit || $limit < 0 || $limit > 1000) {
    $params['limit'] = 10;
  }
}

function civicrm_api3_prestashop_validateDate($d) {
  // format must be YYYY-MM-DD
  if (strlen($d) != 10) {
    throw new Exception("Date parameter must be in YYYY-MM-DD format (saw: $d");
  }

  $dateParts = explode('-', $d);
  if (count($dateParts) != 3) {
    throw new Exception("Date parameter must be in YYYY-MM-DD format (saw: $d");
  }

  if ($dateParts[0] < '2010' || $dateParts[0] > '2050') {
    throw new Exception("Date parameter must be in YYYY-MM-DD format. The year seems to be invalid (saw: " . $dateParts[0]);
  }

  if ($dateParts[1] < '01' || $dateParts[1] > '12') {
    throw new Exception("Date parameter must be in YYYY-MM-DD format. The month seems to be invalid (saw: " . $dateParts[1]);
  }

  if ($dateParts[2] < '01' || $dateParts[2] > '31') {
    throw new Exception("Date parameter must be in YYYY-MM-DD format. The month seems to be invalid (saw: " . $dateParts[2]);
  }

}
