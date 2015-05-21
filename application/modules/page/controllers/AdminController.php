<?php

/**
 * Page_AdminController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Page_AdminController extends MF_Controller_Action {
    
    public function listPageAction() {
        
    }
    
    public function listPageDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $pageService = $this->_service->getService('Page_Service_Page');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $this->getRequest()->setParam('lang', $adminLanguage->getId());
        
        $table = Doctrine_Core::getTable('Page_Model_Doctrine_Page');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table,
            'class' => 'Page_DataTables_Page', 
            'columns' => array('t.title'),
            'searchFields' => array('t.title')
        ));
        
        $types = Page_Model_Doctrine_Page::getAvailableTypes();
        foreach($types as $type => $label) {
            $pageService->fetchPage($type, $adminLanguage->getId());
        }
        
        $results = $dataTables->getResult();
        
        $language = $i18nService->getAdminLanguage();
        
        $rows = array();
    
        $sortResults = Page_Model_Doctrine_Page::getAvailableTypes();
        $typePages = 0;
        // add custom pages to result set
        foreach($results as $result) {
            if(strlen($result['type'])) {
                $typePages = 1;
                $sortResults[$result['type']] = $result;
            }
        }
        // clean not found results
        foreach($sortResults as $type => $result) {
            if(is_string($result)) {
                unset($sortResults[$type]);
            }
        }
        // clean results if no custom pages
        if(!$typePages) {
            $sortResults = array();
        }
        // add pages from database
        foreach($results as $result) {
            if(strlen($result['type']) == 0) {
                $sortResults[] = $result;
            }
        }
        
        foreach($sortResults as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            if($result['type']) {
                $row['DT_RowClass'] = 'info';
            }
            $row[] = $result->Translation[$language->getId()]->title;
            if($result['type']) {
                 $options = '<a href="' . $this->view->adminUrl('edit-page', 'page', array('id' => $result->id)) . '" class="edit-item"><span class="icon24 entypo-icon-settings"></span></a>';
                $row[] = $options;
                
            } else {
                $options = '<a href="' . $this->view->adminUrl('edit-page', 'page', array('id' => $result->id)) . '" class="edit-item"><span class="icon24 entypo-icon-settings"></span></a>';
                $options .= '<a href="' . $this->view->adminUrl('delete-page', 'page', array('id' => $result->id)) . '" class="delete-item"><span class="icon24 icon-remove"></span></a>';
                $row[] = $options;
            }
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
    
    public function addPageAction() {
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $form = $pageService->getPageForm();
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        
        $languages = $i18nService->getLanguageList();
        
        $user = $this->_helper->user();

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $values['user_id'] = $user->getId();
                    $page = $pageService->savePageFromArray($values);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-page', 'page', array('id' => $page->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-page', 'page'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
    }
    
    public function editPageAction() {
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $types = Page_Model_Doctrine_Page::getAvailableTypes();

        
        if(!$page = $pageService->getPage((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        $form = $pageService->getPageForm($page);
        $metatagsForm = $metatagService->getMetatagsSubForm($page->get('Metatag'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        $languages = $i18nService->getLanguageList();
        
        $user = $this->_helper->user();

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    if($metatags = $metatagService->saveMetatagsFromArray($page->get('Metatag'), $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $values['user_id'] = $user->getId();
                    
                    $page = $pageService->savePageFromArray($values);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-page', 'page', array('id' => $page->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-page', 'page'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->admincontainer->findOneBy('id', 'editpage')->setLabel($translator->translate($this->view->admincontainer->findOneBy('id', 'editpage')->getLabel(), $adminLanguage->getId()) . ' ' . $translator->translate($types[$page->getType()], $adminLanguage->getId()));
        
        $this->view->assign('languages', $languages);
        $this->view->assign('page', $page);
        $this->view->assign('form', $form);
    }
    
    public function deletePageAction() {
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        
        if(!$page = $pageService->getPage($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Page not found', 404);
        }

        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
            
            $page->get('Metatag')->delete();
            $pageService->removePage($page);
           
            
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            echo $this->_service->get('log')->log($e->getMessage(), 4);
        }      
       // $this->_helper->viewRenderer->setNoRender();
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-page', 'page'));
    }
    
    public function addPagePhotoAction() {
         $pageService = $this->_service->getService('Page_Service_Page');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
  
        $photoDimension = $photoDimensionService->getDimension('page');
        
        if(!$page = $pageService->getPage((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Page not found');
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

                        $root = $page->get('PhotoRoot');
                        if($root->isInProxyState()) {
                            $root = $photoService->createPhotoRoot();
                            $page->set('PhotoRoot', $root);
                            $page->save();
                        }

                       $photoService->createPhoto($filePath, $name, $pathinfo['filename'], $photoDimension, $root, true);

                       $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('Logger')->log($e->getMessage(), 4);
                    }
                }
            }
        }
        $list = '';
        
        $root = $page->get('PhotoRoot');
        $root->refresh();
        if(!$root->isInProxyState()) {
            $pagePhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/page-photos.phtml', 'product', array('photos' => $pagePhotos, 'page' => $page));
        }
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $page->getId()
        ));
    }
    
    public function movePagePhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        $pageService = $this->_service->getService('Page_Service_Page');
        
        if(!$page = $pageService->getPage($this->getRequest()->getParam('page'))) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Page photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        
        $list = '';
        
        $root = $page->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $pagePhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/page-photos.phtml', 'page', array('photos' => $pagePhotos, 'page' => $page));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $page->getId()
        ));
    }
    
    public function removePagePhotoAction() {   
        $pageService = $this->_service->getService('Page_Service_Page');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$page = $pageService->getPage((int) $this->getRequest()->getParam('page-id'))) {
            throw new Zend_Controller_Action_Exception('Page not found');
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
        
        $root = $page->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $pagePhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/page-photos.phtml', 'page', array('photos' => $pagePhotos, 'page' => $page));
        }   
        
         $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $photo->getId()
        ));
    }
    
}

