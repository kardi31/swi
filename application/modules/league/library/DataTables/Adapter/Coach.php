<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class League_DataTables_Adapter_Coach extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('c');
        $q->addSelect('c.*');
        $q->addSelect('t.name');
        $q->leftJoin('c.Team t');
        
        return $q;
    }
}
