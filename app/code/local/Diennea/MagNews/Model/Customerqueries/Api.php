<?php
/**
 * Created by IntelliJ IDEA.
 * User: Carlo
 * Date: 12/04/14
 * Time: 19.29
 */

class Diennea_MagNews_Model_Customerqueries_Api extends Mage_Api_Model_Resource_Abstract {

    static $maxRes=100;

    public function getmostwishedproduct($customerId=null, $from=null, $to=null, $catId=0, $nres=20){
    	$collection = Mage::getResourceModel('reports/product_collection')->addAttributeToSelect('*');
        if ($nres>self::$maxRes) $nres=self::$maxRes;
        if ($nres<=0) $nres=self::$maxRes;
        $collection = $this->addWishCount($collection,$customerId,$from,$to)->setPageSize($nres);
        //error_log($collection->getSelectSql(true));
        if ($catId!=0) {
            $category=new Mage_Catalog_Model_Category();
            $category->setId($catId);
            $collection->addCategoryFilter($category);
        }
        $ret=$collection->load()->toArray();
        return $ret;
    }

    protected function addWishCount($that,$customerId=null, $from = '', $to = '')
    {
        /**
         * Getting event type id for catalog_product_view event
         */
        foreach (Mage::getModel('reports/event_type')->getCollection() as $eventType) {
            if ($eventType->getEventName() == 'wishlist_add_product') {
                $productWishEvent = (int)$eventType->getId();
                break;
            }
        }

        $that->getSelect()->reset()
            ->from(
                array('report_table_wishes' => $that->getTable('reports/event')),
                array('wishes' => 'COUNT(report_table_wishes.event_id)'))
            ->join(array('e' => $that->getProductEntityTableName()),
                $that->getConnection()->quoteInto(
                    "e.entity_id = report_table_wishes.object_id AND e.entity_type_id = ?",
                    $that->getProductEntityTypeId()))
            ->where('report_table_wishes.event_type_id = ?', $productWishEvent)
            ->where('report_table_wishes.subject_id = ?', $customerId)
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