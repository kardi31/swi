<?php

/**
 * Product_DataTables_Adapter_Producer
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_DataTables_Adapter_Producer extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        $q->andWhere('x.level > ?', 0);
        $q->addOrderBy('x.lft ASC');
        return $q;
    }
}

