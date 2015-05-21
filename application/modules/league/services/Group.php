<?php

/**
 * League_Service_League
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_Service_Group extends MF_Service_ServiceAbstract {
    
    protected $groupTable;
    
    public function init() {
        $this->groupTable = Doctrine_Core::getTable('League_Model_Doctrine_Group');
        parent::init();
    }
    
    public function getAllGroups($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->groupTable->findAll($hydrationMode);
    }

    public function getGroupsForForm(){
        $results = array();
        $groups = $this->getAllGroups(Doctrine_Core::HYDRATE_ARRAY);
        foreach($groups as $group):
            $results[$group['id']] = $group['name'];
        endforeach;
        
        return $results;
    }
    
    public function getGroup($league_id,$weight,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
        $q = $this->groupTable->createQuery('b');
        $q->leftJoin('b.Player p');
        $q->leftJoin('p.Team t');
        $q->addSelect('*, sum(quantity) as kar');
        $q->addWhere('t.league_id = ?',$league_id)
                         ->addWhere('b.weight = ?',$weight)
                         ->addOrderBy('kar DESC')
                         ->addOrderBy('p.last_name DESC')
                         ->addOrderBy('p.first_name DESC')
                         ->addGroupBy('p.last_name')
                         ->addGroupBy('p.first_name')
                         ->addGroupBy('b.weight');
                    $q->addWhere('b.active = 1');
       return $q->execute(array(), $hydrationMode);
    }
    
    public function getSingleGroup($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->groupTable->findOneBy($field, $id, $hydrationMode);
    }
    
    
    
    public function getGroupForm(League_Model_Doctrine_Group $group = null) {
        $form = new League_Form_Group();
        if(null != $group) { 
            $form->populate($group->toArray());
        }
        return $form;
    }
    
    public function saveGroupFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$group = $this->getSingleGroup((int) $values['id'])) {
            $group = $this->groupTable->getRecord();
        }
         
        $group->fromArray($values);
        $group->save();
        
        return $group;
    }
    
    public function removeLeague(League_Model_Doctrine_League $orderStatus) {
        $orderStatus->delete();
    }
    
    public function getAllLeague($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->groupTable->getLeagueQuery();
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