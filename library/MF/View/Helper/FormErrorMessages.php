<?php

/**
 * FormErrors
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_View_Helper_FormErrorMessages extends Zend_View_Helper_Abstract {
    
    public function formErrorMessages(Zend_Form $form, $delimiter = '<br/>') {
        $errors = '';
        foreach($form->getElements() as $element) {
            if($element->hasErrors()) {
                $element->setAttrib('class', 'error');
                $errors .= $element->getLabel() . ' - ' . array_shift($element->getMessages()) . $delimiter;
            }
        }
        return $errors;
    }
}

