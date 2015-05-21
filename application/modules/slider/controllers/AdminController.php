<?php

class Slider_AdminController extends MF_Controller_Action {
    private $animations;
    
    public function init() {
        $this->_helper->ajaxContext
                ->addActionContext('add-slide', 'json')
                ->addActionContext('move-slide-layer', 'json')
                ->initContext();
        parent::init();
    }

    public function listSlideAction() {
        $sliderService = $this->_service->getService('Slider_Service_Slider');
        
        $sliderConfig = $this->_service->get('sliders');
        
        foreach($sliderConfig as $slug => $name) {
            if(!$slider = $sliderService->getSlider($slug, 'slug')) {
                $sliderService->saveSliderFromArray(array('slug' => $slug, 'name' => $name));
            }
        }

        if(!$slider = $sliderService->getSlider($this->getRequest()->getParam('slider', array_shift(array_keys($sliderConfig))), "slug")) {
            throw new Zend_Controller_Action_Exception('Slider not found');
        }
        
        $slideRoot = $sliderService->getSliderSlideRoot($slider);
        $slides = $slideRoot->getNode()->getChildren();
        
        $this->view->assign('sliderConfig', $sliderConfig);
        $this->view->assign('slider', $slider);
        $this->view->assign('slides', $slides);
    }
    
    public function listSlideDataAction() {
        $table = Doctrine_Core::getTable('Slider_Model_Doctrine_Slide');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Slider_DataTables_Slide', 
            'columns' => array('x.title'),
            'searchFields' => array('x.title')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result['id'];
            $row[] = $result['title'];
            $options = '<a href="' . $this->view->adminUrl('edit-slide', 'slider', array('id' => $result['id'])) . '" title="' . $this->view->translate('Edit') . '"><span class="icon16 icon-edit"></span></a>';
            $options .= '<a href="' . $this->view->adminUrl('delete-slide', 'slider', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon icon-remove"></span></a>';
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
    
    // json actions
    public function addSlidePhotoAction() {
        $sliderService = $this->_service->getService('Slider_Service_Slider');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$slider = $sliderService->getSlider($this->getRequest()->getParam('slider'))) {
            throw new Zend_Controller_Action_Exception('Slider not found');
        }
    
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        if(!array_key_exists('domain', $options)) {
            throw new Zend_Controller_Action_Exception('Domain string not set');
        }
        
        $href = $this->getRequest()->getParam('hrefs');

        if(is_array($href) && count($href)) {
            foreach($href as $h) {
                $path = str_replace("http://" . $options['domain'], "", urldecode($h));
                $filePath = $options['publicDir'] . $path;
                if(file_exists($filePath)) {
                    $pathinfo = pathinfo($filePath);
                    $slug = MF_Text::createSlug($pathinfo['basename']);
                    $name = MF_Text::createUniqueFilename($slug, $photoService->photosDir);
                    try {
                        $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                        $slideRoot = $sliderService->getSliderSlideRoot($slider);

                        $slide = $sliderService->saveSlideFromArray(array('slider_id' => $slider->getId()));
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Slider_Model_Doctrine_Slide::getSlidePhotoDimensions()), false, false);

                        $slide->set('PhotoRoot', $photo);
                        
                        $slide->save();

                        $slide->getNode()->insertAsLastChildOf($slideRoot);
                        $slideRoot->refresh();

                        $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('log')->log($e->getMessage(), 4);
                    }
                }
            }
        } elseif(is_string($href) && strlen($href)) {
            $path = str_replace("http://" . $options['domain'], "", urldecode($href));
            $filePath = urldecode($options['publicDir'] . $path);
            if(file_exists($filePath)) {
                $pathinfo = pathinfo($filePath);
                $slug = MF_Text::createSlug($pathinfo['basename']);
                $name = MF_Text::createUniqueFilename($slug, $photoService->photosDir);
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $slideRoot = $sliderService->getSliderSlideRoot($slider);

                    $slide = $sliderService->saveSlideFromArray(array('slider_id' => $slider->getId()));
                    $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Slider_Model_Doctrine_Slide::getSlidePhotoDimensions()), false, false);

                    $slide->set('PhotoRoot', $photo);
                        
