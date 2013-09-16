<?php
require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Selector/Controller.php';
require_once 'CRM/Finance/Selector/Import.php';

class CRM_Finance_Form_ImportBrowser extends CRM_Core_Form
{
    function preProcess() {
        CRM_Utils_Request::retrieve('id', 'Int', $this, false);

        $readonly = CRM_Utils_Request::retrieve('readonly', 'Boolean', $this, false);
        $this->assign('readonly', $readonly);
        
        $id = $this->get('id');
        $params = $this->controller->exportValues();
        $selector = new CRM_Finance_Selector_Import($id, $params);
        
        $this->assign('importId', $id);
        
        $dataExchange = new CRM_Finance_Utils_DataExchange();
        $processData = $dataExchange->getProcessById($id);
        if(isset($processData['data']['status'])) {
            $this->assign('validationSummary', $processData['data']['status']);
        }
        
        require_once('CRM/Finance/BAO/Import/Source.php');
        $sourceOptions = CRM_Finance_BAO_Import_Source::getAllAsOptions();
        $processData['sourceName'] = $sourceOptions[$processData['source']];
        $this->assign('importSummary', $processData);
        
        $output = CRM_Core_Selector_Controller::TEMPLATE;
                
        $sortID = null;
        if ( $this->get( CRM_Utils_Sort::SORT_ID  ) ) {
            $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID  ),
                                                   $this->get( CRM_Utils_Sort::SORT_DIRECTION ) );
        }
        $controller = new CRM_Core_Selector_Controller( $selector ,
                                                               $this->get( CRM_Utils_Pager::PAGE_ID ),
                                                               $sortID,
                                                               CRM_Core_Action::VIEW,
                                                               $this,
                                                               $output);
        $controller->setEmbedded( true );
        $controller->run();
    }
    
    function postProcess() {
        
    }
    
    function buildQuickForm() {
        
        require_once 'CRM/Contact/Form/NewContact.php';
        CRM_Contact_Form_NewContact::buildQuickForm( $this, 1 );
        
        $statuses = array(
            '' => 'All',
            'error' => 'Error',
            'ok' => 'OK',
        );
        $this->add('select', 'status', ts('Status'), $statuses);
        
        $this->addButtons( array(
                                 array ( 'type'      => 'refresh',
                                         'name'      => ts('Search') ,
                                         'isDefault' => true     )
                                 )        
                           );
    }
    
}