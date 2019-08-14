<?php

/**
 * Created by IntelliJ IDEA.
 * User: Carlo
 * Date: 12/04/14
 * Time: 19.29
 */
class Diennea_MagNews_Model_Store_Api extends Mage_Api_Model_Resource_Abstract {

    /**
     * Restituisce la tabella dei tassi di conversione da una valuta ad un'altra.
     * 
     * @return type
     */
    public function getcurrencyrates() {
        $baseCurrencyCode = Mage::app()->getBaseCurrencyCode();
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $currencyRates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
        return $currencyRates;
    }

    /**
     * Restituisce la valuta di default per lo store richiesto o quella di 
     * default del website se niente è stato trovato.
     * 
     * @param type $storeid
     * @return type
     */
    public function getstoredefaultcurrency($storeid = 0) {
        return Mage::app()->getStore($storeid)->getCurrentCurrencyCode();
    }

}
