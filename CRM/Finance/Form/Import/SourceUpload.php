<?php
require_once 'CRM/Core/Form.php';

class CRM_Finance_Form_Import_SourceUpload extends CRM_Core_Form {
    
    function preProcess() {
        $this->assign('formObject', $this);
        
        parent::preProcess( );
    }
    
    /**
     * Returns form element without buttons
     * @return array
     */
    public function getUserElements() {
        $els = array();
        foreach($this->_elements as $el) {
            //matusz: group type represents buttons?
            if($el->getType() != 'group' && $el->getType() != 'hidden') {
                $els[] = $el;
            }
        }
        
        return $els;
    }
    
    public function getTitle() {
        $opts = $this->getImportTypeOptions();
		$sn = $this->get('sourceName');
        $ret = "Upload data";
        if($sn) {
            $name = $opts[$sn];
            $ret .= " - $name";
        }
		return $ret;
    }
    
    private function getImportTypeOptions() {
        require_once('CRM/Finance/BAO/Import/Source.php');
        return CRM_Finance_BAO_Import_Source::getAllAsOptions();
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
        $this->controller->getImportBAO()->buildQuickForm($this);
        
        //$this->addFormRule(array($this, 'formRule'));
        $this->addButtons(array(
            array ('type'      => 'upload', 
                'name'      => ts('Next'), 
                //'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                'isDefault' => true   ), 
//            array ('type'      => 'cancel', 
//                        'name'      => ts('Cancel')
//            ), 
        ));
    }
    
    public function postProcess() {
        $params = $this->controller->exportValues( );
        $bao = $this->controller->getImportBAO();
        $ret = $bao->import($params);
        $id = $ret['id'];
        
        $ops = $this->getImportTypeOptions();
        $ret['sourceName'] = $ops[$ret['source']];
        
        $this->controller->set('importId', $id);
        $this->controller->set('importSummary', $ret);
    }
    
//    public function formRule($values) {
//        return true;
//    }
}
