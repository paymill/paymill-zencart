<?php
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/Clients.php');
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/Payments.php');

class FastCheckout
{
    var $_fastCheckoutFlag = false;
    var $_apiUrl = 'https://api.paymill.com/v2/';
    var $_privateKey;
    var $_clients;
    var $_payments;

    function __construct()
    {
        $this->_privateKey = trim(MODULE_PAYMENT_PAYMILL_ELV_PRIVATEKEY);
        $this->_clients = new Services_Paymill_Clients($this->_privateKey, $this->_apiUrl);
        $this->_payments = new Services_Paymill_Payments($this->_privateKey, $this->_apiUrl);
    }

    /**
     * Determines whether a credit card payment is applicable for fast checkout or not
     * The result is casted to sting to allow print in javascript.
     * @param $userId
     *
     * @return string
     */
    function canCustomerFastCheckoutCc($userId)
    {   
        return $this->hasCcPaymentId($userId) && $this->_fastCheckoutFlag && $this->hasClient($userId) ? 'true' : 'false';
    }

    /**
     * Determines whether a direct debit payment is applicable for fast checkout or not
     * The result is casted to sting to allow print in javascript.
     * @param $userId
     *
     * @return string
     */
    function canCustomerFastCheckoutElv($userId)
    {   
        return $this->hasElvPaymentId($userId) && $this->_fastCheckoutFlag && $this->hasClient($userId) ? 'true' : 'false';
    }

    /**
     * Saves the new Payment and Client id for credit card payment
     *
     * @param $userId
     * @param $newClientId
     * @param $newPaymentId
     *
     * @return bool
     */
    function saveCcIds($userId, $newClientId, $newPaymentId = 'NULL')
    {
        if(!$newClientId || $newClientId === null || $newClientId === ''){
            return false;
        }
        return $this->_saveIds('CC', $userId, $newClientId, $newPaymentId);
    }

    /**
     * Saves the new Payment and Client id for direct debit payment
     *
     * @param $userId
     * @param $newClientId
     * @param $newPaymentId
     *
     * @return bool
     */
    function saveElvIds($userId, $newClientId, $newPaymentId = 'NULL')
    {
        if(!$newClientId || $newClientId === null || $newClientId === ''){
            return false;
        }
        return $this->_saveIds('ELV', $userId, $newClientId, $newPaymentId);
    }

    /**
     * Validates the given data before saving it for the given user and payment
     *
     * @param string $paymentType may be either CC or ELV
     * @param string $userId
     * @param string $newClientId
     * @param string $newPaymentId
     *
     * @throws Exception
     * @return bool
     */
    function _saveIds($paymentType, $userId, $newClientId, $newPaymentId)
    {
        if($paymentType !== 'CC' && $paymentType !== 'ELV'){
            throw new Exception('Invalid Type in _saveIds: '.$paymentType);
        }

        if( !$userId || $userId === '' || $userId === null){
            throw new Exception('Invalid userId: '.var_export($userId, true));
        }

        //Gather Data needed
        $data = $this->loadFastCheckoutData($userId);
        $success = false;

        //Validate Client
        $client = $this->_clients->getOne($newClientId);

        if($client && array_key_exists('id', $client) && !empty($client['id'])){

            //Validate Payment
            $payment = $this->_payments->getOne($newPaymentId);

            if($payment && array_key_exists('id', $payment) && !empty($payment['id'])){

                //Check if valid data is present
                $client = $this->_clients->getOne($data['clientID']);
                if ($client && array_key_exists('id', $client) && !empty($client['id'])) {
                    $success = $this->_updateIds($paymentType, $userId, $newPaymentId);
                } else {
                    $success = $this->_insertIds($paymentType, $userId, $newClientId, $newPaymentId);
                }
            }
        }

        return $success;
    }

