<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class League_DataTables_Adapter_Team extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('t');
        $q->addSelect('t.*');
        
        if($league_id = $this->request->getParam('league_id')){
            $q->addWhere('t.league_id = ?',$league_id);
        }
        return $q;
    }
}
