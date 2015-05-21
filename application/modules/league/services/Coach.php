<?php

/**
 * Order_Service_CoachType
 *

 */
class League_Service_Coach extends MF_Service_ServiceAbstract {
    
    protected $coachTable;
    
    public function init() {
        $this->coachTable = Doctrine_Core::getTable('League_Model_Doctrine_Coach');
        parent::init();
    }
    
    public function getCoach($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->coachTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getCoachTypes() {    
        return $this->coachTable->findAll();
    }
    
    
    public function getAllLeagueCoachs($league_id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->coachTable->createQuery('p');
        $q->leftJoin('p.Team t');
        $q->addSelect('p.*');
        $q->addSelect('t.*');
        $q->orderBy('p.last_name,p.first_name');
        $q->addWhere('t.league_id = ?',$league_id);
        return $q->execute(array(),$hydrationMode);
    }
    
    public function prependCoachOptions($league_id) {   
        $players = $this->getAllLeagueCoachs($league_id);
        $options = array();
        $options[] = '';
        foreach($players as $player):
            $options[$player['id']] = $player['last_name']." ".$player['first_name']."(".$player['Team']['name'].")";
        endforeach;
        
        return $options;
    }
    
    public function getCoachForm(League_Model_Doctrine_Coach $player = null) {
        $form = new League_Form_Coach();
        if(null != $player) { 
            $form->populate($player->toArray());
        }
        return $form;
    }
    
    public function getCoachTypeForm(Order_Model_Doctrine_CoachType $player = null) {
        $form = new Order_Form_CoachType();
        if(null != $player) { 
            $form->populate($player->toArray());
        }
        return $form;
    }
    
    public function saveCoachFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$player = $this->getCoach((int) $values['id'])) {
            $player = $this->coachTable->getRecord();
        }
         
        $player->fromArray($values);
        $player->save();
        
        return $player;
    }
    
    public function removeCoachType(Order_Model_Doctrine_CoachType $player) {
        $player->delete();
    }
}
?>