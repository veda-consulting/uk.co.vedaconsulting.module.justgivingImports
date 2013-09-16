<?php

class CRM_Finance_BAO_Import_Source {
    public static function getAllAsOptions() {
        //matusz: TODO this should be using db table?
        return array(
            'JustGivingCsv' => 'Just Giving (CSV)',
            'JustGivingGiftAidCsv' => 'Just Giving - GiftAid (CSV)',
        );
    }
    
    public static function factory($typeName) {
        switch($typeName) {
            case 'JustGivingCsv':
                require_once('CRM/Finance/BAO/Import/JustGivingCsv.php');
                $bao = new CRM_Finance_BAO_Import_JustGivingCsv();
                break;
            case 'JustGivingGiftAidCsv':
                require_once('CRM/Finance/BAO/Import/JustGivingGiftAidCsv.php');
                $bao = new CRM_Finance_BAO_Import_JustGivingGiftAidCsv();
                break;
            default:
                throw new Exception("No source BAO '$typeName'");
        }
        $bao->setSourceName($typeName);
        
				// Amend to your setup
        $methods = array(
            'JustGivingCsv' => 5,
            'JustGivingGiftAidCsv' => 11,
        );
        
        if(!isset($methods[$typeName])) {
            throw new Exception("No default payment method set for import type");
        }
        $bao->setDefaultPaymentMethodId($methods[$typeName]);

        return $bao;
    }
}