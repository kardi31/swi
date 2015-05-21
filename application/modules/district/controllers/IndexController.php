<?php

/**
 * News_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class District_IndexController extends MF_Controller_Action {
 
    
    public function calendarAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $table = Doctrine_Core::getTable('District_Model_Doctrine_Event');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'District_DataTables_Event', 
            'columns' => array('x.id','xt.title', 'x.created_at','x.updated_at','x.publish_date'),
            'searchFields' => array('x.id','xt.title','x.created_at','x.updated_at','x.publish_date')
        ));
        
        $results = $dataTables->getResult();
        $language = $i18nService->getAdminLanguage();

        $rows = array();
        foreach($results as $result) {
            
            $row = array();
            $row['date'] = $result['publish_date'];
            $row['type'] = 'meeting';
            if(strlen($result['url']))
                $row['title'] = "<a href=".$result['url'].">".$result['Translation'][$this->language]['title']."</a>";
            else{
                $row['title'] = $result['Translation'][$this->language]['title'];
            }
            $row['description'] = $result['Translation'][$this->language]['content'];;
           
           
            $rows[] = $row;
        }
        
        $this->view->assign('data',json_encode($rows));
        
        
        $this->_helper->viewRenderer->setResponseSegment('calendar');

    }
    
    public function articlesAction() {
    
    
    }
    public function nextEventsAction(){
        echo "c";exit;
        $eventService = $this->_service->getService('District_Service_Event');
        
        $nextEvents = $eventService->getNextEvents();
        var_dump($nextEvents);exit;
        $this->view->assign('nextEvents',$nextEvents);
        
       // $this->_helper->viewRenderer->setNoRender();
    }
    
   public function articleAction() {
        $eventService = $this->_service->getService('District_Service_Event');
        $adService = $this->_service->getService('Banner_Service_Ad');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $settingsService = $this->_service->getService('Default_Service_Setting');
        
        if(!$event = $eventService->getFullEvent($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Event not found', 404);
        }
        $ad = $adService->getActiveAd($event['VideoRoot']['Ad']['id']);
       
        $lastServerId = $settingsService->getSetting('server',Doctrine_Core::HYDRATE_RECORD);
        
        $videoUrl = $event['VideoRoot']['url'];
        // jak nie vimeo i youtube
        if(strpos($videoUrl,'vimeo')==false && strpos($videoUrl,'youtube')==false){
            if($lastServerId->value==1){
                $videoUrl = str_replace('stream2', 'stream1', $videoUrl);
                $lastServerId->value = 2;
                $lastServerId->save();
            }
            elseif($lastServerId->value==2){
                $videoUrl = str_replace('stream1', 'stream2', $videoUrl);
                $lastServerId->value = 1;
                $lastServerId->save();
            }
        }
        
        $metatagService->setViewMetatags($event['metatag_id']);
               
        $otherEvents = $eventService->getOtherEvents($event,Doctrine_Core::HYDRATE_ARRAY);
        
      
        $this->view->assign('otherEvents', $otherEvents);
        $this->view->assign('article', $event);
     
       
       $this->view->assign('videoUrl',$videoUrl);
       $this->view->assign('ad',$ad);
       $this->view->assign('otherEvents',$otherEvents);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        
        $this->_helper->layout->setLayout('article');
    }
    
     public function randomPersonAction(){
        $peopleService = $this->_service->getService('District_Service_People');
        
        $person = $peopleService->getRandomPerson();
        $this->view->assign('person',$person);
        
        
        $this->_helper->viewRenderer->setResponseSegment('randomPerson');
    }
    
    public function showPersonAction() {
        $peopleService = $this->_service->getService('District_Service_People');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        
        if(!$person = $peopleService->getFullPerson($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Person not found', 404);
        }
        
        
        $metatagService->setViewMetatags($person->get('Metatags'), $this->view);
       
        $this->view->assign('person', $person);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        
        $this->_helper->layout->setLayout('article');
    }
    
    public function peopleListAction(){
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->layout->setLayout('article');
        
         $peopleService = $this->_service->getService('District_Service_People');
        
        
        if(!$people = $peopleService->getAllPeople($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Person not found', 404);
        }
        
        $this->view->assign('people', $people);
        
    }
    
    /* people - end */
    
    /* attraction - start */
    
    public function listAttractionAction() {
        $attractionService = $this->_service->getService('District_Service_Attraction');
        
        $attractions = $attractionService->getAllAttractions();
        
        $this->view->assign('attractions', $attractions);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        
        $this->_helper->layout->setLayout('article');
    }
    
    public function showAttractionAction(){
        $attractonService = $this->_service->getService('District_Service_Attraction');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        if(!$attraction = $attractonService->getFullAttraction($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Attraction not found', 404);
        }
        
        $metatagService->setViewMetatags($attraction->get('Metatags'), $this->view);
       
        $this->view->assign('attraction', $attraction);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        
        $this->_helper->layout->setLayout('article');
    }
}

