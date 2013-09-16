<?php

class CRM_Finance_Api {
    private static $client;
    
    /**
     *
     * @return JustGivingClient 
     */
    public static function getJustGivingClient() {
        if(self::$client === null) {
    		require_once 'packages/justgiving/JustGivingClient.php';
    		self::$client = new JustGivingClient(CIVICRM_API_JUSTGIVING_ENV, CIVICRM_API_JUSTGIVING_KEY, 1, CIVICRM_API_JUSTGIVING_USER, CIVICRM_API_JUSTGIVING_PASS);
    	}
    	
    	return self::$client;
    }
}