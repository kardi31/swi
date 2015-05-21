<?php

/**
 * District_Service_Event
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class District_Service_Event extends MF_Service_ServiceAbstract{
    
    protected $eventTable;
    
    public function init() {
        $this->eventTable = Doctrine_Core::getTable('District_Model_Doctrine_Event');
    }
    
    public function getAllEvent($countOnly = false) {
        if(true == $countOnly) {
            return $this->eventTable->count();
        } else {
            return $this->eventTable->findAll();
        }
    }
    
    public function getAllArticles($countOnly = false) {
        if(true == $countOnly) {
            return $this->eventTable->count();
        } else {
            return $this->eventTable->findAll();
        }
    }
    
    public function getEvent($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->eventTable->findOneBy($field, $id, $hydrationMode);
    }
  
    
    public function getFullEvent($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->eventTable->getPublishEventQuery();
      //  $q = $this->eventTable->getPhotoQuery($q);
        if(in_array($field, array('id'))) {
            $q->andWhere('e.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('et.' . $field . ' = ?', array($id));
            $q->andWhere('et.lang = ?', 'pl');
        }
        return $q->fetchOne(array(), $hydrationMode);
    }
    
     public function getNextEvent($hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
        $q = $this->eventTable->getPublishEventQuery();
        $q->andWhere('e.publish_date > NOW()');
        $q->orderBy('e.publish_date DESC');
        $q->limit(1);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getNextEvents($limit=3,$hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
        $q = $this->eventTable->getPublishEventQuery();
        $q->andWhere('e.publish_date >= NOW()');
        $q->orderBy('e.publish_date DESC');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
   
    
    public function getNew($limit, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->eventTable->getPublishEventQuery();
        $q = $this->eventTable->getPhotoQuery($q);
        $q = $this->eventTable->getLimitQuery($limit, $q);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }

    public function getEventPaginationQuery($language) {
        $q = $this->eventTable->getPublishEventQuery();
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.publish_date DESC');
        return $q;
    }
    
    public function getOtherEvents($event, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->eventTable->getPublishEventQuery();
        $q->addWhere('e.id != ?',$event['id']);
        $q->addWhere('e.publish_date >= NOW()');
        $q->orderBy('e.publish_date DESC');
        $q->limit(8);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getEventForm(District_Model_Doctrine_Event $event = null) {
         
       
        $form = new News_Form_News();
        $form->setDefault('publish', 1);
        
        if(null != $event) {
            
            $form->populate($event->toArray());
            if($publishDate = $event->getPublishDate()) {
                $date = new Zend_Date($event->getPublishDate(), 'yyyy-MM-dd HH:mm:ss');
                $form->getElement('publish_date')->setValue($date->toString('dd/MM/yyyy HH:mm'));
            }
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('title')->setValue($event->Translation[$language]->title);
                    $i18nSubform->getElement('content')->setValue($event->Translation[$language]->content);
                }
            }
        }
        
        return $form;
    }
    
    public function saveEventFromArray($values,$last_user_id,$user_id = null) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$event = $this->eventTable->getProxy($values['id'])) {
            $event = $this->eventTable->getRecord();
        }
       
        if($user_id!= null)
            $values['user_id'] = $user_id;
        
        $values['last_user_id'] = $last_user_id;
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        if(strpos($values['url'], 'http://') !== 0 && strlen($values['url'])) {
          $values['url'] = 'http://' . $values['url'];
        } 
        if(strlen($values['publish_date'])) {
            $date = new Zend_Date($values['publish_date'], 'dd/MM/yyyy HH:mm');
            $values['publish_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($event['publish_date'])) {
            $values['publish_date'] = date('Y-m-d H:i:s');
        }

        $event->fromArray($values);
 
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['title'])) {
                $event->Translation[$language]->title = $values['translations'][$language]['title'];
               
                $event->Translation[$language]->slug = MF_Text::createUniqueTableSlug('District_Model_Doctrine_EventTranslation', $values['translations'][$language]['title'], $event->getId());
              
                $event->Translation[$language]->content = $values['translations'][$language]['content'];
            }
        }
        $event->save();
       
       
         
        return $event;
    }
    
    public function removeEvent(District_Model_Doctrine_Event $event) {
        $event->delete();
    }
     
    public function searchEvent($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->eventTable->getAllEventQuery();
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "news" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        return $q->execute(array(), $hydrationMode);
    }
     
}

