<?php

/**
 * Product_Service_Comment
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Service_Comment extends MF_Service_ServiceAbstract {
    
    protected $commentTable;
    
    public function init() {
        $this->commentTable = Doctrine_Core::getTable('Product_Model_Doctrine_Comment');
        parent::init();
    }
    
    public function getComment($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->commentTable->findOneBy($field, $id, $hydrationMode);
    }
   
    public function getCommentForm(Product_Model_Doctrine_Comment $comment = null) {
        $form = new Product_Form_Comment();
        if(null != $comment) { 
            $form->populate($comment->toArray());
        }
        return $form;
    }
    
    public function saveCommentFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$comment = $this->getComment((int) $values['id'])) {
            $comment = $this->commentTable->getRecord();
        }
         
        $comment->fromArray($values);
        $comment->save();

        return $comment;
    }
    
    public function removeComment(Product_Model_Doctrine_Comment $comment) {
        $comment->delete();
    }
    
    public function refreshStatusComment($comment){
        if ($comment->isStatus()):
            $comment->setStatus(0);
        else:
            $comment->setStatus(1);
        endif;
        $comment->save();
    }
    
    public function refreshStatusModeration($comment){
        if (!$comment->isModeration()):
            $comment->setModeration(1);
        endif;
        $comment->save();
    }
    
    public function getAllComments($countOnly = false) {
        if(true == $countOnly) {
            return $this->commentTable->count();
        } else {
            return $this->commentTable->findAll();
        }
    }
    
    public function getNewComments($date, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->commentTable->getCommentQuery();
        $q->andWhere('co.created_at > ?', $date);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getPublishComments($limit, $productId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->commentTable->getPublishCommentQuery();
        $q->andWhere('pro.id = ?', $productId);
        $q->andWhere('co.description IS NOT NULL');
        $q->addOrderBy('co.created_at DESC');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getUserComments($userId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->commentTable->getPublishCommentWithProductsQuery();
        $q->andWhere('co.user_id = ?', $userId);
        $q->addOrderBy('co.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
//    public function saveRateProduct($product, $rate, $userId){
//        $productRate = $this->productRateTable->getRecord();
//        $productRate->setProductId($product->getId());
//        $productRate->setRate($rate);
//        $productRate->setUserId($userId);
//        $productRate->save();
//        
//        $productRates = $product->get('ProductRates')->toArray();
//        $counter = 0;
//        foreach($productRates as $rate):
//            $sum += $rate['rate'];
//            $counter++;
//        endforeach;
//        $rateAvarage = round($sum/$counter,1);
//        $product->setRate($rateAvarage);
//        $product->save();
//    }
//    
//    public function findRate($product, $user){
//        $q = $this->productRateTable->getProductRateQuery();
//        $q->andWhere('r.product_id = ?', $product->getId());
//        $q->andWhere('r.user_id = ?', $user['id']);
//        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_RECORD);
//    }
}
?>