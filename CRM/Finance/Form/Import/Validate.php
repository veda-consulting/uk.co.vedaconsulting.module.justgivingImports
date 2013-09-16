<?php
require_once 'CRM/Core/Form.php';
require_once 'CRM/Finance/BAO/Import/Source.php';

class CRM_Finance_Form_Import_Validate extends CRM_Core_Form {
    
    function preProcess() {
        parent::preProcess( );
        
        $bao = $this->controller->getImportBAO();
        $this->assign('importId', $bao->getImportId());
        $this->assign('importSummary', $this->get('importSummary'));
        
    }
    
    public function getTitle() {
        return ts('Validate import');
    }
    
    function buildQuickForm( ) {
        $this->addFormRule(array($this, 'formRule'));
        
//        $buttons = array();
//        $buttons[] = array ('type'   => 'submit', 
//            'name'      => ts('Validate'),
//            'isDefault' => true);
//        $buttons[] = array ('type'   => 'ignoreerrors', 
//            'name'      => ts('Ignore Errors and Proceed'), 
//            'isDefault' => false
//        );
        
//        $this->addButtons($buttons);
    }
    
    public function postProcess() {
        
    }
    
    public function handle($actionName) {
        if($actionName == 'ignoreerrors') {
            $actionName = 'next';
            $this->ignoreErrors = true;
        }
        
        return parent::handle($actionName);
    }
    
    public function formRule($values) {
        if (isset($this->ignoreErrors)) {
            return true;
        }
        
        $bao = $this->controller->getImportBAO();
        $ret = $bao->validate();
        $errors = array();
        
        $this->assign('editorUrl', "/civicrm/finance/import/browser?id=" . $bao->getImportId());
        
        if($ret['total_error'] > 0) {
            $errors['validate'] = "There are {$ret['total_error']} records invalid in total.";
        }
        
        if(!empty($ret['duplicates'])) {
            $errors['duplicates'] = "There are possible duplicates.";
        }
        
        $this->assign('errors', $errors);
        $this->assign('validationSummary', $ret);
        $this->set('validationSummary', $ret);
        //matusz: TODO XXX we want to progress even there are errors
          if(!empty($errors)) {
                return $errors;
          }
        return true;
    }
}
