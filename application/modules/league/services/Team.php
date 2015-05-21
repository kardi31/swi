<?php

/**
 * League_Service_Team
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_Service_Team extends MF_Service_ServiceAbstract {
    
    protected $teamTable;
    
    public function init() {
        $this->teamTable = Doctrine_Core::getTable('League_Model_Doctrine_Team');
        parent::init();
    }
    
    public function getTeam($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->teamTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getLeagueTeams($id, $field = 'slug', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->leftJoin('t.League l');
        $q->addWhere('l.'.$field." = ?",$id);
        return $q->execute(array(),$hydrationMode);
    }
    
    public function getTeamPlayers($id, $field = 'slug', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->leftJoin('t.Players p');
        $q->addWhere('t.'.$field." = ?",$id);
        $wynik = $q->fetchOne(array(),$hydrationMode);
        return $wynik['Players'];
    }
    
    public function getMyTeams($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->innerJoin('t.League l');
        $q->leftJoin('l.Group g');
        $q->addWhere('t.my_team = 1');
        $q->addWhere('l.active = 1');
        
        return $q->execute(array(),$hydrationMode);
    }
    
    public function prependMyTeamsOptions(){
        $teams = $this->getMyTeams();
        $result = array();
        foreach($teams as $team):
            $result[$team['id']] = $team['name']." - ".$team['League']['Group']['name']." - ".$team['League']['name'];
        endforeach;
        
        return $result;
    }
    
    public function getMyTeamCoach($group_id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->innerJoin('t.League l');
        $q->leftJoin('t.Coach c');
        $q->leftJoin('c.Photo p');
        $q->addWhere('l.group_id = ?',$group_id);
        $q->addWhere('t.my_team = 1');
        $q->orderBy("FIELD(position,'Bramkarz','Obrońca','Pomocnik','Napastnik')");
        $wynik = $q->fetchOne(array(),$hydrationMode);
        return $wynik['Coach'];
    }
    
    public function getMyTeam($group_id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->innerJoin('t.League l');
        $q->leftJoin('t.TeamPhoto p');
        $q->addWhere('l.group_id = ?',$group_id);
        $q->addWhere('t.my_team = 1');
        return $q->fetchOne(array(),$hydrationMode);
    }
    
    public function getMyTeamPlayers($group_id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->innerJoin('t.League l');
        $q->leftJoin('t.Players p');
        $q->leftJoin('p.Photo lo');
        $q->addWhere('l.group_id = ?',$group_id);
        $q->addWhere('t.my_team = 1');
        $q->orderBy("FIELD(position,'Bramkarz','Obrońca','Pomocnik','Napastnik')");
        $wynik = $q->fetchOne(array(),$hydrationMode);
        return $wynik['Players'];
    }
    
    public function getTeamsTimetable($league_id,$hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {  
        $q = $this->teamTable->createQuery('t');
        $q->addWhere('t.league_id = ?',$league_id);
        $q->select('t.*');
        $q->leftJoin('t.Matches1 m1');
        $q->leftJoin('t.Matches2 m2');
		
        $q->leftJoin('m1.Team1 t1');
        $q->leftJoin('m1.Team2 t2');
		
        $q->leftJoin('m2.Team1 t3');
        $q->leftJoin('m2.Team2 t4'); 
		
		
	//$q->addWhere('m1.played = 1');
	//$q->addWhere('m2.played = 1');
        $q->addSelect('m1.*');
        $q->addSelect('m2.*');
		
		$q->addSelect('t1.*');
		$q->addSelect('t2.*');
		$q->addSelect('t3.*');
		$q->addSelect('t4.*'); 
        $res = $q->execute(array(),$hydrationMode);
		
        foreach($res as $key=>$r):
            foreach($r['Matches1'] as $match):
			if($match['played']==0)
			continue;
                if($r['id']==$match->team1)
                    $result[$r['slug']][] = $match['Team1']['slug'];
                else
                    $result[$r['slug']][] = $match['Team2']['slug'];
            endforeach;
            
            foreach($r['Matches2'] as $match):
			
			if($match['played']==0)
			continue;
                if($r['id']==$match->team1)
                    $result[$r['slug']][] = $match['Team2']['slug'];
                else
                    $result[$r['slug']][] = $match['Team1']['slug'];
            endforeach;
            $t[$r['slug']] = array_count_values($result[$r['slug']]);
        endforeach;
        return $t;
        
    }
    public function getTeamForm(League_Model_Doctrine_Team $team = null) {
        $form = new League_Form_Team();
        if(null != $team) { 
            $form->populate($team->toArray());
        }
        return $form;
    }
    
    public function saveTeamFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$order = $this->getTeam((int) $values['id'])) {
            $order = $this->teamTable->getRecord();
        }
        $order->fromArray($values);
        $order->save();
        
        return $order;
    }
       
    public function getFullOrder($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->teamTable->getFullOrderQuery();
        $q->andWhere('o.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getUserOrders($email, $field = 'email', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->teamTable->getFullOrderQuery();
        $q->andWhere('u.' . $field . ' like ?', $email);
        $q->addOrderBy('o.created_at');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNewOrders($date, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->teamTable->getFullOrderQuery();
        $q->andWhere('o.created_at > ?', $date);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllOrders($countOnly = false) {
        if(true == $countOnly) {
            return $this->teamTable->count();
        } else {
            return $this->teamTable->findAll();
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