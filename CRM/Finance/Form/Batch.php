<?php
require_once 'CRM/Core/Form.php';

class CRM_Finance_Form_Batch extends CRM_Core_Form {

    function preProcess() {
        parent::preProcess( );
/*        
        $bao = $this->controller->getImportBAO();
        $this->assign('importId', $bao->getImportId());
        
        $this->assign('importSummary', $this->get('importSummary'));
        $this->assign('validationSummary', $this->get('validationSummary'));
*/
    }
    
   
    /**
     * Returns form element without buttons
     * @return array
     */
    public function getUserElements() {
        $els = array();
        foreach($this->_elements as $el) {
            //matusz: group type represents buttons?
            if(in_array($el->getName(), array('buttons', 'qfKey', '_qf_default'))) {
                continue;
            }
            
            $els[] = $el;
        }
        
        return $els;
    }

    public function getTitle() {
        return ts('Batch details');
    }
    
    
    function buildQuickForm( ) {
    
        $this->addElement('hidden', 'batch_status', true );
        $this->addElement('hidden', 'id', $_GET['id'] );

        $this->add( 'text', 'batch_title', 
                    ts('Batch Title'),
                    array('size' => 35, 'maxlength' => 64 , 'style'=>'text-align:right') , true );

        $this->add( 'textarea', 'description', 
                    ts('Description'),
                    null, false );

        $emptySelect1[''] = '- select -';
        $bankAccounts = $bankAccounts = CRM_Finance_BAO_BankAccount::getBankAccountsList($emptySelect1);
        $this->add('select', 'banking_account', ts('Bank Account'), $bankAccounts , true );

        $this->addDate( 'banking_date', ts('Banking Date'), true, array('formatType' => 'activityDate') );

        $this->addElement( 'checkbox', 'exclude_from_posting', ts( 'Exclude from posting' ) , null , null );

        $this->add('select', 'contribution_type_id', ts('Contribution Type'), array(''=>ts( '- select -' )) + CRM_Contribute_PseudoConstant::contributionType( ), true );

        $this->add('select', 'payment_instrument_id', ts('Payment Method'), array(''=>ts( '- select -' )) + CRM_Contribute_PseudoConstant::paymentInstrument( ), true );

        $this->addFormRule(array($this, 'formRule'));
        
        $this->addButtons(array(
            array ('type'      => 'next', 
                'name'      => ts('Save'), 
                'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                'isDefault' => true   ), 
            array ('type'      => 'cancel', 
                    'name'      => ts('Cancel')
            ), 
        ));
        
        $this->assign('formObject', $this);
    }
    
    public function postProcess() {
    
        $params = $this->_submitValues;
        
        $batchDetailsSql  = " UPDATE civicrm_batch SET "; 
        $batchDetailsSql .= "    title = %1 "; 
        $batchDetailsSql .= " ,  description = %2 "; 
        $batchDetailsSql .= " ,  banking_account = %3 ";
        $batchDetailsSql .= " ,  banking_date  = %4 "; 
        $batchDetailsSql .= " ,  exclude_from_posting = %5 ";
        $batchDetailsSql .= " ,  contribution_type_id = %6 ";
        $batchDetailsSql .= " ,  payment_instrument_id = %7 ";
        $batchDetailsSql .= " WHERE id = %8 ";
        
        $bankingDate = CRM_Utils_Date::processDate($params['banking_date']);
                        
        $sqlParams = array();
        $sqlParams[1] = array((string)$params['batch_title'],           'String');
        $sqlParams[2] = array((string)$params['description'],           'String');
        $sqlParams[3] = array((string)$params['banking_account'],       'String');
        $sqlParams[4] = array((string)$bankingDate,                     'String');
        $sqlParams[5] = array((string)$params['exclude_from_posting'],  'String');
        $sqlParams[6] = array((string)$params['contribution_type_id'],  'String');
        $sqlParams[7] = array((string)$params['payment_instrument_id'], 'String');
        $sqlParams[8] = array((int)$params['id'],                       'Integer');

        CRM_Core_DAO::executeQuery($batchDetailsSql, $sqlParams);
                
        drupal_goto( 'civicrm/batch/process' , array('query'=>array('bid'=>$params['id'],'reset'=>'1')));

        CRM_Utils_System::civiExit();
    }
    
    public function formRule($values) {
        return true;
    }
}
