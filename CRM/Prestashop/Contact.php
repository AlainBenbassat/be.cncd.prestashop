<?php

class CRM_Prestashop_Contact {
  const LOCATION_TYPE_HOME = 1;
  const LOCATION_TYPE_INVOICING = 5;
  const PRESTASHOP_GENDER_M = 1;
  const PRESTASHOP_GENDER_F = 2;
  const PREFIX_F = 1;
  const PREFIX_M = 3;
  const GENDER_F = 1;
  const GENDER_M = 2;

  public $contactId;

  public function __construct($customer, $address) {
    if (!$this->findContact($customer)) {
      $this->createContact($customer);
    }

    // add the invoice address (if it does not exist yet)
    if ($address) {
      $this->createOrUpdateInvoiceAddress($address);
    }
  }

  private function findContact($customer) {
    // find by id
    $this->contactId = $this->findByExternalIdentifier($customer->id);
    if ($this->contactId) {
      return TRUE;
    }

    // find by name and email
    $this->contactId = $this->findByNameAndEmail($customer->firstname, $customer->lastname, $customer->email);
    if ($this->contactId) {
      return TRUE;
    }

    // find by name (reversed) and email
    $this->contactId = $this->findByNameAndEmail($customer->lastname, $customer->firstname, $customer->email);
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
      'external_identifier' => 'boutiqueCNCD_c' . $customer->id,
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
    $this->contactId = $contact['values'][0]['id'];

    $this->createEmail($this->contactId, $customer->email);
  }

  private function createEmail($contactId, $email) {
    $params = [
      'sequential' => 1,
      'contact_id' => $contactId,
      'email' => $email,
      'location_type_id' => self::LOCATION_TYPE_HOME,
    ];
    civicrm_api3('Email', 'create', $params);
  }

  private function findByExternalIdentifier($id) {
    $params = [
      'sequential' => 1,
      'external_identifier' => 'boutiqueCNCD_c' . $id,
    ];
    $result = civicrm_api3('Contact', 'get', $params);

    if ($result['count'] > 0) {
      return $result['values'][0]['id'];
    }
    else {
      return FALSE;
    }
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

  private function createOrUpdateInvoiceAddress($addressPrestashop) {
    $addressCiviCRM = $this->getInvoicingAddress();
    if ($addressCiviCRM) {
      $this->updateInvoicingAddress($addressPrestashop, $addressCiviCRM);
    }
    else {
      $this->createInvoicingAddress($addressPrestashop);
    }
  }

  private function getInvoicingAddress() {
    $params = [
      'sequential' => 1,
      'contact_id' => $this->contactId,
      'location_type_id' => self::LOCATION_TYPE_INVOICING,
    ];
    $result = civicrm_api3('Address', 'get', $params);
    if ($result['count'] > 0) {
      return $result['values'][0];
    }
    else {
      return FALSE;
    }
  }

  private function updateInvoicingAddress($addressPrestashop, $addressCiviCRM) {
    if ($this->isAddressDifferent($addressPrestashop, $addressCiviCRM)) {
      $this->deleteInvoicingAddress($addressCiviCRM->id);
      $this->createInvoicingAddress($addressPrestashop);
    }
  }

  private function isAddressDifferent($addressPrestashop, $addressCiviCRM) {
    if ($addressCiviCRM->street_address != $addressPrestashop->address1) {
      return TRUE;
    }
    if ($addressCiviCRM->supplemental_address_1 != $addressPrestashop->address2) {
      return TRUE;
    }
    if ($addressCiviCRM->postal_code != $addressPrestashop->postcode) {
      return TRUE;
    }
    if ($addressCiviCRM->city != $addressPrestashop->city) {
      return TRUE;
    }

    return FALSE;
  }

  private function createInvoicingAddress($address) {
    $params = [
      'contact_id' => $this->contactId,
      'location_type_id' => self::LOCATION_TYPE_INVOICING,
      'street_address' => $address->address1,
      'supplemental_address_1' => $address->address2,
      'postal_code' => $address->postcode,
      'city' => $address->city,
      'country_id' => $this->getCountryId($address->country_iso_code),
    ];

    civicrm_api3('Address', 'create', $params);
  }

  private function deleteInvoicingAddress($addressId) {
    civicrm_api3('Address', 'delete', ['id' => $addressId]);
  }

  private function getCountryId($isoCode) {
    if ($isoCode) {
      return CRM_Core_DAO::singleValueQuery("select id from civicrm_country where iso_code = '$isoCode'");
    }
    else {
      return '';
    }
  }

}
