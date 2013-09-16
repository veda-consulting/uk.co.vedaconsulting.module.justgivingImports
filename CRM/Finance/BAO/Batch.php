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

class CRM_Finance_BAO_Batch extends CRM_Batch_DAO_Batch
{
    function __construct()
    {
        parent::__construct();
    }
    
    static function deleteEntry($batchId, $weight) {

        return true;
    }
    
    static function getBatchCustomValues($optionGroupName , &$result) {
        $optionGroupId = self::getOptionGroupId($optionGroupName);
        
        $result = self::getOptionValues($optionGroupId , $result);
        
        return $result;                      
    }


    static function getOptionGroupId($option_group_name){
    
        require_once 'CRM/Core/BAO/OptionGroup.php';
        $params = array("name" => $option_group_name,);
        $default = array();
        $optionGroup = CRM_Core_BAO_OptionGroup::retrieve($params, $default);
        
        if($optionGroup)
            return $optionGroup->id;
        else
            return;
    }
    
    static function getOptionValues($group_id , &$result ) {
    
        $options_sql = "SELECT * FROM civicrm_option_value WHERE option_group_id = '$group_id'";
        $options_dao = CRM_Core_DAO::executeQuery( $options_sql );
        //$tempArray[''] = '-select-';
        while($options_dao->fetch()) {
          $result[$options_dao->value] = $options_dao->label;
        }
        return $result;
        
    }

    static function getCampaignCodes(&$result ) {
    
        $options_sql = "SELECT * FROM civicrm_campaign WHERE option_group_id = '$group_id'";
        $options_dao = CRM_Core_DAO::executeQuery( $options_sql );
        //$tempArray[''] = '-select-';
        while($options_dao->fetch()) {
          $result[$options_dao->value] = $options_dao->label;
        }
        return $result;
        
    }


    function getValidBatchNumber() {
        $sql = "SELECT MAX(batch_number) as max_receipt_number FROM mtl_batch_details";
        $dao = CRM_Core_DAO::executeQuery( $sql);
        $dao->fetch();
        if (!empty($dao->max_receipt_number)) {
            $receipt_number = $dao->max_receipt_number + 1;  
        } else {
            $receipt_number = CIVICRM_MTL_BATCH_FIRST_BATCH_NUMBER;    
        }
        return $receipt_number;
    }
    
    static function createBatchEntry(array $params) {

        //check mandatory $params
        foreach(array(
            'batch_id',
            'contribution_id',
            'weight',
            'contact_display_name',
            'net_amount',
            'payment_instrument_id',
//            'transaction_type',   //SRH Removed 10/09/12. Set when posting
            ) as $param) {
            
            if(!isset($params[$param])) {
                throw new InvalidArgumentException("No param[$param]");
            }
        }

        return array(
            'batch' => $batchContribution
        );
    }
    
    function createEntityBatch(array $params) {

        $insertSql  = " INSERT INTO civicrm_entity_batch SET ";
        $insertSql .= " entity_table = %0, ";
        $insertSql .= " entity_id    = %1, ";
        $insertSql .= " batch_id     = %2 ";   
                
        $params = array(array('civicrm_contribution',          'String'),
                        array((int)$params['contribution_id'], 'Int'),
                        array((int)$params['batch_id'],        'Int'),
                   );

        CRM_Core_DAO::executeQuery($insertSql, $params);  
         
    }
    