    /**
     * Updates the database entry for the given user in the id table
     * @param $paymentType
     * @param $userId
     * @param $newPaymentId
     *
     * @return bool
     */
    function _updateIds($paymentType, $userId, $newPaymentId)
    {
        global $db;
        $sql = "UPDATE `". DB_PREFIX . "pi_paymill_fastcheckout`SET `paymentID_". $paymentType ."` = '$newPaymentId' WHERE `userID` = '$userId'";
        $success = $db->Execute($sql);
        $success = $success === true;
        return $success;
    }

    /**
     * Inserts the new data for the given user into the id table.
     * If data is already present, it will be replaced
     * @param $paymentType
     * @param $userId
     * @param $newClientId
     * @param $newPaymentId
     *
     * @return bool
     */
    function _insertIds($paymentType, $userId, $newClientId, $newPaymentId)
    {
        global $db;
        $sql = "REPLACE INTO `". DB_PREFIX . "pi_paymill_fastcheckout` (`userID`, `clientID`, `paymentID_". $paymentType ."`) VALUES ('$userId', '$newClientId', '$newPaymentId')";
        $success = $db->Execute($sql);
        $success = $success === true;
        return $success;
    }

    /**
     * Returns the saved fast checkout data for the given user
     * @param $userId
     *
     * @return mixed
     */
    function loadFastCheckoutData($userId)
    {
        global $db;
        $sql = "SELECT * FROM `". DB_PREFIX . "pi_paymill_fastcheckout` WHERE `userID` = '$userId'";
        
        $fastCheckout = $db->Execute($sql);
        
        return $fastCheckout->fields;
    }

    /**
     * Returns if there is a elv payment id for the given user
     * @param $userId
     *
     * @return bool
     */
    function hasElvPaymentId($userId)
    {
        return $this->_hasPaymentId('ELV',$userId);
    }

    /**
     * Returns if there is a cc payment id for the given user
     * @param $userId
     *
     * @return bool
     */
    function hasCcPaymentId($userId)
    {
        return $this->_hasPaymentId('CC',$userId);
    }

    /**
     * Determines if there is a payment id for the given user and payment
     * @param string $paymentType Can be either CC or ELV
     * @param string $userId
     *
     * @return bool
     */
    function _hasPaymentId($paymentType, $userId)
    {
        $hasPaymentId = false;
        $data = $this->loadFastCheckoutData($userId);
        $arrayKey = 'paymentID_'.$paymentType;
        if($data && array_key_exists($arrayKey, $data) && !empty($data[$arrayKey])){
            $payment = $this->_payments->getOne($data[$arrayKey]);
            $hasPaymentId = (isset($payment['id']) && $this->hasClient($userId));
        }

        return $hasPaymentId;
    }

    /**
     * Determines if there is a client for the given user.
     * If the client present is invalid, the client and both payments will be cleared to avoid any further errors
     * @param $userId
     *
     * @return bool
     */
    function hasClient($userId)
    {
        $hasClient = false;
        $data = $this->loadFastCheckoutData($userId);
        if($data && array_key_exists('clientID', $data) && !empty($data['clientID'])){
            $client = $this->_clients->getOne($data['clientID']);
            $hasClient = ($client && array_key_exists('id', $client) && !empty($client['id']));
        }

        if(!$hasClient){
            $this->_removeIds($userId);
        }

        return $hasClient;
    }

    /**
     * Removes all saved ids for a given user
     * @param $userId
     *
     * @return bool
     */
    function _removeIds($userId)
    {
        global $db;
        $sql = "REPLACE INTO `". DB_PREFIX . "pi_paymill_fastcheckout` (`userID`, `clientID`, `paymentID_CC`, `paymentID_ELV`) VALUES ('$userId', NULL, NULL, NULL)";
        $success = $db->Execute($sql);
        $success = $success === true;
        return $success;
    }

    /**
     * Sets the fast checkout flag
     * @param $fastCheckoutFlag
     */
    function setFastCheckoutFlag($fastCheckoutFlag)
    {
        $this->_fastCheckoutFlag = $fastCheckoutFlag;
    }
    
}