<?php

class Diennea_MagNews_Model_Version_Api extends Mage_Api_Model_Resource_Abstract {

    public function getpluginversion() {
        $result = array('version'=>'2.0.3');
        return $result;
    }   
}
