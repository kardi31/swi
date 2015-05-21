<?php

/**
 * Order_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_IndexController extends MF_Controller_Action {
 
    public function indexAction() {

       
        $orderService = $this->_service->getService('Order_Service_Order');
     
        $modelCart = $orderService->getCart();
    
   
    }
    
    public function timetableAction()
    {
        
        $matchService = $this->_service->getService('League_Service_Match');
        $leagueService = $this->_service->getService('League_Service_League');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
        $data = $matchService->getTimetableDate($league->id);
       
        $timetable = $matchService->getTimetable($league->id);
        $this->view->assign('data',$data->toArray());
        $this->view->assign('timetable',$timetable);
         $this->view->assign('league',$league);
         
        
         $this->_helper->actionStack('layout','index','default');
    }
    
    public function showResultAction()
    {
        $leagueService = $this->_service->getService('League_Service_League');
        $matchService = $this->_service->getService('League_Service_Match');
        $shooterService = $this->_service->getService('League_Service_Shooter');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
       
        $results = $matchService->getResults($league->id);
        $this->view->assign('results',$results);
         $this->view->assign('league',$league);
        $this->view->assign('shooterService',$shooterService);
         $this->_helper->actionStack('layout','index','default');
    }
    
    public function showTableAction()
    {
        $leagueService = $this->_service->getService('League_Service_League');
        $matchService = $this->_service->getService('League_Service_Match');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
        $results = $matchService->getTable($league->id);
        $this->view->assign('tabela',$results);
         $this->view->assign('league',$league);
         $this->_helper->actionStack('layout','index','default');
    }

    public function showShootersAction()
    {
        $leagueService = $this->_service->getService('League_Service_League');
        $shooterService = $this->_service->getService('League_Service_Shooter');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
        $results = $shooterService->getShooters($league->id);
        $this->view->assign('strzelcy',$results);
         $this->view->assign('league',$league);
         $this->_helper->actionStack('layout','index','default');
    }
    
    public function showBookingAction()
    {
        $leagueService = $this->_service->getService('League_Service_League');
        $bookingService = $this->_service->getService('League_Service_Booking');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
        $yellowCards = $bookingService->getBooking($league->id,1);
        $redCards = $bookingService->getBooking($league->id,2);
        $this->view->assign('redCards',$redCards);
        $this->view->assign('yellowCards',$yellowCards);
         $this->view->assign('league',$league);
         $this->_helper->actionStack('layout','index','default');
    }

    public function showSquadAction()
    {
         $leagueService = $this->_service->getService('League_Service_League');
        $teamService = $this->_service->getService('League_Service_Team');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
        $team = $teamService->getTeam($this->getRequest()->getParam('team'),'slug');
         $this->view->assign('team',$team);
         $this->_helper->actionStack('layout','index','default');
    }
    
     public function showCupAction() {
       
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        $cupService = $this->_service->getService('League_Service_Cup');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

        if(!$cup = $cupService->getCup($this->getRequest()->getParam('slug'), 'slug', Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Cup not found');
        }
        $photoDimension = $photoDimensionService->getElementDimension('page');
        
        $metatagService->setViewMetatags($cup['metatag_id'], $this->view);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('cup', $cup);
        $this->view->assign('photoDimension', $photoDimension);
    }
    
    public function showTeamAction()
    {
        $teamService = $this->_service->getService('League_Service_Team');
        
	$myCoach = $teamService->getMyTeamCoach(APPLICATION_GROUP,Doctrine_Core::HYDRATE_ARRAY);
	$myPlayers = $teamService->getMyTeamPlayers(APPLICATION_GROUP,Doctrine_Core::HYDRATE_ARRAY);
        $team = $teamService->getMyTeam(APPLICATION_GROUP,Doctrine_Core::HYDRATE_ARRAY);
        
        
//        $metatag->Translation['pl']->title = "Skład drużyny ".$team." Świt Krzeszowice";
//        $metatag = $homepage->get('Metatag');
        
        $this->view->assign('coach',$myCoach);
        $this->view->assign('myPlayers',$myPlayers);
         $this->view->assign('team',$team);
         $this->view->assign('hideSlider',true);
         $this->_helper->actionStack('layout','index','default');
    }

}

