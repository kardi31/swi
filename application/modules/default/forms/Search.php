<?php

/**
 * Default_Form_Search
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Form_Search extends Zend_Form
{
    public function init() {
        $phrase = $this->createElement('text', 'phrase');
        $phrase->setLabel('Search');
        $phrase->addValidators(array(
            array('regex', false, array('pattern' => '/[a-zA-Z0-9\.\,\/\-\?\!\\\]+/'))
        ));
        $phrase->addFilters(array(
            new MF_Filter_Urldecode(),
            'stripTags'
        ));
        $phrase->setDecorators(array(
            'ViewHelper'
        ));
        $phrase->setRequired(false);
        
        $location = $this->createElement('select', 'location');
        $location->setLabel('Location');
        $location->setDecorators(array('Label', 'ViewHelper'));
        
        $independentOrAgency = $this->createElement('multiCheckbox', 'independent_or_agency');
        $independentOrAgency->setLabel('Independent/Agency');
        $independentOrAgency->setDecorators(array('ViewHelper'));
        $independentOrAgency->setSeparator('');
        
        $bisexual = $this->createElement('checkbox', 'bisexual');
        $bisexual->setLabel('Bisexual');
        $bisexual->setDecorators(array('Label', 'ViewHelper'));
        
        $service = $this->createElement('multiCheckbox', 'service');
//        $service->setLabel('Services');
        $service->setDecorators(array('Label', 'ViewHelper'));
        $service->setSeparator('');
        
        $ageFrom = $this->createElement('text', 'age_from');
        $ageFrom->setLabel('From');
        $ageFrom->setDecorators(array('ViewHelper'));
        
        $ageTo = $this->createElement('text', 'age_to');
        $ageTo->setLabel('To');
        $ageTo->setDecorators(array('ViewHelper'));
        
        $priceFrom = $this->createElement('text', 'price_from');
        $priceFrom->setLabel('From');
        $priceFrom->setDecorators(array('ViewHelper'));
        
        $priceTo = $this->createElement('text', 'price_to');
        $priceTo->setLabel('To');
        $priceTo->setDecorators(array('ViewHelper'));
        
        $heightFrom = $this->createElement('text', 'height_from');
        $heightFrom->setLabel('From');
        $heightFrom->setDecorators(array('ViewHelper'));
        
        $heightTo = $this->createElement('text', 'height_to');
        $heightTo->setLabel('To');
        $heightTo->setDecorators(array('ViewHelper'));
        
        $weightFrom = $this->createElement('text', 'weight_from');
        $weightFrom->setLabel('From');
        $weightFrom->setDecorators(array('ViewHelper'));
        
        $weightTo = $this->createElement('text', 'weight_to');
        $weightTo->setLabel('To');
        $weightTo->setDecorators(array('ViewHelper'));
        
        $hair = $this->createElement('multiCheckbox', 'hair');
//        $hair->setLabel('Hair');
        $hair->setDecorators(array('Label', 'ViewHelper'));
        $hair->setSeparator('');
        
        $type = $this->createElement('multiCheckbox', 'type');
        $type->setLabel('Type');
        $type->setDecorators(array('ViewHelper'));
        $type->setSeparator('');
        
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Search');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('name', false);
        
//        $this->addDisplayGroup(array($ageFrom, $ageTo), 'age');
//        $this->getDisplayGroup('age')->setLegend('Age')->setDecorators(array('FormElements', 'Fieldset'));
//        
//        $this->addDisplayGroup(array($priceFrom, $priceTo), 'price');
//        $this->getDisplayGroup('price')->setLegend('Price')->setDecorators(array('FormElements', 'Fieldset'));
//        
//        $this->addDisplayGroup(array($heightFrom, $heightTo), 'height');
//        $this->getDisplayGroup('height')->setLegend('Height')->setDecorators(array('FormElements', 'Fieldset'));
//        
//        $this->addDisplayGroup(array($weightFrom, $weightTo), 'weight');
//        $this->getDisplayGroup('weight')->setLegend('Weight')->setDecorators(array('FormElements', 'Fieldset'));
        
//        $this->addDisplayGroup(array($independentOrAgency, $bisexual), 'basic');
//        $this->getDisplayGroup('basic')->setDecorators(array('FormElements', 'Fieldset'));
//        
        $this->addDisplayGroup(array($hair), 'hair');
        $this->getDisplayGroup('hair')->setLegend('Hair')->setDecorators(array('FormElements', 'Fieldset'));
//        
        $this->addDisplayGroup(array($service), 'service');
        $this->getDisplayGroup('service')->setLegend('Services')->setDecorators(array('FormElements', 'Fieldset'));
//        
//        $this->addDisplayGroup(array($location), 'location');
//        $this->getDisplayGroup('location')->setLegend('Location')->setDecorators(array('FormElements', 'Fieldset'));
        
//        $this->addDisplayGroup(array($type), 'type');
//        $this->getDisplayGroup('type')->setDecorators(array('FormElements', 'Fieldset'));
        
        $this->setElements(array(
            $phrase,
            $location,
            $independentOrAgency,
            $bisexual,
            $service,
            $ageFrom,
            $ageTo,
            $priceFrom,
            $priceTo,
            $heightFrom,
            $heightTo,
            $weightFrom,
            $weightTo,
            $type,
            $hair,
            $submit
        ));
        
        $this->setMethod(Zend_Form::METHOD_GET);
    }
}

