<?php

class CRM_Prestashop_Contact {
  const LOCATION_TYPE_HOME = 1;
  const PRESTASHOP_GENDER_M = 1;
  const PRESTASHOP_GENDER_F = 2;
  const PREFIX_F = 1;
  const PREFIX_M = 3;
  const GENDER_F = 1;
  const GENDER_M = 2;

  public $contactId;

  public function __construct($customer) {
    if (!$this->findContact($customer)) {
      $this->createContact($customer);
    }
  }

  private function findContact($customer) {
    // find by name and email
    $this->contactId = $this->findByNameAndEmail($customer->firstname, $customer->lastname, $customer->email);
    if ($this->contactId) {
      return TRUE;
    }

    // not found, try email only
    $this->contactId = $this->findByEmail($customer->email);
    if ($this->contactId) {
      return TRUE;
    }

    // not found, give up
    return FALSE;
  }

  private function createContact($customer) {
    $params = [
      'sequential' => 1,
      'first_name' => $customer->firstname,
      'last_name' => $customer->lastname,
      'contact_type' => "Individual",
    ];

    if ($customer->birthday > '0000-00-00') {
      $params['birth_date'] = $customer->birthday;
    }

    if ($customer->id_gender == self::PRESTASHOP_GENDER_M) {
      $params['prefix_id'] = self::PREFIX_M;
      $params['gender_id'] = self::GENDER_M;
    }
    elseif ($customer->id_gender == self::PRESTASHOP_GENDER_F) {
      $params['prefix_id'] = self::PREFIX_F;
      $params['gender_id'] = self::GENDER_F;
    }

    $contact = civicrm_api3('Contact', 'create', $params);

    $this->createEmail($contact['values'][0], $customer->email);
  }

  private function createEmail($contactId, $email) {
    $params = [
      'sequential' => 1,
      'contact_id' => $contactId,
      'email' => $email,
      'location_type_id' => self::LOCATION_TYPE_HOME,
    ];
    $contact = civicrm_api3('Contact', 'create', $params);
  }

  private function findByNameAndEmail($firstName, $lastName, $email) {
    $sql = "
      select
        c.id
      from
        civicrm_contact c
      inner join
        civicrm_email e on e.contact_id = c.id
      where
        c.is_deleted = 0
      and
        c.first_name = %1
      and
        c.last_name = %2
      and
        e.email = %3
    ";
    $sqlParams = [
      1 => [$firstName, 'String'],
      2 => [$lastName, 'String'],
      3 => [$email, 'String'],
    ];
    $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
    if ($dao->fetch()) {
      return $dao->id;
    }
    else {
      return FALSE;
    }
  }

  private function findByEmail($email) {
    $sql = "
      select
        c.id
      from
        civicrm_contact c
      inner join
        civicrm_email e on e.contact_id = c.id
      where
        c.is_deleted = 0
      and
        e.email = %1
      order by
        c.id
    ";
    $sqlParams = [
      1 => [$email, 'String'],
    ];
    $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
    if ($dao->fetch()) {
      return $dao->id;
    }
    else {
      return FALSE;
    }
  }

}
