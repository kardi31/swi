<?php

/**
 * User_DataTables_Adapter_Agent
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_DataTables_Adapter_Agent extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = parent::getBaseQuery();
        $q->andWhere('x.role = ?', 'agent')
                ->addSelect('x.*')
                ->addSelect('p.*')
                ->addSelect('pv.*')
                ->addSelect('c.*')
                ->addSelect('o.*, COUNT(DISTINCT o.id) as offer_count')
                ->leftJoin('x.Profile p')
                ->leftJoin('p.Province pv')
                ->leftJoin('p.City c')
                ->leftJoin('x.Offers o')
                ->addGroupBy('o.id, x.id')
                ;
        return $q;
    }
    
}

