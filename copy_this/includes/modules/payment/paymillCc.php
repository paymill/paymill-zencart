<?php
require_once('paymill/paymill_abstract.php');

class paymillCc extends paymill_abstract
{

    function paymillCc()
    {
        parent::paymill_abstract();
        global $order;
        $this->code = 'paymillCc';
        $this->title = MODULE_PAYMENT_PAYMILL_CC_TEXT_TITLE;
        $this->public_title = MODULE_PAYMENT_PAYMILL_CC_TEXT_PUBLIC_TITLE;
        $this->privateKey = trim(MODULE_PAYMENT_PAYMILL_CC_PRIVATEKEY);
        $this->fastCheckout = new FastCheckout($this->privateKey);
        
        if (defined('MODULE_PAYMENT_PAYMILL_CC_STATUS')) {
            $this->enabled = ((MODULE_PAYMENT_PAYMILL_CC_STATUS == 'True') ? true : false);
            $this->sort_order = MODULE_PAYMENT_PAYMILL_CC_SORT_ORDER;
            $this->logging = ((MODULE_PAYMENT_PAYMILL_CC_LOGGING == 'True') ? true : false);
            $this->webHooksEnabled = ((MODULE_PAYMENT_PAYMILL_CC_WEBHOOKS == 'True') ? true : false);
            $this->publicKey = MODULE_PAYMENT_PAYMILL_CC_PUBLICKEY;
            $this->fastCheckoutFlag = ((MODULE_PAYMENT_PAYMILL_CC_FASTCHECKOUT == 'True') ? true : false);
            $this->fastCheckout->setFastCheckoutFlag($this->fastCheckoutFlag);
            $this->payments = new Services_Paymill_Payments(trim($this->privateKey), $this->apiUrl);
            $this->clients = new Services_Paymill_Clients(trim($this->privateKey), $this->apiUrl);
            if ((int) MODULE_PAYMENT_PAYMILL_CC_ORDER_STATUS_ID > 0) {
                $this->order_status = MODULE_PAYMENT_PAYMILL_CC_ORDER_STATUS_ID;
            }
                        
            if ($this->logging) {
                $this->description .= "<p><a href='" . zen_href_link('paymill_logging.php') . "'>PAYMILL Log</a></p>";
            }

            if ($this->webHooksEnabled) {
                $type = 'CC';
                $this->displayWebhookButton($type);
            }

        }
        
        if (is_object($order)) $this->update_status();
    }
    
    function selection()
    {
        $selection = parent::selection();
        return $selection;
    }
        
    function getPayment($userId)
    {
        $payment = array(
            'last4' => '',
            'cvc' => '',
            'card_holder' => '',
            'expire_month' => '',
            'expire_year' => '',
            'card_type' => '',
        );
        
        if ($this->fastCheckout->canCustomerFastCheckoutCc($userId)) {
            $data = $this->fastCheckout->loadFastCheckoutData($userId);
            $payment = $this->payments->getOne($data['paymentID_CC']);
            $payment['last4'] = '************' . $payment['last4'];
            $payment['cvc'] = '***';
        }
        
        return $payment;
    }


