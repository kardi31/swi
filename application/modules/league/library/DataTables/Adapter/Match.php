<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class League_DataTables_Adapter_Match extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('m');
        $q->addSelect('m.*');
        $q->addSelect('t1.*');
        $q->addSelect('t2.*');
        $q->leftJoin('m.Team1 t1');
        $q->leftJoin('m.Team2 t2');
        if($league_id = $this->request->getParam('league_id')){
            $q->addWhere('m.league_id = ?',$league_id);
        }
        return $q;
    }
}
