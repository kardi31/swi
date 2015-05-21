<?php

/**
 * Newsletter_Service_Group
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_Service_Group extends MF_Service_ServiceAbstract {
    
    protected $groupTable;
    
    public function init() {
        $this->groupTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Group');
        parent::init();
    }

    public function getGroup($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->groupTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getGroupForm(Newsletter_Model_Doctrine_Group $group = null) {
        $form = new Newsletter_Form_Group();
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
        if(!$group = $this->groupTable->getProxy($values['id'])) {
            $group = $this->groupTable->getRecord();
        }

        $group->fromArray($values);
        $group->unlink('Subscribers');
        $group->link('Subscribers', $values['subscriber_id']);
        $group->save();
        
        return $group;
    }
    
    public function getAllGroups($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->groupTable->getGroupQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetGroupSelectOptions($prependEmptyValue = false) {
        $items = $this->getAllGroups();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
            $result[$item->getId()] = $item->name;
        }

        return $result;
    }
    
    public function removeGroup($group){
        $group->unlink('Subscribers');
        $group->save();
        $group->delete();        
    }   
}

