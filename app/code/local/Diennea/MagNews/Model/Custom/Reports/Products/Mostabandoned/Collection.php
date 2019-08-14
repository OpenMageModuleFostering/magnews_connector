<?php
/**
 * Created by IntelliJ IDEA.
 * User: Carlo
 * Date: 14/04/14
 * Time: 19.27
 */
class Diennea_MagNews_Model_Custom_Reports_Products_Mostabandoned_Collection extends Mage_Reports_Model_Resource_Product_Collection
{

    public function prepareAbandonedProductQuery()
    {
        $this->_reset()
            ->addAttributeToSelect('*')
            ->addAbandonedProducts()
            ->setOrder('ordered_qty', self::SORT_ORDER_DESC);
        return $this;
    }

    public function addAbandonedProducts()
    {
        $adapter              = $this->getConnection();
        $compositeTypeIds     = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $orderTableAliasName  = $adapter->quoteIdentifier('abandoned');

        $orderJoinCondition   = array(
            $orderTableAliasName . '.entity_id=abandoned_items.quote_id',
            //$adapter->quoteInto("{$orderTableAliasName}.state <> ?", Mage_Sales_Model_Order::STATE_CANCELED),

        );

        $productJoinCondition = array(
            $adapter->quoteInto('(e.type_id NOT IN (?))', $compositeTypeIds),
            'e.entity_id = abandoned_items.product_id',
            $adapter->quoteInto('e.entity_type_id = ?', $this->getProductEntityTypeId())
        );


        $this->getSelect()->reset()
            ->from(
                array('abandoned_items' => $this->getTable('sales/quote_item')),
                array(
                    'ordered_qty' => 'SUM(abandoned_items.qty)'
                ))
            ->joinInner(
                array('abandoned' => $this->getTable('sales/quote')),
                implode(' AND ', $orderJoinCondition),
                array())
            ->joinLeft(
                array('e' => $this->getProductEntityTableName()),
                implode(' AND ', $productJoinCondition),
                array(
                    'entity_id' => 'abandoned_items.product_id',
                    'entity_type_id' => 'e.entity_type_id',
                    'attribute_set_id' => 'e.attribute_set_id',
                    'type_id' => 'e.type_id',
                    'sku' => 'e.sku',
                    'has_options' => 'e.has_options',
                    'required_options' => 'e.required_options',
                    'created_at' => 'e.created_at',
                    'updated_at' => 'e.updated_at'
                ))
            ->where('parent_item_id IS NULL')
            ->group('abandoned_items.product_id')
            ->having('SUM(abandoned_items.qty) > ?', 0);
            //->order('abandoned_item_qty', self::SORT_ORDER_DESC);
        return $this;
    }
}