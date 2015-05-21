<?php

/**
 * Banner_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Banner_IndexController extends MF_Controller_Action {
 
   public function listBannerAction(){
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        if(!$banners = $bannerService->getAllBanners()) {
            throw new Zend_Controller_Action_Exception('Banners not found ');
        }
        
        
        $this->view->assign('banners', $banners);
        $this->_helper->actionStack('layout-serwis10', 'index', 'default');
        
    }
    public function bannerRightAction(){
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $rightBanners = $bannerService->getPositionBanners('Sidebar1');
        $this->_helper->viewRenderer->setResponseSegment('bannerRight');
        $this->view->assign('rightBanners', $rightBanners);
    }
    
    public function bannerOverNewsAction(){
        
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getPositionBanners('OverNews');
        
        
        $this->view->assign('banners', $banners);
    }
    
    
}

