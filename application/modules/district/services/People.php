<?php

/**
 * District_Service_People
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class District_Service_People extends MF_Service_ServiceAbstract{
    
    protected $peopleTable;
    
    public function init() {
        $this->peopleTable = Doctrine_Core::getTable('District_Model_Doctrine_People');
    }
    
    public function getAllPeople($countOnly = false) {
        if(true == $countOnly) {
            return $this->peopleTable->count();
        } else {
            return $this->peopleTable->findAll();
        }
    }
    
    public function getAllArticles($countOnly = false) {
        if(true == $countOnly) {
            return $this->peopleTable->count();
        } else {
            return $this->peopleTable->findAll();
        }
    }
    
    public function getPeople($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->peopleTable->findOneBy($field, $id, $hydrationMode);
    }
  
    
    public function getFullPerson($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->peopleTable->getPublishPeopleQuery();
       // $q = $this->peopleTable->getPhotoQuery($q);
        if(in_array($field, array('id'))) {
            $q->andWhere('pp.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('ppt.' . $field . ' = ?', array($id));
            $q->andWhere('ppt.lang = ?', 'pl');
        }
        return $q->fetchOne(array(), $hydrationMode);
    }
   
    
    public function getRandomPerson($hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
        $q = $this->peopleTable->getPublishPeopleQuery();
        $q->orderBy('rand()');
        $q->limit(1);
        return $q->fetchOne(array(), $hydrationMode);
    }

    public function getPeoplePaginationQuery($language) {
        $q = $this->peopleTable->getPublishPeopleQuery();
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.publish_date DESC');
        return $q;
    }
    
    public function getPeopleForm(District_Model_Doctrine_People $people = null) {
         
       
        $form = new News_Form_News();
        $form->setDefault('publish', 1);
        
        if(null != $people) {
            
            $form->populate($people->toArray());
            if($publishDate = $people->getPublishDate()) {
                $date = new Zend_Date($people->getPublishDate(), 'yyyy-MM-dd HH:mm:ss');
                $form->getElement('publish_date')->setValue($date->toString('dd/MM/yyyy HH:mm'));
            }
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('title')->setValue($people->Translation[$language]->title);
                    $i18nSubform->getElement('content')->setValue($people->Translation[$language]->content);
                }
            }
        }
        
        return $form;
    }
    
    public function savePeopleFromArray($values,$last_user_id,$user_id = null) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$people = $this->peopleTable->getProxy($values['id'])) {
            $people = $this->peopleTable->getRecord();
        }
       
        if($user_id!= null)
            $values['user_id'] = $user_id;
        
        $values['last_user_id'] = $last_user_id;
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        if(strlen($values['publish_date'])) {
            $date = new Zend_Date($values['publish_date'], 'dd/MM/yyyy HH:mm');
            $values['publish_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($people['publish_date'])) {
            $values['publish_date'] = date('Y-m-d H:i:s');
        }

        $people->fromArray($values);
 
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['title'])) {
                $people->Translation[$language]->title = $values['translations'][$language]['title'];
               
                $people->Translation[$language]->slug = MF_Text::createUniqueTableSlug('District_Model_Doctrine_PeopleTranslation', $values['translations'][$language]['title'], $people->getId());
              
                $people->Translation[$language]->content = $values['translations'][$language]['content'];
            }
        }
        $people->save();
       
       
         
        return $people;
    }
    
    public function removePeople(District_Model_Doctrine_People $people) {
        $people->delete();
    }
     
    public function searchPeople($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->peopleTable->getAllPeopleQuery();
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "news" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        return $q->execute(array(), $hydrationMode);
    }
     
}

