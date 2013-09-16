<?php

class CRM_Finance_BAO_Import_ValidateException extends Exception {
	private $value;
	
	public function __construct($message, $code, $value = null) {
		parent::__construct($message, $code, null);
		$this->value = $value;
	}
	
	public function getValue() {
		return $this->value;
	}
}