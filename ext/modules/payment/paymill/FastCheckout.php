<?php

class FastCheckout
{
    private $_fastCheckoutFlag = false;
    
    public function canCustomerFastCheckoutCcTemplate($userId)
    {
        $flag = 'false';
        if ($this->canCustomerFastCheckoutCc($userId)) {
            $flag = 'true';
        }
        
        return $flag;
    }    
    
    public function canCustomerFastCheckoutElvTemplate($userId)
    {
        $flag = 'false';
        if ($this->canCustomerFastCheckoutElv($userId)) {
            $flag = 'true';
        }
        
        return $flag;
    }    
    
    public function canCustomerFastCheckoutCc($userId)
    {   
        return $this->hasCcPaymentId($userId) && $this->_fastCheckoutFlag;
    }
    
    public function canCustomerFastCheckoutElv($userId)
    {   
        return $this->hasElvPaymentId($userId) && $this->_fastCheckoutFlag;
    }
    
    public function saveCcIds($userId, $newClientId, $newPaymentId)
    {
        global $db;
        if ($this->_canUpdate($userId)) {
            $sql = "UPDATE `". DB_PREFIX . "pi_paymill_fastcheckout`SET `paymentID_CC` = '$newPaymentId' WHERE `userID` = '$userId'";
        } else {
            $sql = "INSERT INTO `". DB_PREFIX . "pi_paymill_fastcheckout` (`userID`, `clientID`, `paymentID_CC`) VALUES ('$userId', '$newClientId', '$newPaymentId')";
        }

        $db->Execute($sql);
    }
    
    public function saveElvIds($userId, $newClientId, $newPaymentId)
    {   
        global $db;
        if ($this->_canUpdate($userId)) {
            $sql = "UPDATE `". DB_PREFIX . "pi_paymill_fastcheckout`SET `paymentID_ELV` = '$newPaymentId' WHERE `userID` = '$userId'";
        } else {
            $sql = "INSERT INTO `". DB_PREFIX . "pi_paymill_fastcheckout` (`userID`, `clientID`, `paymentID_ELV`) VALUES ('$userId', '$newClientId', '$newPaymentId')";
        }
        
       $db->Execute($sql);
    }
    
    private function _canUpdate($userId)
    {
        $data = $this->loadFastCheckoutData($userId);
        return $data;
    }
    
    public function loadFastCheckoutData($userId)
    {
        global $db;
        $sql = "SELECT * FROM `". DB_PREFIX . "pi_paymill_fastcheckout` WHERE `userID` = '$userId'";
        
        $fastCheckout = $db->Execute($sql);
        
        return $fastCheckout->fields;
    }
    
    public function hasElvPaymentId($userId)
    {
        $hasPaymentId = false;
        $data = $this->loadFastCheckoutData($userId);
        if(($data && array_key_exists('paymentID_ELV', $data) && !empty($data['paymentID_ELV']))){
            $privateKey = trim(MODULE_PAYMENT_PAYMILL_ELV_PRIVATEKEY);
            $apiUrl = 'https://api.paymill.com/v2/';
            $payments = new Services_Paymill_Payments($privateKey, $apiUrl);
            $payment = $payments->getOne($data['paymentID_ELV']);
            $hasPaymentId = (isset($payment['id']));
        }

        return $hasPaymentId;
    }
    
    public function hasCcPaymentId($userId)
    {
        $hasPaymentId = false;
        $data = $this->loadFastCheckoutData($userId);
        if($data && array_key_exists('paymentID_CC', $data) && !empty($data['paymentID_CC'])){
            $privateKey = trim(MODULE_PAYMENT_PAYMILL_ELV_PRIVATEKEY);
            $apiUrl = 'https://api.paymill.com/v2/';
            $payments = new Services_Paymill_Payments($privateKey, $apiUrl);
            $payment = $payments->getOne($data['paymentID_CC']);
            $hasPaymentId = (isset($payment['id']));
        }

        return $hasPaymentId;
    }

    public function setFastCheckoutFlag($fastCheckoutFlag)
    {
        $this->_fastCheckoutFlag = $fastCheckoutFlag;
    }
    
}