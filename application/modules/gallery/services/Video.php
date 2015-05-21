<?php

/**
 * Gallery_Service_Video
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Gallery_Service_Video extends MF_Service_ServiceAbstract{
    
    protected $videoTable;
    public static $articleItemCountPerPage = 12;
    
    public static function getArticleItemCountPerPage(){
        return self::$articleItemCountPerPage;
    }
    public function init() {
        $this->videoTable = Doctrine_Core::getTable('Gallery_Model_Doctrine_Video');
    }
    
    public function getAllNews($countOnly = false) {
        if(true == $countOnly) {
            return $this->videoTable->count();
        } else {
            return $this->videoTable->findAll();
        }
    }
    
    public function getAllArticles($countOnly = false) {
        if(true == $countOnly) {
            return $this->videoTable->count();
        } else {
            return $this->videoTable->findAll();
        }
    }
    
    public function getVideo($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->videoTable->findOneBy($field, $id, $hydrationMode);
    }
  
    public function getPromotedVideo() {
        $q = $this->videoTable->createQuery('v');
        $q->addWhere('v.promoted = 1');
        return $q->fetchOne(array(),Doctrine_Core::HYDRATE_RECORD);
    }
    
     public function getAllNewsOrder($order = "n.created_at DESC",$limit = false) {
        $q = $this->videoTable->createQuery('n');
        $q->addOrderBy($order);
        if($limit)
            $q->limit($limit);
        return $q->execute(array(),Doctrine_Core::HYDRATE_RECORD);
    }
    
    public function getFullVideo($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->videoTable->createQuery('v');
        $q->leftJoin('v.Translation vt');
        $q->andWhere($field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    public function getArticleWithAd($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->videoTable->getNewsQuery();
        if(in_array($field, array('id'))) {
            $q->andWhere('n.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('nt.' . $field . ' = ?', array($id));
            $q->andWhere('nt.lang = ?', 'pl');
        }
        $q->leftJoin('n.Videos v');
        $q->addSelect('v.*');
        $q->addSelect('n.*');
        $q->leftJoin('v.Ad a');
        $q->addWhere('n.publish = 1');
       $q->addWhere('n.date_from <= NOW()');
       $q->addWhere('n.date_to > NOW()');
        return $q->fetchOne(array(), $hydrationMode);
    }
    
   
    
    public function getNew($limit, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->videoTable->getPublishNewsQuery();
        $q = $this->videoTable->getPhotoQuery($q);
        $q = $this->videoTable->getLimitQuery($limit, $q);
        $q->orderBy('n.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }

    public function getVideoPaginationQuery() {
        $q = $this->videoTable->createQuery('v');
	
	$q->select('v.*,vt.*,vr.*');
	$q->leftJoin('v.Translation vt');
	$q->leftJoin('v.VideoRoot vr');
        $q->addOrderBy('v.id DESC');
	
        return $q;
    }
    
    public function getVideoForm(Gallery_Model_Doctrine_Video $video = null) {
         
       
        $form = new Gallery_Form_Video();
        $form->setDefault('publish', 1);
        $form->getElement('url')->setValue($video['VideoRoot']['url']);
        if(null != $video) {
            
            $form->populate($video->toArray());
            if($publishDate = $video->getPublishDate()) {
                $date = new Zend_Date($video->getPublishDate(), 'yyyy-MM-dd HH:mm:ss');
                $form->getElement('publish_date')->setValue($date->toString('dd/MM/yyyy HH:mm'));
            }
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($video->Translation[$language]->name);
                    $i18nSubform->getElement('description')->setValue($video->Translation[$language]->description);
                }
            }
        }
        return $form;
    }
    
    public function saveVideoFromArray($values,$last_user_id,$user_id = null) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$video = $this->videoTable->getProxy($values['id'])) {
            $video = $this->videoTable->getRecord();
        }
       
        if($user_id!= null)
            $values['user_id'] = $user_id;
        
        $values['last_user_id'] = $last_user_id;
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        if(strlen($values['publish_date'])) {
            $date = new Zend_Date($values['publish_date'], 'dd/MM/yyyy HH:mm');
            $values['publish_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($video['publish_date'])) {
            $values['publish_date'] = date('Y-m-d H:i:s');
        }

        $video->fromArray($values);
 
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['name'])) {
                $video->Translation[$language]->name = $values['translations'][$language]['name'];
               
                $video->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Gallery_Model_Doctrine_VideoTranslation', $values['translations'][$language]['name'], $video->getId());
              
                $video->Translation[$language]->description = $values['translations'][$language]['description'];
            }
        }
        
        
        $video->save();
       
       
         
        return $video;
    }
    
    public function removeNews(News_Model_Doctrine_News $video) {
        $video->delete();
    }
     
    public function searchNews($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->videoTable->getAllNewsQuery();
        $q->addSelect('TRIM(at.name) AS search_name, TRIM(at.description) as search_description, "news" as search_type');
        $q->andWhere('at.name LIKE ? OR at.description LIKE ?', array("%$phrase%", "%$phrase%"));
        return $q->execute(array(), $hydrationMode);
    }
    
      public function getLastNews($limit = 4, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->videoTable->getLastNewsQuery();
        $q->leftJoin('n.VideoRoot v');
        $q->addSelect('v.*');
        $q->orderBy('n.publish_date DESC');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
     public function getBreakingNews($hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->videoTable->getBreakingNewsQuery();
        $q->addWhere('n.breaking_news = 1');
        $q->orderBy('n.publish_date DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
     public function getTargetNewsSelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllNewsOrder('n.created_at DESC',50);
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
                $result[$item->id] = $item->Translation[$language]->name;
        }
        return $result;
    } 
    
    
    public function getPopularNews($limit = 4, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->videoTable->getNewsCommentQuery();
//        $q->andWhere('n.publish = 1');
//        $q->addWhere('n.publish_date > NOW()');
        $q->addSelect('count(DISTINCT c.id) as comment_count');
        $q->addWhere('n.created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH)');
        $q->orderBy('n.views DESC, comment_count DESC');
        $q->limit($limit);
        $q->groupBy('n.id');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getLastCategoryOtherArticles($video, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->videoTable->getLastNewsQuery();
        $q->addWhere('n.category_id = ?',$video['category_id']);
        $q->addWhere('n.id != ?',$video['id']);
        $q->leftJoin('n.VideoRoot v');
        $q->orderBy('n.publish_date DESC');
        $q->limit(8);
        return $q->execute(array(), $hydrationMode);
    }
    
     public function getLastCategoryNews($category_id,$limit = null, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->videoTable->getLastNewsQuery();
        $q->addWhere('n.category_id = ?',$category_id);
        $q->leftJoin('n.VideoRoot v');
        $q->addSelect('v.id');
        $q->orderBy('n.publish_date DESC');
        if($limit!=null){
            $q->limit($limit);
        }
        return $q->execute(array(), $hydrationMode);
    }
    
     public function getCategoryNews($id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->videoTable->getNewsCategoryListQuery();
        $q->addWhere('c.id = ?',$id);
        $q->orderBy('n.publish_date DESC');
        return $q->execute(array(),$hydrationMode);
    }
    
    public function getGroupNews($id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->videoTable->getNewsGroupListQuery();
        $q->addWhere('g.id = ?',$id);
        $q->orderBy('n.publish_date DESC');
        return $q->execute(array(),$hydrationMode);
    }
    
    public function getTagNews($id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->videoTable->getNewsTagListQuery();
        $q->addWhere('t.id = ?',$id);
        $q->orderBy('n.publish_date DESC');
        return $q->execute(array(),$hydrationMode);
    }
    
    public function findNews($string, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->videoTable->getNewsTagListQuery();
        $q->where('nt.name like ?',"%".$string."%");
        $q->orWhere('nt.description like ?',"%".$string."%");
        $q->orWhere('t.name like ?',"%".$string."%");
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getStudentNews($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->videoTable->getNewsStudentListQuery();
        $q->orderBy('n.publish_date DESC');
        return $q->execute(array(),$hydrationMode);
    }
    
    
    public function getAllNewsForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->videoTable->getPublishNewsQuery();
      //  $q = $this->videoTable->getPhotoQuery($q);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllNewsCategoriesForSiteMap() {
        $hydrationMode = Doctrine_Core::HYDRATE_SCALAR;
        
        $q = $this->videoTable->createQuery('n');
        $q->select('n.id');
        $q->leftJoin('n.Category c');
        $q->addSelect('count(c.id) as cnt');
        $q->addSelect('c.slug');
        $q->groupBy('c.id');
        $result = $q->execute(array(), $hydrationMode);
        
        $categories = array();
        
        foreach($result as $category):
            $categories[$category['c_slug']] = $category['c_cnt'];
        endforeach;
        
        return $categories;
    }
    
    public function getAllNewsGroupsForSiteMap() {
        $hydrationMode = Doctrine_Core::HYDRATE_SCALAR;
        
        $q = $this->videoTable->createQuery('n');
        $q->select('n.id');
        $q->leftJoin('n.Group g');
        $q->addSelect('count(g.id) as cnt');
        $q->addSelect('g.slug');
        $q->groupBy('g.id');
        $result = $q->execute(array(), $hydrationMode);
        
        $groups = array();
        
        foreach($result as $group):
            $groups[$group['g_slug']] = $group['g_cnt'];
        endforeach;
        
        return $groups;
    }
    
}

