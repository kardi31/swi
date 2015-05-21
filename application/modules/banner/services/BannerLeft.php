<?php

/**
 * Banner
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Banner_Service_BannerLeft extends MF_Service_ServiceAbstract {
    
    protected $bannerLeftTable;
    
    public function init() {
        $this->bannerLeftTable = Doctrine_Core::getTable('Banner_Model_Doctrine_BannerLeft');
    }
    
    public function getBannerLeft($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->bannerLeftTable->findOneBy($field, $id, $hydrationMode);
    }

    public function getUserFullBannerLeft($bannerId, $userId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->bannerLeftTable->getBannerQuery();
        $q->andWhere('b.id = ?', $bannerId);
        $q->andWhere('b.user_id = ?', $userId);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getUserBannerLeftQuery($userId) {
        $q = $this->bannerLeftTable->getBannerQuery();
        $q->andWhere('b.user_id = ?', $userId);
        $q->addOrderBy('b.created_at DESC');
        return $q;
    }
    
    public function fetchBanners($limit = 10, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->bannerLeftTable->getBannerQuery();
        $q->limit($limit);
        $banners = $q->execute(array(), $hydrationMode);
        $this->incrementViews($banners);
        return $banners;
    }
    
    public function getBannerForm(Banner_Model_Doctrine_BannerLeft $bannerLeft = null) {
        $form = new Banner_Form_Banner();
        if(null != $bannerLeft) {
            $form->populate($bannerLeft->toArray());
        }
        return $form;
    }
    
    public function saveBannerLeftFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$bannerLeft = $this->getBannerLeft((int) $values['id'])) {
            $bannerLeft = $this->bannerLeftTable->getRecord();
        }
        
        if(strlen($values['start_display_date'])) {
            $date = new Zend_Date($values['start_display_date'], 'dd/MM/yyyy HH:mm');
            $values['start_display_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($bannerLeft['start_display_date'])) {
            $values['start_display_date'] = date('Y-m-d H:i:s');
        }
        
        if(strlen($values['end_display_date'])) {
            $date = new Zend_Date($values['end_display_date'], 'dd/MM/yyyy HH:mm');
            $values['end_display_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($bannerLeft['end_display_date'])) {
            $values['end_display_date'] = date('Y-m-d H:i:s');
        }
        
        $bannerLeft->fromArray($values);
        $bannerLeft->save();
        
        if(isset($values['parent_id'])) {
            $parent = $this->getBannerLeft((int) $values['parent_id']);
            $bannerLeft->getNode()->insertAsLastChildOf($parent);
        }
        
        return $bannerLeft;
    }
    
    public function getBannerLeftTree() {
        return $this->bannerLeftTable->getTree();
    }
    
    public function getBannerLeftRoot() {
        return $this->getBannerLeftTree()->fetchRoot();
    }
    
    public function createBannerLeftRoot() {
        $bannerLeft = $this->bannerLeftTable->getRecord();
        $bannerLeft->save();
        $tree = $this->getBannerLeftTree();
        $tree->createRoot($bannerLeft);
        return $bannerLeft;
    }   
    
    public function moveBannerLeft($bannerLeft, $mode) {
        switch($mode) {
            case 'up':
                $prevSibling = $bannerLeft->getNode()->getPrevSibling();
                if($prevSibling->getNode()->isRoot()) {
                    throw new Exception('Cannot move category on root level');
                }
                $bannerLeft->getNode()->moveAsPrevSiblingOf($prevSibling);
                break;
            case 'down':
                $nextSibling = $bannerLeft->getNode()->getNextSibling();
                if($nextSibling->getNode()->isRoot()) {
                    throw new Exception('Cannot move category on root level');
                }
                $bannerLeft->getNode()->moveAsNextSiblingOf($nextSibling);
                break;
        }
    }
    
    public function removeBannerLeft(Banner_Model_Doctrine_BannerLeft $bannerLeft) {
        $bannerLeft->getNode()->delete();
        $bannerLeft->delete();
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

