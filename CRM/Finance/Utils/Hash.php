<?php

class CRM_Veda_AccountHash_Utils_Hash {
    public static function generateHash($sortCode, $accountNumber) {
        return md5($accountNumber . $sortCode);
    }
}