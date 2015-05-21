<?php

/**
 * News_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_AdminController extends MF_Controller_Action {
    
    protected $user;
        
    
     public function init() {
        $this->_helper->ajaxContext()
                ->initContext();
        parent::init();
        
        $authService = $this->_service->getService('User_Service_Auth');
        $this->user = $authService->getAuthenticatedUser();
    }
    
    public function listNewsAction() {}
    public function listNewsDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('News_Model_Doctrine_News');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'News_DataTables_News', 
            'columns' => array('x.id','xt.title','x.created_at','x.updated_at','x.publish_date'),
            'searchFields' => array('x.id','xt.title','x.created_at','x.updated_at','x.publish_date')
        ));
        
        $results = $dataTables->getResult();
        $language = $i18nService->getAdminLanguage();

        $rows = array();
        foreach($results as $result) {
            
            $row = array();
            $row[] = $result->id;
            $row[] = $result->Translation[$language->getId()]->title;
           $row[] = MF_Text::timeFormat($result['created_at'], 'd/m/Y H:i'). "<br />".$result['UserCreated']['last_name']. " ".$result['UserCreated']['first_name'];
            $row[] = MF_Text::timeFormat($result['updated_at'], 'd/m/Y H:i'). "<br /> ".$result['UserUpdated']['last_name']. " ".$result['UserUpdated']['first_name'];
           
            $row[] = MF_Text::timeFormat($result->publish_date, 'd/m/Y H:i');
            
	    $options = '<a href="' . $this->view->adminUrl('edit-news', 'news', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;';
	    $options .= '<a href="' . $this->view->adminUrl('remove-news', 'news', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            
            
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
    
    public function addNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $categoryService = $this->_service->getService('News_Service_Category');
        $groupService = $this->_service->getService('News_Service_Group');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $tagService = $this->_service->getService('News_Service_Tag');
        
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $newsService->getNewsForm();
//        $form->getElement('category_id')->addMultiOptions($categoryService->prependCategoryOptions());
//        $form->getElement('group_id')->addMultiOptions($groupService->prependGroupOptions());
//        $form->getElement('tag_id')->addMultiOptions($tagService->prependTagOptions());
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
                    $news = $newsService->saveNewsFromArray($values,$this->user->getId(),$this->user->getId());
                    
                    if(!$news->photo_root_id){
                        $photoRoot = $photoService->createPhotoRoot();
                        $news->set('PhotoRoot',$photoRoot);
                        $news->save();
                    }
                    
//                    if($this->user['role']=="redaktor"):
//                        $news->set('student',1);
//                        $news->set('student_accept',0);
//                        $news->save();
//                    endif;
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-news', 'news', array('id' => $news->getId())));
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
    
    public function editNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $categoryService = $this->_service->getService('News_Service_Category');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        
        $translator = $this->_service->get('translate');
        
        if(!$news = $newsService->getNews($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $newsService->getNewsForm($news);
        $form->getElement('category_id')->addMultiOptions($categoryService->prependCategoryOptions());
        $form->getElement('category_id')->setValue($news['category_id']);
//        $form->getElement('group_id')->addMultiOptions($groupService->prependGroupOptions());
//        $form->getElement('group_id')->setValue($news['group_id']);
//        $form->getElement('tag_id')->addMultiOptions($tagService->prependTagOptions());
//        $form->getElement('tag_id')->setValue($news->get('Tags')->getPrimaryKeys());
        
        
        $metatagsForm = $metatagService->getMetatagsSubForm($news->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        if(!$news->photo_root_id){
            $photoRoot = $photoService->createPhotoRoot();
            $news->set('PhotoRoot',$photoRoot);
            $news->save();
        }
        
//        if(!$news->video_root_id){
//            $videoRoot = $videoService->createVideoRoot();
//            $news->set('VideoRoot',$videoRoot);
//            $news->save();
//        }
//        if(!$video = $videoService->getVideo($news->video_root_id)) {
//            throw new Zend_Controller_Action_Exception('Video not found');
//        }
//        $videoForm = $newsService->getVideoForm($video);
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
//                    $videoValues = $videoForm->getValues();
//                    $videoValues['id'] = $video['id'];
                    if($metatags = $metatagService->saveMetatagsFromArray($news->get('Metatags'), $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $news = $newsService->saveNewsFromArray($values,$this->user->getId());
//                     $video = $videoService->createVideoFromUpload($videoValues, $videoRoot);
                    
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    
//                     if(isset($_POST['add_video'])){
//                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('add-video', 'news',array('id' => $news->id)));
//                    }
                    
                     if(isset($_POST['save_only'])){
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-news', 'news',array('id' => $news->id)));
                    }

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-news', 'news'));
                } catch(Exception $e) {
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
    
    public function removeNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
         $authService = $this->_service->getService('User_Service_Auth');
        
        
        if($news = $newsService->getNews($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatag = $metatagService->getMetatag((int) $news->getMetatagId());
                $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $news->getMetatagId());

                $photoRoot = $news->get('PhotoRoot');
                $photoService->removePhoto($photoRoot);
                
                $news->set('UserUpdated',$this->user);
                $news->save();
                
                $newsService->removeNews($news);

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
    
    public function setBreakingNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        
        if($news = $newsService->getNews($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                if($news->breaking_news){
                    $news->set('breaking_news',0);
                }
                else{
                    $news->set('breaking_news',1);
                }
                    $news->save();


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-news', 'news'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function setStudentAcceptAction() {
        $newsService = $this->_service->getService('News_Service_News');
        
        if($news = $newsService->getNews($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                if($news->student_accept){
                    $news->set('student_accept',0);
                }
                else{
                    $news->set('student_accept',1);
                }
                    $news->save();


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-news', 'news'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->viewRenderer->setNoRender();
    }
    
   
    public function addNewsMainPhotoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');
        if(!$news = $newsService->getNews((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
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

                    $root = $news->get('PhotoRoot');
                    
                     if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(News_Model_Doctrine_News::getNewsPhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(News_Model_Doctrine_News::getNewsPhotoDimensions()), false);                    
                    }
                    
                    $news->set('PhotoRoot', $photo);
                    $news->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        
       
        $root = $news->get('PhotoRoot');
        $root->refresh();
        $list = $this->view->partial('admin/news-main-photo.phtml', 'news', array('photos' => $root, 'news' => $news));
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $news->getId()
        ));
        
    }
     public function addNewsPhotoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$news = $newsService->getNews((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
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

                         $root = $news->get('PhotoRoot');

                       $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(News_Model_Doctrine_News::getNewsPhotoDimensions()), $root, true);

                       $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('Logger')->log($e->getMessage(), 4);
                    }
                }
            }
        }

        
       
        $root = $news->get('PhotoRoot');
        $root->refresh();
        $photos = $photoService->getChildrenPhotos($root);
        $list = $this->view->partial('admin/news-photos.phtml', 'news', array('photos' => $photos, 'news' => $news));
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $news->getId()
        ));
        
    }
    
    public function editNewsPhotoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$news = $newsService->getNews((int) $this->getRequest()->getParam('news-id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            $this->view->messages()->add($translator->translate('First you have to choose picture'), 'error');
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-news', 'news', array('id' => $news->getId())));
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-news-photo', 'news', array('news-id' => $news->getId(), 'id' => $photo->getId())));
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-news-photo', 'news', array('id' => $news->getId(), 'photo' => $photo->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-news', 'news', array('id' => $news->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('news', $news);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', News_Model_Doctrine_News::getNewsPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeNewsPhotoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('photo-id'))) {
            throw new Zend_Controller_Action_Exception('Photo not found');
        }
        
        if(!$news = $newsService->getNews((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            $photoService->removePhoto($photo);
        
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage(), 4);
        }
        
        $root = $news->get('PhotoRoot');
        $photos = $photoService->getChildrenPhotos($root);
        $list = $this->view->partial('admin/news-photos.phtml', 'news', array('photos' => $photos , 'news' => $news));
        
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $news->getId()
        ));
        
    }
    
    public function removeNewsMainPhotoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$news = $newsService->getNews((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $news->get('PhotoRoot')) {
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
        
        $root = $news->get('PhotoRoot');
        $list = $this->view->partial('admin/news-main-photo.phtml', 'news', array('photos' => $root , 'news' => $news));
        
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $news->getId()
        ));
        
    }
    
     public function addVideoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $adService = $this->_service->getService('Banner_Service_Ad');
        $videoService = $this->_service->getService('Media_Service_VideoUrl');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        if(!$news = $newsService->getNews((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
        }
        
        $root = $news->get('VideoRoot');
        
        $form = new News_Form_Video();
        $form->getElement('ad_id')->addMultiOptions($adService->prependAds());
        $form->removeElement('date_from');
        $form->removeElement('date_to');
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
                 
                    $video = $videoService->createVideoFromUpload($values, $root);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-news', 'news',array('id' => (int) $this->getRequest()->getParam('id'))));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
    }
    
     public function editVideoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $adService = $this->_service->getService('Banner_Service_Ad');
        $videoService = $this->_service->getService('Media_Service_VideoUrl');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
         
        
        if(!$video = $videoService->getVideo((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Video not found');
        }
        
        
        $form = $newsService->getVideoForm($video);
        $form->getElement('ad_id')->addMultiOptions($adService->prependAds());
        $form->getElement('ad_id')->setValue($video->ad_id);
        $form->removeElement('date_from');
        $form->removeElement('date_to');
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
                 
                    $video = $videoService->createVideoFromUpload($values, $root);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-news', 'news',array('id' => (int) $this->getRequest()->getParam('news-id'))));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
    }
    
     public function removeVideoAction() {
        $videoService = $this->_service->getService('Media_Service_VideoUrl');
        
        
        if($video = $videoService->getVideo($this->getRequest()->getParam('id'))){
            try {
                
                $videoService->removeVideo($video);
                
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-news', 'news',array('id' => (int) $this->getRequest()->getParam('news-id'))));
         

            } catch(Exception $e) {
                var_dump($e->getMessage());exit;
               $this->_service->get('doctrine')->getCurrentConnection()->rollback();
               $this->_service->get('log')->log($e->getMessage(), 4);
            }

        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-news', 'news',array('id' => (int) $this->getRequest()->getParam('news-id'))));
         
        $this->_helper->viewRenderer->setNoRender();
               
    }
    
    public function moveNewsPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        $newsService = $this->_service->getService('News_Service_News');
        
        if(!$news = $newsService->getNews($this->getRequest()->getParam('news'))) {
            throw new Zend_Controller_Action_Exception('News not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        
        $list = '';
        
        $root = $news->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $newsPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/news-photos.phtml', 'news', array('photos' => $newsPhotos, 'news' => $news));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $news->getId()
        ));
    }
     public function listGroupAction() {}
    public function listGroupDataAction() {
        $table = Doctrine_Core::getTable('News_Model_Doctrine_Group');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'News_DataTables_Group', 
            'columns' => array('x.id','x.title'),
            'searchFields' => array('x.id','x.title')
        ));
        
        $results = $dataTables->getResult();
        

        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result->id;
            $row[] = $result->title;
           
            $options = '<a href="' . $this->view->adminUrl('edit-group', 'news', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-group', 'news', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addGroupAction() {
        $groupService = $this->_service->getService('News_Service_Group');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $groupService->getGroupForm();
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $metatagValues['translations']['pl'] = $values;
                    $metatagValues['metatags'] = $values['metatags']; 
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $metatagValues, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    $group = $groupService->saveGroupFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group', 'news'));
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
    
    public function editGroupAction() {
        $groupService = $this->_service->getService('News_Service_Group');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $translator = $this->_service->get('translate');
        
        if(!$group = $groupService->getGroup($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Group not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $groupService->getGroupForm($group);
        
        $metatagsForm = $metatagService->getMetatagsSubForm($group->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    
                    
                    $metatagValues['translations']['pl'] = $values;
                    $metatagValues['metatags'] = $values['metatags']; 
                    if($metatags = $metatagService->saveMetatagsFromArray($group->get('Metatags'), $metatagValues, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $group = $groupService->saveGroupFromArray($values);
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                   
                    
                     if(isset($_POST['save_only'])){
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-group', 'news',array('id' => $group->id)));
                    }

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group', 'news'));
                } catch(Exception $e) {
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
    
    public function removeGroupAction() {
        $groupService = $this->_service->getService('News_Service_Group');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        if($group = $groupService->getGroup($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatag = $metatagService->getMetatag((int) $group->getMetatagId());

                $metatagService->removeMetatag($metatag);
                
                $groupService->removeGroup($group);


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group', 'news'));
            } catch(Exception $e) {
                var_dump($e->getMessage());exit;
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->viewRenderer->setNoRender();
    }
    
    
    /* tag - start */
    
    public function listTagAction() {}
    public function listTagDataAction() {
        $table = Doctrine_Core::getTable('News_Model_Doctrine_Tag');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'News_DataTables_Tag', 
            'columns' => array('x.id','x.title'),
            'searchFields' => array('x.id','x.title')
        ));
        
        $results = $dataTables->getResult();
        

        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result->id;
            $row[] = $result->title;
           
            $options = '<a href="' . $this->view->adminUrl('edit-tag', 'news', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-tag', 'news', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addTagAction() {
        $tagService = $this->_service->getService('News_Service_Tag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $tagService->getTagForm();
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $metatagValues['translations']['pl'] = $values;
                    $metatagValues['metatags'] = $values['metatags']; 
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $metatagValues, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    $tag = $tagService->saveTagFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-tag', 'news'));
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
    
    public function editTagAction() {
        $tagService = $this->_service->getService('News_Service_Tag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $translator = $this->_service->get('translate');
        
        if(!$tag = $tagService->getTag($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Tag not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $tagService->getTagForm($tag);
        
        $metatagsForm = $metatagService->getMetatagsSubForm($tag->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    
                    
                    $metatagValues['translations']['pl'] = $values;
                    $metatagValues['metatags'] = $values['metatags']; 
                    if($metatags = $metatagService->saveMetatagsFromArray($tag->get('Metatags'), $metatagValues, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $tag = $tagService->saveTagFromArray($values);
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                   
                    
                     if(isset($_POST['save_only'])){
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-tag', 'news',array('id' => $tag->id)));
                    }

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-tag', 'news'));
                } catch(Exception $e) {
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
    
    public function removeTagAction() {
        $tagService = $this->_service->getService('News_Service_Tag');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        if($tag = $tagService->getTag($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatag = $metatagService->getMetatag((int) $tag->getMetatagId());

                $metatagService->removeMetatag($metatag);
                
                $tagService->removeTag($tag);


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-tag', 'news'));
            } catch(Exception $e) {
                var_dump($e->getMessage());exit;
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->viewRenderer->setNoRender();
    }
    
    /* tag - end */
    
     public function listCommentAction() {}
    public function listCommentDataAction() {
        $table = Doctrine_Core::getTable('News_Model_Doctrine_Comment');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'News_DataTables_Comment', 
            'columns' => array('nt.title','x.name','x.content','x.created_at','x.user_ip','x.active'),
            'searchFields' => array('nt.title','x.name','x.content','x.created_at','x.user_ip','x.active')
        ));
        
        $results = $dataTables->getResult();
        

        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result['News']['Translation']['pl']->title;
            $row[] = $result->name;
             $row[] = $result->content;
              $row[] = $result->created_at; 
              $row[] = $result->user_ip;
              if($result->active)
                    $options = '<a href="' . $this->view->adminUrl('set-active-comment', 'news', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon16 icomoon-icon-checkbox-2"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
              else
                  $options = '<a href="' . $this->view->adminUrl('set-active-comment', 'news', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon16 icomoon-icon-checkbox-unchecked"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            
$options .= '<a href="' . $this->view->adminUrl('remove-comment', 'news', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addCommentAction() {
        $commentService = $this->_service->getService('News_Service_Comment');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $commentService->getCommentForm();
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $metatagValues['translations']['pl'] = $values;
                    $metatagValues['metatags'] = $values['metatags']; 
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $metatagValues, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    $comment = $commentService->saveCommentFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-comment', 'news'));
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
    
    public function editCommentAction() {
        $commentService = $this->_service->getService('News_Service_Comment');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $translator = $this->_service->get('translate');
        
        if(!$comment = $commentService->getComment($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Comment not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $commentService->getCommentForm($comment);
        
        $metatagsForm = $metatagService->getMetatagsSubForm($comment->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    
                    
                    $metatagValues['translations']['pl'] = $values;
                    $metatagValues['metatags'] = $values['metatags']; 
                    if($metatags = $metatagService->saveMetatagsFromArray($comment->get('Metatags'), $metatagValues, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $comment = $commentService->saveNewsFromArray($values);
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                   
                    
                     if(isset($_POST['save_only'])){
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-comment', 'news',array('id' => $comment->id)));
                    }

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-comment', 'news'));
                } catch(Exception $e) {
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
    
    public function removeCommentAction() {
        $commentService = $this->_service->getService('News_Service_Comment');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        if($comment = $commentService->getComment($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $commentService->removeComment($comment);


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-comment', 'news'));
            } catch(Exception $e) {
                var_dump($e->getMessage());exit;
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function setActiveCommentAction() {
        $commentService = $this->_service->getService('News_Service_Comment');
        
        if($comment = $commentService->getComment($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                if($comment->active){
                    $comment->set('active',0);
                }
                else{
                    $comment->set('active',1);
                }
                    $comment->save();


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-comment', 'news'));
            } catch(Exception $e) {
                var_dump($e->getMessage());exit;
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function listStreamAction() {}
    public function listStreamDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('News_Model_Doctrine_Stream');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'News_DataTables_Stream', 
            'columns' => array('x.id','xt.title'),
            'searchFields' => array('x.id','xt.title')
        ));
        
        $results = $dataTables->getResult();
        $language = $i18nService->getAdminLanguage();

        $rows = array();
        foreach($results as $result) {
            
            $row = array();
            $row[] = $result->id;
            $row[] = $result->Translation[$language->getId()]->title;
           
            if($result['publish'] == 1){ 
                    $row[] = '<a href="' . $this->view->adminUrl('set-publish-stream', 'news', array('id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-checkbox-2"><span class="spaninspan">Tak</span></span></a>';
            }
            else{
                    $row[] = '<a href="' . $this->view->adminUrl('set-publish-stream', 'news', array('id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-checkbox-unchecked-2"><span class="spaninspan">Nie</span></span></a>';
            }
            
                $options = '<a href="' . $this->view->adminUrl('edit-stream', 'news', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                $options .= '<a href="' . $this->view->adminUrl('remove-stream', 'news', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            
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
    
    public function addStreamAction() {
        $streamService = $this->_service->getService('News_Service_Stream');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $streamService->getStreamForm();
//        $form->getElement('category_id')->addMultiOptions($categoryService->prependCategoryOptions());
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
                    $stream = $streamService->saveStreamFromArray($values,$this->user->getId(),$this->user->getId());
                    
                    
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-stream', 'news'));
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
    
    public function editStreamAction() {
        $streamService = $this->_service->getService('News_Service_Stream');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        
        $translator = $this->_service->get('translate');
        
        if(!$stream = $streamService->getStream($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Stream not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $streamService->getStreamForm($stream);
        $metatagsForm = $metatagService->getMetatagsSubForm($stream->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    if($metatags = $metatagService->saveMetatagsFromArray($stream->get('Metatags'), $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $stream = $streamService->saveStreamFromArray($values,$this->user->getId());
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    
                    
                     if(isset($_POST['save_only'])){
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-stream', 'news',array('id' => $stream->id)));
                    }

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-stream', 'news'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('stream', $stream);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
    }
    
    public function removeStreamAction() {
        $streamService = $this->_service->getService('News_Service_Stream');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
         $authService = $this->_service->getService('User_Service_Auth');
        
        
        if($stream = $streamService->getStream($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatag = $metatagService->getMetatag((int) $stream->getMetatagId());
                $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $stream->getMetatagId());

                $photoRoot = $stream->get('PhotoRoot');
                $photoService->removePhoto($photoRoot);
                
                $stream->set('UserUpdated',$this->user);
                $stream->save();
                
                $streamService->removeStream($stream);

                $metatagService->removeMetatag($metatag);
                $metatagTranslationService->removeMetatagTranslation($metatagTranslation);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-stream', 'news'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-stream', 'news'));
    }
}

