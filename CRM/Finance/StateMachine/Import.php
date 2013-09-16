<?php

require_once 'CRM/Core/StateMachine.php';

class CRM_Finance_StateMachine_Import extends CRM_Core_StateMachine {

    function __construct( $controller, $action = CRM_Core_Action::NONE ) {
        parent::__construct( $controller, $action );
        
        $this->_pages = array(
                              'CRM_Mailing_Form_Group'   => null,
                              'CRM_Mailing_Form_Settings'=> null,
                              'CRM_Mailing_Form_Upload'  => null,
                              'CRM_Mailing_Form_Test'    => null,
                              );

        $this->addSequentialPages( $this->_pages, $action );
    }

}


