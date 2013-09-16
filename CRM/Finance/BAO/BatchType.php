<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Batch/DAO/Batch.php';
require_once 'CRM/Contact/BAO/Relationship.php';
require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Core/BAO/CustomValue.php';

class CRM_Finance_BAO_BatchType extends CRM_Batch_DAO_Batch
{
    /**
     * static field for all the contribution information that we can potentially import
     *
     * @var array
     * @static
     */
    static $_importableFields = null;

    /**
     * static field for all the contribution information that we can potentially export
     *
     * @var array
     * @static
     */
    static $_exportableFields = null;


    function __construct()
    {
        parent::__construct();
    }
    

    /**
     * Function to get Batch Types list 
     */

    static function getBatchTypesList( &$result ) {
/*    
        $sql = "SELECT * FROM ".CIVICRM_MTL_BATCH_TYPES;
        $dao = CRM_Core_DAO::executeQuery( $sql );
        //$tempArray[''] = '-select-';
        while($dao->fetch()) {
          $result[$dao->id] = $dao->code;
        }
        return $result;
*/ 

        $batchTypeId = 64; //TBD WE NEED TO GET THIS!!

        $sql  = " SELECT id, label ";
        $sql .= " FROM civicrm_option_value ";
        $sql .= " WHERE option_group_id = %1 ";  
        
        $params = array( 1 => array( $batchTypeId, 'Integer' ) );
        
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        while($dao->fetch()) {
          $result[$dao->id] = $dao->label;
        }
        
        return $result;        
    }
    
    static function getContributionTypesList( &$result ) {

        $sql  = " SELECT id, name ";
        $sql .= " FROM  civicrm_financial_type ";
        $sql .= " ORDER BY name ";  
        
        $dao = CRM_Core_DAO::executeQuery( $sql );

        while($dao->fetch()) {
          $result[$dao->id] = $dao->name;
        }
        
        return $result;        
    }
    
    
    static function getBatchTypeExpectedPostingDate() {

        $postingDate = date('YmdHis');
        
        return $postingDate;
    }
}
