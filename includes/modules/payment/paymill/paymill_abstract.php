<?php

require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/PaymentProcessor.php');
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/LoggingInterface.php');
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/Payments.php');
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/Clients.php');
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/FastCheckout.php');
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/WebHooks.php');

/**
 * Paymill payment plugin
 */
class paymill_abstract extends base  implements Services_Paymill_LoggingInterface
{

    var $code, $title, $description = '', $enabled, $privateKey, $logging, $fastCheckoutFlag, $label, $publicKey;
    var $bridgeUrl = 'https://bridge.paymill.com/';
    var $apiUrl = 'https://api.paymill.com/v2/';
    var $version = '1.3.0';
    var $api_version = '2';
    
    /**
     * @var FastCheckout
     */
    var $fastCheckout;
    
    /**
     * @var Services_Paymill_Payments
     */
    var $payments;
    
    /**
     *
     * @var Services_Paymill_PaymentProcessor
     */
    var $paymentProcessor;

    function paymill_abstract()
    {
        $this->description = '';
        $this->description = "<p style='font-weight: bold; text-align: center'>$this->version</p>";
        $this->paymentProcessor = new Services_Paymill_PaymentProcessor();
    }
    
    /**
     * @return FastCheckout
     */
    function getFastCheckout()
    {
        return $this->fastCheckout;
    }
    
