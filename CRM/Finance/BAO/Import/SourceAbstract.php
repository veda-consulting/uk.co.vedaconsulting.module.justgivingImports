<?php

require_once 'CRM/Finance/Utils/DataExchange.php';
require_once 'CRM/Finance/BAO/Import/ValidateException.php';

abstract class CRM_Finance_BAO_Import_SourceAbstract {
    const STATUS_VALIDATE_STARTED = 10;
    const STATUS_VALIDATE_FINISHED = 11;
    const STATUS_PROCESS_STARTED = 20;
    const STATUS_PROCESS_FINISHED = 21;
    
    const DATA_STATUS_VALIDATE_OK = 1;
    const DATA_STATUS_PROCESS_OK = 2;

    const DATA_STATUS_VALIDATE_ERROR = 10;

    //matusz: when addind some error state make sure you add it
    //into the browser selector CRM_Finance_Selector_Import::__construct
    const VALIDATE_ERR_REF_EMPTY = 15;
    const VALIDATE_ERR_REF_NO_CONTACT = 16;
    const VALIDATE_ERR_NO_CONTACT = 17;

    const VALIDATE_ERR_GROSSAMOUNT_INVALID = 21;

    const VALIDATE_ERR_NETAMOUNT_INVALID = 24;

    const VALIDATE_ERR_DONATIONDATE_EMPTY = 25;
    const VALIDATE_ERR_DONATIONDATE_INVALID_FORMAT = 26;
    const VALIDATE_ERR_PAIDTOCHARITYDATE_EMPTY = 27;
    const VALIDATE_ERR_PAIDTOCHARITYDATE_INVALID = 33;
    const VALIDATE_ERR_DIRECT_DEBIT_REF = 28;
    const VALIDATE_ERR_FEE_AMOUNT_INVALID = 29;
    const VALIDATE_ERR_TRANSACTION_EXISTS = 30;

    const VALIDATE_ERR_CAMPAIGN_CODE = 31;
    const VALIDATE_ERR_FINANCIAL_IMPORT_REF_NO_CONTACT = 32;

    const VALIDATE_ERR_DEBIT_INVALID = 36;
    const VALIDATE_ERR_CREDIT_INVALID = 37;
    
    // PS 03/10/2012
    // Added for New Campaign ID validation
    const VALIDATE_ERR_CAMPAIGN_ID = 35;

    const DATA_STATUS_PROCESS_ERROR = 60;    

    //subclasses can implement it's own error codes starting from 100

    public function getStatuses() {
        return array(
            self::DATA_STATUS_VALIDATE_OK => 'Validated',
            self::DATA_STATUS_PROCESS_OK => 'Processed',
            self::DATA_STATUS_VALIDATE_ERROR => 'Validation error',
            self::VALIDATE_ERR_REF_EMPTY => 'External Identifier is empty',
            self::VALIDATE_ERR_REF_NO_CONTACT => "Cannot find contact using External Identifier",            
            self::VALIDATE_ERR_NO_CONTACT => "Invalid contact",
            self::VALIDATE_ERR_GROSSAMOUNT_INVALID => 'Gross amount is invalid',
            self::VALIDATE_ERR_NETAMOUNT_INVALID => 'Net amount is invalid',
            self::VALIDATE_ERR_DONATIONDATE_EMPTY => 'Donation date is empty',
            self::VALIDATE_ERR_DONATIONDATE_INVALID_FORMAT => 'Donation date is invalid',
            self::VALIDATE_ERR_PAIDTOCHARITYDATE_EMPTY => 'Paid to charity date is empty',
            self::VALIDATE_ERR_PAIDTOCHARITYDATE_INVALID => 'Paid to charity date is invalid',
            self::VALIDATE_ERR_DIRECT_DEBIT_REF => 'Direct debit ref does not exist',
            self::VALIDATE_ERR_FEE_AMOUNT_INVALID => 'Fee amount is invalid',
            self::VALIDATE_ERR_TRANSACTION_EXISTS => 'Transaction exists already',
            self::VALIDATE_ERR_CAMPAIGN_CODE => 'Invalid campaign code/approach',
            self::DATA_STATUS_PROCESS_ERROR => 'Processing error',
            self::VALIDATE_ERR_CAMPAIGN_ID => 'Invalid campaign id',
            self::VALIDATE_ERR_FINANCIAL_IMPORT_REF_NO_CONTACT => "Cannot find contact using Financial Import Reference",            
            self::VALIDATE_ERR_DEBIT_INVALID => 'Debit amount is invalid',
            self::VALIDATE_ERR_CREDIT_INVALID => 'Credit amount is invalid',
        );
    }           

    public static function isErrorStatus($status) {
        if ($status >= 10) {
            return true;
        }

        return false;
    }

    private $dataExchange;
    private $importId;
    private $sourceName;
    private $defaultPaymentMethodId;
    protected $grossAmountField = 'gross_amount';
    protected $netAmountField = 'net_amount';
    //check buildExtraDataDefaultQuickForm() for possible values
    protected $hideDefaultFields = array();

    /**
     * Used to create an import from specific source of the provider - file, API
     * Should make use of dataexchange util to create import e.g. importCsvToDb
     */
    abstract public function import($params = null);

    /**
     * Used to validate one record from import table
     *
     * @param array $rec
     * @return array(
     *    'status' => int
     *    'update' => array(field => newvalue)
     * )
     */
    abstract protected function validateRec(array $rec, array $importData);

    /**
     * Used to process one record from import table (already validated),
     * so checked for data consistencies etc.
     *
     * @param array $rec
     * @return int new status of the processed row
     */
    abstract protected function processRec($weight, array $rec, array $importData);

    public function buildQuickForm(CRM_Core_Form $form) {
        //dummy create
    }

    public function getSearchContactName(array $rec) {
        return $rec['contact_display_name'];
    }

