<?php
require_once('abstract/WebHooksAbstract.php');
class WebHooks extends WebHooksAbstract
{
    /**
     * Saves the web-hook into the web-hook table
     *
     * @param String $id
     * @param String $url
     * @param String $mode
     * @param String $type
     * @param String $created_at
     *
     * @throws Exception
     * @return void
     */
    function saveWebhook($id, $url, $mode, $type, $created_at)
    {

        global $db;
        $sql = "REPLACE INTO `".DB_PREFIX . "pi_paymill_webhooks` (`id`, `url`, `mode`, `type`, `created_at`) VALUES('".$id."','".$url."','".$mode."','".$type."','".$created_at."')";
        $success = $db->Execute($sql);
        if(!$success){
            throw new Exception("Webhook data could not be saved.");
        }
    }

    /**
     * Removes the web-hook from the web-hook table
     *
     * @param String $id
     *
     * @throws Exception
     * @return array
     */
    function removeWebhook($id)
    {
        global $db;
        $sql = "DELETE FROM `".DB_PREFIX . "pi_paymill_webhooks` WHERE `id` = '".$id."'";
        $success = $db->Execute($sql);
        if(!$success){
            throw new Exception("Webhook data could not be deleted.");
        }
    }

    /**
     * Returns the ids of all web-hooks from the web-hook table
     *
     * @param String $type
     *
     * @return array
     */
    function loadAllWebHooks($type)
    {
        global $db;
        $sql = "SELECT id FROM `".DB_PREFIX . "pi_paymill_webhooks`  WHERE type = '$type'";
        $store = $db->Execute($sql);
        $result = array();

        while (!$store->EOF) {
            $row = $store->fields;
            $result[] = $row['id'];
            $store->MoveNext();
        }

        return $result;

    }

    /**
     * Required the Libs WebHooks class
     *
     * @return void
     */
    function requireWebhooks()
    {
        require_once('lib/Services/Paymill/Webhooks.php');
    }

    /**
     * Returns the list of events to be created
     *
     * @return array
     */
    function getEventList()
    {
        $eventList = array(
            zen_href_link('../WebHookListener.php', '&notification_action=chargeback&type='.$this->_request['type'], 'SSL', false, false) => 'chargeback.executed',
            zen_href_link('../WebHookListener.php', '&notification_action=refund&type='.$this->_request['type'], 'SSL', false, false) => 'refund.succeeded'
        );

        return $eventList;
    }

    /**
     * Returns the state of the webhook option
     * @param $type
     * @return boolean
     */
    function getWebhookState($type)
    {
        return ((constant('MODULE_PAYMENT_PAYMILL_'.$type.'_WEBHOOKS') == 'True') ? true : false);
    }

    /**
     * Changes the Status of the current order (based on the notification)
     *
     * @return void
     */
    function updateOrderStatus()
    {
        global $db;
        $description = $this->_request['event_resource']['transaction']['description'];
        $eventType = $this->_request['action'];
        $orderId = $this->getOrderIdFromDescription($description);
        $orderStatus = $this->getOrderStatusId($eventType);
        if ($orderStatus && isset($orderId) &&$orderId != 0) {
            $db->Execute("UPDATE " . TABLE_ORDERS . " SET orders_status='" . $orderStatus . "' WHERE orders_id='" . $orderId . "'");
        }

        $this->successAction();
    }

    /**
     * @param $statusName
     *
     * @return mixed
     */
    function getOrderStatusId($statusName)
    {
        global $db;
        $check_query = $db->Execute("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Paymill [".$statusName."]' limit 1");
        $check = $check_query->fields;
        $status_id = $check['orders_status_id'];
        return $status_id;
    }

    /**
     * Overwrites the abstract classes setEventParameters Method to rename the action GET Parameter to avoid ZenCart Issues
     *
     * @param $request
     *
     * @throws Exception
     */
    public function setEventParameters($request)
    {
        if (!array_key_exists('notification_action', $request)) {
            throw new Exception('Action not defined!');
        }

        $request['action'] = $request['notification_action'];

        parent::setEventParameters($request);

    }

    /**
     * Creates the web-hooks for the status update
     */
    public function registerAction()
    {
        $this->requireWebhooks();
        $webHooks = new Services_Paymill_Webhooks($this->_privateKey, $this->_apiUrl);
        $eventList = $this->getEventList();
        $data = array();
        foreach ($eventList as $url => $eventName) {
            $parameters = array(
                'url'         => $url,
                'event_types' => array($eventName)
            );
            $hook = $webHooks->create($parameters);
            $this->saveWebhook($hook['id'],$hook['url'], $hook['livemode']? 'live' : 'test', $this->_request['type'], $hook['created_at']);
            $data[] = $hook;
        }
    }
}