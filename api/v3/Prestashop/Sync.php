<?php
use CRM_Prestashop_ExtensionUtil as E;

function _civicrm_api3_prestashop_Sync_spec(&$spec) {
  $spec['since']['api.required'] = 0;
}

function civicrm_api3_prestashop_Sync($params) {
  try {

    $importer = new CRM_Prestashop_Importer();
    $importer->importOrdersSince($params);
  }
  catch (Exception $e)  {
    throw new API_Exception('Error in ' . $e->getFile() . ' on line ' . $e->getLine() . ': ' . $e->getMessage(), $e->getCode());
  }
}

function civicrm_api3_prestashop_validateParams(&$params) {
  $since = CRM_Utils_Array::value('since', $params);
  if ($since) {
    civicrm_api3_prestashop_validateDate($since);
  }
  else {
    //$params['since'] =
  }
}

function civicrm_api3_prestashop_validateDate($d) {
  // format must be YYYY-MM-DD
  if (strlen($d) != 10) {
    throw new Exception("'Since' parameter must be in YYYY-MM-DD format (saw: $d");
  }

  $dateParts = explode('-', $d);
  if (count($dateParts) != 3) {
    throw new Exception("'Since' parameter must be in YYYY-MM-DD format (saw: $d");
  }

  if ($dateParts[0] < '2010' || $dateParts[0] > '2050') {
    throw new Exception("'Since' parameter must be in YYYY-MM-DD format. The year seems to be invalid (saw: " . $dateParts[0]);
  }

  if ($dateParts[1] < '01' || $dateParts[1] > '12') {
    throw new Exception("'Since' parameter must be in YYYY-MM-DD format. The month seems to be invalid (saw: " . $dateParts[1]);
  }

  if ($dateParts[1] < '01' || $dateParts[1] > '12') {
    throw new Exception("'Since' parameter must be in YYYY-MM-DD format. The month seems to be invalid (saw: " . $dateParts[1]);
  }

}
