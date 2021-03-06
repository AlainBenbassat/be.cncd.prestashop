<?php

use CRM_Prestashop_ExtensionUtil as E;

class CRM_Prestashop_Form_Admin extends CRM_Core_Form {
  private $settings;

  public function __construct($state = NULL, $action = CRM_Core_Action::NONE, $method = 'post', $name = NULL) {
    $this->settings = new CRM_Prestashop_Settings();

    parent::__construct($state, $action, $method, $name);
  }

  public function buildQuickForm() {
    $this->setTitle('Paramètres système passerelle PrestaShop - CiviCRM');

    $this->addFormFields();
    $this->setFormFieldsDefaultValues();
    $this->addFormButtons();

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    try {
      $values = $this->exportValues();
      $this->saveFormFieldValues($values);
      $data = $this->testApi();

      CRM_Core_Session::setStatus($data, '', 'success');
      parent::postProcess();
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), '', 'error');
    }
  }

  private function addFormFields() {
    $this->add('text', 'prestashop_uri', 'Lien de la boutique');
    $this->add('text', 'prestashop_token', 'Clé webservice');
    $this->addRadio('prestashop_access_method', "Méthode d'accès", ['basic' => 'Basic Authentication', 'ws_key' => 'Paramètre ws_key']);
  }

  private function addFormButtons() {
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => 'Sauvegarder et tester',
        'isDefault' => TRUE,
      ],
    ]);
  }

  private function setFormFieldsDefaultValues() {
    $defaults = [];
    $defaults['prestashop_uri'] = $this->settings->getUri();
    $defaults['prestashop_token'] = $this->settings->getToken();
    $defaults['prestashop_access_method'] = $this->settings->getAccessMethod();
    $this->setDefaults($defaults);
  }

  private function saveFormFieldValues($values) {
    $v = CRM_Utils_Array::value('prestashop_uri', $values);
    if ($v) {
      $this->settings->setUri($v);
    }

    $v = CRM_Utils_Array::value('prestashop_token', $values);
    if ($v) {
      $this->settings->setToken($v);
    }

    $v = CRM_Utils_Array::value('prestashop_access_method', $values);
    if ($v) {
      $this->settings->setAccessMethod($v);
    }
  }

  private function testApi() {
    $api = new CRM_Prestashop_Api($this->settings);
    return $api->test();
  }

  private function getRenderableElementNames() {
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
