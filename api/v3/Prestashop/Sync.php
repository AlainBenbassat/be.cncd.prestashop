<?php
use CRM_Prestashop_ExtensionUtil as E;

function _civicrm_api3_prestashop_Sync_spec(&$spec) {
  $spec['from']['api.required'] = 0;
  $spec['to']['api.required'] = 0;
}

function civicrm_api3_prestashop_Sync($params) {
  try {
    civicrm_api3_prestashop_validateParams($params);

    $importer = new CRM_Prestashop_Importer();
    $importer->importOrdersBetween($params['from'], $params['to']);
  }
  catch (Exception $e)  {
    throw new API_Exception('Error in ' . $e->getFile() . ' on line ' . $e->getLine() . ': ' . $e->getMessage(), $e->getCode());
  }
}

function civicrm_api3_prestashop_validateParams(&$params) {
  $from = CRM_Utils_Array::value('from', $params);
  if ($from) {
    civicrm_api3_prestashop_validateDate($from);
  }
  else {
    $params['from'] = date('Y-m-d',strtotime("-1 days"));
  }

  $to = CRM_Utils_Array::value('to', $params);
  if ($to) {
    civicrm_api3_prestashop_validateDate($to);
  }
  else {
    $params['to'] = date('Y-m-d');
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