                    $slide->save();

                    $slide->getNode()->insertAsLastChildOf($slideRoot);
                    $slideRoot->refresh();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $list = '';
        
        $slideRoot = $slider->get('SlideRoot');
        $slides = $slideRoot->getNode()->getChildren();
        $list = $this->view->partial('admin/slider-slide-photos.phtml', 'slider', array('slides' => $slides, 'slider' => $slider));
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $slider->getId()
        ));
    }
    
    public function editSlidePhotoAction() {
        $sliderService = $this->_service->getService('Slider_Service_Slider');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$slider = $sliderService->getSlider($this->getRequest()->getParam('slider'))) {
            throw new Zend_Controller_Action_Exception('Slider not found');
        }
        
        if(!$slide = $sliderService->getSlide($this->getRequest()->getParam('slide'))) {
            throw new Zend_Controller_Action_Exception('Slide not found');
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

                    $root = $slide->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Slider_Model_Doctrine_Slide::getSlidePhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Slider_Model_Doctrine_Slide::getSlidePhotoDimensions()), false);                    
                    }

                    $slide->set('PhotoRoot', $photo);
                    $slide->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $slideRoot = $slider->get('SlideRoot');
        $slides = $slideRoot->getNode()->getChildren();
        $list = $this->view->partial('admin/slider-slide-photos.phtml', 'slider', array('slides' => $slides, 'slider' => $slider));
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $slider->getId()
        ));
    }
    
    public function editSlideAction() {
        $sliderService = $this->_service->getService('Slider_Service_Slider');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$slider = $sliderService->getSlider($this->getRequest()->getParam('slider'))) {
            throw new Zend_Controller_Action_Exception('Slider not found');
        }
        
        if(!$slide = $sliderService->getSlide($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Slide not found');
        }
        
        $form = $sliderService->getSlideForm($slide);
        
        $form->transition->addMultiOptions($sliderService->getTargetTransitionsSelectOptions());

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    $slide = $sliderService->saveSlideFromArray($values);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-slide', 'slider'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $dimensions = Slider_Model_Doctrine_Slide::getSlidePhotoDimensions();
        
        $this->view->assign('slider', $slider);
        $this->view->assign('slide', $slide);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', $dimensions);
        $this->view->assign('form', $form);
    }
    
    public function deleteSliderSlideAction() {
        $sliderService = $this->_service->getService('Slider_Service_Slider');
        
        if($slider = $sliderService->getSlider($this->getRequest()->getParam('slider'))) {
            if($slide = $sliderService->getSlide($this->getRequest()->getParam('id'))) {
                $slide->getNode()->delete();
            }
        }
        
        $list = '';
        
        $slideRoot = $slider->get('SlideRoot');
        $slides = $slideRoot->getNode()->getChildren();
        $list = $this->view->partial('admin/slider-slide-photos.phtml', 'slider', array('slides' => $slides, 'slider' => $slider));
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $slider->getId()
        ));
    }
    
    public function moveSliderSlideAction() {
        $sliderService = $this->_service->getService('Slider_Service_Slider');
        
        if($slider = $sliderService->getSlider($this->getRequest()->getParam('slider'))) {
            if($slide = $sliderService->getSlide($this->getRequest()->getParam('id'))) {
                $sliderService->moveSliderSlide($slide, $this->getRequest()->getParam('dir'));
            }
        }
        
        $list = '';
        
        $slideRoot = $slider->get('SlideRoot');
        $slides = $slideRoot->getNode()->getChildren();
        $list = $this->view->partial('admin/slider-slide-photos.phtml', 'slider', array('slides' => $slides, 'slider' => $slider));
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $slider->getId()
        ));
    }
    
    public function listLayerDataAction() {
        $table = Doctrine_Core::getTable('Slider_Model_Doctrine_SlideLayer');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Slider_DataTables_SlideLayer', 
            'columns' => array(),
            'searchFields' => array()
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result['id'];
            if ($result['type'] == "text"):
                 $row[] = $result['text_html'];
            elseif($result['type'] == "image"):
                if ($result['PhotoRoot']['offset']):
                    $row[] = '<img src="/media/photos/'.$result['PhotoRoot']['offset'].'/126x126/'.$result['PhotoRoot']['filename'].'" data-original="/media/photos/'.$result['PhotoRoot']['offset'].'/'.$result['PhotoRoot']['filename'].'" alt="'.$result['PhotoRoot']['title'].'">';
                else:
                    $row[] = '';
                endif;
            elseif($result['type'] == "video"):
                if ($result['target_href']):
                    $row[] = '<iframe id="videoPreview" width="220" height="115" frameborder="0" allowfullscreen src="'.$result['target_href'].'"></iframe>';
                else:
                    $row[] = '';
                endif;
            endif;
            $row[] = $result['type'];
            $row[] = $result['animation'];
            $row[] = $result['easing'];
            $moving = '<a href="' . $this->view->adminUrl('move-slide-layer', 'slider', array('id' => $result->id, 'move' => 'up')) . '" class="move" title ="' . $this->view->translate('Move up') . '"><span class="icomoon-icon-arrow-up"></span></a>';     
            $moving .= '<a href="' . $this->view->adminUrl('move-slide-layer', 'slider', array('id' => $result->id, 'move' => 'down')) . '" class="move" title ="' . $this->view->translate('Move down') . '"><span class="icomoon-icon-arrow-down"></span></a>';
            $row[] = $moving;
            if ($result['type'] == "text"):
                $options = '<a href="' . $this->view->adminUrl('edit-slide-layer', 'slider', array('id' => $result['id'], 'slide-id' => $result['slide_id'])) . '" title="' . $this->view->translate('Edit') . '"><span class="icon16 icon-edit"></span></a>';
            elseif($result['type'] == "image"):
                $options = '<a href="' . $this->view->adminUrl('edit-slide-layer-image', 'slider', array('id' => $result['id'], 'slide-id' => $result['slide_id'])) . '" title="' . $this->view->translate('Edit') . '"><span class="icon16 icon-edit"></span></a>';
            elseif($result['type'] == "video"):
                $options = '<a href="' . $this->view->adminUrl('edit-slide-layer-video', 'slider', array('id' => $result['id'], 'slide-id' => $result['slide_id'])) . '" title="' . $this->view->translate('Edit') . '"><span class="icon16 icon-edit"></span></a>';
            endif;
            $options .= '<a href="' . $this->view->adminUrl('remove-slide-layer', 'slider', array('id' => $result['id'])) . '" class="remove2" title="' . $this->view->translate('Remove') . '"><span class="icon icon-remove"></span></a>';
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
    
    public function addSlideLayerAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $slideService = $this->_service->getService('Slider_Service_Slider');
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$slide = $slideService->getSlide($this->getRequest()->getParam('slide-id'))) {
            throw new Zend_Controller_Action_Exception('Slide not found');
        }

        $form = $slideLayerService->getLayerForm();
        
        $form->animation->addMultiOptions($slideLayerService->getTargetAnimationsSelectOptions());
        $form->easing->addMultiOptions($slideLayerService->getTargetEasingSelectOptions());
        $form->class->addMultiOptions($slideLayerService->getTargetClassSelectOptions());
        $form->removeElement("width_iframe");
        $form->removeElement("height_iframe");
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $slideLayerRoot = $slideLayerService->getSlideLayerRoot($slide);
                    $values['type'] = "text";
                    $values['slide_id'] = $slide->getId();

                    $slideLayer = $slideLayerService->saveSlideLayerFromArray($values);

                    $slideLayer->getNode()->insertAsLastChildOf($slideLayerRoot);
                    $slideLayerRoot->refresh();
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-slide', 'slider', array('slider' => $slide->getSliderId(), 'id' => $slide->getId())));
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
        $this->view->assign('slide', $slide);
    }
    
    public function editSlideLayerAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $slideService = $this->_service->getService('Slider_Service_Slider');
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$slide = $slideService->getSlide($this->getRequest()->getParam('slide-id'))) {
            throw new Zend_Controller_Action_Exception('Slide not found');
        }
        
        if(!$slideLayer = $slideLayerService->getSlideLayer($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Slide layer not found');
        }

        $form = $slideLayerService->getLayerForm($slideLayer);
        $form->animation->addMultiOptions($slideLayerService->getTargetAnimationsSelectOptions());
        $form->easing->addMultiOptions($slideLayerService->getTargetEasingSelectOptions());
        $form->class->addMultiOptions($slideLayerService->getTargetClassSelectOptions());
        $form->removeElement("width_iframe");
        $form->removeElement("height_iframe");
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $slideLayer = $slideLayerService->saveSlideLayerFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-slide', 'slider', array('slider' => $slide->getSliderId(), 'id' => $slide->getId())));
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
        $this->view->assign('slide', $slide);
    }
    
    public function addSlideLayerImageAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $slideService = $this->_service->getService('Slider_Service_Slider');
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$slide = $slideService->getSlide($this->getRequest()->getParam('slide-id'))) {
            throw new Zend_Controller_Action_Exception('Slide not found');
        }

        $form = $slideLayerService->getLayerForm();
        
        $form->animation->addMultiOptions($slideLayerService->getTargetAnimationsSelectOptions());
        $form->easing->addMultiOptions($slideLayerService->getTargetEasingSelectOptions());
        $form->removeElement('class');
        $form->removeElement('text_html');
        $form->removeElement("width_iframe");
        $form->removeElement("height_iframe");
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $slideLayerRoot = $slideLayerService->getSlideLayerRoot($slide);
                    $values['type'] = "image";
                    $values['slide_id'] = $slide->getId();

                    $slideLayer = $slideLayerService->saveSlideLayerFromArray($values);
                    
                    $root = $slideLayer->get('PhotoRoot');
                    if($root->isInProxyState()) {
                        $root = $photoService->createPhotoRoot();
                        $slideLayer->set('PhotoRoot', $root);
                        $slideLayer->save();
                    }

                    $slideLayer->getNode()->insertAsLastChildOf($slideLayerRoot);
                    $slideLayerRoot->refresh();
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-slide-layer-image', 'slider', array('slide-id' => $slideLayer->getSlideId(), 'id' => $slideLayer->getId())));
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
        $this->view->assign('slide', $slide);
    }
    
    public function editSlideLayerImageAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $slideService = $this->_service->getService('Slider_Service_Slider');
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$slide = $slideService->getSlide($this->getRequest()->getParam('slide-id'))) {
            throw new Zend_Controller_Action_Exception('Slide not found');
        }
        
        if(!$slideLayer = $slideLayerService->getSlideLayer($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Slide layer not found');
        }

        $form = $slideLayerService->getLayerForm($slideLayer);
        $form->animation->addMultiOptions($slideLayerService->getTargetAnimationsSelectOptions());
        $form->easing->addMultiOptions($slideLayerService->getTargetEasingSelectOptions());
        $form->removeElement('class');
        $form->removeElement('text_html');
        $form->removeElement("width_iframe");
        $form->removeElement("height_iframe");
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $slideLayer = $slideLayerService->saveSlideLayerFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-slide', 'slider', array('slider' => $slide->getSliderId(), 'id' => $slide->getId())));
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
        $this->view->assign('slideLayer', $slideLayer);
        $this->view->assign('slide', $slide);
    }
    
    public function addSlideLayerImagePhotoAction() {
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$slideLayer = $slideLayerService->getSlideLayer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Slide layer not found');
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

                    $root = $slideLayer->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Slider_Model_Doctrine_SlideLayer::getSlideLayerPhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Slider_Model_Doctrine_SlideLayer::getSlideLayerPhotoDimensions()), false);
                    }

                    $slideLayer->set('PhotoRoot', $photo);
                    $slideLayer->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $list = '';
        
        $slideLayerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $slideLayer->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $slideLayerPhotos->add($root);
            $list = $this->view->partial('admin/slide-layer-main-photo.phtml', 'slider', array('photos' => $slideLayerPhotos, 'slideLayer' => $slideLayer));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $slideLayer->getId()
        ));
        
    }
    
    public function editSlideLayerImagePhotoAction() {
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$slideLayer = $slideLayerService->getSlideLayer((int) $this->getRequest()->getParam('slide-layer-id'))) {
            throw new Zend_Controller_Action_Exception('Slide layer not found');
        }

        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            $this->view->messages()->add($translator->translate('First you have to choose picture'), 'error');
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-slide-layer-image', 'slider', array('id' => $slideLayer->getId(), 'slide-id' => $slideLayer->getSlideId())));
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-slide-layer-image-photo', 'slider', array('id' => $slideLayer->getId(), 'photo' => $photo->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-slide-layer-image', 'slider', array('id' => $slideLayer->getId(), 'slide-id' => $slideLayer->getSlideId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }

        $this->view->assign('slideLayer', $slideLayer);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Slider_Model_Doctrine_SlideLayer::getSlideLayerPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeSlideLayerImagePhotoAction() {
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$slideLayer = $slideLayerService->getSlideLayer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Slide layer not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $slideLayer->get('PhotoRoot')) {
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
        
        $slideLayerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $slideLayer->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $slideLayerPhotos->add($root);
            $list = $this->view->partial('admin/slide-layer-main-photo.phtml', 'slider', array('photos' => $slideLayerPhotos, 'slideLayer' => $slideLayer));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $slideLayer->getId()
        ));
        
    }
    
    public function addSlideLayerVideoAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $slideService = $this->_service->getService('Slider_Service_Slider');
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$slide = $slideService->getSlide($this->getRequest()->getParam('slide-id'))) {
            throw new Zend_Controller_Action_Exception('Slide not found');
        }

        $form = $slideLayerService->getLayerForm();
        
        $form->animation->addMultiOptions($slideLayerService->getTargetAnimationsSelectOptions());
        $form->easing->addMultiOptions($slideLayerService->getTargetEasingSelectOptions());
        $form->removeElement("class");
        $form->removeElement("text_html");
        $form->getElement("target_href")->setLabel("Video url");
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $slideLayerRoot = $slideLayerService->getSlideLayerRoot($slide);
                    $values['type'] = "video";
                    $values['slide_id'] = $slide->getId();

                    $slideLayer = $slideLayerService->saveSlideLayerFromArray($values);

                    $slideLayer->getNode()->insertAsLastChildOf($slideLayerRoot);
                    $slideLayerRoot->refresh();
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-slide', 'slider', array('slider' => $slide->getSliderId(), 'id' => $slide->getId())));
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
        $this->view->assign('slide', $slide);
    }
    
    public function editSlideLayerVideoAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $slideService = $this->_service->getService('Slider_Service_Slider');
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$slide = $slideService->getSlide($this->getRequest()->getParam('slide-id'))) {
            throw new Zend_Controller_Action_Exception('Slide not found');
        }
        
        if(!$slideLayer = $slideLayerService->getSlideLayer($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Slide layer not found');
        }

        $form = $slideLayerService->getLayerForm($slideLayer);
        $form->animation->addMultiOptions($slideLayerService->getTargetAnimationsSelectOptions());
        $form->easing->addMultiOptions($slideLayerService->getTargetEasingSelectOptions());
        $form->removeElement("class");
        $form->removeElement("text_html");
        $form->getElement("target_href")->setLabel("Video url");
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $slideLayer = $slideLayerService->saveSlideLayerFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-slide', 'slider', array('slider' => $slide->getSliderId(), 'id' => $slide->getId())));
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
        $this->view->assign('slide', $slide);
    }
    
    public function moveSlideLayerAction() {
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
     
        $this->view->clearVars();
        
        $slideLayer = $slideLayerService->getSlideLayer((int) $this->getRequest()->getParam('id'));
        $status = 'success';

        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

            $slideLayerService->moveSlideLayer($slideLayer, $this->getRequest()->getParam('move', 'down'));

            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage());
            $status= 'error';
        }
        
        $this->_helper->viewRenderer->setNoRender();
        
        $this->view->assign('status', $status);
    }
    
    public function removeSlideLayerAction() {
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        
        if(!$slideLayer = $slideLayerService->getSlideLayer($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Slide layer not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
            
            $slideLayerService->removeSlideLayer($slideLayer);
            
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
           $this->_service->get('doctrine')->getCurrentConnection()->rollback();
           $this->_service->get('log')->log($e->getMessage(), 4);
        }

        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $slideLayer->getId()
        ));
        
    }
}

