<?php

/**
 * Admin_Model_GA_Cache
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Admin_Model_GA_Cache extends Admin_Model_GA
{
    protected $_cache;
    
    public function __construct($email, $password, $profileId) {
        parent::__construct($email, $password, $profileId);
        $this->_cache = Zend_Cache::factory('Core', 'File',
            array('caching' => true, 'automatic_serialization' => true),
            array('cache_dir' => APPLICATION_PATH . '/../data/cache')
        );
    }
    
    public function getVisitsInMonth($offset = 0) {
        $offset = ($offset >= 0) ? 0 : $offset;
        $cacheId = 'ga_visits_in_month' . date('Ym', strtotime($offset . ' month'));
        if(($result = $this->_cache->load($cacheId)) === false) {
            $result = parent::getVisitsInMonth($offset);
            $lifetime = ($offset == 0) ? $this->_getNextDayLifetime() : $this->_getNextMonthLifetime();
            $this->_cache->save($result, $cacheId, array(), $lifetime);
        }
        return $result;
    }
    
    public function getTotalVisits() {
        if(($result = $this->_cache->load('ga_total_visits')) === false || 1) {
            $result = parent::getTotalVisits();
            $lifetime = $this->_getNextDayLifetime();
            $this->_cache->save($result, 'ga_total_visits', array(), $lifetime);
        }
        return $result;
    }
    
    public function getPageviewsInMonth($offset = 0) {
        $offset = ($offset >= 0) ? 0 : $offset;
        $cacheId = 'ga_pageviews_in_month' . date('Ym', strtotime($offset . ' month'));
        if(($result = $this->_cache->load($cacheId)) === false) {
            $result = parent::getPageviewsInMonth($offset);
            $lifetime = ($offset == 0) ? $this->_getNextDayLifetime() : $this->_getNextMonthLifetime();
            $this->_cache->save($result, $cacheId, array(), $lifetime);
        }
        return $result;
    }
    
    public function getTotalPageviews() {
        if(($result = $this->_cache->load('ga_total_pageviews')) === false) {
            $result = parent::getTotalPageviews();
            $lifetime = $this->_getNextDayLifetime();
            $this->_cache->save($result, 'ga_total_pageviews', array(), $lifetime);
        }
        return $result;
    }
     
    public function getDailyVisitsInMonth($offset = 0) {
        $offset = ($offset >= 0) ? 0 : $offset;
        $cacheId = 'ga_daily_visits_in_month' . date('Ym', strtotime($offset . ' month'));
        if(($result = $this->_cache->load($cacheId)) === false) {
            $result = parent::getDailyVisitsInMonth($offset);
            $lifetime = ($offset == 0) ? $this->_getNextDayLifetime() : $this->_getNextMonthLifetime();
            $this->_cache->save($result, $cacheId, array(), $lifetime);
        }
        return $result;
    }
    
    public function getDailyPageviewsInMonth($offset = 0) {
        $offset = ($offset >= 0) ? 0 : $offset;
        $cacheId = 'ga_daily_pageviews_in_month' . date('Ym', strtotime($offset . ' month'));
        if(($result = $this->_cache->load($cacheId)) === false) {
            $result = parent::getDailyPageviewsInMonth($offset);
            $lifetime = ($offset == 0) ? $this->_getNextDayLifetime() : $this->_getNextMonthLifetime();
            $this->_cache->save($result, $cacheId, array(), $lifetime);
        }
        return $result;
    }
    
    public function getLastKeywords($count) {
        $cacheId = 'ga_last_keywords';
        if(($result = $this->_cache->load($cacheId)) === false) {
            $result = parent::getLastKeywords($count);
            $lifetime = $this->_getNextDayLifetime();
            $this->_cache->save($result, $cacheId, array(), $lifetime);
        }
        return $result;
    }
    
    protected function _getNextDayLifetime() {
        $nextDay = date_create_from_format('Y-m-d H:i', date('Y-m-d 0:0', strtotime('1 day')));
        return $nextDay->getTimestamp() - time();
    }
    
    protected function _getNextMonthLifetime() {
        $nextMonth = date_create_from_format('Y-m-d H:i', date('Y-m-1 0:0', strtotime('1 month')));
        return $nextMonth->getTimestamp() - time();
    }
    
}