    function confirmation()
    {
        global $order;
        
        $confirmation = parent::confirmation();        
        
        $months_array     = array();
        $months_array[1]  = array('01', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_JANUARY);
        $months_array[2]  = array('02', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_FEBRUARY);
        $months_array[3]  = array('03', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_MARCH);
        $months_array[4]  = array('04', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_APRIL);
        $months_array[5]  = array('05', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_MAY);
        $months_array[6]  = array('06', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_JUNE);
        $months_array[7]  = array('07', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_JULY);
        $months_array[8]  = array('08', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_AUGUST);
        $months_array[9]  = array('09', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_SEPTEMBER);
        $months_array[10] = array('10', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_OCTOBER);
        $months_array[11] = array('11', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_NOVEMBER);
        $months_array[12] = array('12', MODULE_PAYMENT_PAYMILL_CC_TEXT_MONTH_DECEMBER);

        $today = getdate(); 
        $years_array = array();

        for ($i=$today['year']; $i < $today['year']+10; $i++) {
            $years_array[$i] = array(strftime('%Y', mktime(0, 0, 0, 1 , 1, $i)), strftime('%Y',mktime(0, 0, 0, 1, 1, $i)));
        }

        $this->fastCheckout->setFastCheckoutFlag($this->fastCheckoutFlag);
        $payment = $this->getPayment($_SESSION['customer_id']);
        
        $script = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>'
                . '<script type="text/javascript">'
                    . 'var cclogging = "' . MODULE_PAYMENT_PAYMILL_CC_LOGGING . '";'
                    . 'var cc_expiery_invalid = "' .  utf8_encode(html_entity_decode(MODULE_PAYMENT_PAYMILL_CC_TEXT_CREDITCARD_EXPIRY_INVALID)) . '";'
                    . 'var cc_owner_invalid = "' .  utf8_encode(html_entity_decode(MODULE_PAYMENT_PAYMILL_CC_TEXT_CREDITCARD_OWNER_INVALID)) . '";'
                    . 'var cc_card_number_invalid = "' .  utf8_encode(html_entity_decode(MODULE_PAYMENT_PAYMILL_CC_TEXT_CREDITCARD_CARDNUMBER_INVALID)) . '";'
                    . 'var cc_cvc_number_invalid = "' .  utf8_encode(html_entity_decode(MODULE_PAYMENT_PAYMILL_CC_TEXT_CREDITCARD_CVC_INVALID)) . '";'
                    . 'var brand = "' . $payment['card_type'] . '";'
                    . 'var paymill_total = ' . json_encode((int) $_SESSION['paymill']['amount']) . ';'
                    . 'var paymill_currency = ' . json_encode(strtoupper($order->info['currency'])) . ';'
                    . 'var paymill_cc_months = ' . json_encode($months_array) . ';'
                    . 'var paymill_cc_years = ' . json_encode($years_array) . ';'
                    . 'var paymill_cc_number_val = "' . $payment['last4'] . '";'
                    . 'var paymill_cc_cvc_val = "' . $payment['cvc'] . '";'
                    . 'var paymill_cc_card_type = "' . utf8_decode($payment['card_type']) . '";'
                    . 'var paymill_cc_holder_val = "' . utf8_decode($payment['card_holder']) . '";'
                    . 'var paymill_cc_expiry_month_val = "' . $payment['expire_month'] . '";'
                    . 'var paymill_cc_expiry_year_val = "' . $payment['expire_year'] . '";'
                    . 'var paymill_cc_fastcheckout = ' . ($this->fastCheckout->canCustomerFastCheckoutCc($_SESSION['customer_id']) ? 'true' : 'false') . ';'
                    . 'var checkout_payment_link = "' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'step=step2', 'SSL', true, false) . '&payment_error=' . $this->code . '&error=";'
                    . 'var logos =  new Array();'
                    . "logos['amex'] = " . strtolower(MODULE_PAYMENT_PAYMILL_CC_AMEX) . ";"
                    . "logos['carta-si'] = " . strtolower(MODULE_PAYMENT_PAYMILL_CC_CARTASI) . ";"
                    . "logos['dankort'] =  " . strtolower(MODULE_PAYMENT_PAYMILL_CC_DANKORT) . ";"
                    . "logos['carte-bleue'] =  " . strtolower(MODULE_PAYMENT_PAYMILL_CC_CARTEBLEUE) . ";"
                    . "logos['discover'] =  " . strtolower(MODULE_PAYMENT_PAYMILL_CC_DISCOVER) . ";"
                    . "logos['diners-club'] =  " . strtolower(MODULE_PAYMENT_PAYMILL_CC_DINERSCLUB) . ";"
                    . "logos['unionpay'] =  " . strtolower(MODULE_PAYMENT_PAYMILL_CC_UNIONPAY) . ";"
                    . "logos['maestro'] =  " . strtolower(MODULE_PAYMENT_PAYMILL_CC_MAESTRO) . ";"
                    . "logos['jcb'] =  " . strtolower(MODULE_PAYMENT_PAYMILL_CC_JCB) . ";"
                    . "logos['mastercard'] =  " . strtolower(MODULE_PAYMENT_PAYMILL_CC_MASTERCARD) . ";"
                    . "logos['visa'] =  " . strtolower(MODULE_PAYMENT_PAYMILL_CC_VISA) . ";"
                    . "var allBrandsDisabled = !logos['amex'] && !logos['carta-si'] && !logos['dankort'] && !logos['carte-bleue'] && !logos['discover'] && !logos['diners-club'] && !logos['unionpay'] && !logos['maestro'] && !logos['jcb'] && !logos['mastercard'] && !logos['visa'];"
                . '</script>'
                . '<script type="text/javascript" src="ext/modules/payment/paymill/public/javascript/BrandDetection.js"></script>'
                . '<script type="text/javascript" src="ext/modules/payment/paymill/public/javascript/cc.js"></script>';

        $script .= $this->getJavascript();
        