    static function createContribution(array $params) {

        //check mandatory $params
        foreach(array(
            'batch_id',
            //'weight',
            'contact_id',
            'contact_display_name',
            'net_amount',
            'received_date',
            'payment_instrument_id',
            'contribution_type_id',
            //'campaign_id',
            ) as $param) {
            
            if(empty($params[$param])) {
                throw new InvalidArgumentException("No param[$param]");
            }
        }
        
        //fix some parameters
        if(!isset($params['gross_amount']) || !is_numeric($params['gross_amount'])) {
            $params['gross_amount'] = $params['net_amount'];
        }
        
        if(!isset($params['fee_amount']) || !is_numeric($params['fee_amount'])) {
            $params['fee_amount'] = $params['gross_amount'] - $params['net_amount'];
        }
        
        $source = 'Batch #'.$params['batch_id'];
        if(!empty($params['source'])) {
            $source .= ' - ' . $params['source'];
        }

        // Create the actual contribution using api
        $contribParams = array (
            'contact_id' => $params['contact_id'],
            'contribution_type_id' => $params['contribution_type_id'], //SRH TBD isn't set for some reason - hardcoded for now
            'contribution_status_id' => 1 ,
            'total_amount' => $params['gross_amount'] ,
            'source' => $source,
            'receive_date' => $params['received_date'] , 
            'payment_instrument_id' => $params['payment_instrument_id'] ,
            'net_amount' => $params['net_amount'],
            'fee_amount' => $params['fee_amount'],
            'version' => 3,
//            'skipRecentView' => true, //matusz: it will not be shown in "Recent items" 
        );

        if(isset($params['transaction_id'])) {
            $contribParams['trxn_id'] = $params['transaction_id'];
        }
        

        $contribution = civicrm_api('Contribution','Create',$contribParams); // TBD - Doesn't error, gets a contribution_id - but no record created???
        
 //       $contribution['id'] = self::tempCreateContribution($contribParams);

        if($contribution['is_error']) {
            require_once 'CRM/Finance/BAO/Import/ProcessException.php';
            throw new RuntimeException($contribution['error_message']);
        }        
        
        $params['contribution_id'] = $contribution['id'];
        
        self::createEntityBatch($params);
        
        // update campaign id for the contribution, as the api is not saving the campaign against the contribution
        $campaignUpdateSql = "UPDATE civicrm_contribution SET campaign_id = %1 WHERE id = %0";

        self::updateBatchAmounts($params);
        
        return array(
            'contribution' => $contribution
        );
    }
    
    static function getBatchByContributionId($id) {
        // Check if the contribution is already in a batch which is posted, if yes, redirect to view screen.
        require_once 'CRM/Core/DAO/EntityBatch.php';
        $entityBatchDao =& new CRM_Core_DAO_EntityBatch( );
        $entityBatchDao->entity_id = $id;
        $entityBatchDao->find(true);
        if (empty($entityBatchDao->batch_id)) {
            throw new Exception("Contribution wasn't created from a batch");
        }
        
        $batchId = $entityBatchDao->batch_id;
        $batchCheckSql = "SELECT * FROM ".CIVICRM_MTL_BATCH_DETAILS." WHERE entity_id = %0";
        $batchCheckDao =  CRM_Core_DAO::executeQuery( $batchCheckSql, array(
            array($batchId, 'Int')
        ));
        
        if (!$batchCheckDao->fetch()) {
            throw new Exception("Can't load batch #$batchId");
        }
        
        return $batchCheckDao;
    }
    
    /***
     * @author mzeman
     * TODO this should be more flexible... and work with any number of params.
     */
    public static function updateBatchDetails($batchId, array $params) {

        $query = "UPDATE " . CIVICRM_MTL_BATCH_DETAILS . " SET expected_entries = %1, expected_value = %2 WHERE entity_id = %0";
        $queryParams = array(
            array($batchId, 'Int'),
            array($params['expected_entries'], 'Int'),
            array($params['expected_amount'], 'Money'),
        );
        
        CRM_Core_DAO::executeQuery($query, $queryParams);
    }
    
