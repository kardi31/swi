<?php

/**
 * User_DataTables_Adapter_Client
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_DataTables_Adapter_Client extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = parent::getBaseQuery();
        $q->andWhere('x.role = ?', 'client')
                ->addSelect('x.*')
                ->addSelect('p.*')
                ->leftJoin('x.Profile p')
                ;
        return $q;
    }
    
}

