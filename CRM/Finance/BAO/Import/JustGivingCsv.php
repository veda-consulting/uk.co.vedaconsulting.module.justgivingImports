<?php
require_once 'CRM/Finance/BAO/Import/CsvAbstract.php';

class CRM_Finance_BAO_Import_JustGivingCsv extends CRM_Finance_BAO_Import_CsvAbstract {
    
    protected $hideDefaultFields = array('campaign_id', 'received_date');
    private $directTransferCode = 'JG';  
    
    public function __construct() {
        $this->setCsvImportParam('characterSet', 'latin1');
        $this->setCsvFields(array(
            'FundraiserUserId',
            'FundraiserTitle',
            'FundraiserFirstName',
            'FundraiserLastName',
            'FundraiserAddressLine1',
            'FundraiserAddressLine2',
            'FundraiserTown',
            'FundraiserCounty',
            'FundraiserPostcode',
            'FundraiserCountry',
            'FundraiserEmail',
            'FundraiserFurtherContact',
            'FundraiserConnectedBenefit',
            'FundraisingPageId',
            'FundraisingPageStatus',
            'PageCreatedDate',
            'PageEventDate',
            'PageExpiryDate',
            'FundraisingPageOfflineAmount',
            'FundraisingPageTargetAmount',
            'FundraisingPageTitle',
            'FundraisingPageURL',
            'FundraisingPageTeamName',
            'FundraisingPageTeamURL',
            'FundraisingPageTeamMembers',
            'InMemoriamFund',
            'OrganisationPortal',
            'OrganisationPortalURL',
            'FundraisingPageInMemoriamName',
            'FundraisingPageBirthdayName',
            'FundraisingPageWeddingNames',
            'PagePledgeReleaseDate',
            'ReferralSite',
            'ReferralSiteURL',
            'EventId',
            'EventName',
            'PromotedEvent',
            'UserCreatedEvent',
            'EventCategory',
            'OverseasEvent',
            'EventDate',
            'EventExpiryDate',
            'DonorUserId',
            'DonorTitle',
            'DonorFirstName',
            'DonorLastName',
            'DonorAddressLine1',
            'DonorAddressLine2',
            'DonorTown',
            'DonorCounty',
            'DonorPostcode',
            'DonorCountry',
            'DonorEmail',
            'DonorFurtherContact',
            'DonorIsConnected',
            'DonorUKTaxPayerStatus',
            'DonationRef',
            'DonationDate',     
            'IsPledge', 
            'DonationSource',       
            'ProductSource',        
            'PaymentFrequency',     
            'RecurringMandateCreationDate',     
            'AppealName',       
            'PaymentType',      
            'SMSOperator',      
            'SMSOperatorDonorTransactionFee',       
            'DonationPaymentReference',     
            'received_date',//DonationPaymentReferenceDate      
            'BlankColumn1',     
            'BlankColumn2',     
            'BlankColumn3',     
            'gross_amount',//DonationAmount
            'IsDonationGAEligible',     
            'PaymentProcessingFeeRate',     
            'PaymentProcessingFeeAmount',       
            'JustGivingTransactionFeeRate',     
            'JustGivingTransactionFeeAmount',       
            'NetDonationAmount',
            'EstimatedVAT',     
            'AmountOfJustGivingTransactionFeePaid',     
            'AmountOfPaymentProcessingFeePaid',     
            'CommissionPayer',      
            'fee_amount',//NetTotalChargesByJustGiving',        
            'net_amount',//NetDonationAmountPaid    
            'DonationOrigin',       
            'DonationNickname',     
            'MessageFromDonor',     
            'CustomEventCode1',     
            'CustomEventCode2',     
            'CustomEventCode3',     
            'CampID',       
            'FundID',       
            'GFAppeal',     
            'PackageID',        
            'PackageDesc',      
            'ConsID',
        ));
        
    }
    
    public function getSearchContactName(array $rec) {
        return "{$rec['FundraiserFirstName']} {$rec['FundraiserLastName']}";
    }
    
    public function getStatuses() {
        return parent::getStatuses() + array(
            //101 => 'Not paid to the charity yet',
        );
    }
    
    /**
     * Used to validate one record from import table
     * 
     * @param array $rec 
     * @param array $importData
     * @return array(
     *    'status' => int
     *    'update' => array(field => newvalue)
     * )
     */
    protected function validateRec(array $rec, array $importData) {
        $update = array();
        
//        if(trim($rec['donationPaidToCharityStatus']) != 'Paid to Charity') {
//            throw new CRM_Finance_BAO_Import_ValidateException("Not paid to the charity yet", 101);
//        }

        // Added code to determine if the FundraiserUserId is 0 then set the FundraiserUserId to the donor id
        // i.e. these contributions are contributions made directly to the charity without a fundraising page
        if ($rec['FundraiserUserId'] == 0) {
            $rec['FundraiserUserId'] = $rec['DonorUserId'];
        }
        $rec['transaction_id'] = "JG-" . $rec['DonationPaymentReference'] . '/' . $rec['DonationRef'];
        
        //$this->validateField($rec, 'FundraiserUserId', 'financialImportReference', $update);
        $update = $this->validateFinancialTransferRef($this->directTransferCode, $rec['FundraiserUserId'], self::VALIDATE_ERR_FINANCIAL_IMPORT_REF_NO_CONTACT, $rec);
        $update['transaction_id'] = $rec['transaction_id'];
        $this->validateField($rec, 'transaction_id', 'transactionId', $update); // Added to ensure the transaction ID doesn't already exist
        $this->validateField($rec, 'gross_amount', 'grossAmount', $update);
        $this->validateField($rec, 'net_amount', 'netAmount', $update);
        $this->validateField($rec, 'fee_amount', 'feeAmount', $update);
        $this->validateField($rec, 'DonationDate', 'donationDate', $update, array('format' => 'd/m/Y'));
        $this->validateField($rec, 'received_date', 'paidToCharityDate', $update, array('format' => 'd/m/Y'));
        
        // PS 03/10/2012
        // Now the campaign ID is coming from Just Giving
        // $this->validateField($rec, 'Approach', 'campaignCode', $update);
        // $this->validateField($rec, 'CustomFundraisingCode4', 'campaignID', $update);
        
        return array(
            'status' => true,
            'update' => $update
        );
    }
    
    /**
     * Used to process one record from import table (already validated),
     * so checked for data consistencies etc.
     * 
     * @param array $rec
     * @param int $batchId       
     * @param array $batchDetails
     * @return int new status of the processed row
     */
    protected function processRec($weight, array $rec, array $importData) {
        $batchDetails = $importData['data'];
 
        //$contribution_type_id = $batchDetails['contribution_type_id'];
        
        //unset($batchDetails['contribution_type_id']);
        
        $params = array_merge($rec, $batchDetails);
        $params['weight'] = $weight;
        //$params['contribution_type_id'] = $contribution_type_id;

        //mzeman: we don't have transaction_id for this import type
        //unset($params['transaction_id']);
        
        $this->createBatchEntry($params);

 //       $this->createFinancialTransfer($params);
 //       $this->createFinancialTransfer($this->directTransferCode, $params);
        
        return true;
    }
    
}