        if (!((MODULE_PAYMENT_PAYMILL_CC_AMEX === 'False') 
            && (MODULE_PAYMENT_PAYMILL_CC_CARTASI === 'False') 
            && (MODULE_PAYMENT_PAYMILL_CC_DANKORT === 'False') 
            && (MODULE_PAYMENT_PAYMILL_CC_CARTEBLEUE === 'False') 
            && (MODULE_PAYMENT_PAYMILL_CC_DISCOVER === 'False') 
            && (MODULE_PAYMENT_PAYMILL_CC_DINERSCLUB === 'False') 
            && (MODULE_PAYMENT_PAYMILL_CC_UNIONPAY === 'False') 
            && (MODULE_PAYMENT_PAYMILL_CC_MAESTRO === 'False') 
            && (MODULE_PAYMENT_PAYMILL_CC_JCB === 'False') 
            && (MODULE_PAYMENT_PAYMILL_CC_MASTERCARD === 'False')
            && (MODULE_PAYMENT_PAYMILL_CC_VISA === 'False'))
        ) {
            $logos = '';
            if (MODULE_PAYMENT_PAYMILL_CC_AMEX === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_amex.png" alt="American Express"/>';
            }
            
            if (MODULE_PAYMENT_PAYMILL_CC_CARTASI === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_carta-si.png" alt="American Express"/>';
            }
            
            if (MODULE_PAYMENT_PAYMILL_CC_DANKORT === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_dankort.png" alt="American Express"/>';
            }
            
            if (MODULE_PAYMENT_PAYMILL_CC_CARTEBLEUE === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_carte-bleue.png" alt="American Express"/>';
            }
            
            if (MODULE_PAYMENT_PAYMILL_CC_DISCOVER === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_discover.png" alt="American Express"/>';
            }
            
            if (MODULE_PAYMENT_PAYMILL_CC_DINERSCLUB === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_dinersclub.png" alt="American Express"/>';
            }
            
            if (MODULE_PAYMENT_PAYMILL_CC_UNIONPAY === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_unionpay.png" alt="American Express"/>';
            }
            
            if (MODULE_PAYMENT_PAYMILL_CC_MAESTRO === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_maestro.png" alt="American Express"/>';
            }
            
            if (MODULE_PAYMENT_PAYMILL_CC_JCB === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_jcb.png" alt="American Express"/>';
            }
            
            if (MODULE_PAYMENT_PAYMILL_CC_MASTERCARD === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_mastercard.png" alt="American Express"/>';
            }
            
            if (MODULE_PAYMENT_PAYMILL_CC_VISA === 'True') {
                $logos .= '<img src="ext/modules/payment/paymill/public/images/32x20_visa.png" alt="American Express"/>';
            }
            
            array_push($confirmation['fields'], 
                array(
                    'title' => '',
                    'field' => $logos
                )
            );
        }
        
        array_push($confirmation['fields'], 
             array(
                 'title' => $script . '<div class="paymill-label-field">' . MODULE_PAYMENT_PAYMILL_CC_TEXT_CREDITCARD_OWNER . '</div>',
                 'field' => '<span id="card-owner-field"></span><span id="card-owner-error" class="paymill-error"></span>'
             )
        );
                
        array_push($confirmation['fields'], 
            array(
                'title' => '<div class="paymill-label-field">' . MODULE_PAYMENT_PAYMILL_CC_TEXT_CREDITCARD_NUMBER . '</div>',
                'field' => '<span id="card-number-field"></span><span id="card-number-error" class="paymill-error"></span>'
            )
        );
        
        array_push($confirmation['fields'], 
            array(
                'title' => '<div class="paymill-label-field paymill-expiry">' . MODULE_PAYMENT_PAYMILL_CC_TEXT_CREDITCARD_EXPIRY . '</div>',
                'field' => '<div class="paymill-expiry"><span id="card-expiry-month-field"></span>&nbsp;<span id="card-expiry-year-field"></span></span><span id="card-expiry-error" class="paymill-error"></div>'
            )
        );
        
        array_push($confirmation['fields'], 
            array(
                'title' => '<div class="paymill-label-field">' . MODULE_PAYMENT_PAYMILL_CC_TEXT_CREDITCARD_CVC . '<span class="tooltip" title="' . MODULE_PAYMENT_PAYMILL_CC_TEXT_CREDITCARD_CVC_TOOLTIP . '">?</span></div>',
                'field' => '<span id="card-cvc-field" class="card-cvc-row"></span><span id="card-cvc-error" class="paymill-error"></span>'
            )
        );
        
        array_push($confirmation['fields'], 
            array(
                'field' => '<form id="paymill_form" action="' . zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL') . '" method="post" style="display: none;"></form>'
            )
        );

