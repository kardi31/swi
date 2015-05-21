<?php

/**
 * League_Service_League
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_Service_Booking extends MF_Service_ServiceAbstract {
    
    protected $bookingTable;
    
    public function init() {
        $this->bookingTable = Doctrine_Core::getTable('League_Model_Doctrine_Booking');
        parent::init();
    }
    
    public function getBooking($league_id,$weight,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
        $q = $this->bookingTable->createQuery('b');
        $q->leftJoin('b.Player p');
        $q->leftJoin('p.Team t');
        $q->addSelect('*, sum(quantity) as kar');
        $q->addWhere('t.league_id = ?',$league_id)
                         ->addWhere('b.weight = ?',$weight)
                         ->addOrderBy('kar DESC')
                         ->addOrderBy('p.last_name DESC')
                         ->addOrderBy('p.first_name DESC')
                         ->addGroupBy('p.last_name')
                         ->addGroupBy('p.first_name')
                         ->addGroupBy('b.weight');
                    $q->addWhere('b.active = 1');
       return $q->execute(array(), $hydrationMode);
    }
    
    public function getSingleBooking($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->bookingTable->findOneBy($field, $id, $hydrationMode);
    }
    
    
    
    public function getBookingForm(League_Model_Doctrine_Booking $booking = null) {
        $form = new League_Form_Booking();
        if(null != $booking) { 
            $form->populate($booking->toArray());
        }
        return $form;
    }
    
    public function saveBookingFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$booking = $this->getSingleBooking((int) $values['id'])) {
            $booking = $this->bookingTable->getRecord();
        }
         
        $booking->fromArray($values);
        $booking->save();
        
        return $booking;
    }
    
    public function removeLeague(League_Model_Doctrine_League $orderStatus) {
        $orderStatus->delete();
    }
    
    public function getAllLeague($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->bookingTable->getLeagueQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetLeagueSelectOptions($prependEmptyValue = false) {
        $items = $this->getAllLeague();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
                $result[$item->getId()] = $item->name;
        }
        return $result;
    }
}
?>