<?php

/**
 * Account
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_Controller_Action_Helper_Account extends Zend_Controller_Action_Helper_Abstract {
    
    protected $userService;
    protected $escortService;
    protected $agencyService;
    protected $profileInvoiceService;
    protected $user;
    protected $account;
    
    public function init() {
        $serviceBroker = MF_Service_ServiceBroker::getInstance();
        $this->user = $serviceBroker->getService('User_Service_Auth')->getAuthenticatedUser();
        parent::init();
    }
    
    public function direct() {
        return $this->getAccount();
    }
    
    public function getAccount($userId = null, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        if(null != $userId) {
            if(!$user = $this->userService->getUser((int) $userId)) {
                throw new Exception('User not found');
            }
        } elseif(!$user = $this->user) {
            return null;
        }
        switch($user->getType()) {
            case 'i':
                if($escort = $this->escortService->getEscort((int) $user->getId(), 'user_id', $hydrationMode)) {
                    return $this->getEscortAccount((int) $escort['id'], $hydrationMode);
                }
                break;
            case 'a':
                if($agency = $this->agencyService->getAgency((int) $user->getId(), 'user_id', $hydrationMode)) {
                    return $this->getAgencyAccount((int) $agency['id'], $hydrationMode);
                }
                break;
            default:
                return null;
        }
        return $this->account;
    }
    
    public function getEscortAccount($id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->escortService->getEscortAccount($id, $hydrationMode);
    }
    
    public function getAgencyAccount($id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->agencyService->getAgencyAccount($id, $hydrationMode);
    }
    
    public function getAccountForm($featureScope = 0) {
        $account = $this->getAccount();
        switch($this->user->getType()) {
            case 'i':
                $form = $this->getEscortFormForAgency($account, $featureScope);
                $form->getElement('feature_scope')->setValue($featureScope);
                $form->getElement('types')->getValidator('multiCheckboxCount')->setMin(1)->setMax(1);
                if(isset(Escort_Model_EscortFeatureScope::$typeLimit[$featureScope])) {
                    $form->getElement('types')->getValidator('multiCheckboxCount')->setMin(1)->setMax(Escort_Model_EscortFeatureScope::$typeLimit[$featureScope]);
                }
                return $form;
                break;
            case 'a':
                $form = new Escort_Form_AgencyAccount();
                $form->getElement('feature_scope')->setValue($featureScope);
                if($account) {
                    $form->populate($account->toArray());
                }
                return $form;
                break;
            default:
                $this->_forward('noauth', 'error', 'default');
        }
    }
    
    public function updateAccount($values, $photo = null) {
        $account = $this->getAccount();
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        switch($this->user->getType()) {
            case 'i':
                if($account = $this->getAccount()) {
                    $values['id'] = $account->getId();
                }
                $account = $this->escortService->saveEscortFromArray($values);
                if(strlen($values['name'])) {
                    $this->user->setUsername($values['name']);
                    $this->user->save();
                }
                break;
            case 'a':
                if($account = $this->getAccount()) {
                    $values['id'] = $account->getId();
                }
                $account = $this->agencyService->saveAgencyFromArray($values);
                if(strlen($values['name'])) {
                    $this->user->setUsername($values['name']);
                    $this->user->save();
                }
                break;
        }
        if(null != $photo) {
            if(is_integer($photo)) {
                $account->set('photo_root_id', $photo);
            } else {
                $account->set('PhotoRoot', $photo);
            }
        }
        $account->setUserId($this->user->getId());
        $account->save();
        return $account;
    }
    
    public function getEscortFormForAgency($escort = null, $featureScope = 0) {
        $form = new Escort_Form_EscortAccount();
        $form->setDefault('enabled', 1);
        if(null != $escort) {
            if(is_array($escort)) {
                $form->populate($escort);
                
                $languages = $escort['Languages'];
                $values = array();
                foreach($languages as $language) {
                    $values[$language['id']] = $language['language_id'];
                }
                $form->getElement('languages')->setValue($values);

                $types = $escort['Types'];
                $values = array();
                foreach($types as $type) {
                    $values[$type['id']] = $type['type_id'];
                }
                $form->getElement('types')->setValue($values);

                $services = $escort['Services'];
                $values = array();
                foreach($services as $service) {
                    $values[$service['id']] = $service['service_id'];
                }
                $form->getElement('service')->setValue($values);

                $rates = $escort['Rates'];
                foreach($rates as $rate) {
                    if($categorySubform = $form->getSubForm('rates')->getSubForm($rate['category'])) {
                        foreach($categorySubform as $rateElement) {
                            if($rateElement->getName() == $rate['type']) {
                                $rateElement->setValue($rate['value']);
                            }
                        }
                    }
                }
                
                $escortTags = array();
                foreach($escort['Tags'] as $tag) {
                    $escortTags[] = $tag['name'];
                }
                $form->getElement('tags')->setValue(implode(', ', $escortTags));
            } 
            
            if($escort instanceof Escort_Model_Doctrine_Escort) {
                $form->populate($escort->toArray());

                $languages = $escort->Languages->toKeyValueArray('id', 'language_id');
                $form->getElement('languages')->setValue($languages);

                $types = $escort->Types->toKeyValueArray('id', 'type_id');
                $form->getElement('types')->setValue($types);

                $services = $escort->Services->toKeyValueArray('id', 'service_id');
                $form->getElement('service')->setValue($services);

                $rates = $escort->Rates;
                foreach($rates as $rate) {
                    if($categorySubform = $form->getSubForm('rates')->getSubForm($rate->getCategory())) {
                        foreach($categorySubform as $rateElement) {
                            if($rateElement->getName() == $rate->getType()) {
                                $rateElement->setValue($rate->getValue());
                            }
                        }
                    }
                }
                
                $escortTags = $escort->Tags->toKeyValueArray('id', 'name');
                $form->getElement('tags')->setValue(implode(', ', $escortTags));
            }
        }
        
        if(isset(Escort_Model_EscortFeatureScope::$typeLimit[$featureScope])) {
            $form->getElement('types')->getValidator('multiCheckboxCount')->setMin(1)->setMax((int) Escort_Model_EscortFeatureScope::$typeLimit[$featureScope]);
        }
        
        // prepare feature form
//        if($featureScope == 1) {
//            $form->getElement('types')->getValidator('multiCheckboxCount')->setMin(1)->setMax(1);
//        } elseif($featureScope == 2) {
//            $form->getElement('types')->getValidator('multiCheckboxCount')->setMin(1)->setMax(3);
//        } elseif($featureScope == 3) {
//            $form->getElement('types')->getValidator('multiCheckboxCount')->setMin(1)->setMax(5);
//        } elseif($featureScope == 4) {
//            $form->getElement('types')->getValidator('multiCheckboxCount')->setMin(1)->setMax(5);
//        }
        
        return $form;
    }
    
    public function getAccountSelectOptions($withEmails = false) {
        $result = array();
        if($withEmails) {
            $agencies = $this->agencyService->getAllAgencies();
            $escorts = $this->escortService->getEscorts(false);
            foreach($agencies as $agency) {
                $result['Agencies'][$agency->getUserId()] = $agency->getName() . ' (' . $agency->get('User')->getEmail() . ')'; 
            }
            foreach($escorts as $escort) {
                $result['Independent'][$escort->getUserId()] = $escort->getName() . ' (' . $escort->get('User')->getEmail() . ')'; 
            }
        } else {
            $agencies = $this->agencyService->getAgencySelectOptions(false);
            $independentEscorts = $this->escortService->getEscortSelectOptions(false);
            $result['Agencies'] = $agencies;
            $result['Independent'] = $independentEscorts;
        }
        return $result;
    }
    
    public function displayBoost() {
        if(!$this->getAccount()
            || ($this->user && ($this->user->getType() == 'i') && ($activeProfileInvoice != $this->profileInvoiceService->getActiveProfileInvoice($this->user->getId())))
            || ($this->user && ($this->user->getType() == 'a') && (0 == $this->getAccount()->get('Escorts')->count()))    
        ) {
            return true;
        } else {
            return false;
        }
    }
    
    public function willUpload($photo = null) {
        $session = new Zend_Session_Namespace('WILL_UPLOAD');
        if(true === $photo) {
            if(isset($session->root)) {
                unset($session->root);
            }
        } elseif ($photo instanceof Media_Model_Doctrine_Photo) {
            $session->root = $photo->getId();
            return $photo;
        } elseif(is_integer($photo)) {
            $session->root = $photo;
            return $photo;
        } elseif(null === $photo) {
            if(isset($session->root)) {
                return Doctrine_Core::getTable('Media_Model_Doctrine_Photo')->find((int) $session->root);
            }
        }
    }
    
    public function videoWillUpload($video = null) {
        $videoService = MF_Service_ServiceBroker::getInstance()->getService('Media_Service_Video');
        $session = new Zend_Session_Namespace('VIDEO_WILL_UPLOAD');
        if(true === $video) {
            if(isset($session->ids)) {
                unset($session->ids);
            }
        } elseif ($video instanceof Media_Model_Doctrine_Video) {
            $ids = is_array($session->ids) ? $session->ids : array();
            $ids[] = (int) $video->getId();
            $session->ids = $ids;
            return $video;
        } elseif(is_integer($video)) {
            $ids = is_array($session->ids) ? $session->ids : array();
            $ids[] = (int) $video;
            $session->ids = $ids;
            return $video;
        } elseif(null === $video) {
            if(is_array($session->ids) && count($session->ids)) {
                return $videoService->getSortedVideosWithIds($session->ids);
            } else {
                return array();
            }
        }
    }
}

