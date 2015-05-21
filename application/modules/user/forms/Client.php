<?php

class User_Form_Client extends Admin_Form
{
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $companyName = $this->createElement('text', 'company_name');
        $companyName->setLabel('Company name');
        $companyName->setRequired(false);
        $companyName->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $firstName = $this->createElement('text', 'first_name');
        $firstName->setLabel('First name');
        $firstName->setRequired();
        $firstName->addValidators(array(
            array('alnum', false, array('allowWhiteSpace' => true))
        ));
        $firstName->addFilters(array(
            array('alnum', array('allowWhiteSpace' => true))
        ));
        $firstName->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);

        $lastName = $this->createElement('text', 'last_name');
        $lastName->setLabel('Last name');
        $lastName->setRequired();
        $lastName->addValidators(array(
            array('alnum', false, array('allowWhiteSpace' => true))
        ));
        $lastName->addFilters(array(
            array('alnum', array('allowWhiteSpace' => true))
        ));
        $lastName->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $phone = $this->createElement('text', 'phone');
        $phone->setLabel('Phone');
        $phone->setRequired(false);
        $phone->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
                
        $website = $this->createElement('text', 'website');
        $website->setLabel('Website');
        $website->setRequired(false);
        $website->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $email = $this->createElement('text', 'email');
        $email->setLabel('Email');
        $email->setValidators(array('EmailAddress'));
        $email->setRequired(false);
        $email->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $street = $this->createElement('text', 'street');
        $street->setLabel('Street');
        $street->setRequired(true);
        $street->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $houseNr = $this->createElement('text', 'houseNr');
        $houseNr->setLabel('House number');
        $houseNr->setRequired(true);
        $houseNr->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $flatNr = $this->createElement('text', 'flatNr');
        $flatNr->setLabel('Flat number');
        $flatNr->setRequired(false);
        $flatNr->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $postalCode = $this->createElement('text', 'postal_code');
        $postalCode->setRequired(true)
                ->setLabel('Postal code');
        $postalCode->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $postalCode->addValidators(array('PostCode'));
        
        
        
        $city = $this->createElement('text', 'city');
        $city->setLabel('City');
        $city->setRequired(true);
        $city->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $province = $this->createElement('select', 'province_id');
        $province->setLabel('Province');
        $province->setRequired(true);
        $province->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $nip = $this->createElement('text', 'nip');
        $nip->setLabel('NIP');
        $nip->setRequired(false);
        $nip->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $about = $this->createElement('textarea', 'about');
        $about->setLabel('About me');
        $about->setRequired(false);
        $about->setDecorators(User_BootstrapForm::$bootstrapTinymceDecorators);
        
        $discountId = $this->createElement('select', 'discount_id');
        $discountId->setLabel('Discount');
        $discountId->setDecorators(self::$selectDecorators);
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $companyName,
            $firstName,
            $lastName,
            $phone,
            $email,
            $website,
            $street,
            $houseNr,
            $flatNr,
            $postalCode,
            $city,
            $province,
            $nip,
            $about,
            $discountId,
            $submit
        ));
    }
}