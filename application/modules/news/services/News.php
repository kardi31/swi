<?php

/**
 * News_Service_News
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_Service_News extends MF_Service_ServiceAbstract{
    
    protected $newsTable;
    public static $articleItemCountPerPage = 12;
    
    public static function getArticleItemCountPerPage(){
        return self::$articleItemCountPerPage;
    }
    public function init() {
        $this->newsTable = Doctrine_Core::getTable('News_Model_Doctrine_News');
    }
    
    public function getAllNews($countOnly = false) {
        if(true == $countOnly) {
            return $this->newsTable->count();
        } else {
            return $this->newsTable->findAll();
        }
    }
    
    public function getAllArticles($countOnly = false) {
        if(true == $countOnly) {
            return $this->newsTable->count();
        } else {
            return $this->newsTable->findAll();
        }
    }
    
    public function getNews($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->newsTable->findOneBy($field, $id, $hydrationMode);
    }
  
    public function getAllNewStudentNews() {
        $q = $this->newsTable->getPublishNewsQuery();
        $q->addWhere('n.student = 1');
        $q->addWhere('n.student_accept = 0');
        return $q->count();
    }
    
     public function getAllNewsOrder($order = "n.created_at DESC",$limit = false) {
        $q = $this->newsTable->createQuery('n');
        $q->addOrderBy($order);
        if($limit)
            $q->limit($limit);
        return $q->execute(array(),Doctrine_Core::HYDRATE_RECORD);
    }
    
    public function getFullArticle($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->newsTable->getShowNewsQuery();
        if(in_array($field, array('id'))) {
            $q->andWhere('n.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('nt.' . $field . ' = ?', array($id));
            $q->andWhere('nt.lang = ?', 'pl');
        }
        return $q->fetchOne(array(), $hydrationMode);
    }
    public function getArticleWithAd($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->newsTable->getNewsQuery();
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
        $q = $this->newsTable->getPublishNewsQuery();
        $q = $this->newsTable->getPhotoQuery($q);
        $q = $this->newsTable->getLimitQuery($limit, $q);
        $q->orderBy('n.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }

    public function getNewsPaginationQuery($language) {
        $q = $this->newsTable->getPublishNewsQuery();
       // $q = $this->newsTable->getPhotoQuery($q);
        $q->andWhere('nt.lang = ?', $language);
        $q->addOrderBy('n.publish_date DESC');
        return $q;
    }
    
    public function getCategoryPaginationQuery($category_id,$language) {
        $q = $this->newsTable->getNewsCategoryQuery();
       // $q = $this->newsTable->getPhotoQuery($q);
        $q->andWhere('nt.lang = ?', $language);
        $q->addWhere('c.id = ?',$category_id);
        $q->addOrderBy('n.publish_date DESC');
        
        return $q;
    }
    
    public function getGroupPaginationQuery($group_id,$language) {
        $q = $this->newsTable->getNewsGroupListQuery();
       // $q = $this->newsTable->getPhotoQuery($q);
        $q->andWhere('nt.lang = ?', $language);
        $q->addWhere('g.id = ?',$group_id);
        $q->addOrderBy('n.publish_date DESC');
        
        return $q;
    }
    
    public function getVideoForm(Media_Model_Doctrine_VideoUrl $video = null) {
         
       
        $form = new News_Form_Video();
        
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
    public function getNewsForm(News_Model_Doctrine_News $news = null) {
         
       
        $form = new News_Form_News();
        $form->setDefault('publish', 1);
        
        if(null != $news) {
            
            $form->populate($news->toArray());
            if($publishDate = $news->getPublishDate()) {
                $date = new Zend_Date($news->getPublishDate(), 'yyyy-MM-dd HH:mm:ss');
                $form->getElement('publish_date')->setValue($date->toString('dd/MM/yyyy HH:mm'));
            }
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('title')->setValue($news->Translation[$language]->title);
                    $i18nSubform->getElement('content')->setValue($news->Translation[$language]->content);
                }
            }
        }
        if($news->gallery):
            $form->removeElement('gallery');
        endif;
        return $form;
    }
    
    public function saveNewsFromArray($values,$last_user_id,$user_id = null) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$news = $this->newsTable->getProxy($values['id'])) {
            $news = $this->newsTable->getRecord();
        }
       
        if($user_id!= null)
            $values['user_id'] = $user_id;
        
        $values['last_user_id'] = $last_user_id;
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        if(strlen($values['publish_date'])) {
            $date = new Zend_Date($values['publish_date'], 'dd/MM/yyyy HH:mm');
            $values['publish_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($news['publish_date'])) {
            $values['publish_date'] = date('Y-m-d H:i:s');
        }

        $news->fromArray($values);
 
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['title'])) {
                $news->Translation[$language]->title = $values['translations'][$language]['title'];
               
                $news->Translation[$language]->slug = MF_Text::createUniqueTableSlug('News_Model_Doctrine_NewsTranslation', $values['translations'][$language]['title'], $news->getId());
              
                $news->Translation[$language]->content = $values['translations'][$language]['content'];
            }
        }
        
        $news->unlink('Tags');
        foreach($values['tag_id'] as $tag_id):
            $news->link('Tags',$tag_id);
        endforeach;
        
        $news->save();
       
       
         
        return $news;
    }
    
    public function removeNews(News_Model_Doctrine_News $news) {
        $news->delete();
    }
     
    public function searchNews($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->newsTable->getAllNewsQuery();
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "news" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getLastNews($limit = 4, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->newsTable->getLastNewsQuery();
        $q->leftJoin('n.VideoRoot v');
        $q->addSelect('v.*');
        $q->orderBy('n.publish_date DESC');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
     public function getBreakingNews($hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->newsTable->getBreakingNewsQuery();
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
                $result[$item->id] = $item->Translation[$language]->title;
        }
        return $result;
    } 
    
    
    public function getPopularNews($limit = 4, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->newsTable->getNewsCommentQuery();
//        $q->andWhere('n.publish = 1');
//        $q->addWhere('n.publish_date > NOW()');
        $q->addSelect('count(DISTINCT c.id) as comment_count');
        $q->addWhere('n.created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH)');
        $q->orderBy('n.views DESC, comment_count DESC');
        $q->limit($limit);
        $q->groupBy('n.id');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getLastCategoryOtherArticles($news, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->newsTable->getLastNewsQuery();
        $q->addWhere('n.category_id = ?',$news['category_id']);
        $q->addWhere('n.id != ?',$news['id']);
        $q->leftJoin('n.VideoRoot v');
        $q->orderBy('n.publish_date DESC');
        $q->limit(8);
        return $q->execute(array(), $hydrationMode);
    }
    
     public function getLastCategoryNews($category_id,$limit = null, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->newsTable->getLastNewsQuery();
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
        $q = $this->newsTable->getNewsCategoryListQuery();
        $q->addWhere('c.id = ?',$id);
        $q->orderBy('n.publish_date DESC');
        return $q->execute(array(),$hydrationMode);
    }
    
    public function getGroupNews($id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->newsTable->getNewsGroupListQuery();
        $q->addWhere('g.id = ?',$id);
        $q->orderBy('n.publish_date DESC');
        return $q->execute(array(),$hydrationMode);
    }
    
    public function getTagNews($id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->newsTable->getNewsTagListQuery();
        $q->addWhere('t.id = ?',$id);
        $q->orderBy('n.publish_date DESC');
        return $q->execute(array(),$hydrationMode);
    }
    
    public function findNews($string, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->newsTable->getNewsTagListQuery();
        $q->where('nt.title like ?',"%".$string."%");
        $q->orWhere('nt.content like ?',"%".$string."%");
        $q->orWhere('t.title like ?',"%".$string."%");
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getStudentNews($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->newsTable->getNewsStudentListQuery();
        $q->orderBy('n.publish_date DESC');
        return $q->execute(array(),$hydrationMode);
    }
    
    
    public function getAllNewsForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->newsTable->getPublishNewsQuery();
      //  $q = $this->newsTable->getPhotoQuery($q);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllNewsCategoriesForSiteMap() {
        $hydrationMode = Doctrine_Core::HYDRATE_SCALAR;
        
        $q = $this->newsTable->createQuery('n');
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
        
        $q = $this->newsTable->createQuery('n');
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

