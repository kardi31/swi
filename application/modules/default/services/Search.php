<?php

/**
 * Search
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Service_Search extends MF_Service_ServiceAbstract {
    
    protected $escortTable;
    
    public function init() {
        parent::init();
    }
    
    public function createSearchForm() {
        $locationService = $this->getServiceBroker()->getService('Location_Service_Location');
        $translator = $this->getServiceBroker()->get('Zend_Translate');
        
        $searchForm = new Default_Form_Search();
        $searchForm->setMethod(Zend_Form::METHOD_GET);
        $searchForm->getElement('location')->setMultiOptions($locationService->getLocationSelectOptions(true));
        $searchForm->getElement('independent_or_agency')->setMultiOptions(array('i' => 'Independent', 'a' => 'Agency'));
        $searchForm->getElement('type')->setMultiOptions(Escort_Model_Doctrine_Escort::getAvailableTypes());
        $searchForm->getElement('hair')->setMultiOptions(Escort_Model_Doctrine_Escort::getAvailableHair());
        $searchForm->getElement('service')->setMultiOptions(Escort_Model_Doctrine_Escort::getAvailableServices());
        
        $searchForm->getElement('age_from')->setAttrib('placeholder', $translator->translate($searchForm->getElement('age_from')->getLabel()));
        $searchForm->getElement('age_to')->setAttrib('placeholder', $translator->translate($searchForm->getElement('age_to')->getLabel()));
        $searchForm->getElement('price_from')->setAttrib('placeholder', $translator->translate($searchForm->getElement('price_from')->getLabel()));
        $searchForm->getElement('price_to')->setAttrib('placeholder', $translator->translate($searchForm->getElement('price_to')->getLabel()));
        $searchForm->getElement('height_from')->setAttrib('placeholder', $translator->translate($searchForm->getElement('height_from')->getLabel()));
        $searchForm->getElement('height_to')->setAttrib('placeholder', $translator->translate($searchForm->getElement('height_to')->getLabel()));
        $searchForm->getElement('weight_from')->setAttrib('placeholder', $translator->translate($searchForm->getElement('weight_from')->getLabel()));
        $searchForm->getElement('weight_to')->setAttrib('placeholder', $translator->translate($searchForm->getElement('weight_to')->getLabel()));
        
        $searchForm->setDefaults(array(
            'age_from' => 18,
            'age_to' => 75,
            'price_from' => 0,
            'price_to' => 1000,
            'height_from' => '4\'0"',
            'height_to' => '7\'0"',
            'weight_from' => 0,
            'weight_to' => 250
        ));
        
        return $searchForm;
    }
    
    public function getSearchResults($values, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
        return $this->escortTable->getEscortsForSearchCriteria($values, $hydrationMode);
    }
    
    public function getSearchMainForm() {
        $searchForm = new Default_Form_SearchMain();
        return $searchForm;
    }
}

