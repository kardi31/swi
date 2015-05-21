<?php

/**
 * Menu_DataTables_Adapter_MenuItem
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Menu_DataTables_Adapter_MenuItemSerwis8 extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        $q->addSelect('m.*');
        $q->addSelect('xt.*');
        $q->leftJoin('x.Translation xt');
        $q->leftJoin('x.MenuSerwis8 m');
        if($this->request->getParam('id')) {
            $q->andWhere('m.id = ?', (int) $this->request->getParam('id'));
        }
        if($this->request->getParam('item')) {
            $q->andWhere('x.lft > (SELECT x2.lft FROM Menu_Model_Doctrine_MenuItemSerwis8 x2 WHERE x2.id = ?)', array((int) $this->request->getParam('item')));
            $q->andWhere('x.rgt < (SELECT x3.rgt FROM Menu_Model_Doctrine_MenuItemSerwis8 x3 WHERE x3.id = ?)', array((int) $this->request->getParam('item')));
        }
        $q->andWhere('x.level > ?', 0);
        return $q;
    }
}

