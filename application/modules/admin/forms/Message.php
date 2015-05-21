<?php

/**
 * Admin_Form_Message
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Admin_Form_Message extends Zend_Form {
    
    protected static $_standardFormDecorators = array(
        'ViewHelper',
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-input')),
        array('Label', array('class' => 'form-label', 'escape'  => false, 'requiredSuffix' => ' <em>*</em>')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clearfix'))
    );
    
    protected static $_chechgroupFormDecorators = array(
        'ViewHelper',
        array(array('Checkgroup' => 'HtmlTag'), array('tag' => 'div', 'class' => 'checkgroup')),
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-input')),
        array('Label', array('class' => 'form-label', 'escape'  => false, 'requiredSuffix' => ' <em>*</em>')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clearfix'))
    );

    protected static $_submitFormDecorators = array(
        'ViewHelper',
        array('Description', array('tag' => 'button', 'type' => 'reset')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-action clrearfix'))
    );
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $userId = $this->createElement('hidden', 'user_id');
        $userId->setDecorators(array('ViewHelper'));
        
        $subject = $this->createElement('text', 'subject');
        $subject->setLabel('Subject');
        $subject->setDecorators(self::$_standardFormDecorators);
        
        $content = $this->createElement('textarea', 'content');
        $content->setLabel('Content');
        $content->setDecorators(array('ViewHelper'));
        
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Ok');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        
        $this->setElements(array(
            $id,
            $userId,
            $subject,
            $content,
            $submit
        ));
    }
}