    public function setDefaultPaymentMethodId($paymentId) {
        $this->defaultPaymentMethodId = $paymentId;
    }

    public function getDefaultPaymentMethodId() {
        if ($this->defaultPaymentMethodId === null) {
            throw new RuntimeException("No default payment method set");
        }
        return $this->defaultPaymentMethodId;
    }

    public function getGrossAmountField() {
        if (empty($this->grossAmountField)) {
            throw new Exception("No grossAmountField defined");
        }
        return $this->grossAmountField;
    }

    public function getNetAmountField() {
        if (empty($this->netAmountField)) {
            throw new Exception("No netAmountField defined");
        }
        return $this->netAmountField;
    }

    public function buildExtraDataQuickForm(CRM_Core_Form $form) {
        $this->buildExtraDataDefaultQuickForm($form);
    }

    protected function buildExtraDataDefaultQuickForm(CRM_Core_Form $form) {
        require_once 'CRM/Finance/BAO/BatchType.php';
        require_once 'CRM/Finance/BAO/BankAccount.php';
        require_once 'CRM/Contribute/PseudoConstant.php';

        $form->add('text', 'batch_title', ts('Batch Title'), null, true);

        $form->add('textarea', 'description', ts('Description'), null, false);

        $emptySelect1[''] = '- select -';
        $bankAccounts = CRM_Finance_BAO_BankAccount::getBankAccountsList($emptySelect1);
        $form->add('select', 'banking_account', ts('Bank Account'), $bankAccounts, true);

        //TODO: how to get this in civicrm default format?
        $current = CRM_Utils_Date::getToday(null, 'm/d/Y');
        $form->addDate('banking_date', ts('Banking Date'), true, array(
            'formatType' => 'activityDate',
            'value' => $current,
        ));

        $form->addElement('checkbox', 'exclude_from_posting', ts('Exclude from posting'), null, null);

        $emptySelect[''] = '- select -';
        $contributionTypes = CRM_Finance_BAO_BatchType::getContributionTypesList($emptySelect);
        $form->add('select', 'contribution_type_id', ts('Contribution Type'), $contributionTypes, true);


        $el = $form->add('select', 'payment_instrument_id', ts('Payment Method'), array('' => ts('- select -')) + CRM_Contribute_PseudoConstant::paymentInstrument(), true);
        $el->setValue($this->getDefaultPaymentMethodId());

        if (!in_array('campaign_id', $this->hideDefaultFields)) {
            $form->add('text', 'campaign_name', ts('Default Campaign Code'), array('class' => 'form text huge'), true);
            $form->add('hidden', 'campaign_id');
        }

        if (!in_array('received_date', $this->hideDefaultFields)) {
            $form->addDate('received_date', ts('Received date'), true, array(
                'formatType' => 'activityDate',
                'value' => $current
            ));
        }



        //$form->add('select', 'contribution_type_id', ts('Contribution Type ID'), array(''=>ts( '- select -' )) + CRM_Contribute_PseudoConstant::contributionType( ), true );
        //matusz: http://support.vedaconsulting.co.uk/issues/81
        //$form->addDate( 'expected_posting_date', ts('Expected posting date'), true, array('formatType' => 'activityDate') );


        $form->addFormRule(array($this, 'extraDataQuickFormRule'));
    }

    public function extraDataQuickFormRule($values) {
        foreach (array('received_date', 'banking_date') as $required) {
            if (!in_array($required, $this->hideDefaultFields)) {
                if (empty($values[$required])) {
                    $errs[$required] = ucfirst(str_replace('_', ' ', $required)) . " is required field.";
                }
            }
        }

        if (!empty($errs)) {
            return $errs;
        }

        return true;
    }

    protected function preProcess(array $importData) {
        if (!isset($importData['data']['batch_id'])) {
            if (isset($importData['data']['received_date'])) { //TBD ADDED isset()
                $data = $importData['data']['received_date'];
                $date = CRM_Utils_Date::processDate($data, null, null, 'Y-m-d');
                $importData['data']['received_date'] = $date;
            }

            $this->createBatch($importData);
        }
    }

    /**
     *
     * Enter description here ...
     * @param array $rec
     * @param string $field
     * @param string $validType
     * @param array $update
     * @param array $params
     * @throws Exception
     */
    protected function validateField(&$rec, $field, $validType, &$update, $params = array()) {
        if (!array_key_exists($field, $rec)) {
            throw new Exception("Not in record '$field'");
        }

        $value = $rec[$field];

        //do value tranformation
        switch ($validType) {
            case 'updateDate':
            case 'paidToCharityDate':
            case 'donationDate':
                if (isset($params['format'])) {
                    $date = DateTime::createFromFormat($params['format'], $value);
                    if ($date !== false) {
                        $rec[$field] = $date->format('Y-m-d');
                        $update[$field] = $rec[$field];
                    }
                }
                break;
        }

        $updates = $this->validateValue($rec, $field, $validType);     
        
        if (is_array($updates) && count($updates)) {
            $update = array_merge($update, $updates);
            $rec = array_merge($rec, $updates);
        }

    }

