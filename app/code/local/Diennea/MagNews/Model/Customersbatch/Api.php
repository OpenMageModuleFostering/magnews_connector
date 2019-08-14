<?php

class Diennea_MagNews_Model_Customersbatch_Api extends Mage_Api_Model_Resource_Abstract {

    public function getcustomersidpage($pageNum, $pageSize, $storeId = -1) {
        $result = array();
        $all = Mage::getModel('customer/customer')->getCollection();
        
        if ($storeId > -1) {
            $all->addFieldToFilter("store_id", $storeId);
        }
        
        $all->addAttributeToSort('entity_id', 'asc')
                ->setPage($pageNum, $pageSize);
        
        foreach ($all as $customer) {
            $result[] = $customer["entity_id"];
        }
        return $result;
    }

    public function countcustomers($storeId = -1) {
        $all = Mage::getModel('customer/customer')->getCollection();
        
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

    public function getdetailedinfo($customerId, $options = '') {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $allOrdersTotalAmount = 0;
        $allOrdersDateTimes = array();
        $allOrdersTotals = array();
        $allOrdersIds = array();
        $allProductsIds = array();
        $hasorders = false;
        $customer_productids = '';
        $customer_lastorderdate = '';
        $customer_lastordertotal = 0;
        $customer_allorderstotal = 0;
        $customer_company = '';
        $customer_country = '';
        $customer_city = '';
        $customer_region = '';
        $customer_postcode = '';
        $customer_address = '';
        $customer_fax = '';
        $customer_phone = '';
        $customer_lastorder_productsids = '';
        $customer_lastorder_categoryids = '';
        $customer_firstname = $customer->getData('firstname');
        $customer_lastname = $customer->getData('lastname');
        $customer_email = $customer->getData('email');
        $customer_createdat = $customer->getData('created_at');
        $customer_countorders = 0;

        if (strlen($options) === 0 || strpos($options, 'address') !== false) {
            // address
            $customerAddressId = $customer->getDefaultBilling();
            if ($customerAddressId) {
                $address = Mage::getModel('customer/address')->load($customerAddressId);
                $customer_company = $address->getData('company');
                $customer_country = $address->getCountry();
                $customer_city = $address->getData('city');
                $customer_region = $address->getData('region');
                $customer_postcode = $address->getData('postcode');
                $customer_address = $address->getData('street');
                $customer_fax = $address->getData('fax');
                $customer_phone = $address->getData('telephone');
            }
        }
        if (strlen($options) === 0 || strpos($options, 'order') !== false) {
            // last order
            $orders = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addAttributeToFilter('customer_id', $customerId);
            foreach ($orders as $order) {
                if (in_array($order->getStatus(), array("closed", "complete", "processing"))) {
                    $hasorders = true;
                    $currentOrderTotal_number = floatval($order->getGrandTotal());
                    $allOrdersTotalAmount += $currentOrderTotal_number;
                    $currentOrderCreationDate = $order->getCreatedAt();
                    $currentOrderTotal = $currentOrderTotal_number;
                    $currentOrderId = $order->getIncrementId();
                    $allOrdersTotals[$currentOrderId] = $currentOrderTotal;
                    $allOrdersDateTimes[$currentOrderId] = $currentOrderCreationDate;
                    $allOrdersIds[$currentOrderId] = $currentOrderId;
                    $customer_countorders = $customer_countorders + 1;
                    $items = $order->getAllItems();
                    foreach ($items as $item) {
                        $allProductsIds[] = $item->getProductId();
                    }
                }
            }
            if ($hasorders) {
                ksort($allOrdersDateTimes);
                ksort($allOrdersTotals);
                ksort($allOrdersIds);
                $customer_productids = implode(',', $allProductsIds);
                $customer_lastorderdate = end($allOrdersDateTimes);
                $customer_lastordertotal = end($allOrdersTotals);
                $customer_allorderstotal = $allOrdersTotalAmount;
                $lastOrder = Mage::getModel('sales/order')->loadByIncrementId(end($allOrdersIds));
                $items = $lastOrder->getAllItems();
                $productIds = array();
                $categoryIds = array();
                foreach ($items as $item) {
                    $productId = $item->getProductId();
                    $productIds[] = $productId;
                    $product = Mage::getModel('catalog/product')->load($productId);
                    if ($product->getCategoryIds()) {
                        $categoryIds[] = implode(',', $product->getCategoryIds());
                    }
                }
                $customer_lastorder_productsids = implode(',', $productIds);
                $customer_lastorder_categoryids = implode(',', $categoryIds);
            }
        }

        return array(
            'productids' => $customer_productids,
            'lastorder_categoryids' => $customer_lastorder_categoryids,
            'lastorder_productids' => $customer_lastorder_productsids,
            'lastorder_date' => $customer_lastorderdate,
            'lastorder_total' => $customer_lastordertotal,
            'allorders_total' => $customer_allorderstotal,
            'allorders_count' => $customer_countorders,
            'company' => $customer_company,
            'country' => $customer_country,
            'city' => $customer_city,
            'region' => $customer_region,
            'postcode' => $customer_postcode,
            'address' => $customer_address,
            'fax' => $customer_fax,
            'phone' => $customer_phone,
            'firstname' => $customer_firstname,
            'lastname' => $customer_lastname,
            'email' => $customer_email,
            'customer_id' => $customerId,
            'created_at' => $customer_createdat
        );
    }

}
