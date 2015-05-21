<?php

/**
 * Gallery_IndexController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Gallery_IndexController extends MF_Controller_Action {
    
    protected static $galleryItemCountPerPage = 12;
    protected static $videoItemCountPerPage = 4;
    
    public function indexAction() {
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

        if(!$gallery = $galleryService->getI18nGallery($this->getRequest()->getParam('slug'), 'slug', $this->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        $photoDimension = $photoDimensionService->getElementDimension('gallery');
        
        $metatagService->setViewMetatags($gallery['metatag_id'], $this->view);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('hideSlider', true);
        $this->view->assign('gallery', $gallery);
        $this->view->assign('photoDimension', $photoDimension);
    }
    
    public function listGalleryAction() {
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $videoService = $this->_service->getService('Gallery_Service_Video');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

	$query = $galleryService->getGalleryPaginationQuery();

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(self::$galleryItemCountPerPage);
        
        $this->view->assign('paginator', $paginator);
        
        $video_query = $videoService->getVideoPaginationQuery();

        $video_adapter = new MF_Paginator_Adapter_Doctrine($video_query, Doctrine_Core::HYDRATE_ARRAY);
        $video_paginator = new Zend_Paginator($video_adapter);
        $video_paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $video_paginator->setItemCountPerPage(self::$videoItemCountPerPage);
        
        $this->view->assign('video_paginator', $video_paginator);
        $this->view->assign('hideSlider', true);
	
//        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function showVideoAction() {
        $videoService = $this->_service->getService('Gallery_Service_Video');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

        if(!$video = $videoService->getFullVideo($this->getRequest()->getParam('slug'), 'vt.slug', Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Video not found');
        }
        
        
        $metatagService->setViewMetatags($video['metatag_id'], $this->view);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('video', $video);
        $this->view->assign('hideSlider', true);
    }
}

