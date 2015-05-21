<?php

/**
 * Default_Form_SearchMain
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Default_Form_SearchMain extends Zend_Form
{
    public function init() {  
        $phrase = $this->createElement('text', 'phrase');
        $phrase->setLabel('Search');
        $phrase->addValidators(array(
            array('regex', false, array('pattern' => '/[a-zA-Z0-9\.\,\/\-\?\!\\\]+/'))
        ));
        /*$phrase->addFilters(array(
            new MF_Filter_Urldecode(),
            'stripTags'
        ));*/
        $phrase->addFilters(array(
            array('StripTags'),
            array('Alpha', array('allowWhiteSpace' => true))
        ));
        $phrase->setDecorators(array('ViewHelper'));
        $phrase->setRequired(true);
        
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Search');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('name', false);
        
        $this->setElements(array(
            $phrase,
            $submit
        ));
        //$this->setMethod(Zend_Form::METHOD_GET);
    }
}