    protected function validateValue($rec, $field, $validType, $exceptionCode = 0, $import_type = 'JG') {

        if (!array_key_exists($field, $rec)) {
            throw new Exception("Not in record '$field'");
        }
        $value = $origValue = $rec[$field];

        switch ($validType) {
            //===== general types ====
            case 'money':

                //matusz: filter out all which does not make a money value invalid
                $value = str_replace('£', '', $value);
                $value = str_replace(',', '', $value);
                $value = trim($value);

                if (strlen($value) == 0) {
                    // Null entry
//                    throw new CRM_Finance_BAO_Import_ValidateException(
//                            "Can't be empty",
//                            $exceptionCode,
//                            $value);
                    $value = 0;                            
                }
                if (!is_numeric($value)) {

                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "Invalid number",
                            $exceptionCode,
                            $value);
                }
                $value = floatval($value);
                //mzeman: we take negative values also as per discussion with psaleh
//                if ($value < 0) {
//                    throw new CRM_Finance_BAO_Import_ValidateException(
//                            "Value is zero or less",
//                            $exceptionCode,
//                            $value);
//                }

                break;
            case 'contactId':
                if (empty($value)) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "Contact id is empty",
                            self::VALIDATE_ERR_NO_CONTACT,
                            $value);
                }
                // Check if the contact exists
                require_once 'CRM/Contact/DAO/Contact.php';
                $contact = new CRM_Contact_DAO_Contact();
                $contact->id = $value;
                $contact->find();
                if (!$contact->fetch()) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "Can't find contact for this id",
                            self::VALIDATE_ERR_NO_CONTACT,
                            $value);
                }

                break;

            //===== specific types =====
            case 'VARef':
                $value = trim($value);

                if (empty($value)) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "VARef is empty",
                            self::VALIDATE_ERR_REF_EMPTY,
                            $value);
                }

                // Check if the contact exists
                require_once 'CRM/Contact/DAO/Contact.php';
                $contact = new CRM_Contact_DAO_Contact();
                $contact->external_identifier = $value;
                
                $contact->find();
                if (!$contact->fetch()) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "Can't find contact for this VAReference",
                            self::VALIDATE_ERR_REF_NO_CONTACT,
                            $value);
                }
                return array(
                    $field => $value,
                    'contact_id' => $contact->id,
                    'contact_display_name' => $contact->display_name,
                );

                break;

            case 'campaignCode':
                $value = trim($value);

                if (empty($value)) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "Code invalid",
                            self::VALIDATE_ERR_CAMPAIGN_CODE,
                            $value);
                }
                // Check if the contact exists
                require_once 'CRM/Campaign/BAO/Campaign.php';
                $ret = civicrm_api('Campaign', 'GET', array('external_identifier' => $value, 'version' => 3));
                if ($ret['is_error'] || $ret['count'] == 0) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "Can't find campaign for this code",
                            self::VALIDATE_ERR_CAMPAIGN_CODE,
                            $value);
                }

                $id = $ret['id'];

                $contId = $this->getContributionTypeIdByCampaignId($id);

                //matusz: TODO check issue here also if its available and assigned to charity

                return array(
                    $field => $value,
                    'campaign_id' => $id,
                    'contribution_type_id' => $contId,
                );
                break;
            // PS 03/10/2012
            // New case to deal with Campaign ID now being passed in by External Sources
            case 'campaignID':
                $value = trim($value);

                if (empty($value)) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "ID invalid",
                            self::VALIDATE_ERR_CAMPAIGN_ID,
                            $value);
                }
                // Check if the contact exists
                require_once 'CRM/Campaign/BAO/Campaign.php';
                $ret = civicrm_api('Campaign', 'GET', array('id' => $value, 'version' => 3));
                if ($ret['is_error'] || $ret['count'] == 0) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "Can't find campaign for this id",
                            self::VALIDATE_ERR_CAMPAIGN_ID,
                            $value);
                }

                $id = $ret['id'];

                $contId = $this->getContributionTypeIdByCampaignId($id);

                //matusz: TODO check issue here also if its available and assigned to charity

                return array(
                    $field => $value,
                    'campaign_id' => $id,
                    'contribution_type_id' => $contId,
                );
                break;
            case 'grossAmount':
                return $this->validateValue($rec, $field, 'money', self::VALIDATE_ERR_GROSSAMOUNT_INVALID);
                break;
            case 'netAmount':
                return $this->validateValue($rec, $field, 'money', self::VALIDATE_ERR_NETAMOUNT_INVALID);
                break;
            case 'feeAmount':
                try {
                    return $this->validateValue($rec, $field, 'money', self::VALIDATE_ERR_FEE_AMOUNT_INVALID);
                } catch (CRM_Finance_BAO_Import_ValidateException $e) {
                    $rec[$field] = $rec['gross_amount'] - $rec['net_amount'];
                    return $this->validateValue($rec, $field, 'money', self::VALIDATE_ERR_FEE_AMOUNT_INVALID);
                }
                break;
            case 'debit':
                $debit = $this->validateValue($rec, $field, 'money', self::VALIDATE_ERR_DEBIT_INVALID);
                
                return $debit;
                break;
            case 'credit':
                $credit = $this->validateValue($rec, $field, 'money', self::VALIDATE_ERR_CREDIT_INVALID);

                return array(
                    $field => $credit,
                    'net_amount' => $credit,
                );

                break;
            case 'transactionId':
                require_once('CRM/Contribute/BAO/Contribution.php');

                $contribution = new CRM_Contribute_BAO_Contribution();
                $contribution->trxn_id = $value;
                $found = $contribution->find(true);
                if ($found) {
                    //mzeman: TODO XXX DEBUG if exists delete so it's valid
                    //$contribution->delete();
                    //return array();

                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "Transaction ID exists already",
                            self::VALIDATE_ERR_TRANSACTION_EXISTS,
                            $value);
                }
                break;
            case 'donationDate':
                if (empty($value)) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "donationDate is empty",
                            self::VALIDATE_ERR_DONATIONDATE_EMPTY,
                            $value);
                }

                $date = DateTime::createFromFormat('Y-m-d', $value);

                if ($date === false) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "donationDate is not in valid format YYYY-MM-DD",
                            self::VALIDATE_ERR_DONATIONDATE_INVALID_FORMAT,
                            $value);
                }

                break;
            case 'paidToCharityDate':
                if (empty($value)) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "paidToCharityDate is empty",
                            self::VALIDATE_ERR_PAIDTOCHARITYDATE_EMPTY,
                            $value);
                }

                $date = DateTime::createFromFormat('Y-m-d', $value);
                if ($date === false) {
                    throw new CRM_Finance_BAO_Import_ValidateException(
                            "donationDate is not in valid format YYYY-MM-DD",
                            self::VALIDATE_ERR_PAIDTOCHARITYDATE_INVALID,
                            $value);
                }
                break;
            case 'directDebitRef':
                return $this->validateDirectTransferRef('DIRECTDEBIT', $value, self::VALIDATE_ERR_DIRECT_DEBIT_REF);
                break;
            case 'payrollCTCRef':
                return $this->validateDirectTransferRef('PAYROLLCTC', $value, self::VALIDATE_ERR_DIRECT_DEBIT_REF);
                break;
            case 'financialImportReference':
                return $this->validateDirectTransferRef($this->directTransferCode, $value, self::VALIDATE_ERR_DIRECT_DEBIT_REF);
                break;
            default:
                throw new Exception("N/I validType: '$validType'");
        }

        if ($value !== $origValue) {
            return array($field => $value);
        }

        return array();
    }

    protected function validateDirectTransferRef($type, $value, $excCode) {
        $directTransferId = $this->getDirectTransferIdByDirectTransferRef($type, $value);
        //mzeman: TODO XXX - debug only so we have got the contact to assign it to
        //$contactId = 1;

        //if (empty($contactId)) {
        if ((!isset($directTransferId) || $directTransferId == null)) {
            throw new CRM_Finance_BAO_Import_ValidateException(
                    "Can't find contact record",
                    $excCode,
                    $value);
        }

        $contactId = $this->getContactIdByDirectTransferId($directTransferId);
        
        // Check if the contact exists
        require_once 'CRM/Contact/DAO/Contact.php';
        $contact = new CRM_Contact_DAO_Contact();
        $contact->id = $contactId;
        $contact->find();
        if (!$contact->fetch()) {
            throw new CRM_Finance_BAO_Import_ValidateException(
                    "Can't find contact for this direct ref",
                    $excCode,
                    $value);
        }
        
        $ret = array(
            'contact_id' => $contact->id,
            'contact_display_name' => $contact->display_name,
            'direct_transfer_id' => $directTransferId,
        );
        
        // Assume if we've come this far then the contact id is ok
        // lets get the direct transfer ref id
        $campaignId = $this->getCampaignIdByDirectTransferId($directTransferId);
        $contributionTypeId = null;
        if($campaignId) {
            // Get the contribution type id for the contirbution

            $contributionTypeId = $this->getContributionTypeIdByCampaignId($campaignId);
            
            $ret['campaign_id'] = $campaignId;
            $ret['contribution_type_id'] = $contributionTypeId;
        }
        
        // Get the in memory contact
        $inMemContactId = $this->getInMemContactIdByDirectTransferId($directTransferId);
        if($inMemContactId) {
            $ret['in_memory_contact_id'] = $inMemContactId;
        }
        
        return $ret;
    }

    protected function createDirectTransfer($type, array $params) {
        foreach (array(
            'contact_id',
            'direct_transfer_ref',
            'received_date',
            'net_amount') as $required) {

            if (!isset($params[$required]) || empty($params[$required])) {
                throw new InvalidArgumentException("Missing params[$required]");
            }
        }

        $contactId = $params['contact_id'];
        $ref = $params['direct_transfer_ref'];

        //mzeman: we do nothing if reference value is empty
        if(empty($ref)) {
            return;
        }
        
        $ret = civicrm_api('Contact', 'GET', array(
            'version' => 3,
            'id' => $contactId,
            'custom_134' => $ref,
            'custom_135' => $type,
        ));

        if ($ret['count'] == 0) {
            $date = DateTime::createFromFormat('Y-m-d', $params['received_date']);
            $receivedDate = $date->format('Ymd');

            $ret = civicrm_api('CustomValue', 'CREATE', array(
                'version' => 3,
                'entity_id' => $contactId,
                'custom_134' => $ref,
                'custom_135' => $type,
                'custom_136' => $receivedDate,
                'custom_137' => $receivedDate,
                'custom_138' => true,
                'custom_140' => $params['net_amount'],
                'custom_143' => "Batch #" . $params['batch_id'],
            ));
        }
    }
    
    protected function validateFinancialTransferRef($type, $value, $excCode, $rec) {
        $value = trim($value);

        if (empty($value)) {
            throw new CRM_Finance_BAO_Import_ValidateException(
                    "Financial Import invalid",
                    $excCode,
                    $value);
        }

        if (empty($type)) {
            throw new CRM_Finance_BAO_Import_ValidateException(
                    "Financial Import Type invalid",
                    $excCode,
                    $type);
        }
        
        // PS 19112012
        // Need to set this to zero to avoid update failure later on
        // If we have manually attached the contact then we want to indicate that the direct transfer rec needs creating
        $directTransferId = 0;
        
        // PS 19112012
        // If the record has a contact id against it then it must have been allocated already
        // So we can ignore this validation
        // Its up to the source process if it wishes to create the Financial import record or not
        if ( (empty($rec['contact_id']))) {

            // First get the group id for the Financial Import Reference Group
            $custom_group_name = 'Financial_Import_Reference';
            $params = array(
                'version' => 3,      
                'sequential' => 1,
                'name' => $custom_group_name,
            );

            $custom_group_ret = civicrm_api('CustomGroup', 'GET', $params);
//print_r($custom_group_ret);
            if ($custom_group_ret['is_error'] || $custom_group_ret['count'] == 0) {
                throw new CRM_Finance_BAO_Import_ValidateException(
                        "Can't find custom group for Financial_Import_Reference",
                        $excCode,
                        $value);
            }

            $customGroupID = $custom_group_ret['id'];
            $customGroupTableName = $custom_group_ret['values'][0]['table_name'];
//print_r($customGroupTableName);
            // Now try and find a record with the reference passed
            $params = array(
                'version' => 3,      
                'sequential' => 1,
                'custom_group_id' => $customGroupID,
            );

            $custom_field_ret = civicrm_api ('CustomField','GET',$params);
//print_r($custom_field_ret);
            foreach($custom_field_ret['values'] as $k => $field){
                    $field_attributes[$field['name']] = $field;
            }                

            $sourceTypeColumnName = $field_attributes['Source_System']['column_name'];
            $sourceDonorIdentifier = $field_attributes['Identifier']['column_name'];

            $query = "SELECT id, entity_id FROM $customGroupTableName WHERE $sourceTypeColumnName = %0 AND $sourceDonorIdentifier = %1";

            $params = array(
                array($type, 'String'),
                array($value, 'String'),
            );
//print_r($params);
            $financialImportDAO = CRM_Core_DAO::executeQuery($query, $params);

            $contactID = '';
            while ( $financialImportDAO->fetch() ) {
                $contactID = $financialImportDAO->entity_id;
                $directTransferId = $financialImportDAO->id;
//print('directTransferId ='.$directTransferId);
//print('Contact='.$contactID);
            }
        } else {
            $contactID = $rec['contact_id'];
        }

        if (empty($contactID)) {

            throw new CRM_Finance_BAO_Import_ValidateException(
                    "Can't find contact for this reference",
                    $excCode,
                    $value);
        }

        // Get the contact's name
        require_once 'CRM/Contact/DAO/Contact.php';
        $contact = new CRM_Contact_DAO_Contact();               

        $contact->id = $contactID;
        $contact->find();
        $contact->fetch();

        $ret = array(
            'direct_transfer_id' => $directTransferId,
            'financial_import_reference' => $value,
            'contact_id' => $contactID,
            'source' => $type,
            'contact_display_name' => $contact->display_name,
        );
//print_r($ret);
//die;        
        return $ret;
    }
 
    protected function createFinancialTransfer(array $params) {
 
        foreach (array(
            'contact_id',
            'financial_import_reference',
            'source') as $required) {

            if (!isset($params[$required]) || empty($params[$required])) {
                throw new InvalidArgumentException("Missing params[$required]");
            }
        }
        
//        $sourceSystem = $type;
        $dateCreated = date('Y-m-d');
      
        $financialTransferTable = $this->getCustomTableName('Financial Import Reference');
        $sourceSystemColumn     = $this->getCustomFieldname($financialTransferTable->id, 'Source System');
        $identifierColumn       = $this->getCustomFieldname($financialTransferTable->id, 'Identifier');
        $dateCreatedColumn      = $this->getCustomFieldname($financialTransferTable->id, 'Date Created');
        $transferEndDateColumn  = $this->getCustomFieldname($financialTransferTable->id, 'Transfer End Date');
        
        // Check if a Financial Import Reference Exists
        $selectSql  = " SELECT id, entity_id ";        
        $selectSql .= " FROM ". $financialTransferTable->table_name;        
        $selectSql .= " WHERE ".$identifierColumn." = %1 ";    
        $selectSql .= " AND ".$sourceSystemColumn." = %2 ";    

        $selectParams = array(  1 => array( $params['financial_import_reference'], 'String' ),
                                2 => array( $params['source'], 'String' ),
            ); 

        $daoSelect = CRM_Core_DAO::executeQuery( $selectSql, $selectParams );
  
        //If no record found then INSERT a new record
        if (!$daoSelect->fetch()) {  
         
            $insertSql  = " INSERT INTO ". $financialTransferTable->table_name ." SET ";
            $insertSql .= "   entity_id                     = %0 ";
            $insertSql .= " , ". $sourceSystemColumn     ." = %1 ";
            $insertSql .= " , ". $identifierColumn       ." = %2 ";
            $insertSql .= " , ". $dateCreatedColumn      ." = %3 ";
    //        $insertSql .= " , ". $transferEndDateColumn  ." = %4 ";

            $insertSQLParams = array(array((string)$params['contact_id'],       'String'),
                            array((string)$params['source'],               'String'),
                            array((string)$params['financial_import_reference'],'String'),
                            array((string)$dateCreated,                'String'),
    //                        array((string)$transferEndDateColumn,      'String'),
                      );

            CRM_Core_DAO::executeQuery($insertSql, $insertSQLParams);
            
            // We should now have a direct transfer rec so return its id
            $direct_transfer_id = self::createFinancialTransfer($params);
        } else {
            $direct_transfer_id = $daoSelect->id;
        }
        return $direct_transfer_id;
    }
        

    private function getDirectTransferIdByDirectTransferRef($type, $value) {
        //we do not search on empty values!!!
        if ((!isset($value) || $value == null || $value == '')) {
            return null;
        }

        //mzeman: how to get this Civi way?
        $query = "SELECT id
            FROM  `civicrm_value_direct_transfers_32`
            WHERE  `direct_transfer_type_135` = %0
            AND  `reference_number_134` =  %1
            AND  `reference_number_134` != ''";

        $val = CRM_Core_DAO::singleValueQuery($query, array(
                    array($type, 'String'),
                    array($value, 'String'),
                ));

        return $val;
    }
    
    private function getCampaignIdByDirectTransferId($directTransferId) {
        //we do not search on empty values!!!
        if (empty($directTransferId)) {
            return null;
        }

        //mzeman: how to get this Civi way?
        $query = "SELECT pledge_campaign_142
            FROM  `civicrm_value_direct_transfers_32`
            WHERE  `id` = %0";

        $val = CRM_Core_DAO::singleValueQuery($query, array(
                    array($directTransferId, 'Int'),
                ));

        return $val;
    }

    private function getContactIdByDirectTransferId($directTransferId) {
        //we do not search on empty values!!!
        //if (empty($value)) {
        if ((!isset($directTransferId) || $directTransferId == null || $directTransferId == '')) {
            return null;
        }

        //mzeman: how to get this Civi way?
        $query = "SELECT entity_id
            FROM  `civicrm_value_direct_transfers_32`
            WHERE  `id` = %0";

        $val = CRM_Core_DAO::singleValueQuery($query, array(
                    array($directTransferId, 'Int'),
                ));

        return $val;
    }

    private function getInMemContactIdByDirectTransferId($directTransferId) {
        //we do not search on empty values!!!
        if (empty($directTransferId)) {
            return null;
        }

        //mzeman: how to get this Civi way?
        $query = "SELECT IFNULL(pledge_in_memory_of_145,'') pledge_in_memory_of_145
            FROM  `civicrm_value_direct_transfers_32`
            WHERE  `id` = %0";

        $val = CRM_Core_DAO::singleValueQuery($query, array(
                    array($directTransferId, 'Int'),
                ));

        return $val;
    }
    
    /**
     * $status = array(
      'skipped_processed' => 0,
      'skipped_not_valided' => 0,
      'skipped_error' => 0,
      'processed_ok' => 0,
      'processed_error' => 0,
      'total_net_amount' => 0,
      'total_gross_amount' => 0,
      'total_import_net_amount' => 0,
      'total_import_gross_amount' => 0,
      'total_valid' => 0,
      'total_error' => 0,
      );
     *
     * @param array $importData
     * @param array $status
     */
    protected function postProcess(array $importData, array $status) {
        //matusz: TODO XXX how do we handle error states?
        if (!isset($status['total_net_amount'])) {
            throw new Exception('We dont have total_net_amount in data. Why?');
        }
        // PS 22/12/2011 Passing in the Import ID to the Batch so that we can track back to run Exceptions Reports
        $this->updateBatch($this->getBatchId(), $status['total_net_amount'], $status['processed_ok'], $this->getImportId());

    }

    /**
     * Required params
     *
     * From import record usually:
     * 'contact_id',
     * 'contact_display_name',
     * 'net_amount',
     * 'received_date',
     *
     * This usually goes from the form:
     * 'payment_instrument_id',
     * 'contribution_type_id',
     * 'campaign_id',
     *
     * And from the code:
     * 'batch_id',
     * 'weight',
     *
     * Optional:
     * 'transaction_id'
     *
     * @param array $params
     * @return type
     */ 
    protected function createBatchEntry(array $params) {

        //preload what we can
//      if(isset($params['VARef'])) {
//          require_once 'CRM/Contact/DAO/Contact.php';
//          $contact = new CRM_Contact_DAO_Contact();
//          $contact->external_identifier = $params['VARef'];
//          $contact->find();
//          if($contact->fetch() === false) {
//              throw new Exception("Can't load contact using extenal_id = '{$params['VARef']}'");
//          }
//
//          $params['contact_id'] = $contact->id;
//          $params['contactDisplayName'] = $contact->display_name;
//      }
        //check mandatory $params - others are checked in CRM_Batch_BAO_Batch::createContribution()
        foreach (array(
        //'transaction_id', //mzeman - we don't force transaction_id being availabled anymore
        ) as $param) {

            if (empty($params[$param])) {
                throw new InvalidArgumentException("No param[$param] when creating the batch entry");
            }
        }

        if (!isset($params['contact_display_name']) && isset($params['contact_id'])) {

            require_once 'CRM/Contact/DAO/Contact.php';
            $contact = new CRM_Contact_DAO_Contact();
            $contact->id = $params['contact_id'];
            $contact->find();
            if ($contact->fetch() === false) {
                throw new Exception("Can't find user with id = '{$params['contact_id']}'");
            }

            //$params['contact_id'] = $contact->id;
            $params['contact_display_name'] = $contact->display_name;
        }

        try {            
            require_once('CRM/Finance/BAO/Batch.php');
            return CRM_Finance_BAO_Batch::createContribution($params);
        } catch (RuntimeException $e) {
            throw new CRM_Finance_BAO_Import_ProcessException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function setImportId($id) {
        $this->importId = $id;
    }

    public function getImportId() {
        if (empty($this->importId)) {
            throw new RuntimeException("No importId");
        }
        return $this->importId;
    }

    public function getImport() {
        $importData = $this->getDataExchange()->getProcessById($this->getImportId());
        return $importData;
    }

    public function setSourceName($id) {
        $this->sourceName = $id;
    }

    public function getSourceName() {
        if (empty($this->sourceName)) {
            throw new RuntimeException("No sourceName");
        }
        return $this->sourceName;
    }

    public function validate() {
        
        $this->getDataExchange()->setStatus($this->getImportId(), self::STATUS_VALIDATE_STARTED);

        $status = array(
            'skipped_valid' => 0,
            'skipped_error' => 0,
            'validate_ok' => 0,
            'validate_error' => 0,
            'total_net_amount' => 0,
            'total_gross_amount' => 0,
            'total_import_net_amount' => 0,
            'total_import_gross_amount' => 0,
            //matusz: computed at the end
            'total_valid' => 0,
            'total_error' => 0,
        );

        $importData = $this->getDataExchange()->getProcessById($this->getImportId());
        
        // PS 19112012
        // Added params to ensure that the process is in order of status
        // This is because if we've attached an import record to a contact then we want those records to be processed first
        // This should mean that any records that require the same financial import record
        // will have it available when its their turn
        $getDataDAOParams  = array(
            'orderBy' => 'status'
        );

        $dao = $this->getDataDAO($getDataDAOParams);

        while ($dao->fetch()) {

            $rec = $dao->toArray();

            // PS Added
            // We want the total value the file regardless of validated state
            $status['total_import_gross_amount'] += $rec[$this->getGrossAmountField()];
            $status['total_import_net_amount'] += $rec[$this->getNetAmountField()];
                        
            //we want to skip records which have been successfully validated.
            if ($dao->status == self::DATA_STATUS_VALIDATE_OK) {
                $status['skipped_valid']++;
                //count totals
                $status['total_gross_amount'] += $rec[$this->getGrossAmountField()];
                $status['total_net_amount'] += $rec[$this->getNetAmountField()];
                continue;
            } 
//            elseif ($dao->status != 0) {             
               
//                $status['skipped_error']++;
//                continue;

//            }

            $updateData = array();

            try {
                $ret = $this->validateRec($rec, $importData);

                if (is_array($ret)) {
                    if (!isset($ret['status'])) {
                        throw new Exception("No status returned in " . print_r($ret));
                    }
                    $updateData['status'] = $ret['status'];
                    if ($ret['update']) {
                        if (!is_array($ret['update'])) {
                            throw new Exception("Update param is not array");
                        }
                        $updateData = array_merge($updateData, $ret['update']);
                    }
                } else {
                    $updateData['status'] = $ret;
                }

                if ($updateData['status'] === true) {
                    $updateData['status'] = self::DATA_STATUS_VALIDATE_OK;
                } elseif ($updateData['status'] === false) {
                    $updateData['status'] = self::DATA_STATUS_VALIDATE_ERROR;
                }

                //if some validation error occurs from validateValue()
            } catch (CRM_Finance_BAO_Import_ValidateException $e) {
                $updateData['status'] = $e->getCode();
            }
/*
print_r($ret);
print_r($rec);
print_r($updateData);
*/
            if ($updateData['status'] === self::DATA_STATUS_VALIDATE_OK) {
                $status['validate_ok']++;
                
                //merge existing record with updateData - gross/net might be updated
                $rec = array_merge($rec, $updateData);
                
                // PS 19112012
                // If the status is ok and the direct_transfer_id is 0 then we should create a
                // financial import rec and assign the relevant id back
                if (empty($rec['direct_transfer_id'])) {
                    $updateData['direct_transfer_id'] = $this->createFinancialTransfer($rec);
                    //print("updateData['direct_transfer_id']=".$updateData['direct_transfer_id']);
                } 

                $status['total_gross_amount'] += $rec[$this->getGrossAmountField()];
                $status['total_net_amount'] += $rec[$this->getNetAmountField()];
                                
            } else {
                $status['validate_error']++;
            }
/*
print_r($ret);
print_r($rec);
print_r($updateData);
die;
*/
            $this->getDataExchange()->updateData($this->getImportId(), $dao->id, $updateData);
        }

        $status['total_valid'] = $status['validate_ok'] + $status['skipped_valid'];
        $status['total_error'] = $status['validate_error'] + $status['skipped_error'];
        
        //round our results
        $status['total_net_amount'] = round($status['total_net_amount'], 2);
        $status['total_gross_amount'] = round($status['total_gross_amount'], 2);
        $status['total_import_net_amount'] = round($status['total_import_net_amount'], 2);
        $status['total_import_gross_amount'] = round($status['total_import_gross_amount'], 2);
        
        //update import status into fin.import table
        $importData = $this->getDataExchange()->getProcessById($this->getImportId());
        $data = $importData['data'];
        
        //mzeman: http://support.vedaconsulting.co.uk/issues/213
        //check for duplicated imports
        $processIds = $this->getDataExchange()->findProcessIds(array(
            'source' => $this->getSourceName(),
            'count' => $importData['count']
        ));

        $duplicates = array();
        
        foreach($processIds as $processId) {
            //we skip current import
            if($processId == $this->getImportId()) {
                continue;
            }
            
            $process = $this->getDataExchange()->getProcessById($processId);
            //we skip imports without status stored
            //they are either old imports (when this wasn't implemented yet) or imports just under way? TODO How to check for simultaneous import?
            if(!isset($process['data']['status']['total_net_amount'])) {
                continue;
            }
            
            $totalNetAmount = $process['data']['status']['total_net_amount'];
            if($totalNetAmount != $status['total_net_amount']) {
                continue;
            }
            
            //when we reach this poing we have got possible duplicate import
            $duplicates[] = $processId;
        }
        
        $status['duplicates'] = $duplicates;
        
        //END: check for duplicated imports
        
        $data['status'] = $status;
        $this->getDataExchange()->updateProcessData($this->getImportId(), $data);
        
        $this->getDataExchange()->setStatus($this->getImportId(), self::STATUS_VALIDATE_FINISHED);

        return $status;
    }

    public function process($params = null) {
        $this->getDataExchange()->setStatus($this->getImportId(), self::STATUS_PROCESS_STARTED);

        $status = array(
            'skipped_processed' => 0,
            'skipped_not_valided' => 0,
            'skipped_error' => 0,
            'processed_ok' => 0,
            'processed_error' => 0,
            'total_gross_amount' => 0,
            'total_net_amount' => 0,
            //matusz: computed at the end
            'total_valid' => 0,
            'total_error' => 0,
        );

        $importData = $this->getDataExchange()->getProcessById($this->getImportId());

        if ($params !== null && is_array($params)) {
            $importData['data'] = array_merge($importData['data'], $params);
            $this->getDataExchange()->updateProcessData($this->getImportId(), $importData['data']);
        }

        $this->preProcess($importData);

        //preprocess might might change the data -- e.g. we've got batch id now
        $importData = $this->getDataExchange()->getProcessById($this->getImportId());
        
        $dao = $this->getDataDAO();

        //$process = $this->getProcess();
        $weight = 0;

        while ($dao->fetch()) {

            $rec = $dao->toArray();

            if ($dao->status == self::DATA_STATUS_PROCESS_OK) {
                $status['skipped_processed']++;
                continue;
            }
            //if it's processed with error
            elseif ($dao->status > self::DATA_STATUS_PROCESS_OK) {
                $status['skipped_error']++;
                continue;
            } elseif ($dao->status != self::DATA_STATUS_VALIDATE_OK) {
                $status['skipped_not_valided']++;
                continue;
            }
            try {
                $ret = $this->processRec(++$weight, $rec, $importData);

                $updateData = array('status' => $ret);
                if ($updateData['status'] === true) {
                    $updateData['status'] = self::DATA_STATUS_PROCESS_OK;
                } elseif ($updateData['status'] === false) {
                    $updateData['status'] = self::DATA_STATUS_PROCESS_ERROR;
                }
            } catch (CRM_Finance_BAO_Import_ProcessException $e) {
                $updateData['status'] = self::DATA_STATUS_PROCESS_ERROR;
            }

            if ($updateData['status'] === self::DATA_STATUS_PROCESS_OK) {
                $status['processed_ok']++;

                $status['total_gross_amount'] += $rec[$this->getGrossAmountField()];
                $status['total_net_amount'] += $rec[$this->getNetAmountField()];
            } else {
                $status['processed_error']++;
            }

            $this->getDataExchange()->updateData($this->getImportId(), $dao->id, $updateData);
        }

        $status['total_processed'] = $status['processed_ok'] + $status['skipped_processed'];
        $status['total_error'] = $status['processed_error'] + $status['skipped_error'] + $status['skipped_not_valided'];

        $this->postProcess($importData, $status);
        
        //change status to "processed"
        $this->getDataExchange()->setStatus($this->getImportId(), self::STATUS_PROCESS_FINISHED);

        return $status;
    }

    public function getBatchId() {
        $process = $this->getDataExchange()->getProcessById($this->getImportId());
        if (!isset($process['data']['batch_id'])) {
            throw new Exception("No batch id saved for this import");
        }

        return $process['data']['batch_id'];
    }

    /**
     * Used to update the batch with total amount and entries
     *
     * @param int $batchId
     * @param double  $totalAmount
     * @param int $totalEntries
     * @return int status of the update
     */
    protected function updateBatch($batchId, $totalAmount, $totalEntries, $importId) {
        $batchUpdateSql = " UPDATE civicrm_batch SET ";
//        $batchUpdateSql = "   expected_entries = %1 ";
//        $batchUpdateSql = " , expected_value = %2 ";
        $batchUpdateSql .= "   import_id = %1 ";
//        $batchUpdateSql .= " , batch_status = %2 ";
//        $batchUpdateSql = " WHERE entity_id = %0 ";
        $batchUpdateSql .= " WHERE id = %0 ";

        CRM_Core_DAO::executeQuery($batchUpdateSql, array(
            array($batchId, 'Int'),              
//            array($totalEntries, 'Int'),         
//            array($totalAmount, 'Money'),        
            array(3, 'Int'), //Allocated         
//            array($importId, 'Int'), //Import ID 
        ));
    }

    /**
     * Used to create the batch record
     *
     * @param array $batchDetails
     * @return int batchId
     */
    protected function createBatch(array $importData) {
//        require_once('CRM/Finance/BAO/Batch.php');
        $params = $importData['data'];
        $params['batch_status'] = 1;
        $params['entity_type'] = 'financial_import';

        if (!isset($params['exclude_from_posting'])) {
            $params['exclude_from_posting'] = false;
        }

        $ret = CRM_Finance_BAO_Batch::createBatch($params);
        $ret = array_merge($params, $ret);
        $this->getDataExchange()->updateProcessData($this->getImportId(), $ret);
    }

    protected function getContributionTypeIdByCampaignId($campaignId) {
        require_once 'CRM/Finance/BAO/Campaign.php';

        return CRM_Finance_BAO_Campaign::getContributionTypeIdByCampaignId($campaignId);
    }

    /**
     * @return CRM_Core_DAO
     */
    protected function getDataDAO($params = array()) {
        $dao = $this->getDataExchange()->getDataDAO($this->getImportId(), $params);
        return $dao;
    }

    /**
     * @return CRM_Finance_Utils_DataExchange
     */
    protected function getDataExchange() {
        if ($this->dataExchange === null) {
            $this->dataExchange = new CRM_Finance_Utils_DataExchange();
            $this->dataExchange->setAlwaysCreateFields(array(
                'contact_id',
                'contact_display_name',
                'gross_amount',
                'net_amount',
                'fee_amount',
                'transaction_id',
                'source',
                'received_date',
                //'paid_by',
                //'receipt_date',
                //'contribution_status',
                'note',
                //'non_deductible_amount',
                //'invoice_id',
                //'thank_you_sent',
                'campaign_id',
                'contribution_type_id',
                'in_memory_contact_id',
                'gift_aid_eligible',
                'direct_transfer_id',
                'financial_import_reference',
            ));
        }

        return $this->dataExchange;
    }

    function getCustomTablename($customGroupTitle) {
     
        // Find the custom table which contains the Customer Number Field
        $customGroupSql  = " SELECT id ";
        $customGroupSql .= " ,      table_name ";
        $customGroupSql .= " FROM civicrm_custom_group ";
        $customGroupSql .= " WHERE title = %1 ";       

        $params = array( 1 => array( (string)$customGroupTitle, 'String' ) );
        $customGroup = CRM_Core_DAO::executeQuery( $customGroupSql, $params );
 
        $customGroup->fetch();
        
        return $customGroup;
        
    }

    function getCustomFieldname($customGroupId, $fieldLabel) {
        
        $customFieldSql  = " SELECT column_name ";
        $customFieldSql .= " FROM civicrm_custom_field ";
        $customFieldSql .= " WHERE custom_group_id = %1 ";
        $customFieldSql .= " AND label = %2 ";

        $params = array( 1 => array( (string)$customGroupId, 'String' ), 
                         2 => array( (string)$fieldLabel, 'String' ) 
                        );

        $customField = CRM_Core_DAO::executeQuery( $customFieldSql, $params );
        $customField->fetch();

        return $customField->column_name;
        
    }
 
}