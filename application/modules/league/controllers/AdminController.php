<?php

/**
 * Order_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_AdminController extends MF_Controller_Action {
    
    public function listLeagueAction() {
        
    }
    
    public function listLeagueDataAction() {
        $table = Doctrine_Core::getTable('League_Model_Doctrine_League');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'League_DataTables_League', 
            'columns' => array('l.id','l.name','l.active'),
            'searchFields' => array('l.id','l.name','l.active')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            
            $row[] = $result['id'];
            $row[] = $result['name'];
           
	     if($result['active']==1)
                $row[] = '<a href="' . $this->view->adminUrl('set-league-active', 'league', array('id' => $result['id'])) . '" title="' . $this->view->translate('Aktywna') . '"><span class="icon16 icomoon-icon-checkbox"></span></a>';
            else
                $row[] = '<a href="' . $this->view->adminUrl('set-league-active', 'league', array('id' => $result['id'])) . '" title="' . $this->view->translate('Nieaktywna') . '"><span class="icon16 icomoon-icon-checkbox-unchecked"></span></a>';
            
            $options = '<a href="' . $this->view->adminUrl('edit-league', 'league', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;';     
            $options .= '<a href="' . $this->view->adminUrl('remove-league', 'league', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon16 icon-remove"></span></a>&nbsp;&nbsp;';     
            
	    
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
    
    public function addLeagueAction() {
        $leagueService = $this->_service->getService('League_Service_League');
        $groupService = $this->_service->getService('League_Service_Group');
        
        
        $form = $leagueService->getLeagueForm();
        $form->getElement('group_id')->setMultiOptions($groupService->getGroupsForForm()); 
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $league = $leagueService->saveLeagueFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-league', 'league'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
    }
    
    public function editLeagueAction() {
        $leagueService = $this->_service->getService('League_Service_League');
        $groupService = $this->_service->getService('League_Service_Group');
        
        if(!$league = $leagueService->getLeague((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('League not found');
        }
        
        $form = $leagueService->getLeagueForm($league);
        $form->getElement('group_id')->setMultiOptions($groupService->getGroupsForForm()); 
        $form->getElement('group_id')->setValue($league->group_id);
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $league = $leagueService->saveLeagueFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-league', 'league'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
        $this->view->assign('league', $league);
    }
    
    public function setLeagueActiveAction() {
        $leagueService = $this->_service->getService('League_Service_League');
        
        if(!$league = $leagueService->getLeague((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('League not found');
        }
        if($league->get('active'))
            $league->set('active',false);
        else
            $league->set('active',true);
        $league->save();
        $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
               
    } 
    
    public function removeLeagueAction() {
        $leagueService = $this->_service->getService('League_Service_League');
        
        if(!$league = $leagueService->getLeague((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('League not found');
        }
        $league->get('Matches')->delete();
        $league->get('Tabela')->delete();
        $league->get('Team')->delete();
        $league->delete();
        $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
               
    } 
    
    /* league - finish */
    
    /* match - start */
    
    public function listMatchAction() {
        $league_id = $this->getRequest()->getParam('league_id');
        $this->view->assign('league_id',$league_id);
    }
    
    public function listMatchDataAction() {
        $table = Doctrine_Core::getTable('League_Model_Doctrine_Match');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'League_DataTables_Match', 
            'columns' => array('m.id','t1.name','t2.name','m.match_date'),
            'searchFields' => array('m.id','t1.name','t2.name','m.match_date')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            
            $row[] = $result['id'];
            $row[] = $result['Team1']['name'];
            $row[] = $result['Team2']['name'];
            $goal = "Edit &nbsp; &nbsp; <input type = 'checkbox' rel = '".$result['id']."' /><br /><br />";
            $goal .= "<input type = 'text' size='4' style='width:20px' disabled parent = '".$result['id']."' inp_id = '".$result['id']."_1' value='".$result['goal1']."' />&nbsp;";
            $goal .= "<input type = 'text' size = '4' style='width:20px' disabled parent = '".$result['id']."' inp_id = '".$result['id']."_2' value='".$result['goal2']."' /><br />";
            $goal .= "<input type = 'submit' sub_id = '".$result['id']."' disabled parent = '".$result['id']."' name='submit' value='submit' />";
            
            $row[] = $goal;
            $row[] = $result['match_date'];
            $options = '<a href="' . $this->view->adminUrl('edit-match', 'league', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;';     
            
            $options .= '<a href="' . $this->view->adminUrl('remove-match', 'league', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon16 icon-remove"></span></a>&nbsp;&nbsp;';     
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
    
    public function saveMatchAction() {
        $matchService = $this->_service->getService('League_Service_Match');
        $tabelaService = $this->_service->getService('League_Service_Tabela');
        
        if(!$match = $matchService->getMatch((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Match not found');
        }
        
        $match->setGoal1($this->getRequest()->getParam('goal1'));
        $match->setGoal2($this->getRequest()->getParam('goal2'));
        $match->setPlayed();
        $match->save();
        
        $tabelaService->calculateTeamTable($match);
        
        
        $response = array(
            "success" => "success"
        );
        $this->_helper->json($response);
    }
    
    public function editMatchAction() {
        $matchService = $this->_service->getService('League_Service_Match');
        $teamService = $this->_service->getService('League_Service_Team');
        $shooterService = $this->_service->getService('League_Service_Shooter');
        if(!$match = $matchService->getMatch((int) $this->getRequest()->getParam('id'),'id')) {
            throw new Zend_Controller_Action_Exception('Match not found');
        }
        $players1 = $teamService->getTeamPlayers($match['team1'],'id');
        $players2 = $teamService->getTeamPlayers($match['team2'],'id');
        $shooters = $shooterService->getMatchShooters($match['id']);
//        Zend_Debug::dump($shooters);exit;
        
    if(isset($_POST['submit'])){
        $values = $_POST;
        $matchService->saveShootersFromArray($values,$match);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-match', 'league',array('league_id' => $match->league_id)));
                
    }
        
        $this->view->assign('match',$match);
        $this->view->assign('players1',$players1);
        $this->view->assign('players2',$players2);
        $this->view->assign('shooters',$shooters);
        
    }
    
    /* match - finish */
    
    public function timetableAction() {
        $matchService = $this->_service->getService('League_Service_Match');
        $teamService = $this->_service->getService('League_Service_Team');
        $leagueService = $this->_service->getService('League_Service_League');
        $form = new League_Form_Timetable();
        
        if(!$league = $leagueService->getLeague((int) $this->getRequest()->getParam('league_id'))) {
            throw new Zend_Controller_Action_Exception('League not found');
        }
        
        $teams = $teamService->getLeagueTeams($league->id,'id');
        
        $teamsTimetable = $teamService->getTeamsTimetable($league->id);
		
        for($i=1;$i<11;$i++):
            $t = $form->getElement('team'.$i);
            foreach($teams as $team):
                $t->addMultiOption($team->id,$team->name);
            endforeach;
        endfor;
        
        
        $this->view->assign('teamsTimetable',$teamsTimetable);
        $this->view->assign('league',$league);
        $this->view->assign('form',$form);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    $values['league_id'] = $league->id;
                    
                    $matchService->saveTimetableFromArray($values); 

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('timetable', 'league',array('league_id' => $league->id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
        
    }
    
    public function listBookingAction() {
        $league_id = $this->getRequest()->getParam('league_id');
        
        $this->view->assign('league_id', $league_id);
    }
    
    public function listBookingDataAction() {
        $table = Doctrine_Core::getTable('League_Model_Doctrine_Booking');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'League_DataTables_Booking', 
            'columns' => array('b.id','p.last_name', 't.name','p.weight'),
            'searchFields' => array('b.id','p.last_name', 't.name')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result['id'];
            $row[] = $result['Player']['last_name']." ".$result['Player']['first_name'];
            $row[] = $result['Player']['Team']['name'];
            if($result['weight']==2)
                $row[] = "Czerwona";
            else
                $row[] = "Żółta";
            
            $row[] = $result['quantity'];
            if($result['active']==1)
                $row[] = '<a href="' . $this->view->adminUrl('set-booking-active', 'league', array('id' => $result['id'])) . '" title="' . $this->view->translate('Aktywna') . '"><span class="icon16 icomoon-icon-checkbox"></span></a>';
            else
                $row[] = '<a href="' . $this->view->adminUrl('set-booking-active', 'league', array('id' => $result['id'])) . '" title="' . $this->view->translate('Nieaktywna') . '"><span class="icon16 icomoon-icon-checkbox-unchecked"></span></a>';
            
            $options = '<a href="' . $this->view->adminUrl('edit-booking', 'league', array('id' => $result['id'])) .'/league_id/'.$result['Player']['Team']['league_id']. '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
             
            $options .= '<a href="' . $this->view->adminUrl('remove-booking', 'league', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icomoon-icon-remove"></span></a>';
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
    
    public function addBookingAction() {
        $bookingService = $this->_service->getService('League_Service_Booking');
        $playerService = $this->_service->getService('League_Service_Player');
        
//        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
//            throw new Zend_Controller_Action_Exception('Booking not found');
//        }
        
        $league_id = $this->getRequest()->getParam('league_id');
        
        $form = $bookingService->getBookingForm();
        $form->getElement('player_id')->setMultiOptions($playerService->prependPlayerOptions($league_id));
        
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $booking = $bookingService->saveBookingFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-booking', 'league',array('league_id' => $league_id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('league_id', $league_id);
        $this->view->assign('form', $form);
    }
    
    public function editBookingAction() {
        $bookingService = $this->_service->getService('League_Service_Booking');
        $playerService = $this->_service->getService('League_Service_Player');
        
        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Booking not found');
        }
        
        $league_id = $this->getRequest()->getParam('league_id');
        
        $form = $bookingService->getBookingForm($booking);
        $form->getElement('player_id')->setMultiOptions($playerService->prependPlayerOptions($league_id));
        $form->getElement('player_id')->setValue($booking['player_id']);
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $booking = $bookingService->saveBookingFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-booking', 'league',array('league_id' => $league_id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('league_id', $league_id);
        $this->view->assign('form', $form);
    }
    
    public function setBookingActiveAction() {
        $bookingService = $this->_service->getService('League_Service_Booking');
        
        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Booking not found');
        }
        if($booking->get('active'))
            $booking->set('active',false);
        else
            $booking->set('active',true);
        $booking->save();
        $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
               
    } 
    
    public function removeBookingAction() {
        $bookingService = $this->_service->getService('League_Service_Booking');
        
        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Booking not found');
        }
        
        $booking->delete();
        $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
               
    } 
    
    public function addTeamAction() {
        $teamService = $this->_service->getService('League_Service_Team');
        $leagueService = $this->_service->getService('League_Service_League');
        
        $league_id = $this->getRequest()->getParam('league_id');
        
        if(!$league = $leagueService->getLeague($league_id)) {
            throw new Zend_Controller_Action_Exception('League not found');
        }
        
        $form = $teamService->getTeamForm();
        
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $values['league_id'] = $league_id;
                    $team = $teamService->saveTeamFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-team', 'league',array('league_id' => $league_id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('league', $league);
        $this->view->assign('league_id', $league_id);
        $this->view->assign('form', $form);
    }
    
    public function editTeamAction() {
        $teamService = $this->_service->getService('League_Service_Team');
        $leagueService = $this->_service->getService('League_Service_League');
        
	if(!$team = $teamService->getTeam((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Booking not found');
        }
        
        $league_id = $this->getRequest()->getParam('league_id');
        
        if(!$league = $leagueService->getLeague($league_id)) {
            throw new Zend_Controller_Action_Exception('League not found');
        }
        
        $form = $teamService->getTeamForm($team);
        
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $_POST;

		    $team['name'] = $values['name'];
		    
		    $playerCounter = count($team['Players']);
		    foreach($values['new_player1'] as $value):
			if(!strlen($value))
			    break;
			
			$fullname = explode(' ',$value);
			$team['Players'][$playerCounter]['last_name'] = $fullname[0];
			$team['Players'][$playerCounter]['first_name'] = $fullname[1];
			$team['Players'][$playerCounter]['team_id'] = $team['id'];
			$playerCounter++;
		    endforeach;
		    $team->save();
		    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-team', 'league',array('league_id' => $league_id)));
                } catch(Exception $e) {
		    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('league', $league);
        $this->view->assign('league_id', $league_id);
        $this->view->assign('form', $form);
        $this->view->assign('team', $team);
    }
    
     public function listTeamAction() {
        $leagueService = $this->_service->getService('League_Service_League');
        $league_id = $this->getRequest()->getParam('league_id');
        
        if(!$league = $leagueService->getLeague($league_id)) {
            throw new Zend_Controller_Action_Exception('League not found');
        }
        
        $this->view->assign('league', $league);
        $this->view->assign('league_id', $league_id);
    }
    
    public function listTeamDataAction() {
        $table = Doctrine_Core::getTable('League_Model_Doctrine_Team');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'League_DataTables_Team', 
            'columns' => array('t.id','t.name','t.my_team'),
            'searchFields' => array('t.id', 't.name','t.my_team')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result['id'];
            $row[] = $result['name'];
            
	    if($result['my_team']==1)
                $row[] = '<a href="' . $this->view->adminUrl('set-my-team', 'league', array('id' => $result['id'])) . '" title="' . $this->view->translate('Aktywna') . '"><span class="icon16 icomoon-icon-checkbox"></span></a>';
            else
                $row[] = '<a href="' . $this->view->adminUrl('set-my-team', 'league', array('id' => $result['id'])) . '" title="' . $this->view->translate('Nieaktywna') . '"><span class="icon16 icomoon-icon-checkbox-unchecked"></span></a>';
            
	    
            $options = '<a href="' . $this->view->adminUrl('edit-team', 'league', array('id' => $result['id'])) .'/league_id/'.$result['league_id']. '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
             
            $options .= '<a href="' . $this->view->adminUrl('remove-team', 'league', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icomoon-icon-remove"></span></a>';
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
    
    public function setMyTeamAction() {
        $teamService = $this->_service->getService('League_Service_Team');
        
        if(!$team = $teamService->getTeam((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Team not found');
        }
        if($team->get('my_team'))
            $team->set('my_team',false);
        else
            $team->set('my_team',true);
        $team->save();
        $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
               
    } 
    
    
    public function editPlayerAction() {
        $teamService = $this->_service->getService('League_Service_Team');
        $playerService = $this->_service->getService('League_Service_Player');
        $leagueService = $this->_service->getService('League_Service_League');
        
	if(!$player = $playerService->getPlayer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Player not found');
        }
        
        
        $form = $playerService->getPlayerForm($player);
        
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $_POST;

		    $team['name'] = $values['name'];
		    
		    $playerCounter = count($team['Players']);
		    foreach($values['new_player1'] as $value):
			if(!strlen($value))
			    break;
			
			$fullname = explode(' ',$value);
			$team['Players'][$playerCounter]['last_name'] = $fullname[0];
			$team['Players'][$playerCounter]['first_name'] = $fullname[1];
			$team['Players'][$playerCounter]['team_id'] = $team['id'];
			$playerCounter++;
		    endforeach;
		    $team->save();
		    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-team', 'league',array('league_id' => $league_id)));
                } catch(Exception $e) {
		    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('player', $player);
        $this->view->assign('league_id', $league_id);
        $this->view->assign('form', $form);
        $this->view->assign('team', $team);
    }
    
    /*
     * 
     *  coaches
     * 
     * 
     */
    
    public function addCoachAction() {
        $teamService = $this->_service->getService('League_Service_Team');
        $coachService = $this->_service->getService('League_Service_Coach');
               
        $form = $coachService->getCoachForm();
        
        $form->getElement('team_id')->addMultiOptions($teamService->prependMyTeamsOptions());
        
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $_POST;

		    $coachService->saveCoachFromArray($values);
		    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-coach', 'league'));
                } catch(Exception $e) {
		    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
    }
    
    public function editCoachAction() {
        $teamService = $this->_service->getService('League_Service_Team');
        $coachService = $this->_service->getService('League_Service_Coach');
        
	if(!$coach = $coachService->getCoach((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Player not found');
        }
        
        
        $form = $coachService->getCoachForm($coach);
        
        $form->getElement('team_id')->addMultiOptions($teamService->prependMyTeamsOptions());
        $form->getElement('team_id')->setValue($coach->get('team_id'));
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $_POST;

		    $coachService->saveCoachFromArray($values);
		    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-coach', 'league'));
                } catch(Exception $e) {
		    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('player', $player);
        $this->view->assign('league_id', $league_id);
        $this->view->assign('form', $form);
        $this->view->assign('coach', $coach);
    }
    
    public function removeCoachAction() {
        $coachService = $this->_service->getService('League_Service_Coach');
        
	if(!$coach = $coachService->getCoach((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Player not found');
        }
        
        $coach->delete();
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-coach', 'league'));
                
        
    }
    
    
    public function listCoachAction() {
        
    }
    
    public function listCoachDataAction() {
        $table = Doctrine_Core::getTable('League_Model_Doctrine_Coach');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'League_DataTables_Coach', 
            'columns' => array('c.id','c.first_name','c.last_name','t.name'),
            'searchFields' => array('c.id', 'c.first_name','c.last_name','t.name')
        ));
        
        $results = $dataTables->getResult();
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result['id'];
            $row[] = $result['first_name'];
            $row[] = $result['last_name'];
            $row[] = $result['Team']['name'].' <br /> '.$result['Team']['League']['name'].' - '.$result['Team']['League']['Group']['name'];
            
            $options = '<a href="' . $this->view->adminUrl('edit-coach', 'league', array('id' => $result['id'])). '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
             
            $options .= '<a href="' . $this->view->adminUrl('remove-coach', 'league', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icomoon-icon-remove"></span></a>';
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
    
    /*
     * 
     *  coaches
     * 
     * 
     */
    
    public function addBoardAction() {
        $boardService = $this->_service->getService('League_Service_Board');
               
        $form = $boardService->getBoardForm();
        
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $_POST;

		    $boardService->saveBoardFromArray($values);
		    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-board', 'league'));
                } catch(Exception $e) {
		    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
    }
    
    public function editBoardAction() {
        $boardService = $this->_service->getService('League_Service_Board');
        
	if(!$board = $boardService->getBoard((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Board not found');
        }
        
        $form = $boardService->getBoardForm($board);
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $_POST;

		    $boardService->saveBoardFromArray($values);
		    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-board', 'league'));
                } catch(Exception $e) {
		    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('board', $board);
        $this->view->assign('form', $form);
    }
    
    public function removeBoardAction() {
        $boardService = $this->_service->getService('League_Service_Board');
        
	if(!$board = $boardService->getBoard((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Board not found');
        }
        
        $board->delete();
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-board', 'league'));
                
        
    }
    
    
    public function listBoardAction() {
        
    }
    
    public function listBoardDataAction() {
        $table = Doctrine_Core::getTable('League_Model_Doctrine_Board');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'League_DataTables_Board', 
            'columns' => array('b.id','b.first_name','b.last_name'),
            'searchFields' => array('b.id', 'b.first_name','b.last_name')
        ));
        
        $results = $dataTables->getResult();
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result['id'];
            $row[] = $result['first_name'];
            $row[] = $result['last_name'];
            $row[] = $result['position'];
            
            $options = '<a href="' . $this->view->adminUrl('edit-board', 'league', array('id' => $result['id'])). '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
             
            $options .= '<a href="' . $this->view->adminUrl('remove-board', 'league', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icomoon-icon-remove"></span></a>';
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
}