    function update_status()
    {
        global $order, $db;

        if (get_class($this) == 'paymillCc') {
            $zone_id = MODULE_PAYMENT_PAYMILL_CC_ZONE;
        } elseif (get_class($this) == 'paymillElv') {
            $zone_id = MODULE_PAYMENT_PAYMILL_ELV_ZONE;
        } else {
            $zone_id = 0;
        }

        if (($this->enabled == true) && ((int) $zone_id > 0)) {
            $check_flag = false;

            $check_query = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . (int) $zone_id . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
            while (!$check_query->EOF) {
                if ($check_query->fields['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check_query->fields['zone_id'] == $order->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
                
                $check_query->MoveNext();
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
        
        if (empty($this->privateKey) || empty($this->publicKey)) {
            $this->enabled = false;
        }
    }

    function pre_confirmation_check()
    {
        return false;
    }

    function get_error()
    {
        global $_GET;
        $error = '';

        if (isset($_GET['error'])) {

            if (isset($_GET['error'])) {
                $error = urldecode($_GET['error']);
            }
        }

        if($error !== ''){
            $error_text['error'] = utf8_decode(constant('PAYMILL_'.$error));
        }

        return $error_text;

    }

    function javascript_validation()
    {
        return false;
    }

    function selection()
    {
        return array('id' => $this->code,
            'module' => $this->public_title);
    }

    function confirmation()
    {
        global $order;
        $_SESSION['paymill']['amount'] = $this->format_raw($order->info['total']);
        return array(
            'fields' => array(
                array(
                    'title' => '',
                    'field' => '<link rel="stylesheet" type="text/css" href="ext/modules/payment/paymill/public/css/paymill.css" />'
                ),
                array(
                    'title' => '',
                    'field' => '<script type="text/javascript">var PAYMILL_PUBLIC_KEY = "' . $this->publicKey . '";</script>'
                ),
                array(
                    'title' => '',
                    'field' => '<script type="text/javascript" src="' . $this->bridgeUrl . '"></script>'
                ),
            )
        );
    }

    function process_button()
    {
        return false;
    }

    function before_process()
    {
        global $order;

        $_SESSION['paymill_identifier'] = time();

        $this->paymentProcessor->setAmount((int) $this->format_raw($order->info['total']));
        $this->paymentProcessor->setApiUrl((string) $this->apiUrl);
        $this->paymentProcessor->setCurrency((string) strtoupper($order->info['currency']));
        $this->paymentProcessor->setDescription((string) STORE_NAME);
        $this->paymentProcessor->setEmail((string) $order->customer['email_address']);
        $this->paymentProcessor->setName((string) $order->customer['lastname'] . ', ' . $order->customer['firstname']);
        $this->paymentProcessor->setPrivateKey((string) $this->privateKey);
        $this->paymentProcessor->setToken((string) $_POST['paymill_token']);
        $this->paymentProcessor->setLogger($this);
        $this->paymentProcessor->setSource($this->version . '_ZENCART_' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR);

        $this->fastCheckout->setFastCheckoutFlag($this->fastCheckoutFlag);
        
        if ($_POST['paymill_token'] === 'dummyToken') {
            $this->fastCheckout();
        }
        
        $data = $this->fastCheckout->loadFastCheckoutData($_SESSION['customer_id']);
        if ($data && array_key_exists('clientID',$data) && $data['clientID'] != '' && $data['clientID'] != null){
            $this->existingClient($data);
        }

        $result = $this->paymentProcessor->processPayment();
        $_SESSION['paymill']['transaction_id'] = $this->paymentProcessor->getTransactionId();

        if (!$result) {
            unset($_SESSION['paymill_identifier']);
            $errorCode = $this->paymentProcessor->getErrorCode();
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'step=step2', 'SSL', true, false) . '&payment_error=' . $this->code . '&error='.$errorCode);
        }
        
        if ($this->fastCheckoutFlag) {
            $this->savePayment();
        } else {
            $this->saveClient();
        }
        
        unset($_SESSION['paymill_identifier']);
    }

    function existingClient($data)
    {
        global $order;
        if($this->fastCheckout->hasClient($_SESSION['customer_id'])){
            $client = $this->clients->getOne($data['clientID']);
            if ($client['email'] !== $order->customer['email_address']) {
                $this->clients->update(
                    array(
                        'id' => $data['clientID'],
                        'email' => $order->customer['email_address']
                    )
                );
            }

            $clientId = $data['clientID'];
        }

        $this->paymentProcessor->setClientId($clientId);
    }
    
    function fastCheckout()
    {
        if ($this->fastCheckout->canCustomerFastCheckoutCc($_SESSION['customer_id']) === 'true' && $this->code === 'paymillCc') {
            $data = $this->fastCheckout->loadFastCheckoutData($_SESSION['customer_id']);
            if (!empty($data['paymentID_CC'])) {
                $this->paymentProcessor->setPaymentId($data['paymentID_CC']);
            }
        }
        
        if ($this->fastCheckout->canCustomerFastCheckoutElv($_SESSION['customer_id']) === 'true' && $this->code === 'paymillElv') {
            $data = $this->fastCheckout->loadFastCheckoutData($_SESSION['customer_id']);
            
            if (!empty($data['paymentID_ELV'])) {
                $this->paymentProcessor->setPaymentId($data['paymentID_ELV']);
            }
        }
    }

    function savePayment()
    {
        if ($this->code === 'paymillCc') {
            $result = $this->fastCheckout->saveCcIds(
                $_SESSION['customer_id'], $this->paymentProcessor->getClientId(), $this->paymentProcessor->getPaymentId()
            );
        }

        if ($this->code === 'paymillElv') {
            $result = $this->fastCheckout->saveElvIds(
                $_SESSION['customer_id'], $this->paymentProcessor->getClientId(), $this->paymentProcessor->getPaymentId()
            );
        }

        $this->log(
             $result? "Payment saved.": "Payment not saved.",
                 var_export(array(
                                 'userId' => $_SESSION['customer_id'],
                                 'clientId' => $this->paymentProcessor->getClientId(),
                                 'paymentId' => $this->paymentProcessor->getPaymentId()
                            ), true));
    }
    
    function saveClient()
    {
        if ($this->code === 'paymillCc') {
            $result = $this->fastCheckout->saveCcIds(
                $_SESSION['customer_id'], $this->paymentProcessor->getClientId());
        }

        if ($this->code === 'paymillElv') {
            $result = $this->fastCheckout->saveElvIds(
                $_SESSION['customer_id'], $this->paymentProcessor->getClientId());
        }

        $this->log(
             "Client ".$result ? "": "not " ."saved.",
                 var_export(array(
                                 'userId' => $_SESSION['customer_id'],
                                 'clientId' => $this->paymentProcessor->getClientId(),
                            ), true));
    }
    
    function after_process()
    {
        global $order, $insert_id;

        if (get_class($this) == 'paymillCc') {
            $order_status_id = MODULE_PAYMENT_PAYMILL_CC_TRANSACTION_ORDER_STATUS_ID;
        } elseif (get_class($this) == 'paymillElv') {
            $order_status_id = MODULE_PAYMENT_PAYMILL_ELV_TRANSACTION_ORDER_STATUS_ID;
        } else {
            $order_status_id = $order->info['order_status'];
        }

        $sql_data_array = array('orders_id' => $insert_id,
            'orders_status_id' => $order_status_id,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'Payment approved, Transaction ID: ' . $_SESSION['paymill']['transaction_id']);

        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        $this->updateTransaction($_SESSION['paymill']['transaction_id'], $insert_id);

        unset($_SESSION['paymill']);
    }

    function remove()
    {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }

    function getOrderStatusTransactionID()
    {
        global $db;
        $check_query = $db->Execute("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Paymill [Transactions]' limit 1");

        if ($check_query->RecordCount() < 1) {
            $status_query = $db->Execute("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);

            $status_id = $status_query->fields['status_id'] + 1;

            $languages = zen_get_languages();

            foreach ($languages as $lang) {
                $db->Execute("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Paymill [Transactions]')");
            }

            $flags_query = $db->Execute("describe " . TABLE_ORDERS_STATUS . " public_flag");
            if ($flags_query->RecordCount() == 1) {
                $db->Execute("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
            }
        } else {
            $status_id = $check_query->fields['orders_status_id'];
        }

        return $status_id;
    }

    /**
     * @param string $messageInfo
     * @param string $debugInfo
     */
    function log($messageInfo, $debugInfo)
    {
        global $db;
        
        if ($this->logging) {
            if (array_key_exists('paymill_identifier', $_SESSION)) {
                $db->Execute("INSERT INTO `". DB_PREFIX . "pi_paymill_logging` "
                            . "(debug, message, identifier) "
                            . "VALUES('" 
                              . zen_db_input($debugInfo) . "', '" 
                              . zen_db_input($messageInfo) . "', '" 
                              . zen_db_input($_SESSION['paymill_identifier']) 
                            . "')"
                );
            }
        }
    }

    function format_raw($number)
    {
        return number_format(round($number, 2), 2, '', '');
    }


    function install()
    {
        global $db;

        $db->Execute("DROP TABLE IF EXISTS `pi_paymill_logging`");
        $db->Execute("DROP TABLE IF EXISTS `pi_paymill_fastcheckout`");

        $db->Execute(
            "CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "pi_paymill_logging` ("
          . "`id` int(11) NOT NULL AUTO_INCREMENT,"
          . "`identifier` text NOT NULL,"
          . "`debug` text NOT NULL,"
          . "`message` text NOT NULL,"
          . "`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,"
          . "PRIMARY KEY (`id`)"
        . ") AUTO_INCREMENT=1"
        );
        
        $db->Execute(
            "CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "pi_paymill_fastcheckout` ("
           . "`userID` varchar(100),"
           . "`clientID` varchar(100),"
           . "`paymentID_CC` varchar(100),"
           . "`paymentID_ELV` varchar(100),"
           . "PRIMARY KEY (`userID`)"
         . ")"
        );

        $db->Execute(
           "CREATE TABLE IF NOT EXISTS `".DB_PREFIX . "pi_paymill_webhooks` ("
           . "`id` varchar(100),"
           . "`url` varchar(150),"
           . "`mode` varchar(100),"
           . "`type` varchar(100),"
           . "`created_at` varchar(100),"
           . "PRIMARY KEY (`id`)"
           . ")"
        );

        $this->addOrderState('Paymill [Refund]');
        $this->addOrderState('Paymill [Chargeback]');
    }


    /**
     * Displays the register/remove Webhook button in the payment config.
     * @param String $type Can be either CC or ELV
     */
    function displayWebhookButton($type){
        if(empty($this->privateKey)){
            return;
        }

        $webhooks = new WebHooks($this->privateKey);
        $hooks = $webhooks->loadAllWebHooks($type);
        $action = empty($hooks) ? 'register' : 'remove';
        $buttonAction = 'CREATE';
        if($action === 'remove'){
            $buttonAction = 'REMOVE';
        }

        $buttonText = constant('MODULE_PAYMENT_PAYMILL_'.$type.'_WEBHOOKS_LINK_'.$buttonAction);

        $this->description .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>';
        $this->description .= '<script type="text/javascript" src="javascript/paymill_button_webhook.js"></script>';
        $this->description .= '<p><form id="register_webhooks" method="GET">';
        $parameters         = 'notification_action='.$action.'&type='.$type;
        $this->description .= '<input id="listener" type="hidden" value="'.zen_href_link('paymill_webhook_listener.php',$parameters, 'SSL', false, false).'"> ';
        $this->description .= '<button type="submit">'.$buttonText.'</button></form></p>';
    }

    /**
     * Updates the description of target transaction by adding the prefix 'OrderID: ' followed by the order id
     * @param String $id
     * @param String $orderId
     */
    function updateTransaction($id, $orderId)
    {
        $this->log('Updating transaction description', '');
        require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/Transactions.php');
        $transactions = new Services_Paymill_Transactions($this->privateKey, $this->apiUrl);
        $transaction = $transactions->getOne($id);
        $description = 'OrderID: ' . $orderId . ' ' . $transaction['description'];
        $transactions->update(array(
                                   'id'          => $id,
                                   'description' => $description
                              ));


    }

    /**
     * Adds a new order state with the given name for both german and english language sets
     * Therefore the state name should be english
     * @param String $stateName
     */
    function addOrderState($stateName)
    {
        global $db;
        $check_query = $db->Execute("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = '$stateName' limit 1");

        if ($check_query->RecordCount() < 1) {
            $status_query = $db->Execute("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);

            $status_id = $status_query->fields['status_id'] + 1;

        } else {
            $status_id = $check_query->fields['orders_status_id'];
        }

        $languages = zen_get_languages();

        foreach ($languages as $lang) {
            $db->Execute("REPLACE INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', '".$stateName."')");
        }
    }
}

?>
