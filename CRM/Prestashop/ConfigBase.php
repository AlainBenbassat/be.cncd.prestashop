<?php

class CRM_Prestashop_ConfigBase {

  protected function createOrGetCustomGroup($params) {
    try {
      $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
        'name' => $params['name'],
      ]);
    }
    catch (Exception $e) {
      $result = civicrm_api3('CustomGroup', 'create', $params);
      $customGroup = $result['values'][0];
    }

    return $customGroup;
  }

  protected function createOrGetCustomField($params) {
    try {
      $customField = civicrm_api3('CustomField', 'getsingle', [
        'custom_group_id' => $params['custom_group_id'],
        'name' => $params['name'],
      ]);
    }
    catch (Exception $e) {
      $params['sequential'] = 1;
      $result = civicrm_api3('CustomField', 'create', $params);
      $customField = $result['values'][0];
    }

    return $customField;
  }


  protected function createOrGetOptionGroup($params) {
    try {
      $optionGroup = civicrm_api3('OptionGroup', 'getsingle', [
        'name' => $params['name'],
      ]);
    }
    catch (Exception $e) {
      $params['sequential'] = 1;
      $result = civicrm_api3('OptionGroup', 'create', $params);
      $optionGroup = $result['values'][0];
    }

    return $optionGroup;
  }

  protected function createOrGetOptionValue($optionGroupId, $optionValueId, $optionValueLabel) {
    try {
      $optionValue = civicrm_api3('OptionValue', 'getsingle', [
        'option_group_id' => $optionGroupId,
        'value' => $optionValueId,
      ]);
    }
    catch (Exception $e) {
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => $optionGroupId,
        'label' => $optionValueLabel,
        'value' => $optionValueId,
        'name' => CRM_Utils_String::munge($optionValueLabel, '_', 64),
        'is_default' => 0,
        'weight' => $optionValueId,
        'is_optgroup' => '0',
        'is_reserved' => '0',
        'is_active' => '1'
      ]);
    }

    return $optionValue;
  }
}
