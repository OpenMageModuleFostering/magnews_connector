<?php

/**
 * Created by IntelliJ IDEA.
 * User: Carlo
 * Date: 12/04/14
 * Time: 19.29
 */
class Diennea_MagNews_Model_Globalqueries_Api extends Mage_Api_Model_Resource_Abstract {

    static $maxRes = 100;

    public function getbestsellerproduct($from, $to, $term = '', $catId = 0, $storeId = -1, $nres = 20) {
        $collection = Mage::getResourceModel('reports/product_sold_collection');
        $collection->setDateRange($from, $to);
        if ($catId != 0) {
            $category = new Mage_Catalog_Model_Category();
            $category->setId($catId);
            $collection->addCategoryFilter($category);
        }

        if ($nres > self::$maxRes)
            $nres = self::$maxRes;
        if ($nres <= 0)
            $nres = self::$maxRes;
        $collection->setPageSize($nres);

        if ($storeId > -1) {
            $adapter = $collection->getConnection();
            $categoryProductAliasName = $adapter->quoteIdentifier('category_product');
            $categoryAliasName = $adapter->quoteIdentifier('category');

            $categoryProductJoinCondition = array(
                $categoryProductAliasName . '.product_id = e.entity_id'
            );
            $categoryJoinCondition = array(
                $categoryAliasName . '.entity_id = ' . $categoryProductAliasName . '.category_id'
            );

            $select = $collection->getSelect();

            $select->joinInner(
                            array('category_product' => $collection->getTable('catalog/category_product')), implode(' AND ', $categoryProductJoinCondition), array())
                    ->joinInner(
                            array('category' => $collection->getTable('catalog/category')), implode(' AND ', $categoryJoinCondition), array());

            $catRootId = Mage::app()->getStore($storeId)->getRootCategoryId();
            $catSubquery = 'SELECT cat.entity_id FROM ' . Mage::getSingleton('core/resource')->getTableName('catalog/category') . ' cat '
                    . 'WHERE (cat.path = \'1/' . $catRootId . '\' OR cat.path LIKE \'1/' . $catRootId . '/%\')';
            $storeWhere = $categoryAliasName . '.entity_id IN (' . $catSubquery . ')';

            $select->where($storeWhere);
        }

        if (!empty($term)) {
            $productNameAttribute = Mage::getModel('eav/entity_attribute')->loadByCode('4', 'name');
            $collection->getSelect()->joinLeft(
                    array('cpev' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')), 'cpev.entity_id = e.entity_id', array('name' => 'value'));
            $collection->getSelect()->where('e.sku like \'%' . $term . '%\' OR (cpev.attribute_id=' . $productNameAttribute->getAttributeId() . ' AND cpev.value like \'%' . $term . '%\')');
        }

        $res = $collection->load()->toArray();
        return $res;
    }

    public function getmostprofitableproduct($from, $to, $term = '', $catId = 0, $storeId = -1, $nres = 20) {
        $collection = new Diennea_MagNews_Model_Custom_Reports_Products_Mostprofitable_Collection();
        $collection->setParameters($from, $to, $storeId);

        if ($catId != 0) {
            $category = new Mage_Catalog_Model_Category();
            $category->setId($catId);
            $collection->addCategoryFilter($category);
        }

       if (!empty($term)) {
            $productNameAttribute = Mage::getModel('eav/entity_attribute')->loadByCode('4', 'name');
            $collection->getSelect()->joinLeft(
                    array('cpev' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')), 'cpev.entity_id = e.entity_id', array('name' => 'value'));
            $collection->getSelect()->where('e.sku like \'%' . $term . '%\' OR (cpev.attribute_id=' . $productNameAttribute->getAttributeId() . ' AND cpev.value like \'%' . $term . '%\')');
        }

        if ($nres > self::$maxRes)
            $nres = self::$maxRes;
        if ($nres <= 0)
            $nres = self::$maxRes;

        $res = $collection->setPageSize($nres)->load()->toArray();
        foreach ($res as $key => $val) {
            foreach ($val as $k => $v) {
                if ($k == "ordered_qty") {
                    unset($res[$key][$k]);
                    $res[$key]['tot_profit'] = $v;
                }
            }
        }
        return $res;
    }

    public function getmostviewedproduct($from, $to, $term = '', $catId = 0, $storeId = -1, $nres = 20) {
        if ($nres > self::$maxRes)
            $nres = self::$maxRes;
        if ($nres <= 0)
            $nres = self::$maxRes;
        $collection = Mage::getResourceModel('reports/product_collection')->addAttributeToSelect('*')->addViewsCount($from, $to)
                ->setPageSize($nres);

        if ($catId != 0) {
            $category = new Mage_Catalog_Model_Category();
            $category->setId($catId);
            $collection->addCategoryFilter($category);
        }

        if ($storeId > -1) {
            $adapter = $collection->getConnection();
            $categoryProductAliasName = $adapter->quoteIdentifier('category_product');
            $categoryAliasName = $adapter->quoteIdentifier('category');

            $categoryProductJoinCondition = array(
                $categoryProductAliasName . '.product_id = e.entity_id'
            );
            $categoryJoinCondition = array(
                $categoryAliasName . '.entity_id = ' . $categoryProductAliasName . '.category_id'
            );

            $select = $collection->getSelect();

            $select->joinInner(
                            array('category_product' => $collection->getTable('catalog/category_product')), implode(' AND ', $categoryProductJoinCondition), array())
                    ->joinInner(
                            array('category' => $collection->getTable('catalog/category')), implode(' AND ', $categoryJoinCondition), array());

            $catRootId = Mage::app()->getStore($storeId)->getRootCategoryId();
            $catSubquery = 'SELECT cat.entity_id FROM ' . Mage::getSingleton('core/resource')->getTableName('catalog/category') . ' cat '
                    . 'WHERE (cat.path = \'1/' . $catRootId . '\' OR cat.path LIKE \'1/' . $catRootId . '/%\')';
            $storeWhere = $categoryAliasName . '.entity_id IN (' . $catSubquery . ')';

            $select->where($storeWhere);
        }

        if (!empty($term)) {
            $productNameAttribute = Mage::getModel('eav/entity_attribute')->loadByCode('4', 'name');
            $collection->getSelect()->joinLeft(
                    array('cpev' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')), 'cpev.entity_id = e.entity_id', array('name' => 'value'));
            $collection->getSelect()->where('e.sku like \'%' . $term . '%\' OR (cpev.attribute_id=' . $productNameAttribute->getAttributeId() . ' AND cpev.value like \'%' . $term . '%\')');
        }

        $ret = $collection->load()->toArray();
        return $ret;
    }

    public function getmostwishedproduct($from, $to, $term = '', $catId = 0, $storeId = -1, $nres = 20) {
        $collection = Mage::getResourceModel('reports/product_collection')->addAttributeToSelect('*');
        if ($nres > self::$maxRes)
            $nres = self::$maxRes;
        if ($nres <= 0)
            $nres = self::$maxRes;

        $collection = $this->addWishCount($collection, $from, $to)->setPageSize($nres);

        if ($catId != 0) {
            $category = new Mage_Catalog_Model_Category();
            $category->setId($catId);
            $collection->addCategoryFilter($category);
        }

        if ($storeId > -1) {
            $adapter = $collection->getConnection();
            $categoryProductAliasName = $adapter->quoteIdentifier('category_product');
            $categoryAliasName = $adapter->quoteIdentifier('category');

            $categoryProductJoinCondition = array(
                $categoryProductAliasName . '.product_id = e.entity_id'
            );
            $categoryJoinCondition = array(
                $categoryAliasName . '.entity_id = ' . $categoryProductAliasName . '.category_id'
            );

            $select = $collection->getSelect();

            $select->joinInner(
                            array('category_product' => $collection->getTable('catalog/category_product')), implode(' AND ', $categoryProductJoinCondition), array())
                    ->joinInner(
                            array('category' => $collection->getTable('catalog/category')), implode(' AND ', $categoryJoinCondition), array());

            $catRootId = Mage::app()->getStore($storeId)->getRootCategoryId();
            $catSubquery = 'SELECT cat.entity_id FROM ' . Mage::getSingleton('core/resource')->getTableName('catalog/category') . ' cat '
                    . 'WHERE (cat.path = \'1/' . $catRootId . '\' OR cat.path LIKE \'1/' . $catRootId . '/%\')';
            $storeWhere = $categoryAliasName . '.entity_id IN (' . $catSubquery . ')';

            $select->where($storeWhere);
        }

        if (!empty($term)) {
            $productNameAttribute = Mage::getModel('eav/entity_attribute')->loadByCode('4', 'name');
            $collection->getSelect()->joinLeft(
                    array('cpev' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')), 'cpev.entity_id = e.entity_id', array('name' => 'value'));
            $collection->getSelect()->where('e.sku like \'%' . $term . '%\' OR (cpev.attribute_id=' . $productNameAttribute->getAttributeId() . ' AND cpev.value like \'%' . $term . '%\')');
        }

        $ret = $collection->load()->toArray();
        return $ret;
    }

    public function getmostabandonedproduct($term = '', $catId = 0, $storeId = -1, $nres = 20) {
        $collection = new Diennea_MagNews_Model_Custom_Reports_Products_Mostabandoned_Collection();
        $collection->prepareAbandonedProductQuery($storeId);
        if ($catId != 0) {
            $category = new Mage_Catalog_Model_Category();
            $category->setId($catId);
            $collection->addCategoryFilter($category);
        }

        if (!empty($term)) {
            $productNameAttribute = Mage::getModel('eav/entity_attribute')->loadByCode('4', 'name');
            $collection->getSelect()->joinLeft(
                    array('cpev' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')), 'cpev.entity_id = e.entity_id', array('name' => 'value'));
            $collection->getSelect()->where('e.sku like \'%' . $term . '%\' OR (cpev.attribute_id=' . $productNameAttribute->getAttributeId() . ' AND cpev.value like \'%' . $term . '%\')');
        }

        if ($nres > self::$maxRes)
            $nres = self::$maxRes;
        if ($nres <= 0)
            $nres = self::$maxRes;

        $res = $collection->setPageSize($nres)->load()->toArray();
        foreach ($res as $key => $val) {
            foreach ($val as $k => $v) {
                if ($k == "ordered_qty") {
                    unset($res[$key][$k]);
                    $res[$key]['abandoned_qty'] = $v;
                }
            }
        }
        return $res;
    }

    protected function addWishCount($that, $from = '', $to = '') {
        /**
         * Getting event type id for catalog_product_view event
         */
        foreach (Mage::getModel('reports/event_type')->getCollection() as $eventType) {
            if ($eventType->getEventName() == 'wishlist_add_product') {
                $productWishEvent = (int) $eventType->getId();
                break;
            }
        }

        $that->getSelect()->reset()
                ->from(
                        array('report_table_wishes' => $that->getTable('reports/event')), array('wishes' => 'COUNT(report_table_wishes.event_id)'))
                ->join(array('e' => $that->getProductEntityTableName()), $that->getConnection()->quoteInto(
                                "e.entity_id = report_table_wishes.object_id AND e.entity_type_id = ?", $that->getProductEntityTypeId()))
                ->where('report_table_wishes.event_type_id = ?', $productWishEvent)
                ->group('e.entity_id')
                ->order('wishes ' . $that::SORT_ORDER_DESC)
                ->having('COUNT(report_table_wishes.event_id) > ?', 0);

        if ($from != '' && $to != '') {
            $that->getSelect()
                    ->where('logged_at >= ?', $from)
                    ->where('logged_at <= ?', $to);
        }

        //$that->_useAnalyticFunction = true;
        return $that;
    }

}
