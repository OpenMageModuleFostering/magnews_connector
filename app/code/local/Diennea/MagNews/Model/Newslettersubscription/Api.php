<?php

class Diennea_MagNews_Model_Newslettersubscription_Api extends Mage_Api_Model_Resource_Abstract {

    public function getnoncustomerspage($pageNum, $pageSize, $storeId = -1) {
        $result = array();
        $subscriptions = Mage::getModel('newsletter/subscriber')
                ->getCollection()
                ->addFieldToFilter("customer_id", 0);
        
        if ($storeId > -1) {
            $subscriptions->addFieldToFilter("store_id", $storeId);
        }
        
        $subscriptions->getSelect()->limit($pageSize, (($pageNum - 1) * $pageSize));
        $subscriptions->load(false, true);
        foreach ($subscriptions as $subscriber) {
            $result[] = $subscriber->toArray();
        }
        return $result;
    }

    public function getallnoncustomers($storeId = -1) {
        $result = array();
        
        $subscriptions = Mage::getModel('newsletter/subscriber')->getCollection();
        $subscriptions->addFieldToFilter("customer_id", 0);
        
        if ($storeId > -1) {
            $subscriptions->addFieldToFilter("store_id", $storeId);
        }
        
        $subscriptions->load();
        foreach ($subscriptions as $subscriber) {
            $result[] = $subscriber->toArray();
        }
        return $result;
    }

    public function countnoncustomers($storeId = -1) {
        $all = Mage::getModel('newsletter/subscriber')->getCollection();
        $all->addFieldToFilter("customer_id", 0);
        
        if ($storeId > -1) {
            $all->addFieldToFilter("store_id", $storeId);
        }
        
        $all->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns('COUNT(*) AS entitycount');
        $stmt = $all->getSelect()->query();
        $result = $stmt->fetchAll();
        $first = current($result);
        $count = $first['entitycount'];
        return $count;
    }

    public function customersubscription($customer_id) {
        $subscriptions = Mage::getModel('newsletter/subscriber')
                ->getCollection();
        $subscriptions->addFieldToFilter("customer_id", $customer_id);
        $subscriptions->load();
        foreach ($subscriptions as $subscriber) {
            return $subscriber->toArray();
        }
        return array("subscriber_id" => "0", "customer_id" => $customer_id);
    }

    public function subscribe($email) {
        $subscriptions = Mage::getModel('newsletter/subscriber');
        // this method already looks for a customer and uses it if it found
        $subscriptions->subscribe($email);
        return $subscriptions->toArray();
    }
    public function unsubscribe($email) {
        $subscriptions = Mage::getModel('newsletter/subscriber');        
        $subscriptions->loadByEmail($email);
        $subscriptions->unsubscribe();
        return $subscriptions->toArray();
    }

    public function getbyemail($email) {
        $subscriptions = Mage::getModel('newsletter/subscriber');
        $subscriptions->loadByEmail($email);
        return $subscriptions->toArray();
    }

}
