<?php
require_once 'CRM/Finance/BAO/Import/SourceAbstract.php';

abstract class CRM_Finance_BAO_Import_CsvAbstract extends CRM_Finance_BAO_Import_SourceAbstract {
    private $csvFields = array();
    private $csvImportParams = array(
        'fieldsTerminatedBy' => ',',
        'ignoreLines' => 1,
        'optionallyEnclosedBy' => '"',
        'linesTerminatedBy' => '\n',
        'characterSet' => 'utf8',
    );
    
    public function setCsvFields(array $fields) {
        $this->csvFields = $fields;
    }
    
    public function getCsvFields() {
        if(empty($this->csvFields)) {
            throw new RuntimeException("No csvFields");
        }
        return $this->csvFields;
    }
    
    public function setCsvImportParam($param, $value) {
        if(!array_key_exists($param, $this->csvImportParams)) {
            throw new Exception("No import param '$param'");
        }
        
        $this->csvImportParams[$param] = $value;
    }
    
    public function getCsvImportParams() {
        return $this->csvImportParams;
    }
    
    public function buildQuickForm(CRM_Core_Form $form) {
        $form->add('file', 'uploadFile', ts('Import CSV file'), 'size=30 maxlength=255', true);
        $form->addFormRule(array($this, 'quickFormRule'));
    }
    
    public function quickFormRule($values) {
        $errs = array();
        if(!is_uploaded_file($_FILES['uploadFile']['tmp_name'])) {
            $errs['uploadFile'] = 'Please enter a file';
        }
        
        if(!empty($errs)) {
            return $errs;
        }
        
        return true;
    }
    
    public function import($params = null) {
        if(!isset($params['uploadFile']['name'])) {
            throw new Exception("No uploadFile");
        }
        $fileName = $params['uploadFile']['name'];
        $fileName = realpath($fileName);
        
        $ret = $this->getDataExchange()->importCsvToDb($this->getSourceName(), $fileName, $this->getCsvFields(), $this->getCsvImportParams());
        $this->setImportId($ret['id']);
        
        return $ret;
    }
    
}