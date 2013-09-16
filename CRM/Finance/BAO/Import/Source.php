<?php

class CRM_Finance_BAO_Import_Source {
    public static function getAllAsOptions() {
        //matusz: TODO this should be using db table?
        return array(
            'VirginMoney' => 'Virgin Money',
            'VirginMoneyGiftAid' => 'Virgin Money - GiftAid',
            //'JustGiving' => 'Just Giving (API)',
            'JustGivingCsv' => 'Just Giving (CSV)',
            'JustGivingGiftAidCsv' => 'Just Giving - GiftAid (CSV)',
            //'DirectDebit' => 'Direct Debit (Submission)',
            //'DirectDebitAuth' => 'Direct Debit',
            'DirectDebitOasis' => 'Direct Debit',
            'StandingOrderCsv' => 'Standing Orders',
            //'StandingOrderRBS' => 'Standing Orders (RBS)',
            //'PayrollGiving' => 'Payroll Giving',
            //'OnlineTrading' => 'Online Trading',
            //'PayrollCTC' => 'Payroll CTC',
            //'PayrollCAF' => 'Payroll CAF',
            //'BTMyDonateCsv' => 'BT My Donate (CSV)',
            'RBSWorldPayCsv' => 'RBS World Pay (CSV)',
            'CoopCsv' => 'Coop (CSV)',
            'RapidPaidCsv' => 'Rapid Paid (CSV)',
            'CafCsv' => 'CAF (CSV)',
            'CharityTrust' => 'Charity Trust (CSV)',
            'Stewardship' => 'Stewardship (CSV)',
        );
    }
    
    public static function factory($typeName) {
        switch($typeName) {
            case 'VirginMoneyGiftAid':
                require_once('CRM/Finance/BAO/Import/VirginMoneyGiftAid.php');
                $bao = new CRM_Finance_BAO_Import_VirginMoneyGiftAid();
                break;
           case 'VirginMoney':
                require_once('CRM/Finance/BAO/Import/VirginMoney.php');
                $bao = new CRM_Finance_BAO_Import_VirginMoney();
                break;
            case 'DirectDebit':
                require_once('CRM/Finance/BAO/Import/DirectDebit.php');
                $bao = new CRM_Finance_BAO_Import_DirectDebit();
                break;
            case 'DirectDebitAuth':
                require_once('CRM/Finance/BAO/Import/DirectDebitAuth.php');
                $bao = new CRM_Finance_BAO_Import_DirectDebitAuth();
                break;
            case 'DirectDebitOasis':
                require_once('CRM/Finance/BAO/Import/DirectDebitOasis.php');
                $bao = new CRM_Finance_BAO_Import_DirectDebitOasis();
                break;
            case 'StandingOrder':
                require_once('CRM/Finance/BAO/Import/StandingOrder.php');
                $bao = new CRM_Finance_BAO_Import_StandingOrder();
                break;
            case 'StandingOrderCsv':
                require_once('CRM/Finance/BAO/Import/StandingOrderCsv.php');
                $bao = new CRM_Finance_BAO_Import_StandingOrderCsv();
                break;
            case 'StandingOrderRBS':
                require_once('CRM/Finance/BAO/Import/StandingOrderRBS.php');
                $bao = new CRM_Finance_BAO_Import_StandingOrderRBS();
                break;
            case 'OnlineTrading':
                require_once('CRM/Finance/BAO/Import/OnlineTrading.php');
                $bao = new CRM_Finance_BAO_Import_OnlineTrading();
                break;
            case 'JustGiving':
                require_once('CRM/Finance/BAO/Import/JustGiving.php');
                $bao = new CRM_Finance_BAO_Import_JustGiving();
                break;
            case 'JustGivingCsv':
                require_once('CRM/Finance/BAO/Import/JustGivingCsv.php');
                $bao = new CRM_Finance_BAO_Import_JustGivingCsv();
                break;
            case 'BTMyDonateCsv':
                require_once('CRM/Finance/BAO/Import/BTMyDonateCsv.php');
                $bao = new CRM_Finance_BAO_Import_BTMyDonateCsv();
                break;
            case 'JustGivingGiftAidCsv':
                require_once('CRM/Finance/BAO/Import/JustGivingGiftAidCsv.php');
                $bao = new CRM_Finance_BAO_Import_JustGivingGiftAidCsv();
                break;
            case 'PayrollCTC':
                require_once('CRM/Finance/BAO/Import/PayrollCTC.php');
                $bao = new CRM_Finance_BAO_Import_PayrollCTC();
                break;
            case 'PayrollCAF':
                require_once('CRM/Finance/BAO/Import/PayrollCAF.php');
                $bao = new CRM_Finance_BAO_Import_PayrollCAF();
                break;
            case 'RBSWorldPayCsv':
                require_once('CRM/Finance/BAO/Import/RBSWorldPayCsv.php');
                $bao = new CRM_Finance_BAO_Import_RBSWorldPayCsv();
                break;
            case 'CoopCsv':
                require_once('CRM/Finance/BAO/Import/CoopCsv.php');
                $bao = new CRM_Finance_BAO_Import_CoopCsv();
                break;
            case 'RapidPaidCsv':
                require_once('CRM/Finance/BAO/Import/RapidPaidCsv.php');
                $bao = new CRM_Finance_BAO_Import_RapidPaidCsv();
                break;
            case 'CafCsv':
                require_once('CRM/Finance/BAO/Import/CafCsv.php');
                $bao = new CRM_Finance_BAO_Import_CafCsv();
                break;   
            case 'CharityTrust':
                require_once('CRM/Finance/BAO/Import/CharitiesTrustCsv.php');
                $bao = new CRM_Finance_BAO_Import_CharitiesTrustCsv();
                break;   
            case 'Stewardship':
                require_once('CRM/Finance/BAO/Import/StewardshipCsv.php');
                $bao = new CRM_Finance_BAO_Import_StewardshipCsv();
                break;   
            default:
                throw new Exception("No source BAO '$typeName'");
        }
        $bao->setSourceName($typeName);
        
        //mzeman: TODO default payment method - load from DB?
        $methods = array(
            'VirginMoney' => 6,
            'VirginMoneyGiftAid' => 12,
            'JustGiving' => 5,
            'JustGivingCsv' => 5,
            'BTMyDonateCsv' => 0,
            'JustGivingGiftAidCsv' => 11,
            'DirectDebit' => 0,
            'DirectDebitAuth' => 0,
            'DirectDebitOasis' => 0,
            'StandingOrder' => 0,
            'StandingOrderCsv' => 14,
            'StandingOrderRBS' => 0,
            'PayrollGiving' => 0,
            'OnlineTrading' => 0,
            'PayrollCTC' => 0,
            'PayrollCAF' => 0,
            'RBSWorldPayCsv' => 16,
            'CoopCsv' => 17,
            'RapidPaidCsv' => 18,
            'CafCsv' => 19,
            'CharityTrust' => 20,
            'Stewardship' => 21,
        );
        
        if(!isset($methods[$typeName])) {
            throw new Exception("No default payment method set for import type");
        }
        $bao->setDefaultPaymentMethodId($methods[$typeName]);

        return $bao;
    }
}