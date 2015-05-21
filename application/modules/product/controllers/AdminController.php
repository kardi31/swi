<?php

/**
 * ProductController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_AdminController extends MF_Controller_Action {
    
    public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('move-category', 'json')
                ->addActionContext('move-producer', 'json')
                ->addActionContext('remove-category', 'json')
                ->addActionContext('remove-attachment', 'json')
                ->initContext();
        parent::init();
    }
    
    public function listCategoryAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $categoryService = $this->_service->getService('Product_Service_Category');
        
        $adminLanguage = $i18nService->getAdminLanguage();
           
        if(!$categoryRoot = $categoryService->getCategoryRoot()) {
            $languages = array('en' => 'Categories', 'pl' => 'Kategorie');
            $categoryService->createCategoryRoot($languages);
        }

        if(!$parent = $categoryService->getCategory($this->getRequest()->getParam('id', 0))) {
            $parent = $categoryService->getCategoryRoot();
        }

        $categoryTree = $categoryService->getCategoryTree();
           
        if($current = $this->view->admincontainer->findOneBy('id', 'category')) {
            $current->setActive(true);
        }
        
        $this->view->assign('adminLanguage', $adminLanguage->getId());
        $this->view->assign('parent', $parent);
        $this->view->assign('categoryTree', $categoryTree);
    }
    
    public function listCategoryDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Product_Model_Doctrine_Category');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Product_DataTables_Category', 
            'columns' => array('x.name'),
            'searchFields' => array('x.name')
        ));
        $results = $dataTables->getResult();
        
        $language = $i18nService->getAdminLanguage();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            $row[] = $result->Translation[$language->getId()]->name;
            if ($result['status'] == 1)
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-category', 'product', array('category-id' => $result->id)) . '" title=""><span class="icon16  icomoon-icon-lamp-2"></span></a>';
            else 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-category', 'product', array('category-id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-lamp-3"></span></a>';
            $options = '<a href="' . $this->view->adminUrl('edit-category', 'product', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-category', 'product', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getTotal(),
            "iTotalDisplayRecords" => $dataTables->getDisplayTotal(),
            "aaData" => $rows
        );

        $this->_helper->json($response);
    }
    
    public function addCategoryAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $discountService = $this->_service->getService('Product_Service_Discount');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $categoryService->getCategoryForm();
        
        if(!$parent = $categoryService->getCategory($this->getRequest()->getParam('id', 0))) {
            $parent = $categoryService->getCategoryRoot();
        }
        $form->getElement('parent_id')->setValue($parent->getId());
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        $form->setAction($this->view->adminUrl('add-category', 'product'));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }

                    $category = $categoryService->saveCategoryFromArray($values);
                    
                    $root = $category->get('PhotoRoot');
                    if($root->isInProxyState()) {
                        $root = $photoService->createPhotoRoot();
                        $category->set('PhotoRoot', $root);
                        $category->save();
                    }

                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'product'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
        $this->view->assign('parentId', $parent->getId());
    }
    
    public function listSubCategoryAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Category not found');
        }
        
        $form = $categoryService->getCategoryForm($category);

        $metatagsForm = $metatagService->getMetatagsSubForm($category->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($category->get('Metatags'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $category = $categoryService->saveCategoryFromArray($values);

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-category', 'product', array('id' => $category->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        if(!$categoryRoot = $categoryService->getCategoryRoot()) {
            $languages = array('en' => 'Categories', 'pl' => 'Kategorie');
            $categoryService->createCategoryRoot($languages);
        }
     
        $categoryTree = $categoryService->getCategoryTree();
        
        if($current = $this->view->admincontainer->findOneBy('id', 'list-sub-category')) {
            $current->setLabel($translator->translate($current->getLabel()) . ' ' . $category->Translation[$adminLanguage]->name);
            $current->setActive(true);
            $this->view->assign('adminTitle', $current->getLabel());
        }
        
        $this->view->assign('adminLanguage', $adminLanguage->getId());
        $this->view->assign('parent', $category);
        $this->view->assign('categoryTree', $categoryTree);
        $this->view->assign('form', $form);
    }
    
    public function editCategoryAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Category not found');
        }
        
        $form = $categoryService->getCategoryForm($category);

        $metatagsForm = $metatagService->getMetatagsSubForm($category->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($category->get('Metatags'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $category = $categoryService->saveCategoryFromArray($values);

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $parentId = $category->getNode()->getParent() ? $category->getNode()->getParent()->getId() : null;

                    if ($parentId == 1):
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'product', array('id' => $parentId)));
                    else:
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-sub-category', 'product', array('id' => $parentId)));
                    endif;
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        $categoryPhotos = array();
        $root = $category->get('PhotoRoot');
        if ( $root != NULL){
            if(!$root->isInProxyState())
                $categoryPhotos = $photoService->getChildrenPhotos($root);
        }
        else{
            $categoryPhotos = NULL;
        }
        
        $parentId = $category->getNode()->getParent() ? $category->getNode()->getParent()->getId() : null;
        
        if($current = $this->view->admincontainer->findOneBy('id', 'edit-category')) {
            $current->setLabel($translator->translate($current->getLabel()) . ' ' . $category->Translation[$adminLanguage]->name);
            $current->setActive(true);
            $this->view->assign('adminTitle', $current->getLabel());
        }

        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('category', $category);
        $this->view->assign('form', $form);
        $this->view->assign('parentId', $parentId);
        $this->view->assign('categoryPhotos', $categoryPhotos);
    }
    
    public function addCategoryPhotoAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Category not found');
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

                    $root = $category->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Category::getCategoryPhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Category::getCategoryPhotoDimensions()), false);                    
                    }

                    $category->set('PhotoRoot', $photo);
                    $category->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $categoryPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $category->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $categoryPhotos->add($root);
            $list = $this->view->partial('admin/category-main-photo.phtml', 'product', array('photos' => $categoryPhotos, 'category' => $category));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $category->getId()
        ));      
    }
    
    public function editCategoryPhotoAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$category = $categoryService->getCategory((int) $this->getRequest()->getParam('category-id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-category-photo', 'product', array('category-id' => $category->getId(), 'id' => $photo->getId())));
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-category-photo', 'product', array('id' => $photo->getId(), 'category-id' => $category->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-category', 'product', array('id' => $category->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
          
        $this->view->admincontainer->findOneBy('id', 'cropcategoryphoto')->setActive();
        $this->view->admincontainer->findOneBy('id', 'editcategoryphoto')->setLabel($category->Translation[$adminLanguage->getId()]->name);
        $this->view->admincontainer->findOneBy('id', 'editcategoryphoto')->setParam('id', $category->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropcategoryphoto')->getLabel());  
        $this->view->assign('category', $category);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Product_Model_Doctrine_Category::getCategoryPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeCategoryPhotoAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Category not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $category->get('PhotoRoot')) {
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
        
        $categoryPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $category->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $categoryPhotos->add($root);
            $list = $this->view->partial('admin/category-main-photo.phtml', 'product', array('photos' => $categoryPhotos, 'category' => $category));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $category->getId()
        ));
        
    }
    
    public function moveCategoryAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
     
        $this->view->clearVars();
        
        $category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'));
        $status = 'success';
        
        $dest = $categoryService->getCategory((int) $this->getRequest()->getParam('dest_id'));
  
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

            $categoryService->moveCategory($category, $dest, $this->getRequest()->getParam('mode', 'after'));

            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage());
            $status= 'error';
        }
        
        $this->_helper->viewRenderer->setNoRender();
        
        $this->view->assign('status', $status);
    }
    
    public function removeCategoryAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
     
        $this->view->clearVars();
        
        $status = 'success';
        
        if($category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $parent = $category->getNode()->getParent();
                
                if (!$category->getNode()->getChildren()):
                    $metatag = $metatagService->getMetatag((int) $category->getMetatagId());
                    $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $category->getMetatagId());
                    
                    $metatagService->removeMetatag($metatag);
                    $metatagTranslationService->removeMetatagTranslation($metatagTranslation);
                else:
                    foreach($category->getNode()->getDescendants() as $desc):
                        $metatag = $metatagService->getMetatag((int) $desc->getMetatagId());
                        $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $desc->getMetatagId());

                        $metatagService->removeMetatag($metatag);
                        $metatagTranslationService->removeMetatagTranslation($metatagTranslation);
                    endforeach;
                    
                    $metatag = $metatagService->getMetatag((int) $category->getMetatagId());
                    $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $category->getMetatagId());
                    
                    $metatagService->removeMetatag($metatag);
                    $metatagTranslationService->removeMetatagTranslation($metatagTranslation);
                endif;
                
                $photoRoot = $category->get('PhotoRoot');
                $photoService->removeGallery($photoRoot);
                
                $categoryService->removeCategory($category);
                
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                if(!$this->getRequest()->isXmlHttpRequest()) {
                    if ($parent->getId() == 1):
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'product', array('id' => $parent->getId())));
                    else:
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-sub-category', 'product', array('id' => $parent->getId())));
                    endif;
                }
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                $this->_service->get('log')->log($e->getMessage());
                $status = 'error';
            }
        }
        
        $this->_helper->viewRenderer->setNoRender();
        
        $this->view->assign('status', $status);
    }
    
    public function refreshStatusCategoryAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        
        if(!$category = $categoryService->getCategory((int) $this->getRequest()->getParam('category-id'))) {
            throw new Zend_Controller_Action_Exception('Category not found');
        }
        
        $categoryService->refreshStatusCategory($category);
        
        $parentId = $category->getNode()->getParent() ? $category->getNode()->getParent()->getId() : null;

        if ($parentId == 1):
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'product'));
        else:
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-sub-category', 'product', array('id' => $parentId)));
        endif;
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function listProducerAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        
        if(!$producerRoot = $producerService->getProducerRoot()) {
            $producerService->createProducerRoot();
        }
    }
    
    public function listProducerDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Product_Model_Doctrine_Producer');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Product_DataTables_Producer', 
            'columns' => array('x.name', 'x.owner', 'x.website'),
            'searchFields' => array('x.name', 'x.owner')
        ));
        
        $language = $i18nService->getAdminLanguage();
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result->Translation[$language->getId()]->name;
            $row[] = $result['owner'];
            $row[] = $result['website'];
            if ($result['status'] == 1)
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-producer', 'product', array('producer-id' => $result['id'])) . '" title=""><span class="icon16  icomoon-icon-lamp-2"></span></a>';
            else 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-producer', 'product', array('producer-id' => $result['id'])) . '" title=""><span class="icon16 icomoon-icon-lamp-3"></span></a>';
            $moving = '<a href="' . $this->view->adminUrl('move-producer', 'product', array('id' => $result->id, 'move' => 'up')) . '" class="move" title ="' . $this->view->translate('Move up') . '"><span class="icomoon-icon-arrow-up"></span></a>';     
            $moving .= '<a href="' . $this->view->adminUrl('move-producer', 'product', array('id' => $result->id, 'move' => 'down')) . '" class="move" title ="' . $this->view->translate('Move down') . '"><span class="icomoon-icon-arrow-down"></span></a>';
            $row[] = $moving;
            $options = '<a href="' . $this->view->adminUrl('edit-producer', 'product', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $options .= '<a href="' . $this->view->adminUrl('remove-producer', 'product', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addProducerAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $discountService = $this->_service->getService('Product_Service_Discount');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        $form = $producerService->getProducerForm();
        
        if(!$parent = $producerService->getProducer($this->getRequest()->getParam('id', 0))) {
            $parent = $producerService->getProducerRoot();
        }

        $form->getElement('parent_id')->setValue($parent->getId());
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        
        $form->getElement('discount_id')->setMultiOptions($discountService->getTargetDiscountSelectOptions(true, $adminLanguage->getId()));
     
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try{
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $values = $form->getValues();
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $producer = $producerService->saveProducerFromArray($values); 
                    
                    $root = $producer->get('PhotoRoot');
                    if($root->isInProxyState()) {
                        $root = $photoService->createPhotoRoot();
                        $producer->set('PhotoRoot', $root);
                    }
                    
                    $producer->save();
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                   // $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-producer', 'product', array('id' => $producer->getId())));
                } catch(Exception $e) {
                    var_dump($e->getMessage()); exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }       
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);   
    }
    
    public function editProducerAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $discountService = $this->_service->getService('Product_Service_Discount');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');

        if(!$producer = $producerService->getFullProducer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
        }
        $form = $producerService->getProducerForm($producer);
        
        $metatagsForm = $metatagService->getMetatagsSubForm($producer->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        $form->getElement('discount_id')->setMultiOptions($discountService->getTargetDiscountSelectOptions(true, $adminLanguage->getId()));
        
        $form->setAction($this->view->adminUrl('edit-producer', 'product'));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($producer->get('Metatags'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $producerService->saveProducerFromArray($values);
                   
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-producer', 'product'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
        $producerPhotos = array();
        $root = $producer->get('PhotoRoot');
        if ( $root != NULL){
            if(!$root->isInProxyState())
                $producerPhotos = $photoService->getChildrenPhotos($root);
        }
        else{
            $producerPhotos = NULL;
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
        $this->view->assign('producer', $producer);
        $this->view->assign('producerPhotos', $producerPhotos);
    } 
    
    public function removeProducerAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');

        if($producer = $producerService->getProducer($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $metatag = $metatagService->getMetatag((int) $producer->getMetatagId());
                $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $producer->getMetatagId());
                           
                $photoRoot = $producer->get('PhotoRoot');
                $photoService->removeGallery($photoRoot);
     
                $producerService->removeProducer($producer);
                
                $metatagService->removeMetatag($metatag);
                $metatagTranslationService->removeMetatagTranslation($metatagTranslation);
                
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-producer', 'product'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-producer', 'product'));
    }
  
    public function addLogoPhotoAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$producer = $producerService->getProducer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
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

                    $root = $producer->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Producer::getLogoPhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Producer::getLogoPhotoDimensions()), false);                    
                    }

                    $producer->set('PhotoRoot', $photo);
                    $producer->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $producerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $producer->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $producerPhotos->add($root);
            $list = $this->view->partial('admin/producer-main-logo.phtml', 'product', array('photos' => $producerPhotos, 'producer' => $producer));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $producer->getId()
        ));      
    }
   
    public function editLogoPhotoAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$producer = $producerService->getProducer((int) $this->getRequest()->getParam('producer-id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-producer-photo', 'product', array('producer-id' => $producer->getId(), 'id' => $photo->getId())));
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-producer-photo', 'product', array('id' => $photo->getId(), 'producer-id' => $producer->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-producer', 'product', array('id' => $producer->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
          
        $this->view->admincontainer->findOneBy('id', 'cropproducerphoto')->setActive();
        $this->view->admincontainer->findOneBy('id', 'editphotoproducer')->setLabel($producer->Translation[$adminLanguage->getId()]->name);
        $this->view->admincontainer->findOneBy('id', 'editphotoproducer')->setParam('id', $producer->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropproducerphoto')->getLabel());  
        $this->view->assign('producer', $producer);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Product_Model_Doctrine_Producer::getLogoPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeLogoPhotoAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$producer = $producerService->getProducer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $producer->get('PhotoRoot')) {
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
        
        $producerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $producer->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $producerPhotos->add($root);
            $list = $this->view->partial('admin/producer-main-logo.phtml', 'product', array('photos' => $producerPhotos, 'producer' => $producer));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $producer->getId()
        ));
        
    }
    
    public function addProducerPhotoAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$producer = $producerService->getProducer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
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

                        $root = $producer->get('PhotoRoot');
                        if($root->isInProxyState()) {
                            $root = $photoService->createPhotoRoot();
                            $producer->set('PhotoRoot', $root);
                            $producer->save();
                        }

                       $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Producer::getProducerPhotoDimensions()), $root, true);

                       $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('Logger')->log($e->getMessage(), 4);
                    }
                }
            }
        }
        $list = '';
        
        $root = $producer->get('PhotoRoot');
        $root->refresh();
        if(!$root->isInProxyState()) {
            $producerPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/producer-main-photo.phtml', 'product', array('photos' => $producerPhotos, 'producer' => $producer));
        }
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $producer->getId()
        ));
    }
    
    public function editProducerPhotoAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$producer = $producerService->getProducer((int) $this->getRequest()->getParam('producer-id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-producer-photo', 'product', array('producer-id' => $producer->getId(), 'id' => $photo->getId())));
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-producer-photo', 'product', array('id' => $photo->getId(), 'producer-id' => $producer->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-producer', 'product', array('id' => $producer->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
          
        $this->view->admincontainer->findOneBy('id', 'cropproducerphoto')->setActive();
        $this->view->admincontainer->findOneBy('id', 'editphotoproducer')->setLabel($producer->Translation[$adminLanguage->getId()]->name);
        $this->view->admincontainer->findOneBy('id', 'editphotoproducer')->setParam('id', $producer->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropproducerphoto')->getLabel());
        
        $this->view->assign('producer', $producer);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Product_Model_Doctrine_Producer::getProducerPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeProducerPhotoAction() {   
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$producer = $producerService->getProducer((int) $this->getRequest()->getParam('producer-id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
        }
        
        if(!$photo = $photoService->getPhoto($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Producer photo not found');
        }
        
        try {
            $photoService->removePhoto($photo);
            
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-producer', 'product', array('id' => $producer->getId())));
        } catch(Exception $e) {
            $this->_service->get('Logger')->log($e->getMessage(), 4);
        }
              
        $list = '';
        
        $root = $producer->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $producerPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/producer-main-photo.phtml', 'product', array('photos' => $producerPhotos, 'producer' => $producer));
        }     
    }
    
    public function moveProducerPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        $producerService = $this->_service->getService('Product_Service_Producer');
        
        if(!$producer = $producerService->getProducer($this->getRequest()->getParam('producer'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Escort photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-producer', 'product', array('id' => $producer->getId())));
        $list = '';
        
        $root = $producer->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $producerPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/producer-main-photo.phtml', 'product', array('photos' => $producerPhotos, 'producer' => $producer));
        }
    }
    
    public function refreshStatusProducerAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        
        if(!$producer = $producerService->getProducer((int) $this->getRequest()->getParam('producer-id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
        }
        
        $producerService->refreshStatusProducer($producer);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-producer', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function moveProducerAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
     
        $this->view->clearVars();
        
        $producer = $producerService->getProducer((int) $this->getRequest()->getParam('id'));
        $status = 'success';

        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

            $producerService->moveProducer($producer, $this->getRequest()->getParam('move', 'down'));

            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage());
            $status= 'error';
        }
        
        $this->_helper->viewRenderer->setNoRender();
        
        $this->view->assign('status', $status);
    }
    
    public function listProductAction() {
        
    }
    
    public function listProductDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Product_Model_Doctrine_Product');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Product_DataTables_Product', 
            'columns' => array('pt.name', 'ct.name'),
            'searchFields' => array('pt.name', 'ct.name')
        ));
        
        $results = $dataTables->getResult();
        
        $language = $i18nService->getAdminLanguage();

        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result->id;
            $row[] = $result->Translation[$language->getId()]->name;
            $cat = '';
            foreach ($result['Categories'] as $category):
                if ($cat == ''){
                    $cat = $category->Translation[$language->getId()]->name;
                }
                else{
                    $cat .= '<br>'.$category->Translation[$language->getId()]->name;
                }
            endforeach;
     
            $row[] = $cat;
            $row[] = $result['price'];
            $row[] = $result['availability'];
            if ($result['status'] == 1)
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-product', 'product', array('product-id' => $result->id)) . '" title=""><span class="icon16  icomoon-icon-lamp-2"></span></a>';
            else 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-product', 'product', array('product-id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-lamp-3"></span></a>';
            

            
          //  $options = '<a href="' . $this->view->adminUrl('relate-product', 'product', array('id' => $result['id'])) . '" title="' . $this->view->translate('Relate') . '"><span class="icon16 icomoon-icon-share"></span></a>&nbsp;&nbsp;';
            $options = '<a href="' . $this->view->adminUrl('edit-product', 'product', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-product', 'product', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
   
    public function addProductAction() { 
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $productService = $this->_service->getService('Product_Service_Product');
        $producerService = $this->_service->getService('Product_Service_Producer');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $discountService = $this->_service->getService('Product_Service_Discount');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $adminLanguage = $i18nService->getAdminLanguage();
          
        $translator = $this->_service->get('translate');
        
        $form = $productService->getProductForm();
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        //$form->getElement('producer_id')->setMultiOptions($producerService->getTargetProducerSelectOptions(true, $adminLanguage->getId()));
        $form->removeElement('producer_id');
	$form->getElement('category_id')->setMultiOptions($categoryService->getTargetCategorySelectOptions(null, $adminLanguage->getId()));
        $form->getElement('discount_id')->setMultiOptions($discountService->getTargetDiscountSelectOptions(true, $adminLanguage->getId()));
        $form->getElement('product_id')->setMultiOptions($productService->getTargetProductSelectOptions(true, $adminLanguage->getId()));
        if($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if($form->isValid($this->getRequest()->getPost())) {
                try{
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $values = $form->getValues();
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $product = $productService->saveProductFromArray($values); 
                    
                    $root = $product->get('PhotoRoot');
                    if($root->isInProxyState()) {
                        $root = $photoService->createPhotoRoot();
                        $product->set('PhotoRoot', $root);
                        $product->save();
                    }
                    
                    // if this product is a set
                    // get lowest availability of set's products and save it as set availability
                    if($values['product_id']){
                        $availability = $productService->getSetAvailability($values['product_id'],Doctrine_Core::HYDRATE_SINGLE_SCALAR);
                        $product->setAvailability($availability);
                        $product->save();
                       
                    }
                    
                    // check if this product is IN any set
                    $productService->updateProductParentSets($product['id']);
                    
                    $productSet = $productService->saveSetProductsFromArray($product, $values); 
                    
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product', 'product', array('id' => $product->getId())));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }     
        
        
        if(!$categoryRoot = $categoryService->getCategoryRoot()) {
            $languages = array('en' => 'Categories', 'pl' => 'Kategorie');
            $categoryService->createCategoryRoot($languages);
        }

        if(!$parent = $categoryService->getCategory($this->getRequest()->getParam('id', 0))) {
            $parent = $categoryService->getCategoryRoot();
        }

        $categoryTree = $categoryService->getCategoryTree();
           
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage->getId());
        $this->view->assign('languages', $languages);
        $this->view->assign('parent', $parent);
        $this->view->assign('categoryTree', $categoryTree);
        $this->view->assign('form', $form);       
    }
  
    public function editProductAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $producerService = $this->_service->getService('Product_Service_Producer');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $discountService = $this->_service->getService('Product_Service_Discount');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$product = $productService->getFullProductAdmin((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        $form = $productService->getProductForm($product);
        $metatagsForm = $metatagService->getMetatagsSubForm($product->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        $form->removeElement('producer_id');
	//$form->getElement('producer_id')->setMultiOptions($producerService->getTargetProducerSelectOptions(true, $adminLanguage->getId()));
        $form->getElement('category_id')->setMultiOptions($categoryService->getTargetCategorySelectOptions(false, $adminLanguage->getId()));
        $form->getElement('discount_id')->setMultiOptions($discountService->getTargetDiscountSelectOptions(true, $adminLanguage->getId()));
        $form->getElement('category_id')->setValue($product->get('Categories')->getPrimaryKeys());
        $form->getElement('product_id')->setMultiOptions($productService->getTargetProductSelectOptions(true, $adminLanguage->getId()));
        $form->getElement('product_id')->setValue(array_keys($product->get('SetProducts')->getSetProducts()));
        
        if($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($product->get('Metatags'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $product = $productService->saveProductFromArray($values);
                    
                     // if this product is a set
                    // get lowest availability of set's products and save it as set availability
                    if($values['product_id']){
                        $availability = $productService->getSetAvailability($values['product_id'],Doctrine_Core::HYDRATE_SINGLE_SCALAR);
                        $product->setAvailability($availability);
                        $product->save();
                       
                    }
                    
                    // check if this product is IN any set
                    $productService->updateProductParentSets($product['id']);
                    
                    $productSet = $productService->saveSetProductsFromArray($product, $values); 
                    
                    
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
        $productPhotos = array();
        $root = $product->get('PhotoRoot');
        if ( $root != NULL){
            if(!$root->isInProxyState())
                $productPhotos = $photoService->getChildrenPhotos($root);
        }
        else{
            $productPhotos = NULL;
        }
        
        if(!$categoryRoot = $categoryService->getCategoryRoot()) {
            $languages = array('en' => 'Categories', 'pl' => 'Kategorie');
            $categoryService->createCategoryRoot($languages);
        }

        if(!$parent = $categoryService->getCategory($this->getRequest()->getParam('id', 0))) {
            $parent = $categoryService->getCategoryRoot();
        }

        $categoryTree = $categoryService->getCategoryTree();
           
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage->getId());
        $this->view->assign('languages', $languages);
        $this->view->assign('parent', $parent);
        $this->view->assign('categoryTree', $categoryTree);
        $this->view->assign('form', $form);
        $this->view->assign('product', $product);
        $this->view->assign('productPhotos', $productPhotos);
    } 
    
    public function removeProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $attachmentService = $this->_service->getService('Product_Service_Attachment');
        
        if($product = $productService->getProduct($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $metatag = $metatagService->getMetatag((int) $product->getMetatagId());
                $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $product->getMetatagId());
                $photoRoot = $product->get('PhotoRoot');
                $photoService->removeGallery($photoRoot);
                
                $attachmentService->removeAllAtachmentsProduct($product->getId());
                
                $productService->removeProduct($product);
                
                $metatagService->removeMetatag($metatag);
                $metatagTranslationService->removeMetatagTranslation($metatagTranslation);
                
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
            } catch(Exception $e) {
                var_dump($e->getMessage()); exit; 
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
    }
    
    public function addProductPhotoAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
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

                        $root = $product->get('PhotoRoot');
                        if($root->isInProxyState()) {
                            $root = $photoService->createPhotoRoot();
                            $product->set('PhotoRoot', $root);
                            $product->save();
                        }

                       $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Product::getProductPhotoDimensions()), $root, true);

                       $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('Logger')->log($e->getMessage(), 4);
                    }
                }
            }
        }
        $list = '';
        
        $root = $product->get('PhotoRoot');
        $root->refresh();
        if(!$root->isInProxyState()) {
            $productPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/product-main-photo.phtml', 'product', array('photos' => $productPhotos, 'product' => $product));
        }
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $product->getId()
        ));
    }
    
    public function editProductPhotoAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('product-id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-product-photo', 'product', array('product-id' => $product->getId(), 'id' => $photo->getId())));
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product-photo', 'product', array('id' => $photo->getId(), 'product-id' => $product->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product', 'product', array('id' => $product->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
          
        $this->view->admincontainer->findOneBy('id', 'cropproductphoto')->setActive();
        $this->view->admincontainer->findOneBy('id', 'editphotoproduct')->setLabel($product->Translation[$adminLanguage->getId()]->name);
        $this->view->admincontainer->findOneBy('id', 'editphotoproduct')->setParam('id', $product->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropproductphoto')->getLabel());
        
        $this->view->assign('product', $product);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Product_Model_Doctrine_Product::getProductPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeProductPhotoAction() {   
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('product-id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        if(!$photo = $photoService->getPhoto($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }
        
        try {
            $photoService->removePhoto($photo);
            
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product', 'product', array('id' => $product->getId())));
        } catch(Exception $e) {
            $this->_service->get('Logger')->log($e->getMessage(), 4);
        }
              
        $list = '';
        
        $root = $product->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $productPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/product-main-photo.phtml', 'product', array('photos' => $productPhotos, 'product' => $product));
        }   
    }
    
    public function addProductMainPhotoAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
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

                    $root = $product->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Product::getProductMainPhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Product::getProductMainPhotoDimensions()), false);                    
                    }

                    $product->set('PhotoRoot', $photo);
                    $product->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $productPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $product->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $productPhotos->add($root);
            $list = $this->view->partial('admin/product-main-logo.phtml', 'product', array('photos' => $productPhotos, 'product' => $product));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $product->getId()
        ));      
    }
    
    public function editProductMainPhotoAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('product-id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-product-photo', 'product', array('product-id' => $product->getId(), 'id' => $photo->getId())));
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product-photo', 'product', array('id' => $photo->getId(), 'product-id' => $product->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product', 'product', array('id' => $product->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
          
        $this->view->admincontainer->findOneBy('id', 'cropproductphoto')->setActive();
        $this->view->admincontainer->findOneBy('id', 'editphotoproduct')->setLabel($product->Translation[$adminLanguage->getId()]->name);
        $this->view->admincontainer->findOneBy('id', 'editphotoproduct')->setParam('id', $product->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropproducerphoto')->getLabel());  
        $this->view->assign('product', $product);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Product_Model_Doctrine_Product::getProductMainPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeProductMainPhotoAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $product->get('PhotoRoot')) {
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
        
        $productPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $product->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $productPhotos->add($root);
            $list = $this->view->partial('admin/product-main-logo.phtml', 'product', array('photos' => $productPhotos, 'product' => $product));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $product->getId()
        ));
        
    }
    
    public function relateProductAction() {
       $productService = $this->_service->getService('Product_Service_Product');
       $i18nService = $this->_service->getService('Default_Service_I18n');
       
       if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
       }
       $adminLanguage = $i18nService->getAdminLanguage();
       
       $translator = $this->_service->get('translate');
       
       $form = $productService->getRelateForm();
       $form->getElement('product_id')->setMultiOptions($productService->getTargetProductSelectOptionsToRelate($product->getId(), true, $adminLanguage->getId()));
       $form->getElement('product_id')->setValue(array_keys($product->get('RelatedProducts')->getRelatesProducts()));
         
       if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    $product = $productService->saveRelatedProductsFromArray($product, $values); 
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
                } catch(Exception $e) {
                    var_dump($e->getMessage()); exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    

       $this->view->assign('product', $product);
       $this->view->assign('form', $form);
    }
    
    public function listAttachmentDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Product_Model_Doctrine_Attachment');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Product_DataTables_Attachment', 
            'columns' => array('a.name'),
            'searchFields' => array('a.name')
        ));
        
        $language = $i18nService->getAdminLanguage();
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result->Translation[$language->getId()]->title;
            $row[] = $result['extension'];
            $options = '<a href="' . $this->view->adminUrl('edit-attachment', 'product', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;'; 
            $options .= '<a href="' . $this->view->adminUrl('remove-attachment', 'product', array('id' => $result['id'])) . '" class="remove2" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $results->count(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }
    
    public function addAttachmentAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $productService = $this->_service->getService('Product_Service_Product');
        $attachmentService = $this->_service->getService('Product_Service_Attachment'); 

        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $fileForm = new Product_Form_UploadAttachment();
        $fileForm->setDecorators(array('FormElements'));
        $fileForm->removeElement('submit');
        $fileForm->getElement('file')->setValueDisabled(true);
        
        if($this->getRequest()->isPost()) {
            if($fileForm->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $attachment = $attachmentService->createAttachmentFromUpload($fileForm->getElement('file')->getName(), $fileForm->getValue('file'), $product->getId(), null, $adminLanguage->getId());

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();             
                } catch(Exception $e) {
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                    var_dump($e->getMessage()); exit;
                }
     
            }
        }
        $this->_helper->viewRenderer->setNoRender();  
    }
    
    public function editAttachmentAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $attachmentService = $this->_service->getService('Product_Service_Attachment');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$attachment = $attachmentService->getAttachment((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Attachment not found');
        }
        
        $form = $attachmentService->getAttachmentForm($attachment);
      
        //$form->setAction($this->view->adminUrl('edit-product', 'product'));
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    $attachmentService->saveAttachmentFromArray($values); 

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product', 'product', array('id' => $attachment->getProductId())));
                } catch(Exception $e) {
                    var_dump($e->getMessage()); exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
       
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
        $this->view->assign('attachment', $attachment);
    } 
    
    public function removeAttachmentAction() {
        $attachmentService = $this->_service->getService('Product_Service_Attachment');
        
        if(!$attachment = $attachmentService->getAttachment($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Attachment not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
            
            $attachmentService->removeAttachment($attachment);
            
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
           $this->_service->get('doctrine')->getCurrentConnection()->rollback();
           $this->_service->get('log')->log($e->getMessage(), 4);
        }

        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $attachment->getId()
        ));
        
    }
    
    public function moveProductPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getProduct($this->getRequest()->getParam('product'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product', 'product', array('id' => $product->getId())));
        $list = '';
        
        $root = $product->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $productPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/product-main-photo.phtml', 'product', array('photos' => $productPhotos, 'product' => $product));
        }
    }
    
    public function refreshStatusProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('product-id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        $productService->refreshStatusProduct($product);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function refreshDistributorProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('product-id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        $productService->refreshDistributorProduct($product);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
//    public function refreshPromotionProductAction() {
//        $productService = $this->_service->getService('Product_Service_Product');
//        
//        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('product-id'))) {
//            throw new Zend_Controller_Action_Exception('Product not found');
//        }
//        
//        $productService->refreshPromotionProduct($product);
//        
//        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
//        $this->_helper->viewRenderer->setNoRender();
//    }
    
    public function listDiscountAction() {
        
    }
    
    public function listDiscountDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Product_Model_Doctrine_Discount');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Product_DataTables_Discount', 
            'columns' => array('x.name', 'x.start_date', 'x.finish_date'),
            'searchFields' => array('x.name')
        ));
        
        $language = $i18nService->getAdminLanguage();
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result->Translation[$language->getId()]->name;
            
            $row[] = MF_Text::timeFormat($result->start_date, 'H:i d/m/Y');
            $row[] = MF_Text::timeFormat($result->finish_date, 'H:i d/m/Y');
            $row[] = $result['amount_discount'];
            if ($result['status'] == 1)
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-discount', 'product', array('discount-id' => $result['id'])) . '" title=""><span class="icon16  icomoon-icon-lamp-2"></span></a>';
            else 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-discount', 'product', array('discount-id' => $result['id'])) . '" title=""><span class="icon16 icomoon-icon-lamp-3"></span></a>';
            $options = '<a href="' . $this->view->adminUrl('list-assign-discount', 'product', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Assign discount') . '"><span class="icon16 icomoon-icon-pencil-2"></span></a>&nbsp;&nbsp;';   
            $options .= '<a href="' . $this->view->adminUrl('edit-discount', 'product', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $options .= '<a href="' . $this->view->adminUrl('remove-discount', 'product', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
   
    public function addDiscountAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $discountService = $this->_service->getService('Product_Service_Discount');
        
        $adminLanguage = $i18nService->getAdminLanguage();
          
        $translator = $this->_service->get('translate');
        
        $form = $discountService->getDiscountForm();
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try{
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
              
                    $values = $form->getValues();
 
                    $discount = $discountService->saveDiscountFromArray($values); 
                             
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-discount', 'product'));
                } catch(Exception $e) {
                    var_dump($e->getMessage()); exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }     
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);       
    }
   
    public function editDiscountAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $discountService = $this->_service->getService('Product_Service_Discount');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$discount = $discountService->getDiscount((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Discount not found');
        }
        
        $form = $discountService->getDiscountForm($discount);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                    $values = $form->getValues();
                          
                    $discount = $discountService->saveDiscountFromArray($values);

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-discount', 'product'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('discount', $discount);
        $this->view->assign('form', $form);
    }
    
    public function removeDiscountAction() {
        $discountService = $this->_service->getService('Product_Service_Discount');

        if($discount = $discountService->getDiscount($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $discountService->removeDiscount($discount);
                     
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-discount', 'product'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('log')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-discount', 'product'));
    }
    
    public function refreshStatusDiscountAction() {
        $discountService = $this->_service->getService('Product_Service_Discount');
        
        if(!$discount = $discountService->getDiscount((int) $this->getRequest()->getParam('discount-id'))) {
            throw new Zend_Controller_Action_Exception('Discount not found');
        }
        
        $discountService->refreshStatusDiscount($discount);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-discount', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function listAssignDiscountAction() {
        $discountService = $this->_service->getService('Product_Service_Discount');
        
        if(!$discount = $discountService->getDiscount((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Discount not found');
        }
        
        $this->view->assign('discount', $discount);    
    }
    
    public function assignDiscountProducerAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $discountService = $this->_service->getService('Product_Service_Discount');
        $producerService = $this->_service->getService('Product_Service_Producer');
          
        if(!$discount = $discountService->getDiscount((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Discount not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();

        $translator = $this->_service->get('translate');
        
        $form = $discountService->getAssignDiscountForm();
        
        $form->getElement('producer_id')->setMultiOptions($producerService->getUnSelectedDiscountSelectOptions($discount->getId(), $adminLanguage->getId()));
        $form->getElement('producer_selected')->setMultiOptions($producerService->getTargetProducerSelectOptions(false, $adminLanguage->getId()));
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try{
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
              
                    $values = $form->getValues();
                    
                    $producerService->saveAssignedDiscountsFromArray($values, $discount->getId()); 
                             
                    $this->view->messages()->add($translator->translate('Discounts has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-assign-discount', 'product', array('id' => $discount->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }     
        $form->getElement('producer_selected')->setMultiOptions($producerService->getSelectedDiscountSelectOptions($discount->getId(), $adminLanguage->getId()));
        
        $this->view->assign('discount', $discount);    
        $this->view->assign('form', $form);       
    }
    
    public function assignDiscountProductAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $discountService = $this->_service->getService('Product_Service_Discount');
        $productService = $this->_service->getService('Product_Service_Product');
          
        if(!$discount = $discountService->getDiscount((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Discount not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        $form = $discountService->getAssignDiscountForm();
        
        $form->getElement('product_id')->setMultiOptions($productService->getUnSelectedDiscountSelectOptions($discount->getId(), $adminLanguage->getId()));
        $form->getElement('product_selected')->setMultiOptions($productService->getTargetProductSelectOptions(false, $adminLanguage->getId()));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try{
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
              
                    $values = $form->getValues();
                    
                    $productService->saveAssignedDiscountsFromArray($values, $discount->getId()); 
                             
                    $this->view->messages()->add($translator->translate('Discounts has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-assign-discount', 'product', array('id' => $discount->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }     
        $form->getElement('product_selected')->setMultiOptions($productService->getSelectedDiscountSelectOptions($discount->getId(), $adminLanguage->getId()));
        
        $this->view->assign('discount', $discount);    
        $this->view->assign('form', $form);       
    } 
    
    public function assignDiscountClientAction() {
        $discountService = $this->_service->getService('Product_Service_Discount');
        $userService = $this->_service->getService('User_Service_User');
          
        if(!$discount = $discountService->getDiscount((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Discount not found');
        }
        
        $translator = $this->_service->get('translate');
        
        $form = $discountService->getAssignDiscountForm();
        
        $form->getElement('user_id')->setMultiOptions($userService->getUnSelectedDiscountSelectOptions($discount->getId()));
        $form->getElement('user_selected')->setMultiOptions($userService->getTargetClientSelectOptions(false));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try{
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
              
                    $values = $form->getValues();
                    
                    $userService->saveAssignedDiscountsFromArray($values, $discount->getId()); 
                             
                    $this->view->messages()->add($translator->translate('Discounts has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-assign-discount', 'product', array('id' => $discount->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }     
        $form->getElement('user_selected')->setMultiOptions($userService->getSelectedDiscountSelectOptions($discount->getId()));
        
        $this->view->assign('discount', $discount);    
        $this->view->assign('form', $form);       
    } 
    
    public function assignDiscountGroupClientAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $discountService = $this->_service->getService('Product_Service_Discount');
        $groupService = $this->_service->getService('User_Service_Group');
          
        if(!$discount = $discountService->getDiscount((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Discount not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        $form = $discountService->getAssignDiscountForm();
        
        $form->getElement('group_id')->setMultiOptions($groupService->getUnSelectedDiscountSelectOptions($discount->getId(), $adminLanguage->getId()));
        $form->getElement('group_selected')->setMultiOptions($groupService->getTargetGroupSelectOptions(false, $adminLanguage->getId()));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try{
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
              
                    $values = $form->getValues();
                    
                    $groupService->saveAssignedDiscountsFromArray($values, $discount->getId()); 
                             
                    $this->view->messages()->add($translator->translate('Discounts has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-assign-discount', 'product', array('id' => $discount->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }     
        $form->getElement('group_selected')->setMultiOptions($groupService->getSelectedDiscountSelectOptions($discount->getId(), $adminLanguage->getId()));
        
        $this->view->assign('discount', $discount);    
        $this->view->assign('form', $form);       
    } 
    
    public function listCommentAction() {
        if($dashboardTime = $this->_helper->user->get('dashboard_time')) {
            if(isset($dashboardTime['new_comments'])) {
                $dashboardTime['new_comments'] = time();
                $this->_helper->user->set('dashboard_time', $dashboardTime);
            }
        }
    }
    
    public function listCommentDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Product_Model_Doctrine_Comment');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Product_DataTables_Comment', 
            'columns' => array('pt.name', 'CONCAT_WS(" ", u.first_name, u.last_name)', 'c.created_at'),
            'searchFields' => array('pt.name', 'CONCAT_WS(" ", u.first_name, u.last_name)', 'c.created_at')
        ));
        
        $language = $i18nService->getAdminLanguage();
        
        $results = $dataTables->getResult();

        $rows = array();
        foreach($results as $result) {
            $row = array();
            
            if ($result['moderation'] == 0):
                $row['DT_RowClass'] = 'info';
            endif;
            
            $row[] = $result['Product']->Translation[$language->getId()]['name'];
            $row[] = $result['nick']; 
            $row[] = $result['created_at'];
            if ($result['status'] == 1)
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-comment', 'product', array('comment-id' => $result->id)) . '" title=""><span class="icon16  icomoon-icon-lamp-2"></span></a>';
            else 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-comment', 'product', array('comment-id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-lamp-3"></span></a>';
            $options = '<a href="' . $this->view->adminUrl('edit-comment', 'product', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $options .= '<a href="' . $this->view->adminUrl('remove-comment', 'product', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function editCommentAction() {
        $commentService = $this->_service->getService('Product_Service_Comment');
        $productService = $this->_service->getService('Product_Service_Product');
        
        $translator = $this->_service->get('translate');
        
        if(!$comment = $commentService->getComment((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Comment not found');
        }
        
        if(!$product = $productService->getProduct($comment->getProductId(), 'id')) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        $form = $commentService->getCommentForm($comment);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                    $values = $form->getValues();
                          
                    $comment = $commentService->saveCommentFromArray($values);
                    
                    $commentService->refreshStatusModeration($comment);
                    
                    $productComments = $product->get('Comments')->toArray();
                    $sum = 0;
                    $counter = 0;
                    foreach($productComments as $productComment):
                        if($productComment['moderation'] == 1):
                            $sum += $productComment['partial_rate'];
                            $counter++;
                        endif;
                    endforeach;
                    $rateAverage = round($sum/$counter, 1);
                    $product->setRate($rateAverage);
                    $product->save();

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-comment', 'product'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $this->view->assign('comment', $comment);
        $this->view->assign('form', $form);
    }
    
    public function removeCommentAction() {
        $commentService = $this->_service->getService('Product_Service_Comment');

        if($comment = $commentService->getComment($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $commentService->removeComment($comment);
                     
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-comment', 'product'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('log')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-comment', 'product'));
    }
    
    public function refreshStatusCommentAction() {
        $commentService = $this->_service->getService('Product_Service_Comment');
        
        if(!$comment = $commentService->getComment((int) $this->getRequest()->getParam('comment-id'))) {
            throw new Zend_Controller_Action_Exception('Comment not found');
        }
        
        $commentService->refreshStatusComment($comment);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-comment', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function listPreviewAction() {
        
    }
    
    public function listPreviewDataAction() {
        $table = Doctrine_Core::getTable('Product_Model_Doctrine_Preview');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Product_DataTables_Preview', 
            'columns' => array('p.name', 'pro.name'),
            'searchFields' => array('p.name', 'pro.name')
        ));
        
        $results = $dataTables->getResult();

        $rows = array();
        foreach($results as $result) {
            $row = array();
            
            $row[] = $result['name'];
            $row[] .= $result['Producer']['name'];
            $cat = '';
            foreach ($result['Categories'] as $category):
                if ($cat == ''){
                    $cat = $category['name'].'<br>';
                }
                else{
                    $cat .= $category['name'].'<br>';
                }
            endforeach;
     
            $row[] = $cat;
            if ($result['status'] == 1)
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-preview', 'product', array('preview-id' => $result->id)) . '" title=""><span class="icon16  icomoon-icon-lamp-2"></span></a>';
            else 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-preview', 'product', array('preview-id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-lamp-3"></span></a>';
            
            $options = '<a href="' . $this->view->adminUrl('edit-preview', 'product', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $options .= '<a href="' . $this->view->adminUrl('remove-preview', 'product', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addPreviewAction() { 
        $previewService = $this->_service->getService('Product_Service_Preview');
        $producerService = $this->_service->getService('Product_Service_Producer');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
          
        $translator = $this->_service->get('translate');
        
        $form = $previewService->getPreviewForm();
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        $form->getElement('producer_id')->setMultiOptions($producerService->getTargetProducerSelectOptions(true));
        $form->getElement('category_id')->setMultiOptions($categoryService->getTargetCategorySelectOptions());
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try{
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $preview = $previewService->savePreviewFromArray($values); 
                    
                    $root = $preview->get('PhotoRoot');
                    if($root->isInProxyState()) {
                        $root = $photoService->createPhotoRoot();
                        $preview->set('PhotoRoot', $root);
                        $preview->save();
                    }
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-preview', 'product', array('id' => $preview->getId())));
                } catch(Exception $e) {
                    var_dump($e->getMessage());
                    exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }     
        
        if(!$categoryRoot = $categoryService->getCategoryRoot()) {
            $categoryService->createCategoryRoot($this->_service->get('translate')->translate('Categories'));
        }

        if(!$parent = $categoryService->getCategory($this->getRequest()->getParam('id', 0))) {
            $parent = $categoryService->getCategoryRoot();
        }

        $categoryTree = $categoryService->getCategoryTree();
           
        $this->view->assign('parent', $parent);
        $this->view->assign('categoryTree', $categoryTree);
        $this->view->assign('form', $form);       
    }
    
    public function editPreviewAction() {
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $previewService = $this->_service->getService('Product_Service_Preview');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $producerService = $this->_service->getService('Product_Service_Producer');
        $categoryService = $this->_service->getService('Product_Service_Category');
        
        $translator = $this->_service->get('translate');
        
        if(!$preview = $previewService->getFullPreview((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Preview not found');
        }
        
        $form = $previewService->getPreviewForm($preview);
        $metatagsForm = $metatagService->getMetatagsSubForm($preview->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        $form->getElement('producer_id')->setMultiOptions($producerService->getTargetProducerSelectOptions(true));
        $form->getElement('category_id')->setMultiOptions($categoryService->getTargetCategorySelectOptions(false));
        $form->getElement('category_id')->setValue($preview->get('Categories')->getPrimaryKeys());
        
        $form->setAction($this->view->adminUrl('edit-preview', 'product'));
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($preview->get('Metatags'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $preview = $previewService->savePreviewFromArray($values); 
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-preview', 'product'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
        $previewPhotos = array();
        $root = $preview->get('PhotoRoot');
        if ( $root != NULL){
            if(!$root->isInProxyState())
                $previewPhotos = $photoService->getChildrenPhotos($root);
        }
        else{
            $previewPhotos = NULL;
        }
        
        if(!$categoryRoot = $categoryService->getCategoryRoot()) {
            $categoryService->createCategoryRoot($this->_service->get('translate')->translate('Categories'));
        }

        if(!$parent = $categoryService->getCategory($this->getRequest()->getParam('id', 0))) {
            $parent = $categoryService->getCategoryRoot();
        }

        $categoryTree = $categoryService->getCategoryTree();
           
        
        $this->view->assign('parent', $parent);
        $this->view->assign('categoryTree', $categoryTree);
        $this->view->assign('form', $form);
        $this->view->assign('preview', $preview);
        $this->view->assign('previewPhotos', $previewPhotos);
    } 
    
    public function removePreviewAction() {
        $previewService = $this->_service->getService('Product_Service_Preview');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if($preview = $previewService->getPreview($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $metatag = $metatagService->getMetatag((int) $preview->getMetatagId());
                $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $preview->getMetatagId());
                $photoRoot = $preview->get('PhotoRoot');
                $photoService->removeGallery($photoRoot);
                       
                $previewService->removePreview($preview);
                
                $metatagService->removeMetatag($metatag);
                $metatagTranslationService->removeMetatagTranslation($metatagTranslation);
                
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-preview', 'product'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-preview', 'product'));
    }
    
    public function addPreviewMainPhotoAction() {
        $previewService = $this->_service->getService('Product_Service_Preview');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$preview = $previewService->getPreview((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Preview not found');
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

                    $root = $preview->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Preview::getPreviewMainPhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Preview::getPreviewMainPhotoDimensions()), false);                    
                    }

                    $preview->set('PhotoRoot', $photo);
                    $preview->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $previewPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $preview->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $previewPhotos->add($root);
            $list = $this->view->partial('admin/preview-main-logo.phtml', 'product', array('photos' => $previewPhotos, 'preview' => $preview));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $preview->getId()
        ));      
    }
    
    public function editPreviewMainPhotoAction() {
        $previewService = $this->_service->getService('Product_Service_Preview');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$preview = $previewService->getPreview((int) $this->getRequest()->getParam('preview-id'))) {
            throw new Zend_Controller_Action_Exception('Preview not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }

        $form = $photoService->getPhotoForm($photo);
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-preview-photo', 'product', array('id' => $photo->getId(), 'product-id' => $product->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-preview', 'product', array('id' => $preview->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
          
        $this->view->admincontainer->findOneBy('id', 'croppreviewphoto')->setActive();
        $this->view->admincontainer->findOneBy('id', 'editphotopreview')->setLabel($preview->getName());
        $this->view->admincontainer->findOneBy('id', 'editphotopreview')->setParam('id', $preview->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropproducerphoto')->getLabel());  
        $this->view->assign('preview', $preview);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Product_Model_Doctrine_Preview::getPreviewMainPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removePreviewMainPhotoAction() {
        $previewService = $this->_service->getService('Product_Service_Preview');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$preview = $previewService->getPreview((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Preview not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $preview->get('PhotoRoot')) {
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
        
        $previewPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $preview->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $previewPhotos->add($root);
            $list = $this->view->partial('admin/preview-main-logo.phtml', 'product', array('photos' => $previewPhotos, 'preview' => $preview));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $preview->getId()
        ));
        
    }
    
    public function addPreviewPhotoAction() {
        $previewService = $this->_service->getService('Product_Service_Preview');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$preview = $previewService->getPreview((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Preview not found');
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

                        $root = $preview->get('PhotoRoot');
                        if($root->isInProxyState()) {
                            $root = $photoService->createPhotoRoot();
                            $preview->set('PhotoRoot', $root);
                            $preview->save();
                        }

                       $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Product_Model_Doctrine_Preview::getPreviewPhotoDimensions()), $root, true);

                       $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('Logger')->log($e->getMessage(), 4);
                    }
                }
            }
        }
        $list = '';
        
        $root = $preview->get('PhotoRoot');
        $root->refresh();
        if(!$root->isInProxyState()) {
            $previewPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/preview-main-photo.phtml', 'product', array('photos' => $previewPhotos, 'preview' => $preview));
        }
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $preview->getId()
        ));
    }
    
    public function editPreviewPhotoAction() {
        $previewService = $this->_service->getService('Product_Service_Preview');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$preview = $previewService->getPreview((int) $this->getRequest()->getParam('preview-id'))) {
            throw new Zend_Controller_Action_Exception('Preview not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-preview-photo', 'product', array('preview-id' => $preview->getId(), 'id' => $photo->getId())));
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-preview-photo', 'product', array('id' => $photo->getId(), 'preview-id' => $preview->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-preview', 'product', array('id' => $preview->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
          
        $this->view->admincontainer->findOneBy('id', 'croppreviewphoto')->setActive();
        $this->view->admincontainer->findOneBy('id', 'editphotopreview')->setLabel($preview->getName());
        $this->view->admincontainer->findOneBy('id', 'editphotopreview')->setParam('id', $preview->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'croppreviewphoto')->getLabel());
        
        $this->view->assign('preview', $preview);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Product_Model_Doctrine_Preview::getPreviewPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removePreviewPhotoAction() {   
        $previewService = $this->_service->getService('Product_Service_Preview');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$preview = $previewService->getPreview((int) $this->getRequest()->getParam('preview-id'))) {
            throw new Zend_Controller_Action_Exception('Preview not found');
        }
        
        if(!$photo = $photoService->getPhoto($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }
        
        try {
            $photoService->removePhoto($photo);
            
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-preview', 'product', array('id' => $preview->getId())));
        } catch(Exception $e) {
            $this->_service->get('Logger')->log($e->getMessage(), 4);
        }
              
        $list = '';
        
        $root = $preview->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $previewPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/preview-main-photo.phtml', 'product', array('photos' => $previewPhotos, 'preview' => $preview));
        }   
    }
    
    public function movePreviewPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        $previewService = $this->_service->getService('Product_Service_Preview');
        
        if(!$preview = $previewService->getPreview($this->getRequest()->getParam('preview'))) {
            throw new Zend_Controller_Action_Exception('Preview not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Preview photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-preview', 'product', array('id' => $preview->getId())));
        $list = '';
        
        $root = $preview->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $previewPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/preview-main-photo.phtml', 'product', array('photos' => $previewPhotos, 'preview' => $preview));
        }
    }
    
    public function refreshStatusPreviewAction() {
        $previewService = $this->_service->getService('Product_Service_Preview');
        
        if(!$preview = $previewService->getPreview((int) $this->getRequest()->getParam('preview-id'))) {
            throw new Zend_Controller_Action_Exception('Preview not found');
        }
        
        $previewService->refreshStatusPreview($preview);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-preview', 'product'));
        $this->_helper->viewRenderer->setNoRender();
        
    }
}
?>