<?php

class RMethods extends Remonline {

    private $getParams;

    public function __construct($apiKey) {
        parent::__construct($apiKey);
    }

    private function unsetParams() {
        if(!empty($this->getParams)) unset($this->getParams);
    }
    
    public function convertDateToTimestamp($time) {
        $ts = strtotime($time);
        return intval($ts."000");
    }

    public function preparedTimestamp($interval = 0) {
        $preventMinute = time() - $interval;
        $preventMinute .= '000';
        $preventMinute = intval($preventMinute);
        return $preventMinute;
    }

    public function prepareOrderData($order, $bxStage) {
        $orderId = $order->id;
        $dealPreparedParams = [];
        $dealPreparedParams[$order->id] = [
            "manager" => $this->getUser($order->manager_id, true),
            "engineer" => $this->getUser($order->engineer_id, true),
            "client_id" => $order->client->id
        ];
        
        $dealPreparedParams[$orderId]["deal"] = [
            "CATEGORY_ID" => 1,
            "TITLE" => $order->model,
            "UF_CRM_1581460699249" => $order->custom_fields->f560573,
            "UF_CRM_REM_ORDER_ID" => $orderId,
            "UF_CRM_REM_LABEL_ID" => $order->id_label,
            "OPPORTUNITY" => $order->price,
            "UF_CRM_1581461063088" => $order->serial,
            "UF_CRM_1581461116" => $order->appearance,
            "UF_CRM_1581461076591" => $order->malfunction,
            "UF_CRM_1581461285" => $order->manager_notes,
            "STAGE_ID" => $bxStage
        ];
        $clientName = $order->client->name;
        $dealPreparedParams[$orderId]["contact"] = [
            "NAME" => $clientName,
            "PHONE" => [
                [
                    "VALUE" => $order->client->phone[0]
                ]
            ]
        ];

        return $dealPreparedParams;

    }

    

    public function getSales() {
        return parent::call("retail/sales/")->data;
    }

    public function getClients($params = []) {
        $this->unsetParams();
        $this->getParams = [
            'type' => 'get',
            'query' => true,
            'params' => $params
        ];
        $find = parent::call('clients/', $this->getParams)->data;
        if(count($find) === 1) {
            return $find[0];
        } else {
            return $find;
        }
    }

    public function getClient($id) {
        $clients = $this->getClients();
        foreach($clients as $client) {
            if($client->id === $id) {
                return $client;
            }
        }
    }

    public function getOrders($params = []) {
        if(!empty($params)) {
            $this->unsetParams();;
            $this->getParams = [
                'type' => 'get',
                'query' => true,
                'params' => $params
            ];
            $result = parent::call('order/', $this->getParams)->data;
        } else {
            $result = parent::call('order/')->data;
        }
        return $result;
    }

    public function getOrder($id) {
        return $this->getOrders([
            "ids[]" => $id
        ])[0];
    }

    public function getBranches() {
        return parent::call('branches/')->data;
    }

    public function getOrderTypes() {
        return parent::call('order/types/')->data;
    }

    public function getOrderTypeIdByName($name) {
        $orderTypes = $this->getOrderTypes();
        foreach($orderTypes as $type) {
            if($type->name === $name) {
                return $type->id;
            }
        }
    }

    public function getCustomFields($entity) {
        return parent::call($entity."/custom-fields/");
    }

    public function getUserList() {
        $employees = [];
        $users = parent::call('employees/')->data;
        foreach($users as $user) {
            if($user->deleted !== true) {
                $employees[] = $user;
            }
        }
        return $employees;
    }

    public function findUserByName($name, $id = false) {
        $users = $this->getUserList();
        foreach($users as $user) {
            $userFullName = $user->first_name." ".$user->last_name;
            if($id !== false) {
                return $user->id;
            }
            if($userFullName === $name) {
                return $user;
            }
        }
    }

    public function getUser($id, $onlyName = false) {
        if($id < 1) return (object)["error" => "user ID is invalid"];

        $users = $this->getUserList();
        $result = [];
        foreach($users as $user) {
            if($user->id === $id) {
                $result = $user;
            }
        }

        if($onlyName === true) {
            return $result->first_name." ".$result->last_name;
        } else {
            return (object)$result;
        }

        
    }

    public function findSourceByName($name) {
        $sourceList = $this->getSources();
        foreach($sourceList as $src) {
            if($src->title === $name) {
                return $src;
            }
        }
    }

    public function getSources() {
        return parent::call('clients/marketing-sources/')->data;
    }

    public function getStatuses($group = 0) {
        $statusList = parent::call('statuses/')->data;
        $statusListForReturn = [];
        if($group > 0) {
            foreach($statusList as $status) {
                if($status->group === $group) {
                    $statusListForReturn[] = $status;
                }
            }
        } else {
            $statusListForReturn[] = $statusList;
        }
        return $statusListForReturn;
    }

    public function changeOrderStatus($orderId, $statusId) {
        $this->unsetParams();
        $this->getParams = [
            "type" => "post",
            "query" => true,
            "params" => [
                "order_id" => $orderId,
                "status_id" => $statusId
            ]
        ];
        return parent::call("order/status/", $this->getParams);
    }

    public function createOrder($params) {
        $this->unsetParams();
        $this->getParams = [
            'type' => 'post',
            'query' => true,
            'params' => $params
        ];
        return parent::call('order/', $this->getParams);
    }

    public function createClient($params) {
        $this->unsetParams();
        $this->getParams = [
            'type' => 'post',
            'query' => true,
            'params' => $params
        ];
        return parent::call('clients/', $this->getParams);
    }

    public function updateClient($params) {
        $this->unsetParams();
        if(!array_key_exists("id", $params)) return (object)["error" => "ID isn't set"];
        $this->getParams = [
            "type" => "put",
            "query" => true,
            "params" => $params
        ];
        return parent::call("clients/", $this->getParams);
    }

    public function setOrderStatus(int $statusId) {
        $this->unsetParams();
        $this->getParams = [
            'type' => 'post',
            'query' => true,
            'params' => [
                'status_id' => $statusId
            ]
        ];
        return parent::call('order/status/', $this->getParams);
    }

    
}