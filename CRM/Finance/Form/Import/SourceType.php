<?php
require_once 'CRM/Core/Form.php';
require_once 'CRM/Finance/BAO/Import/Source.php';

class CRM_Finance_Form_Import_SourceType extends CRM_Core_Form {
    
    function preProcess() {
        parent::preProcess( );
    }
    
    public function getTitle() {
        return ts('Select import source');
    }
    
    private function getSources() {
        require_once('CRM/Finance/BAO/Import/Source.php');
        return CRM_Finance_BAO_Import_Source::getAllAsOptions();
    }
    
    function buildQuickForm( ) {
        $this->add('select', 'source', 
            ts('Import source'),
            $this->getSources(), true);
        
        //$this->addFormRule(array($this, 'formRule'));
        $this->addButtons(array(
            array ('type'   => 'next', 
                'name'      => ts('Next'), 
                //'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                'isDefault' => true   ), 
            ));
        }
    
    public function postProcess() {
        $params = $this->controller->exportValues( );
        $sourceName = $params['source'];
        
        $source = CRM_Finance_BAO_Import_Source::factory($sourceName);
        $this->controller->set('sourceName', $sourceName);
    }
    
    public function formRule($values) {
        //$errors = array('source' => 'This is not valid source type');
        //return $errors;
        return true;
    }
}
