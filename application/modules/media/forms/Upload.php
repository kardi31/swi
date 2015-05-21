<?php

class Media_Form_Upload extends Zend_Form 
{
    public function init() { 
        $file = $this->createElement('file', 'file');
        $file->setLabel('Load file');
        $file->setDecorators(array('File', 'Errors'));
        $file->setRequired(true);
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Ok');
        $submit->setAttrib('type', 'submit');
        
        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));
        
        $this->setElements(array(
            $file,
            $submit
        ));
        
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
    }
}

