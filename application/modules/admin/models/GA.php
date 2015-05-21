<?php

/**
 * Admin_Model_GA
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Admin_Model_GA 
{
    protected $_profileId;
    protected $_client;
    protected $_service;
    
    public function __construct($email, $password, $profileId) {
        $this->_profileId = $profileId;
        $this->_client = self::createClient($email, $password);
        $this->_service = self::getService($this->_client);
    }
    
    public static function createClient($email, $password) {
        return Zend_Gdata_ClientLogin::getHttpClient($email, $password, Zend_Gdata_Analytics::AUTH_SERVICE_NAME);  
    }
    
    public static function getService($client) {
        return new Zend_Gdata_Analytics($client); 
    }
    
    public function getTotalVisits() {
        $query = $this->getQuery()
            ->addMetric('ga:visits')   
            ->setStartDate('2005-01-01') 
            ->setEndDate(date('Y-m-d'))   
            ->setMaxResults(1);  

        $result = $this->_service->getDataFeed($query);   
        return (isset($result[0])) ? $result[0]->getMetric('ga:visits')->__toString() : null;
    }
    
    public function getVisitsInMonth($offset = 0) {
        $startDate = date_create_from_format('Y-m-d H:i', date('Y-m-1 00:00', strtotime($offset . ' month')));
        $endDate = date_create_from_format('Y-m-d H:i', date('Y-m-1 00:00', strtotime($offset + 1 . ' month')));
        $query = $this->getQuery()
            ->addMetric('ga:visits')   
            ->setStartDate($startDate->format('Y-m-d')) 
            ->setEndDate($endDate->format('Y-m-d'))   
            ->setMaxResults(1)
            ;  

        $result = $this->_service->getDataFeed($query);   
        return (isset($result[0])) ? (int) $result[0]->getMetric('ga:visits')->__toString() : null;
    }
    
    public function getPageviewsInMonth($offset = 0) {
        $startDate = date_create_from_format('Y-m-d H:i', date('Y-m-1 00:00', strtotime($offset . ' month')));
        $endDate = date_create_from_format('Y-m-d H:i', date('Y-m-1 00:00', strtotime($offset + 1 . ' month')));
        $query = $this->getQuery()
            ->addMetric('ga:pageviews')   
            ->setStartDate($startDate->format('Y-m-d')) 
            ->setEndDate($endDate->format('Y-m-d'))   
            ->setMaxResults(1)
            ;  

        $result = $this->_service->getDataFeed($query);   
        return (isset($result[0])) ? (int) $result[0]->getMetric('ga:pageviews')->__toString() : null;
    }
    
    public function getTotalPageviews() {
        $query = $this->getQuery()
            ->addMetric('ga:pageviews')   
            ->setStartDate('2005-01-01') 
            ->setEndDate(date('Y-m-d'))   
            ->setMaxResults(1);  

        $result = $this->_service->getDataFeed($query);   
        return (isset($result[0])) ? $result[0]->getMetric('ga:pageviews')->__toString() : null;
    }
    
    public function getDailyVisitsInMonth($offset = 0) {
        $startDate = date_create_from_format('Y-m-d H:i', date('Y-m-1 00:00', strtotime($offset . ' month')));
        $endDate = date_create_from_format('Y-m-d H:i', date('Y-m-1 00:00', strtotime($offset + 1 . ' month')));
        $query = $this->getQuery()
            ->addDimension('ga:day')
            ->addMetric('ga:visits')   
            ->setStartDate($startDate->format('Y-m-d')) 
            ->setEndDate($endDate->format('Y-m-d'))   
            ->addSort('ga:day')  
            ->setMaxResults(date('d'));  

        $result = $this->_service->getDataFeed($query);   
        
        $data = array();
        foreach($result as $row) {
            $data[] = $row->getMetric('ga:visits');
        }
        return $data;
    }
    
    public function getDailyPageviewsInMonth($offset = 0) {
        $startDate = date_create_from_format('Y-m-d H:i', date('Y-m-1 00:00', strtotime($offset . ' month')));
        $endDate = date_create_from_format('Y-m-d H:i', date('Y-m-1 00:00', strtotime($offset + 1 . ' month')));
        $query = $this->getQuery()
            ->addDimension('ga:day')
            ->addMetric('ga:pageviews')   
            ->setStartDate($startDate->format('Y-m-d')) 
            ->setEndDate($endDate->format('Y-m-d'))   
            ->addSort('ga:day')  
            ->setMaxResults(date('d'));  

        $result = $this->_service->getDataFeed($query);   
        
        $data = array();
        foreach($result as $row) {
            $data[] = $row->getMetric('ga:pageviews');
        }
        return $data;
    }
    
    /**
     * Retrieve keywords in last month
     * @param int $count
     * @return array 
     */
    public function getLastKeywords($count = 10) {
        $startDate = date_create_from_format('Y-m-d H:i', date('Y-m-1 00:00', strtotime('-1 month')));
        $endDate = date_create_from_format('Y-m-d H:i', date('Y-m-1 00:00', strtotime('1 month')));
        $query = $this->getQuery()
            ->addDimension('ga:keyword')
            ->addDimension('ga:day')
            ->addMetric('ga:organicSearches')   
            ->setStartDate($startDate->format('Y-m-d')) 
            ->setEndDate($endDate->format('Y-m-d'))   
            ->addSort('ga:day')  
            ->setMaxResults($count);  

        $result = $this->_service->getDataFeed($query);   
        
        $data = array();
        foreach($result as $key => $row) {
            if($key == 0) continue; // ( avoiding "not provided" keyword )
            $data[] = $row->getDimension('ga:keyword')->__toString();
        }
        return $data;
    }
    
    public function getQuery() {
        return $this->_service->newDataQuery()->setProfileId($this->_profileId);
    }
    
    public function countMonthNumber($month, $offset) {
       $monthNumber = $month + ($offset % 12);
       if($monthNumber <= 0) {
           $monthNumber = 12 + $monthNumber;
       }
       return $monthNumber;
    }
}

