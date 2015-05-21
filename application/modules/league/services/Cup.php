<?php

/**
 * League_Service_Cup
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_Service_Cup extends MF_Service_ServiceAbstract {
    
    protected $cupTable;
    
    public function init() {
        $this->cupTable = Doctrine_Core::getTable('League_Model_Doctrine_Cup');
        parent::init();
    }
    
    public function getCup($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->cupTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getAllCups($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->cupTable->createQuery('c');
        $q->orderBy('c.id DESC');
        return $q->execute(array(),$hydrationMode);
    }
    
    public function getGoals($match_id,$team_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
      {
           $q = $this->cupTable->createQuery('s');
           $q->leftJoin('s.Player p');
           $q->addWhere('s.match_id = ?',$match_id)
                 ->addWhere('p.team_id = ?',$team_id)
                 ->orderBy('s.goal DESC');
           return $q->execute(array(),$hydrationMode);
      }
    
      
              public function getCups($league_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
              {
                  $q = $this->cupTable->createQuery('s');
                  $q->leftJoin('s.Player p');
                  $q->leftJoin('p.Team t');
                  $q->addSelect('*,sum(s.goal) as goals');
                  $q->addWhere('t.league_id = ?',$league_id)
                        ->groupBy('p.id')
                         ->orderBy('goals DESC')
                         ;
                        return $q->execute(array(),$hydrationMode);
               
              }
    public function getTimetableDate($league_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
        $q = $this->cupTable->createQuery('m');
        $q->addWhere('m.played = 0')
             ->addWhere('m.league_id = ?',$league_id)
             ->addWhere('m.match_date > NOW()')
             ->groupBy('m.match_date');
    
    return $q->execute(array(),$hydrationMode);

    }
    public function getTimetable($league_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
    $q = $this->cupTable->createQuery('m');
    $q->addWhere('m.played = 0')
         ->addWhere('m.league_id = ?',$league_id)
         ->addWhere('m.match_date > NOW()') 
         ->orderBy('m.match_date ASC');
    return $q->execute(array(),$hydrationMode);
    }
    
    public function getOrderForm(Order_Model_Doctrine_Order $order = null) {
        $form = new Order_Form_Order();
        if(null != $order) { 
            $form->populate($order->toArray());
        }
        return $form;
    }
    
     public function getResults($league_id,$limit=100,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
     {
         $q = $this->cupTable->createQuery('m');
         $q->addWhere('m.league_id = ?',$league_id)
                 ->where('m.played = 1')
                 ->limit($limit)
                 ->orderBy('m.match_date DESC','m.id DESC')
                 ->groupBy('m.match_date');
         return $q->execute(array(),$hydrationMode);
    }
    
    public function saveOrderFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$order = $this->getOrder((int) $values['id'])) {
            $order = $this->cupTable->getRecord();
        }
        $order->fromArray($values);
        $order->save();
        
        return $order;
    }
       
    public function getFullOrder($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->cupTable->getFullOrderQuery();
        $q->andWhere('o.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getUserOrders($email, $field = 'email', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->cupTable->getFullOrderQuery();
        $q->andWhere('u.' . $field . ' like ?', $email);
        $q->addOrderBy('o.created_at');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNewOrders($date, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->cupTable->getFullOrderQuery();
        $q->andWhere('o.created_at > ?', $date);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllOrders($countOnly = false) {
        if(true == $countOnly) {
            return $this->cupTable->count();
        } else {
            return $this->cupTable->findAll();
        }
    }
    
    public function getCart() {
        if(!$this->cart) {
            $this->cart = new Order_Model_Cart();
        }
        return $this->cart;
    }
}
?>