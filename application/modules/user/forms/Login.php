<?php 
class User_Form_Login extends Zend_Form
{
    public function init() {
        $username = $this->createElement('text', 'username');
        $username->setLabel('email');

        $password = $this->createElement('password', 'password');
        $password->setLabel('password');

        $remember = $this->createElement('checkbox', 'remember');
        $remember->setLabel('remember me');
        $remember->setDecorators(array('ViewHelper', 'Description', 'DtDdWrapper'));
        $remember->setDescription('remember me');
            
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Log in');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $username,
            $password,
            $remember,
            $submit
        ));

        $this->setEnctype(Zend_Form::ENCTYPE_URLENCODED);
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('login');
    }
}