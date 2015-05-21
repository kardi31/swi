<?php

/**
 * GroupService
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_Service_Group extends MF_Service_ServiceAbstract {
    
    protected $groupTable;
    
    public function init() {
        $this->groupTable = Doctrine_Core::getTable('User_Model_Doctrine_Group');
        parent::init();
    }
    
     public function getGroup($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->groupTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getGroupForm(User_Model_Doctrine_Group $group = null) {
        $form = new User_Form_Group();
        if(null != $group) { 
            $form->populate($group->toArray());
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($group->Translation[$language]->name);
                    $i18nSubform->getElement('description')->setValue($group->Translation[$language]->description);
                }
            }
        }
        return $form;
    }
    
    public function saveGroupFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$group = $this->getGroup((int) $values['id'])) {
            $group = $this->groupTable->getRecord();
        }
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $group->fromArray($values);
        
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['name'])) {
                $group->Translation[$language]->name = $values['translations'][$language]['name'];
                $group->Translation[$language]->slug = MF_Text::createUniqueTableSlug('User_Model_Doctrine_GroupTranslation', $values['translations'][$language]['name'], $group->getId());
                $group->Translation[$language]->description = $values['translations'][$language]['description'];
            }
        }
        
        $group->unlink('Users');
        $group->link('Users', $values['user_id']);
        $group->save();
        
        return $group;
    }   
    
    public function removeGroup(User_Model_Doctrine_Group $group) {
        $group->unlink('Users');
        $group->get('Translation')->delete();
        $group->save();
        $group->delete();
    }
    
    public function refreshStatusGroupClient($group){
        if ($group->isStatus()):
            $group->setStatus(0);
        else:
            $group->setStatus(1);
        endif;
        $group->save();
    }
    
    public function getAllGroups($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->groupTable->getGroupQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetGroupSelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllGroups();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->name;
        }
        return $result;
    }
    
    public function getUnSelectedDiscountSelectOptions($discountId, $language = null) {
        $q = $this->groupTable->getGroupQuery();
        $q->andWhere('gr.discount_id != ? OR gr.discount_id IS NULL', $discountId);
        $items = $q->execute(array(), $hydrationMode);
        $result = array();
        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->name;
        }
        return $result;
    }
    
    public function getSelectedDiscountSelectOptions($discountId, $language = null) {
        $q = $this->groupTable->getGroupQuery();
        $q->andWhere('gr.discount_id = ?', $discountId);
        $items = $q->execute(array(), $hydrationMode);
        $result = array();
        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->name;
        }
        return $result;
    }
    
    public function unSelectDiscountGroups($selectedGroups, $newSelectedGroups){
        foreach($selectedGroups as $key => $selectedGroup):
            $flag = false;
            foreach($newSelectedGroups as $newSelectedGroup):
                if ($key == $newSelectedGroup):
                    $flag = true;
                endif;
            endforeach;
            if ($flag == false):
                $group = $this->getGroup($key);
                $group->setDiscountId(NULL);
                $group->save();
            endif;
        endforeach;
    }
    
    public function saveAssignedDiscountsFromArray($values, $discountId){
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        $selectedGroups = $this->getSelectedDiscountSelectOptions($discountId);
        $this->unSelectDiscountGroups($selectedGroups, $values['group_selected']);
        foreach($values['group_selected'] as $value):
            $group = $this->getGroup($value);
            $group->setDiscountId($discountId);
            $group->save();
        endforeach;
    }
}

