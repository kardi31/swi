<?php

/**
 * League_Service_League
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_Service_League extends MF_Service_ServiceAbstract {
    
    protected $leagueTable;
    
    public function init() {
        $this->leagueTable = Doctrine_Core::getTable('League_Model_Doctrine_League');
        parent::init();
    }
    
    public function getLeague($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->leagueTable->findOneBy($field, $id, $hydrationMode);
    }
    
    
    
    public function getLeagueForm(League_Model_Doctrine_League $orderStatus = null) {
        $form = new League_Form_League();
        if(null != $orderStatus) { 
            $form->populate($orderStatus->toArray());
        }
        return $form;
    }
    
    public function saveLeagueFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$orderStatus = $this->getLeague((int) $values['id'])) {
            $orderStatus = $this->leagueTable->getRecord();
        }
         
        
        $orderStatus->fromArray($values);
        $orderStatus->slug = MF_Text::createUniqueTableSlug('League_Model_Doctrine_League',$values['name'],$values['id']);
        $orderStatus->save();
        
        return $orderStatus;
    }
    
    public function removeLeague(League_Model_Doctrine_League $orderStatus) {
        $orderStatus->delete();
    }
    
    public function getAllLeague($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->leagueTable->getLeagueQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getActiveLeague($group_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->leagueTable->createQuery('l');
        $q->leftJoin('l.Tabela t');
        $q->addWhere('l.group_id = ?',$group_id);
        $q->addWhere('l.active = 1');
        $q->orderBy('l.id DESC,t.points DESC,(t.goals_scored-t.goals_lost) DESC');
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getActiveLeagues($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->leagueTable->createQuery('l');
        $q->addWhere('l.active = 1');
        $q->orderBy('l.id DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getActiveLeagueIds($hydrationMode = Doctrine_Core::HYDRATE_SINGLE_SCALAR) {
        $q = $this->leagueTable->createQuery('l');
	$q->select('l.id');
        $q->addWhere('l.active = 1');
        $q->orderBy('l.id DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getActiveLeaguesWithTable($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->leagueTable->createQuery('l');
        $q->leftJoin('l.Tabela t');
        $q->addWhere('l.active = 1');
        $q->orderBy('l.name,t.points DESC,(goals_scored - goals_lost) DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNextLeagueMatches($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->leagueTable->createQuery('l');
        $q->leftJoin('l.Matches m');
        $q->addWhere('l.active = 1');
	$q->addWhere('DATE(m.match_date) = (SQL:SELECT DATE(m3.match_date) from league_match m3 where m3.match_date > NOW() order by m3.match_date DESC limit 1)');
        $q->orderBy('l.name');
	
	// select * from league_match m1 where DATE(m1.match_date) = (select DATE(m3.match_date) from league_match m3 where m3.match_date > NOW() limit 1)
	
//	$subquery = $q->createSubquery('m3')
//    ->select("DATE(m3.match_date)")
//    ->from("League_Model_Doctrine_Match m3")
//    ->where("m3.match_date > NOW()")
//		->addWhere('m3.league_id = l.id')
//		->orderBy('m3.match_date')
//		->limit(1)
//;
//	echo $subquery->getSqlQuery();exit;
	
//	$q->addWhere('DATE(m.match_date) = ('.$subquery->getDql().')');
	echo $q->getSqlQuery();exit;
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getPrevLeagueMatches($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->leagueTable->createQuery('l');
        $q->leftJoin('l.Matches m');
        $q->addWhere('l.active = 1');
//	$q->addWhere('DATE(m.match_date) = (select DATE(mt.match_date) from league_match mt where mt.match_date < NOW() DESC limit 1)');
        $q->orderBy('l.name');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetLeagueSelectOptions($prependEmptyValue = false) {
        $items = $this->getAllLeague();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
                $result[$item->getId()] = $item->name;
        }
        return $result;
    }
}
?>