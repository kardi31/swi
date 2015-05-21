<?php

/**
 * AdminFormErrors
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Admin_View_Helper_AdminFormErrors extends Zend_View_Helper_Abstract {
    
    public function adminFormErrors(Zend_Form $form, $delimiter = '<br/>') {
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

