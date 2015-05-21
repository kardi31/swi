<?php

/**
 * Default_Model_Location
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Service_Location extends MF_Service_ServiceAbstract {
    
    protected $provinceTable;
    protected $cityTable;
    
    public function init() {
        $this->provinceTable = Doctrine_Core::getTable('Default_Model_Doctrine_Province');
        $this->cityTable = Doctrine_Core::getTable('Default_Model_Doctrine_City');
        parent::init();
    }
    
    public function getProvinceSelectOptions() {
        $query = $this->provinceTable->getProvinceQuery();
        $provinces = $query->execute();
        $result = array();
        foreach($provinces as $province) {
            $result[$province->getId()] = $province->getName();
        }
        asort($result, SORT_LOCALE_STRING);
        return $result;
    }
    
    public function getCitySelectOptions($provinceId = null) {
        $query = $this->cityTable->getCityQuery();
        if(null != $provinceId) {
            $query->andWhere('c.province_id = ?', (int) $provinceId);
        }
        $query->orderBy('c.name ASC');
        $cities = $query->execute();
        $result = array();
        foreach($cities as $city) {
            $result[$city->getId()] = $city->getName();
        }
        asort($result, SORT_LOCALE_STRING);
        return $result;
    }
    
}

