<?php

/**
 * Article
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class News_DataTables_Adapter_Article extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        $q->addSelect('xt.*');
        $q->addSelect('c.*');
        $q->addSelect('ct.*');
        $q->leftJoin('x.Translation xt');
        $q->leftJoin('x.Category c');
        $q->leftJoin('c.Translation ct');
        return $q;
    }
}

