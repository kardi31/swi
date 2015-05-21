<?php

/**
 * Page
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Page_DataTables_Adapter_Page extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->addSelect('x.*');
        $q->addSelect('t.*');
        $q->addSelect('u.*');
        $q->leftJoin('x.Translation t');
        $q->leftJoin('x.User u');
//        $q->addOrderBy('x.type DESC, x.id');
        if($this->request->getParam('lang')) {
            $q->andWhere('t.lang = ?', $this->request->getParam('lang'));
        }
        return $q;
    }
}
