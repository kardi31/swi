<?php

/**
 * Banner_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Banner_AdminController extends MF_Controller_Action {
   
  
     public function listBannerAction() {

   }
    
   public function listBannerDataAction() {    
        $i18nService = $this->_service->getService('Default_Service_I18n');
       
        $table = Doctrine_Core::getTable('Banner_Model_Doctrine_Banner');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Banner_DataTables_Banner', 
            'columns' => array('p.id','pt.name','p.position','p.date_from','p.date_to','p.status'),
            'searchFields' => array('p.id','pt.name','p.position','p.date_from','p.date_to','p.status')
        ));
        
        $language = $i18nService->getAdminLanguage();
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result->id;
            $row[] = $result->Translation[$language->getId()]->name;
            $row[] = $result->position;
//            $row[] = MF_Text::timeFormat($result['created_at'],'d/m/Y H:i');
            $row[] = MF_Text::timeFormat($result['date_from'],'d/m/Y H:i');
            $row[] = MF_Text::timeFormat($result['date_to'],'d/m/Y H:i');
            if($result['status'] == 1){ 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-banner', 'banner', array('id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-checkbox-2"></span></a>';
            }
            else{
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-banner', 'banner', array('id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-checkbox-unchecked-2"></span></a>';
            }
           $options = '<a href="' . $this->view->adminUrl('edit-banner', 'banner', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-banner', 'banner', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    
    
    public function addBannerAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $attachmentService = $this->_service->getService('Media_Service_Attachment'); 
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $bannerService->getBannerForm();
        
        $form->setDecorators(array('FormElements'));
        $form->getElement('photo')->setValueDisabled(true);
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                       
                    $banner = $bannerService->saveBannerFromArray($values);
                    
                    $attachment = $attachmentService->createAttachmentFromUpload($form->getElement('photo')->getName(), $form->getValue('photo'), null, $adminLanguage->getId());

                    $banner->set('AttachmentRoot',$attachment);
                    $banner->save();
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-banner', 'banner'));
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
    
    public function editBannerAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $attachmentService = $this->_service->getService('Media_Service_Attachment'); 
        
        $translator = $this->_service->get('translate');
        
        if(!$banner = $bannerService->getBanner($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $bannerService->getBannerForm($banner);
        
        $form->translations->pl->getElement('name')->setLabel('Nazwa banneru');
        
        $metatagsForm = $metatagService->getMetatagsSubForm($banner->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $values = $form->getValues();
                    $banner = $bannerService->saveBannerFromArray($values);
                  if(strlen($values['photo'])){
                      $attachment = $attachmentService->createAttachmentFromUpload($form->getElement('photo')->getName(), $form->getValue('photo'), null, $adminLanguage->getId());
                      
                    $banner->set('AttachmentRoot',$attachment);
                    $banner->save();
                    }
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-banner', 'banner'));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
            else{
                var_dump($e->getMessages());exit;
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('banner', $banner);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
    }
    
    public function removeBannerAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if($banner = $bannerService->getBanner($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatag = $metatagService->getMetatag((int) $banner->getMetatagId());
                $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $banner->getMetatagId());

                $photoRoot = $banner->get('PhotoRoot');
                $photoService->removePhoto($photoRoot);
                
                $bannerService->removeBanner($banner);

                $metatagService->removeMetatag($metatag);
                $metatagTranslationService->removeMetatagTranslation($metatagTranslation);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-banner', 'banner'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-banner', 'banner'));
    }
    
    public function addBannerPhotoAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
  
        
        if(!$banner = $bannerService->getBanner((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        if(!array_key_exists('domain', $options)) {
            throw new Zend_Controller_Action_Exception('Domain string not set');
        }
        
        $href = $this->getRequest()->getParam('hrefs');

        if(is_string($href) && strlen($href)) {
            $path = str_replace("http://" . $options['domain'], "", urldecode($href));
            $filePath = urldecode($options['publicDir'] . $path);
            if(file_exists($filePath)) {
                $pathinfo = pathinfo($filePath);
                $slug = MF_Text::createSlug($pathinfo['basename']);
                $name = MF_Text::createUniqueFilename($slug, $photoService->photosDir);
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $root = $banner->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Banner_Model_Doctrine_Banner::getBannerPhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Banner_Model_Doctrine_Banner::getBannerPhotoDimensions()), false);
                    }

                    $banner->set('PhotoRoot', $photo);
                    $banner->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $list = '';
        
        $bannerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $banner->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $bannerPhotos->add($root);
            $list = $this->view->partial('admin/banner-main-photo.phtml', 'banner', array('photos' => $bannerPhotos, 'banner' => $banner));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $banner->getId()
        ));
        
    }
    
    public function editBannerPhotoAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$banner = $bannerService->getBanner((int) $this->getRequest()->getParam('banner-id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            $this->view->messages()->add($translator->translate('First you have to choose picture'), 'error');
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-banner', 'banner', array('id' => $banner->getId())));
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-banner-photo', 'banner', array('banner-id' => $banner->getId(), 'id' => $photo->getId())));
        
        $photosDir = $photoService->photosDir;
        $offsetDir = realpath($photosDir . DIRECTORY_SEPARATOR . $photo->getOffset());
        if(strlen($photo->getFilename()) > 0 && file_exists($offsetDir . DIRECTORY_SEPARATOR . $photo->getFilename())) {
            list($width, $height) = getimagesize($offsetDir . DIRECTORY_SEPARATOR . $photo->getFilename());
            $this->view->assign('imgDimensions', array('width' => $width, 'height' => $height));
        }

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $photo = $photoService->saveFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-banner-photo', 'banner', array('id' => $banner->getId(), 'photo' => $photo->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-banner', 'banner', array('id' => $banner->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
        
      
        $this->view->admincontainer->findOneBy('id', 'edit-banner')->setLabel($banner->Translation[$adminLanguage->getId()]->name);
        $this->view->admincontainer->findOneBy('id', 'edit-banner')->setParam('id', $banner->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropbannerphoto')->getLabel());

        $this->view->assign('banner', $banner);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Banner_Model_Doctrine_Banner::getBannerPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeBannerPhotoAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$banner = $bannerService->getBanner((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $banner->get('PhotoRoot')) {
                if($root && !$root->isInProxyState()) {
                    $photo = $photoService->updatePhoto($root);
                    $photo->setOffset(null);
                    $photo->setFilename(null);
                    $photo->setTitle(null);
                    $photo->save();
                }
            }
        
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage(), 4);
        }
        
        $list = '';
        
        $bannerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $banner->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $bannerPhotos->add($root);
            $list = $this->view->partial('admin/banner-main-photo.phtml', 'banner', array('photos' => $bannerPhotos, 'banner' => $banner));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $banner->getId()
        ));
        
    }
    
    public function refreshStatusBannerAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        if(!$banner = $bannerService->getBanner((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        $bannerService->refreshStatusBanner($banner);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-banner', 'banner'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function refreshStatusAdAction() {
        $adService = $this->_service->getService('Banner_Service_Ad');
        
        if(!$ad = $adService->getAd((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Ad not found');
        }
        
        $adService->refreshStatusAd($ad);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-ad', 'banner'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    
     public function listAdAction() {

   }
    
   public function listAdDataAction() {    
        $i18nService = $this->_service->getService('Default_Service_I18n');
       
        $table = Doctrine_Core::getTable('Banner_Model_Doctrine_Ad');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Banner_DataTables_Ad', 
            'columns' => array('p.id','pt.title', 'p.created_at','p.date_from','p.date_to','p.publish'),
            'searchFields' => array('p.id','pt.title', 'p.created_at','p.date_from','p.date_to','p.publish')
        ));
        
        $language = $i18nService->getAdminLanguage();
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result->id;
            $row[] = $result->Translation[$language->getId()]->title;
            $row[] = MF_Text::timeFormat($result['created_at'],'d/m/Y H:i');
            $row[] = MF_Text::timeFormat($result['date_from'],'d/m/Y H:i');
            $row[] = MF_Text::timeFormat($result['date_to'],'d/m/Y H:i');
            if($result['publish'] == 1){ 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-ad', 'banner', array('id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-checkbox-2"></span></a>';
            }
            else{
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-ad', 'banner', array('id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-checkbox-unchecked-2"></span></a>';
            }
           $options = '<a href="' . $this->view->adminUrl('edit-ad', 'banner', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-ad', 'banner', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    
    public function addAdAction() {
        $adService = $this->_service->getService('Banner_Service_Ad');
        $videoService = $this->_service->getService('Media_Service_VideoUrl');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $form = $adService->getAdForm();
       
        $this->view->assign('form',$form);
        
       
        $languages = $i18nService->getLanguageList();
        $adminLanguage = $i18nService->getAdminLanguage();
        $this->view->assign('languages', $languages);
        $this->view->assign('adminLanguage', $adminLanguage->getId());
        
        
         if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $values = $form->getValues();  
                  
                    $videoRoot = $videoService->createVideoRoot();
            
                    $video = $videoService->createVideoFromUpload($values, $videoRoot);
                    
                    $adService->saveAdFromArray($values,$video->id);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-ad', 'banner'));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
//     
    }
    
    public function editAdAction() {
        $adService = $this->_service->getService('Banner_Service_Ad');
        $videoService = $this->_service->getService('Media_Service_VideoUrl');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        
        if(!$ad = $adService->getAd($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Ad not found');
        }
        
        $form = $adService->getAdForm($ad);
       
        $this->view->assign('form',$form);
        
       
        $languages = $i18nService->getLanguageList();
        $adminLanguage = $i18nService->getAdminLanguage();
        $this->view->assign('languages', $languages);
        $this->view->assign('adminLanguage', $adminLanguage->getId());
        
        
         if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $values = $form->getValues();  
                  
                    if(!strlen($ad['video_root_id']))
                        $videoRoot = $videoService->createVideoRoot();
            
                    if($values['url']!= $ad['VideoRoot']['url'])
                        $video = $videoService->createVideoFromUpload($values, $videoRoot);
                    
                    $adService->saveAdFromArray($values,$video->id);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-ad', 'banner'));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
    }
    
     public function removeAdAction() {
        $adService = $this->_service->getService('Banner_Service_Ad');
        
        
        if($ad = $adService->getAd($this->getRequest()->getParam('id'))){
            try {
                
                $ad->delete();
                
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-ad', 'banner'));
         

            } catch(Exception $e) {
                var_dump($e->getMessage());exit;
               $this->_service->get('doctrine')->getCurrentConnection()->rollback();
               $this->_service->get('log')->log($e->getMessage(), 4);
            }

        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-ad', 'banner'));
         
        $this->_helper->viewRenderer->setNoRender();
               
    }
    
    
}

