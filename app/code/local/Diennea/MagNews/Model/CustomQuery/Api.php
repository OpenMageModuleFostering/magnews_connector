<?php

class Diennea_MagNews_Model_CustomQuery_Api extends Mage_Api_Model_Resource_Abstract {

    const TABLE_PREFIX_PLACEHOLDER = '__TABLE_PREFIX__';

    private function replaceTablePrefix($query) {
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $tablePrefix = "{$tablePrefix}";

        return str_replace(self::TABLE_PREFIX_PLACEHOLDER, $tablePrefix, $query);
    }

    public function getbaseconfig() {
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $defaultCartUrl = Mage::helper('checkout/cart')->getCartUrl();

        return array(
            'ok' => true,
            'defaultCartUrl' => $defaultCartUrl,
            'tablePrefix' => "{$tablePrefix}"
        );
    }

    public function select($query) {
        $query = str_replace("&lt;", "<", $query);
        $query = $this->replaceTablePrefix($query);

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        return $readConnection->fetchAll($query);
    }

    public function write($query) {
        $query = str_replace("&lt;", "<", $query);
        $query = $this->replaceTablePrefix($query);

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        $queryExec = $writeConnection->query($query);

        $res = array(
            'ok' => true,
            'count' => $queryExec->rowCount()
        );
        return $res;
    }

}
