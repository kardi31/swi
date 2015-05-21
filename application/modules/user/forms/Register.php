<?php 

class User_Form_Register extends User_BootstrapForm
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
        $firstName->setDecorators(self::$bootstrapElementDecorators);

        $lastName = $this->createElement('text', 'last_name');
        $lastName->setLabel('Last name');
        $lastName->addValidators(array(
            array('alpha', false, array('allowWhiteSpace' => true))
        ));
        $lastName->addFilters(array(
            array('alpha', array('allowWhiteSpace' => true))
        ));
        $lastName->setRequired();
        $lastName->setDecorators(self::$bootstrapElementDecorators);

        $username = $this->createElement('text', 'username');
        $username->setLabel('Username');
        $username->addValidators(array(
            array('alpha', false, array('allowWhiteSpace' => true))
        ));
        $username->addFilters(array(
            array('alpha', array('allowWhiteSpace' => true))
        ));
        
        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('Email');
        $email->setValidators(array('EmailAddress'));
        $email->setRequired(true);
        $email->setDecorators(self::$bootstrapElementDecorators);
//        $email->setDecorators(array(
//            'ViewHelper',
//            'Errors',
//            'Description',
//            array('HtmlTag', array('tag' => 'dd')),
//            array('Label', array('tag' => 'dt', 'escape'  => false, 'requiredSuffix' => ' <em>*</em>')),
//        ));
		
		$password = $this->createElement('password', 'password');
		$password->setLabel('Password');
		$password->setRequired(true);
        $password->setDecorators(self::$bootstrapElementDecorators);
//        $password->setDecorators(array(
//            'ViewHelper',
//            'Errors',
//            'Description',
//            array('HtmlTag', array('tag' => 'dd')),
//            array('Label', array('tag' => 'dt', 'escape'  => false, 'requiredSuffix' => ' <em>*</em>')),
//        ));
		
		$confirmPassword = $this->createElement('password', 'confirm_password');
		$confirmPassword->setLabel('Confirm password');
		$confirmPassword->setValidators(array(array('Identical', false, array('token' => 'password'))));
		$confirmPassword->setRequired();
        $confirmPassword->setDecorators(self::$bootstrapElementDecorators);
//        $confirmPassword->setDecorators(array(
//            'ViewHelper',
//            'Errors',
//            'Description',
//            array('HtmlTag', array('tag' => 'dd')),
//            array('Label', array('tag' => 'dt', 'escape'  => false, 'requiredSuffix' => ' <em>*</em>')),
//        ));

        $type = $this->createElement('radio', 'type');
        $type->setRequired();
        $type->setDecorators(self::$bootstrapElementDecorators);
        $type->setSeparator('');

        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Register');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

		$this->setElements(array(
            $firstName,
            $lastName,
            $username,
			$email,
			$password,
			$confirmPassword,
            $type,
			$submit,
			$csrf
		));
        
        $this->setEnctype(Zend_Form::ENCTYPE_URLENCODED);
        $this->setMethod(Zend_Form::METHOD_POST);
		
	}
}