    static function createBatch(array $batchDetails) {

        //check mandatory $params
        foreach(array(
            //'description',
            'banking_date',//
            'banking_account',
//            'batch_type',
            'batch_status',
            'exclude_from_posting',
            'payment_instrument_id',
            
            ) as $param) {
            
            if(!isset($batchDetails[$param])) {
                throw new InvalidArgumentException("No param[$param]");
            }
        }
        
        // PS Commented out this way of creating the batch
        // Replaced with using the batch description and tidying
        //$batch_sql = "SELECT max(id) as max_id FROM civicrm_batch";
        //$batch_dao = CRM_Core_DAO::executeQuery($batch_sql);
        //$batch_dao->fetch();
    
        //$nextId = $batch_dao->max_id + 1;
        $session =& CRM_Core_Session::singleton( );
        //$nextId = CRM_Utils_String::titleToVar($batchDetails['description']);
        
        if (!isset($batchDetails['batch_title'])) {
          $batchDetails['batch_title'] = 'Contrib Batch '.CRM_Utils_Date::currentDBDate();
        }
          

        $batchParams = array(
            'title' => $batchDetails['batch_title'],
//            'name'  => $batchDetails['batch_title'],
            'description' => $batchDetails['description'],
            'created_id' => $session->get('userID'),
            'created_date' => CRM_Utils_Date::currentDBDate(),
            'type_id' => 1,
            'status_id' => 2,
//            'total' => $batchDetails['total_net_amount'],
//            'item_count' => $batchDetails['validate_ok'],
        );
        
        // Create the batch     
        require_once 'CRM/Batch/BAO/Batch.php';
        $createdBatch   =& CRM_Batch_BAO_Batch::create( $batchParams );
        $batchDetails['batch_id'] = $createdBatch->id;
        
        require_once 'CRM/Utils/Date.php';
        $batchDetails['banking_date'] = CRM_Utils_Date::processDate($batchDetails['banking_date']);
        
//        require_once 'CRM/Finance/BAO/BatchType.php';
        $expectedPostingDate = CRM_Finance_BAO_BatchType::getBatchTypeExpectedPostingDate();
        
        //matusz: copied from CRM_Batch_Page_AJAX::getContributionTypeForCampaign()
        //$batchDetails['contribution_type_id']
        $campaignId = null;
        if(!empty($batchDetails['campaign_id'])) {
            $campaignId = $batchDetails['campaign_id'];
        }
 /*       
        $contributionTypeId = null;
        if($campaignId !== null) {
            $select_dao = CRM_Core_DAO::executeQuery("SELECT * FROM ".CIVICRM_MTL_CAMPAIGN_FUND_CODE." WHERE campaign_id = %0", array(
                array($campaignId, 'Int')
            ));
            if (!$select_dao->fetch()){
                throw new Exception("No contribution_type_id found by using campaign id '$campaignId'");
            }

            $batchDetails['contribution_type_id'] = $select_dao->contribution_type_id;
        }
 */       
        if(empty($batchDetails['contribution_type_id'])) { 
            $batchDetails['contribution_type_id'] = 0;
        }
        
        $batchDetails['exclude_from_posting'] = empty($batchDetails['exclude_from_posting']) ? 0 : 1;

        $sqlParams = array(
            array($batchDetails['payment_instrument_id'], 'Int'),
            array($expectedPostingDate, 'Timestamp'),
            array($batchDetails['exclude_from_posting'], 'Boolean'),
            array($batchDetails['banking_date'], 'Timestamp'),
            array($batchDetails['banking_account'], 'Int'),
            array($batchDetails['contribution_type_id'], 'Int'),
            array((string)$batchDetails['batch_title'], 'String'),
        );

        $batchDetailsSql  = " UPDATE civicrm_batch SET "; 
        $batchDetailsSql .= "    payment_instrument_id = %0 "; 
        $batchDetailsSql .= " ,  expected_posting_date = %1 "; 
        $batchDetailsSql .= " ,  exclude_from_posting = %2 ";
        $batchDetailsSql .= " ,  banking_date  = %3 "; 
        $batchDetailsSql .= " ,  banking_account = %4 ";
        $batchDetailsSql .= " ,  contribution_type_id = %5 ";
        $batchDetailsSql .= " ,  title = %6 ";
        
        $parameterIndex = 6;

        if($campaignId !== null) {
        
            $parameterIndex = $parameterIndex + 1;            
            $batchDetailsSql .= ", campaign_id = %$parameterIndex ";
            
            $sqlParams[] = array($campaignId, 'Int');
            
        }
/*        
        if(isset($batchDetails['expected_entries'])) {
            $parameterIndex = $parameterIndex + 1;            
            $batchDetailsSql .= ", expected_entries = %$parameterIndex ";
            $sqlParams[] = array($batchDetails['expected_entries'], 'Int');
        }
        
        if(isset($batchDetails['expected_value'])) {
            $parameterIndex = $parameterIndex + 1;            
            $batchDetailsSql .= ", expected_value = %$parameterIndex ";
            $sqlParams[] = array($batchDetails['expected_value'], 'Money');
        }
*/        
        if(isset($batchDetails['entity_type'])) {
            $parameterIndex = $parameterIndex + 1;            
            $batchDetailsSql .= ", entity_type = %$parameterIndex ";
            $sqlParams[] = array($batchDetails['entity_type'], 'String');
        }
        
        $parameterIndex = $parameterIndex + 1;            
        
        $batchDetailsSql .= " WHERE id = %$parameterIndex ";
        $sqlParams[] = array($batchDetails['batch_id'], 'Int');

        CRM_Core_DAO::executeQuery($batchDetailsSql, $sqlParams);
        
        return $batchDetails;
    }
    
