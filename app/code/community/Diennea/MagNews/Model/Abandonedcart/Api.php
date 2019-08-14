<?php

class Diennea_MagNews_Model_Abandonedcart_Api extends Mage_Api_Model_Resource_Abstract {
    
    public function getlastbycustomer($customer_id,$options='') {

        $lastcart_total = 0;
        $lastcart_date = 0;
        $lastcart_present = 0;
        $quoteId = 0;
        $lastcart_productids = '';
        $lastcart_categoryids = '';

        $cartCollection = Mage::getResourceModel('reports/quote_collection');
        $cartCollection->prepareForAbandonedReport(array());
        $cartCollection->addFieldToFilter('customer_id', $customer_id);
        $cartCollection->load();

        foreach ($cartCollection as $lastCart) {
            $lastcart_date = $lastCart->updated_at;
            $lastcart_total = $lastCart->grand_total;
            $quoteId = $lastCart->getId();
            $lastcart_present = 1;
        }

        if ($lastcart_present === 1) {
            $quote = Mage::getModel("sales/quote");
            $quote->loadByIdWithoutStore($quoteId);
            $productids = array();
            $categoryIds = array();
            foreach ($quote->getAllItems() as $item) {
                $productId = $item->getProductId();
                $productids[] = $productId;
                if (strlen($options) === 0 || strpos($options, 'categories') !== false) {
                    $product = Mage::getModel('catalog/product')->load($productId);                
                    if ($product->getCategoryIds()) {
                        $categoryIds[] = implode(',', $product->getCategoryIds());
                    }
                }
            }
            $lastcart_productids = implode(',', $productids);
            $lastcart_categoryids = implode(',', $categoryIds);
        }

        $result = array(
            'total' => $lastcart_total,
            'ts' => $lastcart_date,
            'exist' => $lastcart_present,
            'customer_id' => $customer_id,
            'productids' => $lastcart_productids,
            'categoryids' => $lastcart_categoryids
        );
        return $result;
    }

}
