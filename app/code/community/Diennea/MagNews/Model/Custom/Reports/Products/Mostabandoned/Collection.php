<?php

/**
 * Created by IntelliJ IDEA.
 * User: Carlo
 * Date: 14/04/14
 * Time: 19.27
 */
class Diennea_MagNews_Model_Custom_Reports_Products_Mostabandoned_Collection extends Mage_Reports_Model_Resource_Product_Collection {

    public function prepareAbandonedProductQuery($storeId = -1) {
        $this->_reset()
                ->addAttributeToSelect('*')
                ->addAbandonedProducts($storeId)
                ->setOrder('ordered_qty', self::SORT_ORDER_DESC);
        return $this;
    }

    public function addAbandonedProducts($storeId = -1) {
        $adapter = $this->getConnection();
        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $orderTableAliasName = $adapter->quoteIdentifier('abandoned');
        $categoryProductAliasName = $adapter->quoteIdentifier('category_product');
        $categoryAliasName = $adapter->quoteIdentifier('category');

        $orderJoinCondition = array(
            $orderTableAliasName . '.entity_id=abandoned_items.quote_id',
                //$adapter->quoteInto("{$orderTableAliasName}.state <> ?", Mage_Sales_Model_Order::STATE_CANCELED),
        );

        $productJoinCondition = array(
            $adapter->quoteInto('(e.type_id NOT IN (?))', $compositeTypeIds),
            'e.entity_id = abandoned_items.product_id',
            $adapter->quoteInto('e.entity_type_id = ?', $this->getProductEntityTypeId())
        );

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
                        array('abandoned_items' => $this->getTable('sales/quote_item')), array(
                    'ordered_qty' => 'SUM(abandoned_items.qty)'
                ))
                ->joinInner(
                        array('abandoned' => $this->getTable('sales/quote')), implode(' AND ', $orderJoinCondition), array())
                ->joinLeft(
                        array('e' => $this->getProductEntityTableName()), implode(' AND ', $productJoinCondition), array(
                    'entity_id' => 'abandoned_items.product_id',
                    'entity_type_id' => 'e.entity_type_id',
                    'attribute_set_id' => 'e.attribute_set_id',
                    'type_id' => 'e.type_id',
                    'sku' => 'e.sku',
                    'has_options' => 'e.has_options',
                    'required_options' => 'e.required_options',
                    'created_at' => 'e.created_at',
                    'updated_at' => 'e.updated_at'
        ));

        if ($storeId > -1) {
            $select->joinInner(
                            array('category_product' => $this->getTable('catalog/category_product')), implode(' AND ', $categoryProductJoinCondition), array())
                    ->joinInner(
                            array('category' => $this->getTable('catalog/category')), implode(' AND ', $categoryJoinCondition), array());
        }

        $select->where('parent_item_id IS NULL' . $storeWhere)
                ->group('abandoned_items.product_id')
                ->having('SUM(abandoned_items.qty) > ?', 0);
        //->order('abandoned_item_qty', self::SORT_ORDER_DESC);
        return $this;
    }

}