    static function validateRowForImport($row) {
        
        $is_valid = null;
          
        if ( empty($row) ) {
            return $is_valid;
        }
        
        // Check if VA reference, contribution amount and contribution dates are empty
        if ( empty($row['VAReference']) || empty($row['grossAmount']) || empty($row['donationDate']) ) {
            return $is_valid;
        }
        
        // Check if the contact exists
        require_once 'CRM/Contact/DAO/Contact.php';
        $contact =& new CRM_Contact_DAO_Contact();
          $contact->external_identifier = $row['VAReference'];
          $contact->find();
          if ( ! $contact->fetch()) {
             return $is_valid;         
          }
        
        $is_valid = 1;
        return $is_valid;         
    }
    
    static function importBatch ($batchDetails , $rows) {

        // Step 1: Create batch
        // Get max batch id
        //$batch_sql = "SELECT max(id) as max_id FROM civicrm_batch";
        //  $batch_dao = CRM_Core_DAO::executeQuery($batch_sql);
        //$batch_dao->fetch();
           
        $batchParams['label']  = $batch_dao->max_id + 1;
        $batchParams['name']  =  CRM_Utils_String::titleToVar($batch_dao->max_id + 1, 63 );

        $session =& CRM_Core_Session::singleton( );
        $batchParams['created_id'] = $session->get( 'userID' );
        $batchParams['created_date'] = date("YmdHis");
            
        // Create the batch     
        require_once 'CRM/Core/BAO/Batch.php';
        $createdBatch   =& CRM_Core_BAO_Batch::create( $batchParams );
        $batchID        = $createdBatch->id;
        
        require_once 'CRM/Contact/DAO/Contact.php';
        require_once 'CRM/Core/DAO/EntityBatch.php';
        $expectedAmt = 0;
        $expectedEntries = count($rows);
        $i = 1;
        foreach ($rows as $key => $row) {

            $contact =& new CRM_Contact_DAO_Contact();
              $contact->external_identifier = $tempdao->VAReference;
              $contact->find();
              $contact->fetch();
            
            // Insert into the quick entries table (Entered rows)  
            $insert_sql = "INSERT INTO ".CIVICRM_MTL_BATCH_ALLOCATION_DETAILS." SET batch_id = '".$batchID."' , weight = '".$i."' , name = '".$contact->display_name."' ,  
                                   amount = '".$row['grossAmount']."' , payment_instrument_id = '".$batchDetails['payment_instrument_id']."'";
            $insert_dao = CRM_Core_DAO::executeQuery( $insert_sql );
            
            // Create the actual contribution using api
            $params = array (
                              'contact_id' => $contact->id ,
                              'contribution_type_id' => $batchDetails['contribution_type_id'],
                              'total_amount' => $row['grossAmount'] ,
                              'receive_date' => $row['donationDate'] , 
                              'payment_instrument_id' => $batchDetails['payment_instrument_id'] ,
                              'source' => 'Batch Allocation. Batch #'.$batchID ,
                              //'campaign_id' => $batchDetails['campaign_id'] , 
                              'version' => 3
                            );

            $contribution = civicrm_api('Contribution','Create',$params);
            $contribution_id = $contribution['id'];
            
            // update campaign id for the contribution, as the api is not saving the campaign against the contribution
            $campaignUpdateSql = "UPDATE civicrm_contribution SET campaign_id = ".$batchDetails['campaign_id']." WHERE id = ".$contribution_id;
            CRM_Core_DAO::executeQuery( $campaignUpdateSql );
            
            $expectedAmt += $row['grossAmount']; 
            
            // Add the contribution to the batch            
            $batchContribution = new CRM_Core_DAO_EntityBatch( );
                    $batchContribution->entity_table = 'civicrm_contribution';
                    $batchContribution->entity_id    = $contribution_id;
                    $batchContribution->batch_id     = $batchID;
                    $batchContribution->save( );
            
            CRM_Core_DAO::executeQuery("INSERT INTO ".CIVICRM_MTL_BATCH_ENTITY_WEIGHT." SET entity_id = {$contribution_id} , batch_id = '{$batchID}' , weight = {$i}");
            
            $i++;
        }
        
        $batchDetailsSql = "INSERT INTO ".CIVICRM_MTL_BATCH_DETAILS." SET entity_id = $batchID , expected_entries = '".$expectedEntries."' ,
                    expected_value = '".$expectedAmt."' , batch_status = '3' , batch_type = '".$batchDetails['batch_type']."' , 
                payment_instrument_id = '".$batchDetails['payment_instrument_id']."' , expected_posting_date = '".$batchDetails['expected_posting_date']."', 
                campaign_id = '".$batchDetails['campaign_id']."', banking_date  = '".$batchDetails['banking_date']."' , 
                banking_account = '".$batchDetails['banking_account']."' , contribution_type_id = '".$batchDetails['contribution_type_id']."' , 
                exclude_from_posting = '".$batchDetails['exclude_from_posting']."'";
        CRM_Core_DAO::executeQuery($batchDetailsSql);
         
        return $batchID;             
    }
    
