<?php

/**
 * User_BootstrapForm
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_BootstrapForm extends Zend_Form {
    
    public static $bootstrapElementDecorators = array(
        'ViewHelper',
        array('Errors'),
        array('Description', array('tag' => 'span', 'class' => 'help-block')), 
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'controls')),
        array('Label', array('class' => 'control-label')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'control-group'))
    );
    
    public static $bootstrapTinymceDecorators = array(
        'ViewHelper',
        array('Errors'),
        array('Description', array('tag' => 'span', 'class' => 'help-inline')), 
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'tinymce-wrapper')),
        array('Label', array('class' => 'control-label')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'control-group'))
    );
    
    public static $bootstrapSubmitDecorators = array(
        'ViewHelper',
        array('Description', array('tag' => 'span', 'class' => 'help-inline')), 
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'controls')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'control-group'))
    );
    
    public function isValid($data) {
        $valid = parent::isValid($data);
 
        foreach ($this->getElements() as $element) {
            if ($element->hasErrors()) {
                $oldClass = $element->getAttrib('class');
                if (!empty($oldClass)) {
                    $element->setAttrib('class', $oldClass . ' error');
                } else {
                    $element->setAttrib('class', 'error');
                }
            }
        }
 
        return $valid;
    }
}

