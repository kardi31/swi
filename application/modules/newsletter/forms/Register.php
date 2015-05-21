<?php 

/**
 * Newsletter_Form_Message
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_Form_Register extends Zend_Form
{
    const SALT = '6e26899d3195dabb8553dffe84899e0c';
    
    public function init() {
        
        $csrf = $this->createElement('hash', 'csrf');
	$csrf->setSalt(self::SALT);
	$csrf->setDecorators(array('ViewHelper'));
        
        $firstName = $this->createElement('text', 'first_name');
        $firstName->setLabel('First name');
        $firstName->addValidators(array(
            array('alpha', false, array('allowWhiteSpace' => true))
        ));
        $firstName->addFilters(array(
            array('alpha', array('allowWhiteSpace' => true))
        ));
        $firstName->setRequired();
        $firstName->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);

        $lastName = $this->createElement('text', 'last_name');
        $lastName->setLabel('Last name');
        $lastName->addValidators(array(
            array('alpha', false, array('allowWhiteSpace' => true))
        ));
        $lastName->addFilters(array(
            array('alpha', array('allowWhiteSpace' => true))
        ));
        $lastName->setRequired();
        $lastName->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $username = $this->createElement('text', 'username');
        $username->setLabel('Nick');
        $username->setRequired();
        $username->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('Email');
        $email->setValidators(array('EmailAddress'));
        $email->setRequired(true);
        $email->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Sign in');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $username,
            $firstName,
            $lastName,
            $email,
            $csrf,
            $submit
        ));

        $this->setEnctype(Zend_Form::ENCTYPE_URLENCODED);
        $this->setMethod(Zend_Form::METHOD_POST);
    }
}