<?php

/**
 * User_DataTables_Adapter_Admin
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_DataTables_Adapter_Admin extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = parent::getBaseQuery();
        $q->andWhere('x.role = ?', 'admin')
                ->addSelect('x.*')
                ->addSelect('p.*')
                ->leftJoin('x.Profile p')
                ;
        return $q;
    }
    
}

