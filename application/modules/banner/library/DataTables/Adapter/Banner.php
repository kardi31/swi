<?php

/**
 * Banner_DataTables_Adapter_BannerSerwis10
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Banner_DataTables_Adapter_Banner extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('p');
        $q->select('p.*');
        $q->addSelect('pt.*');
        $q->leftJoin('p.Translation pt');
        return $q;
    }
}

