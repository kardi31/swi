<?php

/**
 * News_Model_Doctrine_Stream
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage News
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class News_Model_Doctrine_Stream extends News_Model_Doctrine_BaseStream
{
   
    
     public function setId($id) {
        $this->_set('id', $id);
    }
    
     public function increaseView() {
        $this->views++;
        $this->save();
    }
    
    public function getId() {
        return $this->_get('id');
    }
    
    public function setPublish($publish = true) {
        $this->_set('publish', $publish);
    }
    
    public function isPublish() {
        return $this->_get('publish');
    }
    
    public function setPublishDate($publishDate) {
        $this->_set('publish_date', $publishDate);
    }
    public function setCreated($value) {
        $this->_set('created_at', $value);
    }
    
    public function getPublishDate() {
        return $this->_get('publish_date');
    }
    
    public function getMetatagId() {
        return $this->_get('metatag_id');    
    }
    
    
    public function setUp() {
        parent::setUp();
        $this->hasOne('Default_Model_Doctrine_Metatag as Metatags', array(
            'local' => 'metatag_id',
            'foregin' => 'id'
        ));
  
    }
}