<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Gallery_DataTables_Adapter_Gallery extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->addSelect('x.*');
        $q->addSelect('t.*');
        $q->leftJoin('x.Translation t');
//        $q->addOrderBy('x.type DESC, x.id');
        if($this->request->getParam('lang')) {
            $q->andWhere('t.lang = ?', $this->request->getParam('lang'));
        }
        return $q;
    }
}
