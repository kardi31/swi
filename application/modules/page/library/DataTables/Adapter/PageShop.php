<?php

/**
 * Page_DataTables_Adapter_PageShop
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Page_DataTables_Adapter_PageShop extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        $q->addSelect('t.*');
        $q->leftJoin('x.Translation t');
        $q->addOrderBy('x.type DESC, x.id');
        return $q;
    }
}
