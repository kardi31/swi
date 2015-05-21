<?php

/**
 * Page_IndexController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Page_IndexController extends MF_Controller_Action {
    
    public function indexAction() {
       
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

        if(!$page = $pageService->getI18nPage($this->getRequest()->getParam('slug'), 'slug', $this->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        $leagueService = $this->_service->getService('League_Service_League');
        $matchService = $this->_service->getService('League_Service_Match');
        $leagues = $leagueService->getActiveLeaguesWithTable();
	$leagueIds = $leagueService->getActiveLeagueIds();
	$nextMatches = $matchService->getNextMatches($leagueIds);
	$prevMatches = $matchService->getPrevMatches($leagueIds);
	
        $this->view->assign('prevMatches', $prevMatches);
        $this->view->assign('nextMatches', $nextMatches);
        $this->view->assign('leagues', $leagues);
        
        $eventService = $this->_service->getService('District_Service_Event');
        $nextEvents = $eventService->getNextEvents();
        $this->view->assign('nextEvents',$nextEvents);
        
        $metatagService->setViewMetatags($page['metatag_id'], $this->view);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('page', $page);
        $this->view->assign('hideSlider', true);
    }
}

