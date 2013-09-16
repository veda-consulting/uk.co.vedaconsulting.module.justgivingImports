<?php
require_once 'CRM/Core/Form.php';

class CRM_Finance_Form_Import_ExtraData extends CRM_Core_Form {
    function preProcess() {
        parent::preProcess( );
        
        $bao = $this->controller->getImportBAO();
        $this->assign('importId', $bao->getImportId());
        
        $this->assign('importSummary', $this->get('importSummary'));
        $this->assign('validationSummary', $this->get('validationSummary'));
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
    
//    public function setDefaultValues() {
//        $item = $this->getItem();
//        
//        return array(
//            'supporterName' => 'TODO Supporter name',
//            'contact[1]' => 'TODO Matus Zeman',
//            'contact_select_id[1]' => 1,
//            'pageName' => $item['pageShortName'],
//            'pageDescription' => 'TODO page desc',//$item['pageDescription'],
//            'campaign_id' => 1,//$item['pageDescription'],
//            'issueName' => 'TODO issue #',
//        );
//    }
    
    function buildQuickForm( ) {
        $bao = $this->controller->getImportBAO();
        $bao->buildExtraDataQuickForm($this);
        
        $this->addFormRule(array($this, 'formRule'));
        
        $this->addButtons(array(
            array ('type'      => 'next', 
                'name'      => ts('Process'), 
                'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                'isDefault' => true   ), 
            array ('type'      => 'cancel', 
                    'name'      => ts('Cancel')
            ), 
        ));
        
        $this->assign('formObject', $this);
    }
    
    public function postProcess() {
        $bao = $this->controller->getImportBAO();
        
        $params = $this->controller->exportValues();
        
        if (!array_key_exists('exclude_from_posting', $params)) {
          $params['exclude_from_posting'] = 0;
        }
                
        $values = array();
        foreach($this->getUserElements() as $el) {

            $values[$el->getName()] = $params[$el->getName()];
        }
        $status = $bao->process($values);

        $batchId = $bao->getBatchId();
        
        CRM_Core_Session::setStatus('Import successfully processed');

        // PS D7 24072012         drupal_goto('civicrm/batch/process', 'bid='.$batchId.'&reset=1');
        drupal_goto( 'civicrm/batch/process' , array('query'=>array('bid'=>$batchId,'reset'=>'1')));

        CRM_Utils_System::civiExit();
    }
    
    public function formRule($values) {
        return true;
    }
}
