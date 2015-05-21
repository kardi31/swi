<?php

/**
 * Gallery_AdminController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Gallery_AdminController extends MF_Controller_Action {
    
    public function listGalleryAction() {
        
    }
    
    public function listGalleryDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
                
        $table = Doctrine_Core::getTable('Gallery_Model_Doctrine_Gallery');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table,
            'class' => 'Gallery_DataTables_Gallery', 
            'columns' => array('t.name'),
            'searchFields' => array('t.name')
        ));
        
       
        $results = $dataTables->getResult();
        
        $language = $i18nService->getAdminLanguage();
        
        $rows = array();
    
        
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            $row[] = $result->Translation[$language->getId()]->name;
            
            $options = '<a href="' . $this->view->adminUrl('edit-gallery', 'gallery', array('id' => $result->id)) . '" class="edit-item"><span class="icon24 entypo-icon-settings"></span></a>';
            $options .= '<a href="' . $this->view->adminUrl('delete-gallery', 'gallery', array('id' => $result->id)) . '" class="delete-item"><span class="icon24 icon-remove"></span></a>';
            $row[] = $options;
        
            $rows[] = $row;
        }
        
        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );

        $this->_helper->json($response);
    }
    
    public function addGalleryAction() {
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $form = $galleryService->getGalleryForm();
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        
        $languages = $i18nService->getLanguageList();
        
        $user = $this->_helper->user();

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $values['user_id'] = $user->getId();
                    $gallery = $galleryService->saveGalleryFromArray($values);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-gallery', 'gallery', array('id' => $gallery->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-gallery', 'gallery'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
    }
    
    public function editGalleryAction() {
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        if(!$gallery = $galleryService->getGallery((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        $form = $galleryService->getGalleryForm($gallery);
        $metatagsForm = $metatagService->getMetatagsSubForm($gallery->get('Metatag'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        $languages = $i18nService->getLanguageList();
        
        $user = $this->_helper->user();

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    if($metatags = $metatagService->saveMetatagsFromArray($gallery->get('Metatag'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $values['user_id'] = $user->getId();
                    
                    $gallery = $galleryService->saveGalleryFromArray($values);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-gallery', 'gallery', array('id' => $gallery->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-gallery', 'gallery'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        //$this->view->admincontainer->findOneBy('id', 'editgallery')->setLabel($translator->translate($this->view->admincontainer->findOneBy('id', 'editgallery')->getLabel(), $adminLanguage->getId()) . ' ' . $translator->translate($types[$gallery->getType()], $adminLanguage->getId()));
        
        $this->view->assign('languages', $languages);
        $this->view->assign('gallery', $gallery);
        $this->view->assign('form', $form);
    }
    
    public function deleteGalleryAction() {
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        
        if(!$gallery = $galleryService->getGallery($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found', 404);
        }

        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
            
            $gallery->get('Metatag')->delete();
            $galleryService->removeGallery($gallery);
           
            
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            echo $this->_service->get('log')->log($e->getMessage(), 4);
        }      
       // $this->_helper->viewRenderer->setNoRender();
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-gallery', 'gallery'));
    }
    
    public function addGalleryPhotoAction() {
         $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
  
        
        if(!$gallery = $galleryService->getGallery((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        if(!array_key_exists('domain', $options)) {
            throw new Zend_Controller_Action_Exception('Domain string not set');
        }
        
        $hrefs = $this->getRequest()->getParam('hrefs');

        if(is_array($hrefs) && count($hrefs)) {
            foreach($hrefs as $href) {
                $path = str_replace("http://" . $options['domain'], "", urldecode($href));
                $filePath = $options['publicDir'] . $path;
                if(file_exists($filePath)) {
                    $pathinfo = pathinfo($filePath);
                    $slug = MF_Text::createSlug($pathinfo['basename']);
                    $name = MF_Text::createUniqueFilename($slug, $photoService->photosDir);
                    try {
                        $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                        $root = $gallery->get('PhotoRoot');
                        if($root->isInProxyState()) {
                            $root = $photoService->createPhotoRoot();
                            $gallery->set('PhotoRoot', $root);
                            $gallery->save();
                        }

                       $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Gallery_Model_Doctrine_Gallery::getGalleryPhotoDimensions()), $root, true);

                       $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('Logger')->log($e->getMessage(), 4);
                    }
                }
            }
        }
        $list = '';
        
        $root = $gallery->get('PhotoRoot');
        $root->refresh();
        if(!$root->isInProxyState()) {
            $galleryPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/gallery-photos.phtml', 'product', array('photos' => $galleryPhotos, 'gallery' => $gallery));
        }
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $gallery->getId()
        ));
    }
    
    public function moveGalleryPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        
        if(!$gallery = $galleryService->getGallery($this->getRequest()->getParam('gallery'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Gallery photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        
        $list = '';
        
        $root = $gallery->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $galleryPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/gallery-photos.phtml', 'gallery', array('photos' => $galleryPhotos, 'gallery' => $gallery));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $gallery->getId()
        ));
    }
    
    public function changePhotoNameAction() {   
        $photoService = $this->_service->getService('Media_Service_Photo');
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        
        if(!$gallery = $galleryService->getGallery((int) $this->getRequest()->getParam('gallery-id'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        
        if(!$photo = $photoService->getPhoto($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo not found');
        }
        
        $photo->setTitle($this->getRequest()->getParam('name'));
        $photo->save();
        
        
        $list = '';
        
        $root = $gallery->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $galleryPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/gallery-photos.phtml', 'gallery', array('photos' => $galleryPhotos, 'gallery' => $gallery));
        }   
        
         $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $photo->getId()
        ));
    }
    
    public function removeGalleryPhotoAction() {   
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$gallery = $galleryService->getGallery((int) $this->getRequest()->getParam('gallery-id'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        if(!$photo = $photoService->getPhoto($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }
        
        try {
            $photoService->removePhoto($photo);
            
        } catch(Exception $e) {
            $this->_service->get('Logger')->log($e->getMessage(), 4);
        }
              
        $list = '';
        
        $root = $gallery->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $galleryPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/gallery-photos.phtml', 'gallery', array('photos' => $galleryPhotos, 'gallery' => $gallery));
        }   
        
         $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $photo->getId()
        ));
    }
    
    public function listVideoAction() {}
    public function listVideoDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Gallery_Model_Doctrine_Video');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Gallery_DataTables_Video', 
            'columns' => array('x.id','xt.title','x.publish_date'),
            'searchFields' => array('x.id','xt.title','x.publish_date')
        ));
        
        $results = $dataTables->getResult();
        $language = $i18nService->getAdminLanguage();

        $rows = array();
        foreach($results as $result) {
            
            $row = array();
            $row[] = $result->id;
            $row[] = $result->Translation[$language->getId()]->name;
	    
	     if($result->promoted)
                    $row[] = '<a href="' . $this->view->adminUrl('set-promoted-video', 'gallery', array('id' => $result->id)) . '" title="' . $this->view->translate('Remove promoted video') . '"><span class="icon16 icomoon-icon-checkbox-2"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
              else
                  $row[] = '<a href="' . $this->view->adminUrl('set-promoted-video', 'gallery', array('id' => $result->id)) . '" title="' . $this->view->translate('Promote video') . '"><span class="icon16 icomoon-icon-checkbox-unchecked"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            
	    
           $row[] = MF_Text::timeFormat($result['created_at'], 'd/m/Y H:i'). "<br />".$result['UserCreated']['last_name']. " ".$result['UserCreated']['first_name'];
            $row[] = MF_Text::timeFormat($result['updated_at'], 'd/m/Y H:i'). "<br /> ".$result['UserUpdated']['last_name']. " ".$result['UserUpdated']['first_name'];
           
            $row[] = MF_Text::timeFormat($result->publish_date, 'd/m/Y H:i');
            
	    
	    
	    $options = '<a href="' . $this->view->adminUrl('edit-video', 'gallery', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;';
	    $options .= '<a href="' . $this->view->adminUrl('remove-video', 'gallery', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            
            
             $row[] = $options;
            $rows[] = $row;
        }

         $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );

        $this->_helper->json($response);
        
    }
    
    public function addVideoAction() {
        $videoService = $this->_service->getService('Gallery_Service_Video');
        $i18nService = $this->_service->getService('Default_Service_I18n');
	
	$user = $this->user;
	
        $videoUrlService = $this->_service->getService('Media_Service_VideoUrl');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $videoService->getVideoForm();
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    $video = $videoService->saveVideoFromArray($values,0);//,$user->getId(),$user->getId());
                    
                    if(!$video->photo_root_id){
                        $photoRoot = $photoService->createPhotoRoot();
                        $video->set('PhotoRoot',$photoRoot);
                        $video->save();
                    }
		    
		    if(!$video->video_root_id){
			$videoRoot = $videoUrlService->createVideoRoot();
			$video->set('VideoRoot',$videoRoot);
			$video->save();
		    }
		    
                    $videoUrlService->createVideoFromUpload($values, $videoRoot);
                    
		    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-video', 'gallery'));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
    }
    
    public function editVideoAction() {
        $videoService = $this->_service->getService('Gallery_Service_Video');
	
        $videoUrlService = $this->_service->getService('Media_Service_VideoUrl');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $user = $this->user;
	
        $translator = $this->_service->get('translate');
        
        if(!$video = $videoService->getVideo($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Video not found');
        }
        
  
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $videoService->getVideoForm($video);
//        $form->getElement('category_id')->addMultiOptions($categoryService->prependCategoryOptions());
//        $form->getElement('category_id')->setValue($video['category_id']);
//        $form->getElement('group_id')->addMultiOptions($groupService->prependGroupOptions());
//        $form->getElement('group_id')->setValue($video['group_id']);
//        $form->getElement('tag_id')->addMultiOptions($tagService->prependTagOptions());
//        $form->getElement('tag_id')->setValue($video->get('Tags')->getPrimaryKeys());
        
        
        $metatagsForm = $metatagService->getMetatagsSubForm($video->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
//        if(!$video->photo_root_id){
//            $photoRoot = $photoService->createPhotoRoot();
//            $video->set('PhotoRoot',$photoRoot);
//            $video->save();
//        }
        
//        if(!$video->video_root_id){
//            $videoRoot = $videoService->createVideoRoot();
//            $video->set('VideoRoot',$videoRoot);
//            $video->save();
//        }
//        if(!$video = $videoService->getVideo($video->video_root_id)) {
//            throw new Zend_Controller_Action_Exception('Video not found');
//        }
//        $videoForm = $videoService->getVideoForm($video);
//        $videoForm->getElement('ad_id')->addMultiOptions($adService->prependAds());
//        $videoForm->getElement('ad_id')->setValue($video->ad_id);
//        $videoForm->removeElement('date_from');
//        $videoForm->removeElement('date_to');
//        $this->view->assign('videoForm',$videoForm);
        
        
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {//$videoForm->isValid($this->getRequest()->getParams())
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    $video = $videoService->saveVideoFromArray($values,0);//,$user->getId());
                    
//                    if(!$video->photo_root_id){
//                        $photoRoot = $photoService->createPhotoRoot();
//                        $video->set('PhotoRoot',$photoRoot);
//                        $video->save();
//                    }
//		    
//		    if(!$video->video_root_id){
//			$videoRoot = $videoUrlService->createVideoRoot();
//			$video->set('VideoRoot',$videoRoot);
//			$video->save();
//		    }
		    
                    $video->get('VideoRoot')->set('url',$values['url']);
                    $video->save();
//                    Zend_Debug::dump($video->toArray());exit;
                    
		    
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                     if(isset($_POST['save_only'])){
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-video', 'gallery',array('id' => $video->id)));
                    }

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-video', 'gallery'));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('news', $news);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
    }
    
    public function removeVideoAction() {
        $newsService = $this->_service->getService('Gallery_Service_Video');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
         $authService = $this->_service->getService('User_Service_Auth');
        
        
        if($news = $newsService->getVideo($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatag = $metatagService->getMetatag((int) $news->getMetatagId());
                $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $news->getMetatagId());

                $photoRoot = $news->get('PhotoRoot');
                $photoService->removePhoto($photoRoot);
                
                $news->set('UserUpdated',$this->user);
                $news->save();
                
                $newsService->removeVideo($news);

                $metatagService->removeMetatag($metatag);
                $metatagTranslationService->removeMetatagTranslation($metatagTranslation);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-news', 'news'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-news', 'news'));
	
	
    }
    
    public function setPromotedVideoAction() {
        $videoService = $this->_service->getService('Gallery_Service_Video');
        
        if($video = $videoService->getVideo($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                if($video->promoted){
                    $video->set('promoted',0);
                }
                else{
                    $video->set('promoted',1);
                }
                    $video->save();


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-video', 'gallery'));
            } catch(Exception $e) {
                var_dump($e->getMessage());exit;
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->viewRenderer->setNoRender();
    }
}

