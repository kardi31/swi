<?php

/**
 * Order_Service_BoardType
 *

 */
class League_Service_Board extends MF_Service_ServiceAbstract {
    
    protected $boardTable;
    
    public function init() {
        $this->boardTable = Doctrine_Core::getTable('League_Model_Doctrine_Board');
        parent::init();
    }
    
    public function getBoard($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->boardTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getBoardTypes() {    
        return $this->boardTable->findAll();
    }
    
    
    public function getAllBoards($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->boardTable->createQuery('b');
        $q->orderBy("FIELD(position,'prezes%','Prezes','%prezes%','%Prezes%') DESC, last_name, first_name");
        return $q->execute(array(),$hydrationMode);
    }
    
    public function prependBoardOptions($league_id) {   
        $players = $this->getAllLeagueBoards($league_id);
        $options = array();
        $options[] = '';
        foreach($players as $player):
            $options[$player['id']] = $player['last_name']." ".$player['first_name']."(".$player['Team']['name'].")";
        endforeach;
        
        return $options;
    }
    
    public function getBoardForm(League_Model_Doctrine_Board $player = null) {
        $form = new League_Form_Board();
        if(null != $player) { 
            $form->populate($player->toArray());
        }
        return $form;
    }
    
    public function getBoardTypeForm(Order_Model_Doctrine_BoardType $player = null) {
        $form = new Order_Form_BoardType();
        if(null != $player) { 
            $form->populate($player->toArray());
        }
        return $form;
    }
    
    public function saveBoardFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$player = $this->getBoard((int) $values['id'])) {
            $player = $this->boardTable->getRecord();
        }
        $player->fromArray($values);
        $player->save();
        
        return $player;
    }
    
    public function removeBoardType(Order_Model_Doctrine_BoardType $player) {
        $player->delete();
    }
}
?>