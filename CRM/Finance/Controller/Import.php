<?php

require_once 'CRM/Core/Controller.php';
require_once 'CRM/Core/Action.php';
require_once 'CRM/Finance/Form/Import/SourceType.php';
require_once 'CRM/Finance/Form/Import/SourceUpload.php';
require_once 'CRM/Finance/Form/Import/Validate.php';
require_once 'CRM/Finance/Form/Import/ExtraData.php';

class CRM_Finance_Controller_Import extends CRM_Core_Controller {
    private $importBAO;
    
    /**
     * class constructor
     */
    function __construct( $title = null, $action = CRM_Core_Action::NONE, $modal = true ) {
        parent::__construct( $title, $modal, null, false, true );

        $this->_stateMachine = new CRM_Core_StateMachine($this, $action);
        
        //matusz: TODO how to add extra step for justgiving?
        $source = $this->get('sourceName');
        if($source == 'JustGiving') {
        	$p = array(
	          'CRM_Finance_Form_Import_SourceType'=> null,
	          'CRM_Finance_Form_Import_JustGiving_SourceUpload' => null,
	          'CRM_Finance_Form_Import_Validate' => null,
	          'CRM_Finance_Form_Import_ExtraData' => null,
	        );
        }
        else {
        	$p = array(
	          'CRM_Finance_Form_Import_SourceType'=> null,
	          'CRM_Finance_Form_Import_SourceUpload' => null,
	          'CRM_Finance_Form_Import_Validate' => null,
	          'CRM_Finance_Form_Import_ExtraData' => null,
	        );
        }
        
        $this->_stateMachine->addSequentialPages($p, $action);

        // create and instantiate the pages
        $this->addPages( $this->_stateMachine, $action);

        $this->addActions();
    }
    
    public function getImportBAO() {
        if($this->importBAO === null) {
            require_once 'CRM/Finance/BAO/Import/Source.php';
			$this->importBAO = CRM_Finance_BAO_Import_Source::factory($this->get('sourceName'));
            $importId = $this->get('importId');
            if($importId) {
                $this->importBAO->setImportId($importId);
            }
        }
        return $this->importBAO;
    }

}


