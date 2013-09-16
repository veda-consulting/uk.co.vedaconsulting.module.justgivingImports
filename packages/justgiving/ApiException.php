<?php

class JustGiving_ApiException extends Exception {
	private $errors;
	
	public function __construct($message, $code = 0, $prev = null, $errors = array()) {
		if($errors) {
			$this->errors = $errors;
			
			$errs = array();
			foreach($errors as $err) {
				$errs[] = "{$err->id}: {$err->desc}"; 
			}
			$message .= " - " . implode("; ", $errs);
		}
		
		parent::__construct($message, $code, null);
	}
	
	public function getErrors() {
		$this->errors;
	}
}