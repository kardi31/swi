<?php

/**
 * Banner
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Banner_Service_BannerRight extends MF_Service_ServiceAbstract {
    
    protected $bannerRightTable;
    
    public function init() {
        $this->bannerRightTable = Doctrine_Core::getTable('Banner_Model_Doctrine_BannerRight');
    }
    
    public function getBannerRight($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->bannerRightTable->findOneBy($field, $id, $hydrationMode);
    }

    public function getUserFullBannerRight($bannerId, $userId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->bannerRightTable->getBannerQuery();
        $q->andWhere('b.id = ?', $bannerId);
        $q->andWhere('b.user_id = ?', $userId);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getUserBannerRightQuery($userId) {
        $q = $this->bannerRightTable->getBannerQuery();
        $q->andWhere('b.user_id = ?', $userId);
        $q->addOrderBy('b.created_at DESC');
        return $q;
    }
    
    public function fetchBanners($limit = 10, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->bannerRightTable->getBannerQuery();
        $q->limit($limit);
        $banners = $q->execute(array(), $hydrationMode);
        $this->incrementViews($banners);
        return $banners;
    }
    
    public function getBannerForm(Banner_Model_Doctrine_BannerRight $bannerRight = null) {
        $form = new Banner_Form_Banner();
        if(null != $bannerRight) {
            $form->populate($bannerRight->toArray());
        }
        return $form;
    }
    
    public function saveBannerRightFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$bannerRight = $this->getBannerRight((int) $values['id'])) {
            $bannerRight = $this->bannerRightTable->getRecord();
        }
        
        if(strlen($values['start_display_date'])) {
            $date = new Zend_Date($values['start_display_date'], 'dd/MM/yyyy HH:mm');
            $values['start_display_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($bannerRight['start_display_date'])) {
            $values['start_display_date'] = date('Y-m-d H:i:s');
        }
        
        if(strlen($values['end_display_date'])) {
            $date = new Zend_Date($values['end_display_date'], 'dd/MM/yyyy HH:mm');
            $values['end_display_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($bannerRight['end_display_date'])) {
            $values['end_display_date'] = date('Y-m-d H:i:s');
        }
        
        $bannerRight->fromArray($values);
        $bannerRight->save();
        
        if(isset($values['parent_id'])) {
            $parent = $this->getBannerRight((int) $values['parent_id']);
            $bannerRight->getNode()->insertAsLastChildOf($parent);
        }
        
        return $bannerRight;
    }
    
    public function getBannerRightTree() {
        return $this->bannerRightTable->getTree();
    }
    
    public function getBannerRightRoot() {
        return $this->getBannerRightTree()->fetchRoot();
    }
    
    public function createBannerRightRoot() {
        $bannerRight = $this->bannerRightTable->getRecord();
        $bannerRight->save();
        $tree = $this->getBannerRightTree();
        $tree->createRoot($bannerRight);
        return $bannerRight;
    }   
    
    public function moveBannerRight($bannerRight, $mode) {
        switch($mode) {
            case 'up':
                $prevSibling = $bannerRight->getNode()->getPrevSibling();
                if($prevSibling->getNode()->isRoot()) {
                    throw new Exception('Cannot move category on root level');
                }
                $bannerRight->getNode()->moveAsPrevSiblingOf($prevSibling);
                break;
            case 'down':
                $nextSibling = $bannerRight->getNode()->getNextSibling();
                if($nextSibling->getNode()->isRoot()) {
                    throw new Exception('Cannot move category on root level');
                }
                $bannerRight->getNode()->moveAsNextSiblingOf($nextSibling);
                break;
        }
    }
    
    public function removeBannerRight(Banner_Model_Doctrine_BannerRight $bannerRight) {
        $bannerRight->getNode()->delete();
        $bannerRight->delete();
    }
    
    public function incrementViews($banners) {
        if($banners instanceof Doctrine_Collection) {
            foreach($banners as $banner) {
               
                $banner['views'] = is_numeric($banner['views']) ? $banner['views'] + 1 : 1;
                $banner->save();
            }
        } elseif($banners instanceof Doctrine_Record) {
            $banners['views'] = is_numeric($banners['views']) ? $banners['views'] + 1 : 1;
            $banners->save();
        }
    }
    
    public function incrementClicks($banners) {
        if($banners instanceof Doctrine_Collection) {
            foreach($banners as $banner) {
                $banner['clicks'] = is_numeric($banner['clicks']) ? $banner['clicks'] + 1 : 1;
                $banner->save();
            }
        } elseif($banners instanceof Doctrine_Record) {
            $banners['clicks'] = is_numeric($banners['clicks']) ? $banners['clicks'] + 1 : 1;
            $banners->save();
        }
    }
}

