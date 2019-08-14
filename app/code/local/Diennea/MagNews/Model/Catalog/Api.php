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

}
