<?php

/**
 * District_Service_Attraction
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class District_Service_Attraction extends MF_Service_ServiceAbstract{
    
    protected $attractionTable;
    
    public function init() {
        $this->attractionTable = Doctrine_Core::getTable('District_Model_Doctrine_Attraction');
    }
    
    public function getAllAttraction($countOnly = false) {
        if(true == $countOnly) {
            return $this->attractionTable->count();
        } else {
            return $this->attractionTable->findAll();
        }
    }
    
    public function getAllArticles($countOnly = false) {
        if(true == $countOnly) {
            return $this->attractionTable->count();
        } else {
            return $this->attractionTable->findAll();
        }
    }
    
    public function getAttraction($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->attractionTable->findOneBy($field, $id, $hydrationMode);
    }
  
    
    public function getFullAttraction($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->attractionTable->getPublishAttractionQuery();
        if(in_array($field, array('id'))) {
            $q->andWhere('a.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('at.' . $field . ' = ?', array($id));
            $q->andWhere('at.lang = ?', 'pl');
        }
        return $q->fetchOne(array(), $hydrationMode);
    }
   
     public function getAllAttractions($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->attractionTable->getPublishAttractionQuery();
        $q->addOrderBy('a.publish_date DESC');
        
        return $q->execute(array(), $hydrationMode);
    }

    public function getAttractionPaginationQuery($language) {
        $q = $this->attractionTable->getPublishAttractionQuery();
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.publish_date DESC');
        return $q;
    }
    
    public function getAttractionForm(District_Model_Doctrine_Attraction $attraction = null) {
         
       
        $form = new News_Form_News();
        $form->setDefault('publish', 1);
        
        if(null != $attraction) {
            
            $form->populate($attraction->toArray());
            if($publishDate = $attraction->getPublishDate()) {
                $date = new Zend_Date($attraction->getPublishDate(), 'yyyy-MM-dd HH:mm:ss');
                $form->getElement('publish_date')->setValue($date->toString('dd/MM/yyyy HH:mm'));
            }
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('title')->setValue($attraction->Translation[$language]->title);
                    $i18nSubform->getElement('content')->setValue($attraction->Translation[$language]->content);
                }
            }
        }
        
        if($attraction->gallery):
            $form->removeElement('gallery');
        endif;
        return $form;
    }
    
    public function saveAttractionFromArray($values,$last_user_id,$user_id = null) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$attraction = $this->attractionTable->getProxy($values['id'])) {
            $attraction = $this->attractionTable->getRecord();
        }
       
        if($user_id!= null)
            $values['user_id'] = $user_id;
        
        $values['last_user_id'] = $last_user_id;
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        if(strlen($values['publish_date'])) {
            $date = new Zend_Date($values['publish_date'], 'dd/MM/yyyy HH:mm');
            $values['publish_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($attraction['publish_date'])) {
            $values['publish_date'] = date('Y-m-d H:i:s');
        }

        $attraction->fromArray($values);
 
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['title'])) {
                $attraction->Translation[$language]->title = $values['translations'][$language]['title'];
               
                $attraction->Translation[$language]->slug = MF_Text::createUniqueTableSlug('District_Model_Doctrine_AttractionTranslation', $values['translations'][$language]['title'], $attraction->getId());
              
                $attraction->Translation[$language]->content = $values['translations'][$language]['content'];
            }
        }
        $attraction->save();
       
       
         
        return $attraction;
    }
    
    public function removeAttraction(District_Model_Doctrine_Attraction $attraction) {
        $attraction->delete();
    }
     
    public function searchAttraction($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->attractionTable->getAllAttractionQuery();
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "news" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        return $q->execute(array(), $hydrationMode);
    }
     
}

