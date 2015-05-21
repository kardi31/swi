<?php

/**
 * Banner_DataTables_Adapter_BannerRight
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Banner_DataTables_Adapter_BannerRight extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('b');
        $q->select('b.*');
        $q->andWhere('b.level > ?', 0);
        $q->addOrderBy('b.lft ASC');
        return $q;
    }
}

