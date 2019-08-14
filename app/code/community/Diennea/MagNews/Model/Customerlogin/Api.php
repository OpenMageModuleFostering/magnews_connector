<?php

class Diennea_MagNews_Model_Customerlogin_Api extends Mage_Api_Model_Resource_Abstract {        
    public function lastloginforcustomer($customer_id) {        
        $loginbyuser = Mage::getModel('log/customer')->loadByCustomer($customer_id);                            
        $result = $loginbyuser->toArray();
        if (count($result) === 0) {
            return array("customer_id"=>$customer_id);
        } else {
            return $result;
        }
    }
}
