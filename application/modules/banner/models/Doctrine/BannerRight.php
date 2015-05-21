<?php

/**
 * Banner_Model_Doctrine_BannerRight
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage Banner
 * @author     Andrzej Wilczyński <and.wilczynski@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Banner_Model_Doctrine_BannerRight extends Banner_Model_Doctrine_BaseBannerRight
{   
    public static $bannerPhotoDimensions = array(
        '126x126' => 'Photo in admin panel', // admin
        '218x' => 'Side banner'
    );
    
    public static function getBannerPhotoDimensions() {
        return self::$bannerPhotoDimensions;
    }
    
    public function setId($id) {
        $this->_set('id', $id);
    }
    
    public function getId() {
        return $this->_get('id');
    }
    
    public function setName($name) {
        $this->_set('name', $name);
    }
    
    public function getName() {
        return $this->_get('name');
    }
    
    public function setHref($href) {
        $this->_set('href', $href);
    }
    
    public function getHref() {
        return $this->_get('href');
    }
    
    public function setUp() {
        $this->hasOne('Media_Model_Doctrine_Photo as PhotoRoot', array(
            'local' => 'photo_root_id',
            'foreign' => 'id'
        ));
        parent::setUp();
    }
}