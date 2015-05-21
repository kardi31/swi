<?php

/**
 * Invoice_Model_Cart
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Invoice_Model_Cart {
    
    protected $session;
    
    public function __construct() {
        $this->session = new Zend_Session_Namespace('CART');
    }
    
    public function getItems($class = null) {
        if(!$items = $this->session->items) {
            $items = array();
            $this->session->items = $items;
        }
        if(null != $class) {
            if(isset($items[$class])) {
                return $items[$class];
            }
        }
        return $items;
    }
    
    public function get($class, $id) {
        $items = $this->getItems();
        if(isset($items[$class])) {
            if(isset($items[$class][$id])) {
                return $items[$class][$id];
            }
        }
    }
    
    public function add($class, $id, $name, $price, $count, $absolutePrice = false) {
        $items = $this->getItems();
        if(!isset($items[$class])) {
            $items[$class] = array();
        }
        $price = $absolutePrice ? $price : $price * $count;
        $items[$class][$id] = array('name' => $name, 'price' => $price, 'count' => $count, 'absolute' => $absolutePrice);
        $this->session->items = $items;
    }
    
    public function remove($class, $id = null) {
        $items = $this->getItems();
        if(isset($items[$class])) {
            if(null != $id) {
                unset($items[$class][$id]);
            } else {
                unset($items[$class]);
            }
        }
        $this->session->items = $items;
    }
    
    public function count($class = null, $id = null) {
        $items = $this->getItems();
        
        $result = 0;
        
        if(null == $class) {
            foreach($items as $class => $ids) {
                foreach($ids as $id => $data) {
                    if(isset($data['count'])) {
                        $result += (int) $data['count'];
                    }
                }
            }
        } else {
            if(isset($items[$class])) {
                if(null == $id) {
                    foreach($items[$class] as $ids) {
                        foreach($ids as $id => $data) {
                            if(isset($data['count'])) {
                                $result += (int) $data['count'];
                            }
                        }
                    }
                } else {
                    if(isset($items[$class][$id])) {
                        $result += $items[$class][$id]['count'];
                    }
                }
            }
        }
        
        return $result;
    }
    
    public function getSum() {
        $items = $this->getItems();
        
        $result = 0;

        foreach($items as $class => $ids) {
            foreach($ids as $id => $data) {
                if(isset($data['price'])) {
                    if(isset($data['count']) && !$data['absolute']) {
                        $result += $data['count'] * $data['price'];
                    } else {
                        $result += $data['price'];
                    }
                }
            }
        }
        
        return $result;
    }
    
    public function clean() {
        $this->session->items = array();
    }
}

