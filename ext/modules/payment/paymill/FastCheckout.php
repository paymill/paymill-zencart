<?php
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/Clients.php');
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/Payments.php');

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
        return $this->hasCcPaymentId($userId) && $this->_fastCheckoutFlag && $this->hasClient($userId);
    }
    
    public function canCustomerFastCheckoutElv($userId)
    {   
        return $this->hasElvPaymentId($userId) && $this->_fastCheckoutFlag && $this->hasClient($userId);
    }
    
    public function saveCcIds($userId, $newClientId, $newPaymentId)
    {
        global $db;
        if ($this->_canUpdate($userId)) {
            $sql = "UPDATE `". DB_PREFIX . "pi_paymill_fastcheckout`SET `paymentID_CC` = '$newPaymentId' WHERE `userID` = '$userId'";
        } else {
            $sql = "REPLACE INTO `". DB_PREFIX . "pi_paymill_fastcheckout` (`userID`, `clientID`, `paymentID_CC`) VALUES ('$userId', '$newClientId', '$newPaymentId')";
        }

        $db->Execute($sql);
    }
    
    public function saveElvIds($userId, $newClientId, $newPaymentId)
    {   
        global $db;
        if ($this->_canUpdate($userId)) {
            $sql = "UPDATE `". DB_PREFIX . "pi_paymill_fastcheckout`SET `paymentID_ELV` = '$newPaymentId' WHERE `userID` = '$userId'";
        } else {
            $sql = "REPLACE INTO `". DB_PREFIX . "pi_paymill_fastcheckout` (`userID`, `clientID`, `paymentID_ELV`) VALUES ('$userId', '$newClientId', '$newPaymentId')";
        }
        
       $db->Execute($sql);
    }
    
    private function _canUpdate($userId)
    {
        $privateKey = trim(MODULE_PAYMENT_PAYMILL_ELV_PRIVATEKEY);
        $apiUrl = 'https://api.paymill.com/v2/';
        $data = $this->loadFastCheckoutData($userId);

        $client = new Services_Paymill_Clients($privateKey, $apiUrl);
        $clientData = $client->getOne($data['clientID']);
        $result = $clientData && array_key_exists('id', $clientData) && !empty($clientData['id']);
        return $result ? $data : false;

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
            $hasPaymentId = (isset($payment['id']) && $this->hasClient($userId));
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
            $hasPaymentId = (isset($payment['id']) && $this->hasClient($userId));
        }

        return $hasPaymentId;
    }

    public function hasClient($userId)
    {
        $hasClient = false;
        $data = $this->loadFastCheckoutData($userId);
        if($data && array_key_exists('clientID', $data) && !empty($data['clientID'])){
            $privateKey = trim(MODULE_PAYMENT_PAYMILL_ELV_PRIVATEKEY);
            $apiUrl = 'https://api.paymill.com/v2/';
            $clients = new Services_Paymill_Clients($privateKey, $apiUrl);
            $client = $clients->getOne($data['clientID']);
            $hasClient = (isset($client['id']));
        }

        if(!$hasClient){
            $this->saveCcIds($userId, "", "");
            $this->saveElvIds($userId, "", "");
        }

        return $hasClient;
    }

    public function setFastCheckoutFlag($fastCheckoutFlag)
    {
        $this->_fastCheckoutFlag = $fastCheckoutFlag;
    }
    
}