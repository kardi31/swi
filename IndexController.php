<?php

class Default_IndexController extends MF_Controller_Action
{
    
    public function indexAction()
    {
//           $videoService = $this->_service->getService('Gallery_Service_Video');
//    $promotedVideo = $videoService->getPromotedVideo();
//    
//        $this->view->assign('promotedVideo', $promotedVideo);
      echo "dd";exit;
        $this->_helper->actionStack('layout');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $pageService = $this->_service->getService('Page_Service_Page');
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');

        // 1 - seniorzy
        $lastNews = $newsService->getLastCategoryNews(APPLICATION_GROUP,3);
        $this->view->assign('lastNews', $lastNews);
        
        $leagueService = $this->_service->getService('League_Service_League');
        $matchService = $this->_service->getService('League_Service_Match');
        $league = $leagueService->getActiveLeague(APPLICATION_GROUP);
	$myNextMatches = $matchService->getMyNextMatches(APPLICATION_GROUP);
	$lastResult = $matchService->getLastResult(APPLICATION_GROUP,Doctrine_Core::HYDRATE_ARRAY);
        $this->view->assign('myNextMatches', $myNextMatches);
        $this->view->assign('lastResult', $lastResult);
        $this->view->assign('league', $league);
	
        $teamService = $this->_service->getService('League_Service_Team');
	$myPlayers = $teamService->getMyTeamPlayers(APPLICATION_GROUP,Doctrine_Core::HYDRATE_ARRAY);
        
        $this->view->assign('myPlayers',$myPlayers);
         $galleryService = $this->_service->getService('Gallery_Service_Gallery');

	$lastGalleries = $galleryService->getLastGalleries(APPLICATION_GROUP,3,Doctrine_Core::HYDRATE_ARRAY);

        
        $this->view->assign('lastGalleries', $lastGalleries);
        
	$category = 'seniorzy';
        $this->view->assign('category',$category);
//        
        $this->_helper->actionStack('layout', 'index', 'default');
        
//          $eventService = $this->_service->getService('District_Service_Event');
//        $nextEvents = $eventService->getNextEvents();
//        $this->view->assign('nextEvents',$nextEvents);
      
        
        if(!$homepage = $pageService->getI18nPage('homepage', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Homepage not found');
        }
        $metatag = $homepage->get('Metatag');
        $metatag->Translation['pl']->title = null;
        if($homepage != NULL):
            $metatagService->setViewMetatags($metatag, $this->view);
        endif;
        
        $this->view->assign('homepage', $homepage);
        
        $this->view->modelPhoto = $photoService;
        
    }
        
        
        
//        var_dump(isset($this->view->nextEvents));exit;
    

    public function layoutAction()
    {
        
       
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getAllActiveBanners();
        $this->view->assign('banners', $banners);
        $this->_helper->actionStack('slider');
        $this->_helper->actionStack('menu');
        $this->_helper->viewRenderer->setNoRender(true);
    }


    public function leftSidebarAction()
    {
       
        $modelNews = new Application_Model_News();
        
        
        
        $news = $modelNews->getAllNewsNoPagination(6);
       
        $this->view->assign('news',$news);
        
        $this->_helper->viewRenderer->setResponseSegment('leftSidebar');
    }

    public function sidebarAction()
    {
        $resultService = $this->_service->getService('League_Service_Match');
          
        $results = $resultService->getLastResults();
        $this->view->assign('lastResults',$results);
        $this->_helper->viewRenderer->setResponseSegment('sidebar');
    }

    public function footerAction()
    {
        $this->_helper->viewRenderer->setResponseSegment('footer');
    }

    public function showNewsAction()
    {
         $modelNews = new Application_Model_News();
         $modelPhoto = new Application_Model_Photo();
        
        
//         $allNews = $modelNews->getAllNewsNoPagination(1500);
//    
//         foreach($allNews as $n):
//        //     echo "ok";
//             $slug = Application_Model_News::createUniqueTableSlug('aktualnosci', $n['tytul']);
//             $modelNews->addSlug($n['id_news'],$slug);
//         endforeach;
//         exit;
        if(!$news = $modelNews->getNews($this->getRequest()->getParam('slug'))){
            throw new Zend_Exception('News not found');
        }
        $this->view->assign('news',$news);
        $this->view->modelPhoto = $modelPhoto;
        $this->_helper->actionStack('layout');
    }

     public function contactAction() {
        
        
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $serviceService = $this->_service->getService('Default_Service_Service');
        
        
        if(!$page = $pageService->getI18nPage('contact', 'type', $this->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
 
        $contactEmail = $this->getInvokeArg('bootstrap')->getOption('contact_email');
        $mailerEmail = $this->getInvokeArg('bootstrap')->getOption('mailer_email');
        
        if ($page != NULL):
            $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
        endif;
        $form = new Default_Form_Contact();
        
	$form->getElement('name')->clearDecorators();
	$form->getElement('name')->addDecorator('viewHelper');
	$form->getElement('name')->addDecorator('Errors');
	
	$form->getElement('email')->clearDecorators();
	$form->getElement('email')->addDecorator('viewHelper');
	$form->getElement('email')->addDecorator('Errors');
	
	$form->getElement('phone')->clearDecorators();
	$form->getElement('phone')->addDecorator('viewHelper');
	$form->getElement('phone')->addDecorator('Errors');
	
	$form->getElement('message')->clearDecorators();
	$form->getElement('message')->addDecorator('viewHelper');
	$form->getElement('message')->addDecorator('Errors');
	
        $captchaDir = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('captchaDir');
        $form->addElement('captcha', 'captcha',
            array(
            'label' => 'Rewrite the chars', 
            'captcha' => array(
                'captcha' => 'Image',  
                'wordLen' => 5,  
                'timeout' => 300,
                'font' => APPLICATION_PATH . '/../data/arial.ttf',  
                'imgDir' => $captchaDir,  
                'imgUrl' => $this->view->serverUrl() . '/captcha/',  
            )
        ));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    
                   if(!strlen($contactEmail)){
                        $this->_helper->redirector->gotoUrl($this->view->url(array('success' => 'fail'), 'domain-contact'));
                    }
                    $values = $_POST;
                    $serviceService->sendMail($values,$contactEmail,$mailerEmail);
                    
                    $this->view->messages()->add($this->view->translate('Message sent'));
                    $this->_helper->redirector->gotoUrl($this->view->url(array('success' => 'fail'), 'domain-contact'));
                    
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
          

        $this->view->assign('form', $form);
        $this->view->assign('page', $page);
        $this->view->assign('hideSlider', true);
        $this->view->assign('success',$this->getRequest()->getParam('success'));
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function sliderAction() {
        $sliderService = $this->_service->getService('Slider_Service_Slider');
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        $mainSliderSlides = $sliderService->getAllForSlider("main");
        $mainSlides = array();
        foreach($mainSliderSlides[0]['Slides'] as $slide):
            $layers = $slideLayerService->getLayersForSlide($slide['id']);
            $slide['Layers'] = $layers;
            $mainSlides[] = $slide;
        endforeach;
        $this->view->assign('mainSlides',$mainSlides);
        $this->_helper->viewRenderer->setResponseSegment('slider');
	
	
     
    }
    
    public function menuAction(){
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $menuService = $this->_service->getService('Menu_Service_Menu');
        
       
        if(!$menu = $menuService->getMenu(APPLICATION_GROUP)) {
            throw new Zend_Controller_Action_Exception('Menu not found');
        }
        
        $treeRoot = $menuService->getMenuItemTree($menu, $this->view->language);
        $tree = $treeRoot[0]->getNode()->getChildren();
            
        $activeLanguages = $i18nService->getLanguageList();
        
        $this->view->assign('activeLanguages', $activeLanguages);
        
        $this->view->assign('menu', $menu);
        $this->view->assign('tree', $tree);
        
        $this->_helper->viewRenderer->setNoRender();
    }
    
     public function aboutUsAction() {
       
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

        if(!$page = $pageService->getI18nPage('about-us', 'type', $this->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        if(!$dlaMediow = $pageService->getI18nPage('dla-mediow', 'type', $this->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        $boardService = $this->_service->getService('League_Service_Board');
        $boards = $boardService->getAllBoards(Doctrine_Core::HYDRATE_ARRAY);
        $this->view->assign('boards',$boards);
        
        $metatagService->setViewMetatags($page['metatag_id'], $this->view);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('page', $page);
        $this->view->assign('dlaMediow', $dlaMediow);
        $this->view->assign('hideSlider', true);
    }
}
