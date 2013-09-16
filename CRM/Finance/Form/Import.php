<?php
require_once 'CRM/Core/Form.php';

class CRM_Finance_Form_Import extends CRM_Core_Form {
    function preProcess() {
        //$source = CRM_Utils_Request::retrieve('source', 'String', $this, true);
        //$id = CRM_Utils_Request::retrieve('id', 'Int', $this, true);
        //$this->loadItem($source, $id);
        
        CRM_Utils_System::setTitle('Import');
        
        parent::preProcess( );
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
        $this->add('text', 'supporterName', 
            ts('Supporter name'),
            array('readonly' => 'readonly', 'size' => 70));
        
        //CRM_Contact_Form_NewContact::buildQuickForm($this, 1);
        
        $this->add('text', 'pageName', 
            ts('Page name'),
            array('readonly' => 'readonly', 'size' => 70));
        
        $this->add('textarea', 'pageDescription', 
            ts('Page description'),
            array('readonly' => 'readonly'));
        
        //CRM_Campaign_BAO_Campaign::addCampaign( $this, CRM_Utils_Array::value( 'campaign_id', $this->_values ) );        

        $this->add('text', 'issueName', 
            ts('Issue'));
        $this->addElement('hidden', 'issueId');
    
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
    }
    
    public function postProcess() {
        $params = $this->controller->exportValues( );

        $contactId = $params['contact_select_id'];
    }
    
    public function formRule($values) {
        $errors = array( );

        return $errors;
    }
}
