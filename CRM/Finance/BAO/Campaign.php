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

require_once 'CRM/Core/DAO/Batch.php';

class CRM_Finance_BAO_Campaign 
{
    /**
     * static field for all the contribution information that we can potentially import
     *
     * @var array
     * @static
     */
//    static $_importableFields = null;

    /**
     * static field for all the contribution information that we can potentially export
     *
     * @var array
     * @static
     */
//    static $_exportableFields = null;


    function __construct()
    {
        parent::__construct();
    }
    

    /**
     * Function to get Batch Types list 
     */
/*
    static function getIssueCodes( $result ) {
    
        $sql = "SELECT * FROM ".CIVICRM_MTL_ISSUE_CODE;
        $dao = CRM_Core_DAO::executeQuery( $sql );
        while($dao->fetch()) {
          $result[$dao->code] = $dao->code;
        }
        return $result;
    }
    
    static function getTargetCodes( $result ) {
    
        $sql = "SELECT * FROM ".CIVICRM_MTL_TARGET_CODE;
        $dao = CRM_Core_DAO::executeQuery( $sql );
        while($dao->fetch()) {
          $result[$dao->code] = $dao->code;
        }
        return $result;
    }
    
    static function getMediumCodes( $result ) {
    
        $sql = "SELECT * FROM ".CIVICRM_MTL_MEDIUM_CODE;
        $dao = CRM_Core_DAO::executeQuery( $sql );
        while($dao->fetch()) {
          $result[$dao->code] = $dao->code;
        }
        return $result;
    }
*/   

    public static function getContributionTypeIdByCampaignId($campaignId) {
/*    
        $select_dao = CRM_Core_DAO::executeQuery("SELECT * FROM " . CIVICRM_MTL_CAMPAIGN_FUND_CODE . " WHERE campaign_id = %0", array(
                    array($campaignId, 'Int')
                ));
        if (!$select_dao->fetch()) {
            throw new Exception("No campaign found by id '$campaignId'");
        }
*/
        // TBD HOW DO WE GET THE CONTRIBUTION_TYPE_ID ???
watchdog('getContributionTypeIdByCampaignId', 'getContributionTypeIdByCampaignId l109');        
        $contribution_type_id = 3;
        
        return $contribution_type_id;
    }


}
