<?php

/**
 * League_Service_Shooter
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_Service_Shooter extends MF_Service_ServiceAbstract {
    
    protected $shooterTable;
    
    public function init() {
        $this->shooterTable = Doctrine_Core::getTable('League_Model_Doctrine_Shooter');
        parent::init();
    }
    
    public function getTeam($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->shooterTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getGoals($match_id,$team_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
      {
           $q = $this->shooterTable->createQuery('s');
           $q->leftJoin('s.Player p');
           $q->addWhere('s.match_id = ?',$match_id)
                 ->addWhere('p.team_id = ?',$team_id)
                 ->orderBy('s.goal DESC');
           return $q->execute(array(),$hydrationMode);
      }
    public function getMatchShooters($match_id,$hydrationMode = Doctrine_Core::HYDRATE_ARRAY)
      {
           $q = $this->shooterTable->createQuery('s');
           $q->leftJoin('s.Player p');
           $q->addWhere('s.match_id = ?',$match_id);
           $res =  $q->execute(array(),$hydrationMode);
           $team1 = $res[0]['Player']['team_id'];
           foreach($res as $r):
               if($r['Player']['team_id']==$team1)
                   $result[2][] = array('player_id' => $r['player_id'] , 'goal' => $r['goal']);
               else
                   $result[1][] = array('player_id' => $r['player_id'] , 'goal' => $r['goal']);
               
           endforeach;
           return $result;
      }
      
              public function getShooters($league_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
              {
                  $q = $this->shooterTable->createQuery('s');
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
        $q = $this->shooterTable->createQuery('m');
        $q->addWhere('m.played = 0')
             ->addWhere('m.league_id = ?',$league_id)
             ->addWhere('m.match_date > NOW()')
             ->groupBy('m.match_date');
    
    return $q->execute(array(),$hydrationMode);

    }
    public function getTimetable($league_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
    $q = $this->shooterTable->createQuery('m');
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
         $q = $this->shooterTable->createQuery('m');
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
            $order = $this->shooterTable->getRecord();
        }
        $order->fromArray($values);
        $order->save();
        
        return $order;
    }
       
    public function getFullOrder($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->shooterTable->getFullOrderQuery();
        $q->andWhere('o.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getUserOrders($email, $field = 'email', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->shooterTable->getFullOrderQuery();
        $q->andWhere('u.' . $field . ' like ?', $email);
        $q->addOrderBy('o.created_at');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNewOrders($date, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->shooterTable->getFullOrderQuery();
        $q->andWhere('o.created_at > ?', $date);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllOrders($countOnly = false) {
        if(true == $countOnly) {
            return $this->shooterTable->count();
        } else {
            return $this->shooterTable->findAll();
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