    function updateBatchAmounts($params) {

        $batchID = $params['batch_id'];
        
        $selectSql  = " SELECT * ";
        $selectSql .= " FROM civicrm_batch ";
        $selectSql .= " WHERE id = %1 ";

        $selectParams = array( 1 => array( $batchID, 'Integer' ) );

        $daoBatch = CRM_Core_DAO::executeQuery( $selectSql, $selectParams );

        $daoBatch->fetch();
        
        if (is_null($daoBatch->total)) {
            $total = 0;
        }
        else {
            $total = $daoBatch->total;
        }
        
        $total = $total + $params['gross_amount'];
        $itemCount = $daoBatch->item_count + 1;
        
        $updateParams = array(
            array($total,     'String'),
            array($itemCount, 'Int'),
            array($batchID,   'Int'),
        );
        
        $updateSql  = " UPDATE civicrm_batch SET "; 
        $updateSql .= "    total = %0 "; 
        $updateSql .= " ,  item_count = %1 "; 
        $updateSql .= " WHERE id = %2 ";
       
        CRM_Core_DAO::executeQuery($updateSql, $updateParams);               
        
    }
    
    function tempCreateContribution($params) {
        
        $sql  = " INSERT INTO civicrm_contribution SET ";
        $sql .= "    contact_id             = %1 ";
        $sql .= " ,  contribution_type_id   = %2 ";
        $sql .= " ,  contribution_status_id = %3 ";
        $sql .= " ,  total_amount           = %4 ";
        $sql .= " ,  source                 = %5 ";
        $sql .= " ,  receive_date           = %6";
        $sql .= " ,  payment_instrument_id  = %7 ";
        $sql .= " ,  net_amount             = %8 ";
        $sql .= " ,  fee_amount             = %9 ";
        $sql .= " ,  trxn_id                = %10 ";
 //       $sql .= " ,  honor_type_id          = %11 ";
 //       $sql .= " ,  honor_contact_id       = %12 ";

        $contributionParams = array();
        $contributionParams[1]  = array((int)$params['contact_id'],             'Int');
        $contributionParams[2]  = array((int)$params['contribution_type_id'],   'Int');
        $contributionParams[3]  = array((int)$params['contribution_status_id'], 'Int');
        $contributionParams[4]  = array((string)$params['total_amount'],        'String');
        $contributionParams[5]  = array((string)$params['source'],              'String');
        $contributionParams[6]  = array((string)$params['receive_date'],        'String');
        $contributionParams[7]  = array((int)$params['payment_instrument_id'],  'Int');
        $contributionParams[8]  = array((string)$params['net_amount'],          'String');
        $contributionParams[9]  = array((string)$params['fee_amount'],          'String');
        $contributionParams[10] = array((string)$params['trxn_id'],             'String');
 //       $contributionParams[11] = array((int)$params['honor_type_id'],          'Int');
 //       $contributionParams[12] = array((int)$params['honor_contact_id'],       'Int');

        $dao = CRM_Core_DAO::executeQuery($sql, $contributionParams);
                
        return $dao->id;
  
    }    
}
