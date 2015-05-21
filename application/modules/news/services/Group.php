<?php

/**
 * News_Service_News
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_Service_Group extends MF_Service_ServiceAbstract{
    
    protected $groupTable;
    
    public function init() {
        $this->groupTable = Doctrine_Core::getTable('News_Model_Doctrine_Group');
    }
    
    public function getAllCategories($countOnly = false) {
        if(true == $countOnly) {
            return $this->groupTable->count();
        } else {
            return $this->groupTable->findAll();
        }
    }
    
    public function getGroup($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->groupTable->findOneBy($field, $id, $hydrationMode);
    }
   
    
    public function getGroupForm(News_Model_Doctrine_Group $group = null) {
         
       
        $form = new News_Form_Group();
        if($group!=null)
            $form->populate($group->toArray());
        
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
       
        $group->slug = MF_Text::createUniqueTableSlug('News_Model_Doctrine_Group', $values['title'], $group->getId());
              
        
        $group->fromArray($values);
 
        $group->save();
       
        return $group;
    }
    
    public function removeGroup(News_Model_Doctrine_Group $group) {
        $group->delete();
    }
    
    public function prependGroupOptions() {
       
       $options = array('' => '');
       $categories = $this->getAllCategories();
       
       foreach($categories as $group):
           $options[$group['id']] = $group['title'];
       endforeach;
       
       return $options;
    }
     
   
}

