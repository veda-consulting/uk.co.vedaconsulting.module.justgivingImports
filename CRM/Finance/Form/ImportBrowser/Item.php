<?php
require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Selector/Controller.php';
require_once 'CRM/Finance/Selector/Import.php';

class CRM_Finance_Form_ImportBrowser_Item extends CRM_Core_Form
{
    /**
     * @var CRM_Finance_Utils_DataExchange
     */
    private $dataExchange;
    private $data = array();
    
    function preProcess() {
        CRM_Utils_Request::retrieve('import_id', 'Int', $this, false);
        CRM_Utils_Request::retrieve('id', 'Int', $this, false);
        
        //CRM_Utils_Request::retrieve('snippet', 'Int', $this, false, 2);
        
        $importId = $this->get('import_id');
        $id = $this->get('id');
        
        $de = $this->getDataExchange();
        $this->data = $de->retrieveData($importId, $id);
        
        
        $this->assign('formElementNames', $this->getFormElementNames());
//        var_dump($data);
//        exit;
        
        
    }
    
    function setDefaultValues() {
        return $this->data;
    }
    
    public function getFormElementNames() {
        return array_keys($this->data);
    }
    
    private function getDataExchange() {
        if($this->dataExchange === null) {
            $this->dataExchange = new CRM_Finance_Utils_DataExchange();
        }
        
        return $this->dataExchange;
    }
    
    function postProcess() {
        $vals = $this->exportValues($this->getFormElementNames(), true);
        $importId = $this->get('import_id');
        $id = $this->get('id');
        $this->getDataExchange()->updateData($importId, $id, $vals);
    }
    
    function buildQuickForm() {
        foreach($this->data as $field => $value) {
            $this->add('text', $field, ts(ucfirst(str_replace('_', ' ', $field))), $value);
        }
        
        require_once('CRM/Contact/Form/NewContact.php');
        CRM_Contact_Form_NewContact::buildQuickForm($this, 1);
        
        $this->addFormRule(array($this, 'formRule'));
        
        $this->addButtons( array(
                                 array ( 'type'      => 'submit',
                                         'name'      => ts('Submit') ,
                                         'isDefault' => true     )
                                 )        
                           );
    }
    
    public function formRule($values) {
        //$errors = array('source' => 'This is not valid source type');
        //return $errors;
        return true;
    }
    
}