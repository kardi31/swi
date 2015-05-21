<?php

/**
 * Order_Service_PlayerType
 *

 */
class League_Service_Player extends MF_Service_ServiceAbstract {
    
    protected $playerTable;
    
    public function init() {
        $this->playerTable = Doctrine_Core::getTable('League_Model_Doctrine_Player');
        parent::init();
    }
    
    public function getPlayerType($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->playerTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getPlayer($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->playerTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getPlayerTypes() {    
        return $this->playerTable->findAll();
    }
    
    
    public function getAllLeaguePlayers($league_id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->playerTable->createQuery('p');
        $q->leftJoin('p.Team t');
        $q->addSelect('p.*');
        $q->addSelect('t.*');
        $q->orderBy('p.last_name,p.first_name');
        $q->addWhere('t.league_id = ?',$league_id);
        return $q->execute(array(),$hydrationMode);
    }
    
    public function prependPlayerOptions($league_id) {   
        $players = $this->getAllLeaguePlayers($league_id);
        $options = array();
        $options[] = '';
        foreach($players as $player):
            $options[$player['id']] = $player['last_name']." ".$player['first_name']."(".$player['Team']['name'].")";
        endforeach;
        
        return $options;
    }
    
    public function getPlayerForm(League_Model_Doctrine_Player $player = null) {
        $form = new League_Form_Player();
        if(null != $player) { 
            $form->populate($player->toArray());
        }
        return $form;
    }
    
    public function getPlayerTypeForm(Order_Model_Doctrine_PlayerType $player = null) {
        $form = new Order_Form_PlayerType();
        if(null != $player) { 
            $form->populate($player->toArray());
        }
        return $form;
    }
    
    public function savePlayerFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$player = $this->getPlayerType((int) $values['id'])) {
            $player = $this->playerTable->getRecord();
        }
         
        $player->fromArray($values);
        $player->save();
        
        return $player;
    }
    
    public function removePlayerType(Order_Model_Doctrine_PlayerType $player) {
        $player->delete();
    }
}
?>