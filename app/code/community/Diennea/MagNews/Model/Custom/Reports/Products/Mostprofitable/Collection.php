<?php

/**
 * Created by IntelliJ IDEA.
 * User: Carlo
 * Date: 13/04/14
 * Time: 20.29
 */
class Diennea_MagNews_Model_Custom_Reports_Products_Mostprofitable_Collection extends Mage_Reports_Model_Resource_Product_Collection {

    public function setParameters($from, $to, $storeId) {
        $this->_reset()
                ->addAttributeToSelect('*')
                ->addTotProfit($from, $to, $storeId)
                ->setOrder('ordered_qty', self::SORT_ORDER_DESC);
        return $this;
    }

    public function addTotProfit($from = '', $to = '', $storeId = -1) {
        $adapter = $this->getConnection();
        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $orderTableAliasName = $adapter->quoteIdentifier('order');
        $categoryProductAliasName = $adapter->quoteIdentifier('category_product');
        $categoryAliasName = $adapter->quoteIdentifier('category');

        $orderJoinCondition = array(
            $orderTableAliasName . '.entity_id = order_items.order_id',
            $adapter->quoteInto("{$orderTableAliasName}.state <> ?", Mage_Sales_Model_Order::STATE_CANCELED),
        );

        $productJoinCondition = array(
            $adapter->quoteInto('(e.type_id NOT IN (?))', $compositeTypeIds),
            'e.entity_id = order_items.product_id',
            $adapter->quoteInto('e.entity_type_id = ?', $this->getProductEntityTypeId())
        );

        if ($from != '' && $to != '') {
            $fieldName = $orderTableAliasName . '.created_at';
            $orderJoinCondition[] = $this->_prepareBetweenSql($fieldName, $from, $to);
        }

        $storeWhere = '';
        if ($storeId > -1) {
            $categoryProductJoinCondition = array(
                $categoryProductAliasName . '.product_id = e.entity_id'
            );
            $categoryJoinCondition = array(
                $categoryAliasName . '.entity_id = ' . $categoryProductAliasName . '.category_id'
            );

            $catRootId = Mage::app()->getStore($storeId)->getRootCategoryId();
            $catSubquery = 'SELECT cat.entity_id FROM ' . Mage::getSingleton('core/resource')->getTableName('catalog/category') . ' cat '
                    . 'WHERE (cat.path = \'1/' . $catRootId . '\' OR cat.path LIKE \'1/' . $catRootId . '/%\')';
            $storeWhere = ' AND ' . $categoryAliasName . '.entity_id IN (' . $catSubquery . ')';
        }

        $select = $this->getSelect();
        $select->reset()
                ->from(
                        array('order_items' => $this->getTable('sales/order_item')), array(
                    'ordered_qty' => 'SUM(order_items.qty_ordered*order_items.price)',
                    'order_items_name' => 'order_items.name'
                ))
                ->joinInner(
                        array('order' => $this->getTable('sales/order')), implode(' AND ', $orderJoinCondition), array());

        $select->joinLeft(
                        array('e' => $this->getProductEntityTableName()), implode(' AND ', $productJoinCondition), array(
                    'entity_id' => 'order_items.product_id',
                    'entity_type_id' => 'e.entity_type_id',
                    'attribute_set_id' => 'e.attribute_set_id',
                    'type_id' => 'e.type_id',
                    'sku' => 'e.sku',
                    'has_options' => 'e.has_options',
                    'required_options' => 'e.required_options',
                    'created_at' => 'e.created_at',
                    'updated_at' => 'e.updated_at'
                ))
                ->where('parent_item_id IS NULL' . $storeWhere)
                ->group('order_items.product_id')
                ->having('SUM(order_items.qty_ordered*order_items.price) > ?', 0);
        
        if ($storeId > -1) {
            $select->joinInner(
                            array('category_product' => $this->getTable('catalog/category_product')), implode(' AND ', $categoryProductJoinCondition), array())
                    ->joinInner(
                            array('category' => $this->getTable('catalog/category')), implode(' AND ', $categoryJoinCondition), array());
        }
        
        return $this;
    }

}
