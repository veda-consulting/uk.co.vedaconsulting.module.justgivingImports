<?php
require_once 'CRM/Core/Form.php';

class CRM_Finance_Form_Import_JustGiving_SourceUpload extends CRM_Finance_Form_Import_SourceUpload {
    private $errors = array();
    
    function preProcess() {
        parent::preProcess( );
        
//    	if(!empty($values['range_from']) && !empty($values['range_from'])) {
//    		$from = CRM_Utils_Date::processDate($values['range_from'], null, null, 'Y-m-d');
//    		$to = CRM_Utils_Date::processDate($values['range_to'], null, null, 'Y-m-d');
//        
//    		$this->set('range_from', $from);
//    		$this->set('range_to', $to);
//    	}

        $values = array(
        	'range_from' => CRM_Utils_Request::retrieve('range_from', 'String', $this, false, null, 'POST'),
        	'range_to' => CRM_Utils_Request::retrieve('range_to', 'String', $this, false, null, 'POST'),
        );
        
        if(!empty($values['range_from']) && !empty($values['range_from'])) {
    		$from = CRM_Utils_Date::processDate($values['range_from'], null, null, 'Y-m-d');
    		$to = CRM_Utils_Date::processDate($values['range_to'], null, null, 'Y-m-d');
        
            $fromDate = new DateTime($from);
    		$toDate = new DateTime($to);
            $diff = $fromDate->diff($toDate);
            
            $days = $diff->days;
            //https://bugs.php.net/bug.php?id=51184
            if($days == 6015) {
                $y1 = $fromDate->format('Y');
                $y2 = $toDate->format('Y');
                $z1 = $fromDate->format('z');
                $z2 = $toDate->format('z');

                $days = intval($y1 * 365.2425 + $z1) - intval($y2 * 365.2425 + $z2);
            }
                        
    		if($days > 93 || $diff->m >= 3) {
    			$this->setElementError('range_to', "Date range can't exceed 3 months");
    			
    			$this->set('range_from', null);
    			$this->set('range_to', null);
    		}
    		else {
    			$this->set('range_from', $from);
    			$this->set('range_to', $to);
    			
    			$this->renderPayments();
    		}
    	}
    	
    }
    
    private function renderPayments() {
    	$from = $this->get('range_from');
        $to = $this->get('range_to');
        
        if(!empty($from) && !empty($to)) {
        	$bao = $this->controller->getImportBAO();
        	$paymentsApi = $bao->fetchAllPayments($from, $to);
        	$pays = array();
        	
        	require_once 'CRM/Utils/Money.php';
        			
        	foreach ($paymentsApi as $item) {
        		$pays[] = array(
        			'id' => $item->PaymentRef,
        			'date' => $item->PaymentDate,
        			'net' => CRM_Utils_Money::format($item->Net),
        		);
        	}
        	$this->assign('payments', $pays);
        }
    }
    
    function buildQuickForm( ) {
    	$this->controller->getImportBAO()->buildQuickForm($this);
        $this->addFormRule(array($this, 'formRule'));
        $buttons = array(
            array ('type'      => 'submit', 
                'name'      => ts('Get Payment List'), 
                'isDefault' => true   ), 
        );
        
        $from = $this->getTemplate()->get_template_vars('payments');
        if(!empty($from)) {
        	$buttons[] = array ('type'      => 'next', 
                'name'      => ts('Upload Donations'), 
                'isDefault' => true   ); 
        }
        
        $this->createElement( 'submit', $buttonName, $button['name'], $attrs );
        
        $this->addButtons($buttons);
    }
    
    public function formRule($values) {
    	list($source, $actionName) = $this->controller->getActionName();
    	if($actionName == 'next') {
	    	if(empty($values['payment_id'])) {
	    		//$this->renderPayments();
	    		
	    		return array('payment_id' => 'Please select Payment Record');
	    	}
	    	
	    	return true;
    	}
    	
    	//$values = $this->controller->exportValues();
    	return array('notyet' => 'ERROR');
    }
}