        return $confirmation;
    }

    function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYMILL_CC_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    function install()
    {
        global $db;

        parent::install();

        include(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/paymillCc.php');

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                     " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_STATUS_TITLE) . "', '" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_STATUS_DESC) .
                     "', 'MODULE_PAYMENT_PAYMILL_CC_STATUS', 'True', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                     " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_FASTCHECKOUT_TITLE) . "', '" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_FASTCHECKOUT_DESC) .
                     "', 'MODULE_PAYMENT_PAYMILL_CC_FASTCHECKOUT', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                     " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_WEBHOOKS_TITLE) . "', '" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_WEBHOOKS_DESC) .
                     "', 'MODULE_PAYMENT_PAYMILL_CC_WEBHOOKS', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                     " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_SORT_ORDER_TITLE) . "', '" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_SORT_ORDER_DESC) .
                     "', 'MODULE_PAYMENT_PAYMILL_CC_SORT_ORDER', '0', '6', '0', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_AMEX_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_AMEX_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_AMEX', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_VISA_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_VISA_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_VISA', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_UNIONPAY_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_UNIONPAY_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_UNIONPAY', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_MASTERCARD_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_MASTERCARD_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_MASTERCARD', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_JCB_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_JCB_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_JCB', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_DISCOVER_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_DISCOVER_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_DISCOVER', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_DINERSCLUB_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_DINERSCLUB_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_DINERSCLUB', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_CARTEBLEUE_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_CARTEBLEUE_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_CARTEBLEUE', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_DANKORT_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_DANKORT_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_DANKORT', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_CARTASI_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_CARTASI_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_CARTASI', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_MAESTRO_TITLE) . "', '" .
                mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_MAESTRO_DESC) .
                "', 'MODULE_PAYMENT_PAYMILL_CC_MAESTRO', 'False', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                     " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_PRIVATEKEY_TITLE) . "', '" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_PRIVATEKEY_DESC) .
                     "', 'MODULE_PAYMENT_PAYMILL_CC_PRIVATEKEY', '0', '6', '0', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                     " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_PUBLICKEY_TITLE) . "', '" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_PUBLICKEY_DESC) .
                     "', 'MODULE_PAYMENT_PAYMILL_CC_PUBLICKEY', '0', '6', '0', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                     " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_ORDER_STATUS_ID_TITLE) . "', '" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_ORDER_STATUS_ID_DESC) .
                     "', 'MODULE_PAYMENT_PAYMILL_CC_ORDER_STATUS_ID', '0',  '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                     " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_LOGGING_TITLE) . "', '" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_LOGGING_DESC) .
                     "', 'MODULE_PAYMENT_PAYMILL_CC_LOGGING', 'False', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                     " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_TRANS_ORDER_STATUS_ID_TITLE) . "', '" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_TRANS_ORDER_STATUS_ID_DESC) .
                     "', 'MODULE_PAYMENT_PAYMILL_CC_TRANSACTION_ORDER_STATUS_ID', '" .
                     $this->getOrderStatusTransactionID() .
                     "', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION .
                     " (configuration_title, configuration_description, configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_ZONE_TITLE) . "', '" .
                     mysql_real_escape_string(MODULE_PAYMENT_PAYMILL_CC_ZONE_DESC) .
                     "', 'MODULE_PAYMENT_PAYMILL_CC_ZONE', '0', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    }

    function keys()
    {
        return array(
            'MODULE_PAYMENT_PAYMILL_CC_STATUS',
            'MODULE_PAYMENT_PAYMILL_CC_FASTCHECKOUT',
            'MODULE_PAYMENT_PAYMILL_CC_WEBHOOKS',
            'MODULE_PAYMENT_PAYMILL_CC_AMEX',
            'MODULE_PAYMENT_PAYMILL_CC_VISA',
            'MODULE_PAYMENT_PAYMILL_CC_UNIONPAY',
            'MODULE_PAYMENT_PAYMILL_CC_MASTERCARD',
            'MODULE_PAYMENT_PAYMILL_CC_JCB',
            'MODULE_PAYMENT_PAYMILL_CC_DISCOVER',
            'MODULE_PAYMENT_PAYMILL_CC_DINERSCLUB',
            'MODULE_PAYMENT_PAYMILL_CC_CARTEBLEUE',
            'MODULE_PAYMENT_PAYMILL_CC_DANKORT',
            'MODULE_PAYMENT_PAYMILL_CC_CARTASI',
            'MODULE_PAYMENT_PAYMILL_CC_MAESTRO',
            'MODULE_PAYMENT_PAYMILL_CC_PRIVATEKEY',
            'MODULE_PAYMENT_PAYMILL_CC_PUBLICKEY',
            'MODULE_PAYMENT_PAYMILL_CC_ORDER_STATUS_ID',
            'MODULE_PAYMENT_PAYMILL_CC_TRANSACTION_ORDER_STATUS_ID',
            'MODULE_PAYMENT_PAYMILL_CC_ZONE',
            'MODULE_PAYMENT_PAYMILL_CC_LOGGING',
            'MODULE_PAYMENT_PAYMILL_CC_SORT_ORDER'
        );
    }
}
?>
