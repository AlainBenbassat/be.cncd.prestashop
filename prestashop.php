<?php

require_once 'prestashop.civix.php';
// phpcs:disable
use CRM_Prestashop_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function prestashop_civicrm_config(&$config) {
  _prestashop_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function prestashop_civicrm_xmlMenu(&$files) {
  _prestashop_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function prestashop_civicrm_install() {
  _prestashop_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function prestashop_civicrm_postInstall() {
  _prestashop_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function prestashop_civicrm_uninstall() {
  _prestashop_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function prestashop_civicrm_enable() {
  _prestashop_civix_civicrm_enable();
  $config = new CRM_Prestashop_Config();
  $config->checkConfig();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function prestashop_civicrm_disable() {
  _prestashop_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function prestashop_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _prestashop_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function prestashop_civicrm_managed(&$entities) {
  _prestashop_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function prestashop_civicrm_caseTypes(&$caseTypes) {
  _prestashop_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function prestashop_civicrm_angularModules(&$angularModules) {
  _prestashop_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function prestashop_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _prestashop_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function prestashop_civicrm_entityTypes(&$entityTypes) {
  _prestashop_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_themes().
 */
function prestashop_civicrm_themes(&$themes) {
  _prestashop_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function prestashop_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function prestashop_civicrm_navigationMenu(&$menu) {
  _prestashop_civix_insert_navigation_menu($menu, 'Administer', [
    'label' => 'Boutique CNCD',
    'name' => 'boutique_cncd',
    'url' => NULL,
    'permission' => 'administer CiviCRM',
    'operator' => NULL,
    'separator' => NULL,
  ]);

  _prestashop_civix_insert_navigation_menu($menu, 'Administer/boutique_cncd', [
    'label' => 'Paramètres système',
    'name' => 'boutique_cncd_parameters',
    'url' => CRM_Utils_System::url('civicrm/prestashop-admin', 'reset=1', TRUE),
    'permission' => 'administer CiviCRM',
    'operator' => NULL,
    'separator' => 0,
  ]);

  _prestashop_civix_insert_navigation_menu($menu, 'Administer/boutique_cncd', [
    'label' => 'Synchroniser une commande',
    'name' => 'boutique_cncd_import_order',
    'url' => CRM_Utils_System::url('civicrm/prestashop-import-order', 'reset=1', TRUE),
    'permission' => 'administer CiviCRM',
    'operator' => NULL,
    'separator' => 0,
  ]);
}
