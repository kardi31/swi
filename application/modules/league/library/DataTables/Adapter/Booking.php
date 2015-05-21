<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class League_DataTables_Adapter_Booking extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('b');
        $q->addSelect('b.*');
        $q->addSelect('p.*');
        $q->addSelect('t.*');
        $q->leftJoin('b.Player p');
        $q->leftJoin('p.Team t');
        if($league_id = $this->request->getParam('league_id')){
            $q->addWhere('t.league_id = ?',$league_id);
        }
        return $q;
    }
}
