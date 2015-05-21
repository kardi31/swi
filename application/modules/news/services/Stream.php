<?php

/**
 * News_Service_News
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_Service_Stream extends MF_Service_ServiceAbstract{
    
    protected $streamTable;
    
    public function init() {
        $this->streamTable = Doctrine_Core::getTable('News_Model_Doctrine_Stream');
    }
    
    public function getAllStream($countOnly = false) {
        if(true == $countOnly) {
            return $this->streamTable->count();
        } else {
            return $this->streamTable->findAll();
        }
    }
    
    public function getAllArticles($countOnly = false) {
        if(true == $countOnly) {
            return $this->streamTable->count();
        } else {
            return $this->streamTable->findAll();
        }
    }
    
    public function getStream($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->streamTable->findOneBy($field, $id, $hydrationMode);
    }
  
    public function getAllNewStudentStream() {
        $q = $this->streamTable->getPublishStreamQuery();
        $q->addWhere('n.student = 1');
        $q->addWhere('n.student_accept = 0');
        return $q->count();
    }
    
    public function getFullStream($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->streamTable->getShowStreamQuery();
        if(in_array($field, array('id'))) {
            $q->andWhere('n.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('nt.' . $field . ' = ?', array($id));
            $q->andWhere('nt.lang = ?', 'pl');
        }
        return $q->fetchOne(array(), $hydrationMode);
    }
    public function getArticleWithAd($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->streamTable->getPublishStreamQuery();
        if(in_array($field, array('id'))) {
            $q->andWhere('n.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('nt.' . $field . ' = ?', array($id));
            $q->andWhere('nt.lang = ?', 'pl');
        }
        $q->leftJoin('n.Videos v');
        $q->addSelect('v.*');
        $q->addSelect('a.*');
        $q->leftJoin('v.Ad a');
        $q->addWhere('a.publish = 1');
       $q->addWhere('a.date_from <= NOW()');
       $q->addWhere('a.date_to > NOW()');
        return $q->fetchOne(array(), $hydrationMode);
    }
    
   
    
    public function getNew($limit, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->streamTable->getPublishStreamQuery();
        $q = $this->streamTable->getPhotoQuery($q);
        $q = $this->streamTable->getLimitQuery($limit, $q);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }

    public function getStreamPaginationQuery($language) {
        $q = $this->streamTable->getPublishStreamQuery();
       // $q = $this->streamTable->getPhotoQuery($q);
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.publish_date DESC');
        return $q;
    }
    
    public function getVideoForm(Media_Model_Doctrine_VideoUrl $video = null) {
         
       
        $form = new Stream_Form_Video();
        
        if(null != $video) {
            
            $form->populate($video->toArray());
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($video->Translation[$language]->title);
                }
            }
        }
        return $form;
    }
    public function getStreamForm(News_Model_Doctrine_Stream $stream = null) {
         
       
        $form = new News_Form_Stream();
        $form->setDefault('publish', 1);
        
        if(null != $stream) {
            
            $form->populate($stream->toArray());
            
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('title')->setValue($stream->Translation[$language]->title);
                    $i18nSubform->getElement('content')->setValue($stream->Translation[$language]->content);
                }
            }
        }
        return $form;
    }
    
    public function saveStreamFromArray($values,$last_user_id,$user_id = null) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$stream = $this->streamTable->getProxy($values['id'])) {
            $stream = $this->streamTable->getRecord();
        }
       
        if($user_id!= null)
            $values['user_id'] = $user_id;
        
        $values['last_user_id'] = $last_user_id;
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $stream->fromArray($values);
 
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['title'])) {
                $stream->Translation[$language]->title = $values['translations'][$language]['title'];
               
                $stream->Translation[$language]->slug = MF_Text::createUniqueTableSlug('News_Model_Doctrine_StreamTranslation', $values['translations'][$language]['title'], $stream->getId());
              
                $stream->Translation[$language]->content = $values['translations'][$language]['content'];
            }
        }
        
        $stream->unlink('Tags');
        foreach($values['tag_id'] as $tag_id):
            $stream->link('Tags',$tag_id);
        endforeach;
        
        $stream->save();
       
       
         
        return $stream;
    }
    
    public function removeStream(News_Model_Doctrine_Stream $stream) {
        $stream->delete();
    }
     
    public function searchStream($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->streamTable->getAllStreamQuery();
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "stream" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        return $q->execute(array(), $hydrationMode);
    }
    
      public function getLastStream($limit = 4, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->streamTable->getLastStreamQuery();
        $q->leftJoin('n.VideoRoot v');
        $q->addSelect('v.*');
        $q->orderBy('n.publish_date DESC');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    
     public function getTargetStreamSelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllStream();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
                $result[$item->id] = $item->Translation[$language]->title;
        }
        return $result;
    } 
    
    
    
}

