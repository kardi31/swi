<?php

/**
 * MetatagTranslation
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Default_Service_MetatagTranslation extends MF_Service_ServiceAbstract {
    
    protected $metatagTranslationTable;
    
    public function init() {
        $this->metatagTranslationTable = Doctrine_Core::getTable('Default_Model_Doctrine_MetatagTranslation');
    }
    
    public function removeMetatagTranslation($metatag) {
        $metatag->delete();
    }
    
    public function getMetatagTranslation($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->metatagTranslationTable->findOneBy($field, $id, $hydrationMode);
    }
    
}

