<?php

require_once 'financialImports.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function financialImports_civicrm_config(&$config) {
    $template =& CRM_Core_Smarty::singleton( );

    $batchingRoot = dirname( __FILE__ );

    $batchingDir = $batchingRoot . DIRECTORY_SEPARATOR . 'templates';

    if ( is_array( $template->template_dir ) ) {
        array_unshift( $template->template_dir, $batchingDir );
    } else {
        $template->template_dir = array( $batchingDir, $template->template_dir );
    }

    // also fix php include path
    $include_path = $batchingRoot . PATH_SEPARATOR . get_include_path( );
    set_include_path( $include_path );
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function financialImports_civicrm_xmlMenu(&$files) {
  _financialImports_civix_civicrm_xmlMenu($files);
}

function financialImports_civicrm_navigationMenu(&$params) {
    $maxKey = ( max( array_keys($params) ) );
    
    $params['civicrm_finance'] = array(
        'attributes' => array (
            'label'      => 'Financial processing',
            'name'       => 'Financial processing',
            'url'        => null,
            //'permission' => 'access FinancialProcessing',
            'operator'   => null,
            'separator'  => null,
            'parentID'   => null,
            'navID'      => 'civicrm_finance',
            'active'     => 1,
        ),
        'child'      => array(
            array(
                'attributes' => array (
                    'label'      => 'Financial import',
                    'name'       => 'Financial import',
                    'url'        => 'civicrm/finance/import?reset=1',
                    //'permission' => 'access FinancialProcessing',
                    'operator'   => null,
                    'separator'  => null,
                    'parentID'   => null,
                    'navID'      => 'civicrm_finance_import',
                    'active'     => 1,
                ),
                'child'      => array(
                )
            ),
        )
    );
    
}


/**
 * Implementation of hook_civicrm_install
 */
function financialImports_civicrm_install() {
  
    // On install, create a table for keeping track of online direct debits
    require_once "CRM/Core/DAO.php";
    
    CRM_Core_DAO::executeQuery("
        CREATE TABLE IF NOT EXISTS `veda_civicrm_financial_import` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `type` varchar(16) NOT NULL,
          `source` varchar(64) NOT NULL,
          `status` int(10) unsigned NOT NULL DEFAULT '0',
          `created_by_id` int(11) NOT NULL COMMENT 'The user contact id that created the record',
          `process_start` datetime NOT NULL,
          `process_end` datetime DEFAULT NULL,
          `handle` varchar(128) DEFAULT NULL,
          `fields` text,
          `count` int(11) DEFAULT NULL,
          `data` text,
          `amount` decimal(20,2) DEFAULT NULL,
          `first_paid_date` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ");
    
    CRM_Core_DAO::executeQuery("
        CREATE TABLE IF NOT EXISTS `civicrm_financial_bank_account` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
          `account_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `account_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `nominal_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `default` tinyint(4) COLLATE utf8_unicode_ci DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ");

/*
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_batch` 
        ADD COLUMN  `payment_instrument_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Payment Method';
    ");
*/    


    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_batch` 
        ADD COLUMN  `expected_posting_date` datetime DEFAULT NULL COMMENT 'Expected Posting Date';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_batch` 
        ADD COLUMN  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'Campaign Id';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_batch` 
        ADD COLUMN  `banking_date` date DEFAULT NULL COMMENT 'Banking Date';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_batch` 
        ADD COLUMN  `banking_account` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Banking Account';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_batch` 
        ADD COLUMN  `contribution_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Contribution Type Id';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_batch` 
        ADD COLUMN  `exclude_from_posting` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Exclude from posting';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_batch` 
        ADD COLUMN  `post_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Contact ID who do post';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_batch` 
        ADD COLUMN  `entity_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_batch` 
        ADD COLUMN  `import_id` int(10) DEFAULT NULL;
    ");
    

/*
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `log_civicrm_batch` 
        ADD COLUMN  `payment_instrument_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Payment Method';
    ");
*/    

/*
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `log_civicrm_batch` 
        ADD COLUMN  `expected_posting_date` datetime DEFAULT NULL COMMENT 'Expected Posting Date';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `log_civicrm_batch` 
        ADD COLUMN  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'Campaign Id';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `log_civicrm_batch` 
        ADD COLUMN  `banking_date` date DEFAULT NULL COMMENT 'Banking Date';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `log_civicrm_batch` 
        ADD COLUMN  `banking_account` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Banking Account';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `log_civicrm_batch` 
        ADD COLUMN  `contribution_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Contribution Type Id';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `log_civicrm_batch` 
        ADD COLUMN  `exclude_from_posting` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Exclude from posting';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `log_civicrm_batch` 
        ADD COLUMN  `post_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Contact ID who do post';
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `log_civicrm_batch` 
        ADD COLUMN  `entity_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;
    ");
    
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `log_civicrm_batch` 
        ADD COLUMN  `import_id` int(10) DEFAULT NULL;
    ");
*/

}

/**
 * Implementation of hook_civicrm_uninstall
 */
function financialImports_civicrm_uninstall() {
  return _financialImports_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function financialImports_civicrm_enable() {
  return _financialImports_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function financialImports_civicrm_disable() {
  return _financialImports_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function financialImports_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _financialImports_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function financialImports_civicrm_managed(&$entities) {
  return _financialImports_civix_civicrm_managed($entities);
}
