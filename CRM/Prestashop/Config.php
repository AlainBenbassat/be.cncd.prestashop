<?php

class CRM_Prestashop_Config extends CRM_Prestashop_ConfigBase {
  public function checkConfig() {
    $this->getCustomField_orderedProducts();
  }

  public function getCustomField_orderedProducts() {
    static $cache = '';

    if ($cache) {
      return $cache;
    }

    $params = [
      'custom_group_id' => $this->getCustomGroup_ShoppingCartDetail()['id'],
      'name' => 'ordered_products',
      'label' => 'Produits',
      'data_type' => 'String',
      'html_type' => 'CheckBox',
      'is_searchable' => '1',
      'is_search_range' => '0',
      'weight' => '1',
      'is_active' => '1',
      'options_per_line' => '2',
      'text_length' => '255',
      'note_columns' => '60',
      'note_rows' => '4',
      'column_name' => 'ordered_products',
      'option_group_id' => $this->getOptionGroup_PrestashopProducts()['id'],
      'in_selector' => '0'
    ];
    $cache = $this->createOrGetCustomField($params);
    return $cache;
  }

  public function getOptionGroup_PrestashopProducts() {
    $params = [
      'name' => 'prestashop_products',
      'title' => 'Produits Prestashop',
      'data_type' => 'String',
      'is_reserved' => '0',
      'is_active' => '1',
      'is_locked' => '0'
    ];
    return $this->createOrGetOptionGroup($params);
  }

  public function getCustomGroup_ShoppingCartDetail() {
    static $cache = '';

    if ($cache) {
      return $cache;
    }

    $params = [
      'name' => 'shopping_cart_detail',
      'title' => 'Detail panier',
      'extends' => 'Contribution',
      'extends_entity_column_value' => [
        '11'
      ],
      'style' => 'Inline',
      'collapse_display' => '0',
      'weight' => '1',
      'is_active' => '1',
      'table_name' => 'civicrm_value_shopping_cart_detail',
      'is_multiple' => '0',
      'collapse_adv_display' => '0',
      'is_reserved' => '0',
      'is_public' => '0'
    ];
    $cache = $this->createOrGetCustomGroup($params);
    return $cache;
  }

  public function addPrestashopProduct($id, $label) {
    static $optionGroupId = 0;

    if ($optionGroupId == 0) {
      $optionGroupId = $this->getOptionGroup_PrestashopProducts()['id'];
    }

    $this->createOrGetOptionValue($optionGroupId, $id, $label);
  }
}
