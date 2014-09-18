<?php
require_once('abstract/FastCheckoutAbstract.php');
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/Clients.php');
require_once(DIR_FS_CATALOG . 'ext/modules/payment/paymill/lib/Services/Paymill/Payments.php');
class FastCheckout extends FastCheckoutAbstract
{
    /**
     * Executes sql query
     *
     * @param $sql
     *
     * @return resource
     */
    function dbQuery($sql)
    {
        global $db;
        $success = $db->Execute($sql);
        $success = $success === true;
        return $success;
    }

    /**
     * Executes sql statements returning an array
     * @param $sql
     *
     * @return array|bool|mixed
     */
    function dbFetchArray($sql)
    {
        global $db;
        $data = $db->Execute($sql);
        if($data->fields === null){
            return array();
        }
        return $data->fields;

    }

    /**
     * Returns the name of the Fast Checkout Table as a string
     * @return string
     */
    function getFastCheckoutTableName()
    {
        return DB_PREFIX . "pi_paymill_fastcheckout";
    }
}
