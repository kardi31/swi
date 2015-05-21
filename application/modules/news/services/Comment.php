<?php

/**
 * News_Service_News
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_Service_Comment extends MF_Service_ServiceAbstract{
    
    protected $commentTable;
    
    public function init() {
        $this->commentTable = Doctrine_Core::getTable('News_Model_Doctrine_Comment');
    }
    
    public function getAllComments($countOnly = false) {
        if(true == $countOnly) {
            return $this->commentTable->count();
        } else {
            return $this->commentTable->findAll();
        }
    }
    
    public function getComment($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->commentTable->findOneBy($field, $id, $hydrationMode);
    }
   
    
    public function getCommentForm(News_Model_Doctrine_Comment $comment = null) {
         
       
        $form = new News_Form_Comment();
        if($comment!=null)
            $form->populate($comment->toArray());
        
        return $form;
    }
    
    public function saveCommentFromArray($values) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$comment = $this->commentTable->getProxy($values['id'])) {
            $comment = $this->commentTable->getRecord();
        }
       
        $comment->slug = MF_Text::createUniqueTableSlug('News_Model_Doctrine_Comment', $values['title'], $comment->getId());
              
        
        $comment->fromArray($values);
 
        $comment->save();
       
        return $comment;
    }
    
    public function removeComment(News_Model_Doctrine_Comment $comment) {
        $comment->delete();
    }
    
    public function prependCommentOptions() {
       
       $options = array('' => '');
       $categories = $this->getAllCategories();
       
       foreach($categories as $comment):
           $options[$comment['id']] = $comment['title'];
       endforeach;
       
       return $options;
    }
     
    public function getNewsComments($news_id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->commentTable->createQuery('c');
        $q->addWhere('c.news_id = ?',$news_id);
	$q->addWhere('c.active = 1');
        $q->orderBy('c.id DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function countNewsComments($news_id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->commentTable->createQuery('c');
        $q->addSelect('count(c.id) as comment_count');
        $q->addWhere('c.news_id = ?',$news_id);
	$q->addWhere('c.active = 1');
        $q->groupBy('c.news_id');
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function addComment($values) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
            
        $comment = $this->commentTable->getRecord();
        $values['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $comment->fromArray($values);
 
        $comment->save();
       
        return $comment;
    }
   
}

