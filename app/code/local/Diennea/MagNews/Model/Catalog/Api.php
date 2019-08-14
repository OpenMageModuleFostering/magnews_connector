<?php

/**
 * Estensione metodi sul catalogo.
 * 
 * @author Andrea Mallegni
 * @version $Id$
 */
class Diennea_MagNews_Model_Catalog_Api extends Mage_Api_Model_Resource_Abstract {

    /**
     * Restituisce il min e il max id della tabella dei prodotti.
     * 
     * @return int max id prodotto
     */
    public function getminandmaxproductid() {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT MIN(entity_id) AS min_id, MAX(entity_id) as max_id FROM ' . $resource->getTableName('catalog/product');
        return $readConnection->fetchAll($query);
    }

    /**
     * Restituisce i prodotti appartenenti a categorie su cui lo store fornito 
     * ha visibilità.
     * 
     * @param type $storeId
     * @param type $filters
     * @return type
     */
    public function getproductsbystore($storeId, $filters) {
        if (empty($storeId) || $storeId < 0) {
            die('YOU MUST SUPPLY A STORE ID');
        }

        $resource = Mage::getSingleton('core/resource');

        $idFrom = $filters["product_id"]["from"];
        $idTo = $filters["product_id"]["to"];

        $query = 'SELECT DISTINCT p.entity_id AS product_id '
                . 'FROM ' . $resource->getTableName('catalog/product') . ' p '
                . 'INNER JOIN ' . $resource->getTableName('catalog/category_product') . ' cp ON p.entity_id=cp.product_id '
                . 'INNER JOIN ' . $resource->getTableName('catalog/category') . ' c ON cp.category_id=c.entity_id '
                . 'WHERE 1=1';

        if (!empty($idFrom)) {
            $query .= ' AND p.entity_id >= ' . $idFrom;
        }
        if (!empty($idTo)) {
            $query .= ' AND p.entity_id <= ' . $idTo;
        }

        $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
        $catSubquery = 'SELECT cat.entity_id FROM ' . $resource->getTableName('catalog/category') . ' cat '
                . 'WHERE (cat.path = \'1/' . $rootId . '\' OR cat.path LIKE \'1/' . $rootId . '/%\')';
        $query .= ' AND c.entity_id IN (' . $catSubquery . ') '
                . 'GROUP BY p.entity_id';

        $value = $resource->getConnection('core_read')->query($query);
        $rows = $value->fetchAll();

        return $rows;
    }

    /**
     * Restituisce l'id dei prodotti figli per il prodotto configurabile fornito.
     * 
     * @param type $parentProductId
     * @return type
     */
    public function getproductrelation($parentProductId) {
        $resource = Mage::getSingleton('core/resource');
        $query = 'SELECT child_id FROM ' . $resource->getTableName('catalog/product_relation') . ' WHERE parent_id = ' . $parentProductId;

        $value = $resource->getConnection('core_read')->query($query);
        $rows = $value->fetchAll();

        return $rows;
    }

}
