<?php

use CRM_Prestashop_ExtensionUtil as E;

class CRM_Prestashop_Form_ImportOrder extends CRM_Core_Form {
  public function buildQuickForm() {
    $this->setTitle('Importer une commande de la boutique CNCD (PrestaShop)');

    $this->addFormFields();
    $this->addFormButtons();

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    try {
      $importer = new CRM_Prestashop_Importer();
      $importer->throwErrorWhenNotFound = TRUE;
      $orderId = $this->getSubmittedOrderId();

      if ($orderId) {
        $importer->importOrder($orderId);
        CRM_Core_Session::setStatus('OK', '', 'success');
      }
      else {
        [$before, $after] = $importer->fixMissedOrders();
        CRM_Core_Session::setStatus("Nombre de trous dans la liste : avant = $before, après : $after", '', 'success');
      }
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), '', 'error');
    }

    parent::postProcess();
  }

  private function getSubmittedOrderId() {
    $values = $this->exportValues();
    $orderId = CRM_Utils_Array::value('order_id', $values);
    return $orderId;
  }

  private function addFormFields() {
    $this->add('text', 'order_id', 'Importer une commande spécifique:<br><br><br>ou laisser vide pour vérifier les trous dans la liste de numéros de livraison', ['placeholder' => 'p.ex. 4674'], FALSE);
  }

  private function addFormButtons() {
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => 'Importer',
        'isDefault' => TRUE,
      ],
    ]);
  }

  public function getRenderableElementNames() {
    $elementNames = array();
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
