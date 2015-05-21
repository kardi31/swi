<?php

/**
 * User_Form_Company
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_Form_Company extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $parentId = $this->createElement('hidden', 'parent_id');
        $parentId->setDecorators(self::$hiddenDecorators);
        
        $languages = $i18nService->getLanguageList();

        $translations = new Zend_Form_SubForm();

        foreach($languages as $language) {
            $translationForm = new Zend_Form_SubForm();
            $translationForm->setName($language);
            $translationForm->setDecorators(array(
                'FormElements'
            ));

            $name = $translationForm->createElement('text', 'name');
            $name->setBelongsTo($language);
            $name->setLabel('Name');
            $name->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            $name->setAttrib('class', 'span8');
            
            $shortDescription = $translationForm->createElement('textarea', 'short_description');
            $shortDescription->setBelongsTo($language);
            $shortDescription->setLabel('Short description');
            $shortDescription->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            $shortDescription->setAttrib('class', 'span8 tinymce');
            
            $description = $translationForm->createElement('textarea', 'description');
            $description->setBelongsTo($language);
            $description->setLabel('Full description');
            $description->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            $description->setAttrib('class', 'span8 tinymce');
            
            $translationForm->setElements(array(
                $name,
                $shortDescription,
                $description
            ));

            $translations->addSubForm($translationForm, $language);
        }

        $this->addSubForm($translations, 'translations');
        
        $contactName = $this->createElement('text', 'contact_name');
        $contactName->setLabel('Name and lastname');
        $contactName->addValidators(array(
            array('alpha', false, array('allowWhiteSpace' => true))
        ));
        $contactName->addFilters(array(
            array('alpha', array('allowWhiteSpace' => true))
        ));
        $contactName->setRequired();
        $contactName->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $contactName->setAttrib('class', 'span8');
        
        $contactPhone = $this->createElement('text', 'contact_phone');
        $contactPhone->setLabel('Phone');
        $contactPhone->setRequired(true);
        $contactPhone->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $contactPhone->setAttrib('class', 'span8'); 
        
        $contactEmail = $this->createElement('text', 'contact_email');
        $contactEmail->setLabel('Email');
        $contactEmail->setValidators(array('EmailAddress'));
        $contactEmail->setRequired(true);
        $contactEmail->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $contactEmail->setAttrib('class', 'span8');
        
        $email = $this->createElement('text', 'email');
        $email->setLabel('Email');
        $email->setValidators(array('EmailAddress'));
        $email->setRequired(true);
        $email->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $email->setAttrib('class', 'span8');
        
        $email2 = $this->createElement('text', 'email2');
        $email2->setLabel('Next e-mail');
        $email2->setValidators(array('EmailAddress'));
        $email2->setRequired(false);
        $email2->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $email2->setAttrib('class', 'span8');
        
        $email3 = $this->createElement('text', 'email3');
        $email3->setLabel('Next e-mail');
        $email3->setValidators(array('EmailAddress'));
        $email3->setRequired(false);
        $email3->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $email3->setAttrib('class', 'span8');
       
        $phone = $this->createElement('text', 'phone');
        $phone->setLabel('Phone');
        $phone->setRequired(true);
        $phone->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $phone->setAttrib('class', 'span8');    
                
        $website = $this->createElement('text', 'website');
        $website->setLabel('Website');
        $website->setRequired(false);
        $website->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $website->setAttrib('class', 'span8');
        
        $website2 = $this->createElement('text', 'website2');
        $website2->setLabel('Next website');
        $website2->setRequired(false);
        $website2->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $website2->setAttrib('class', 'span8');
        
        $website3 = $this->createElement('text', 'website3');
        $website3->setLabel('Next website');
        $website3->setRequired(false);
        $website3->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $website3->setAttrib('class', 'span8');
        
        $province = $this->createElement('select', 'province');
        $province->setLabel('Province');
        $province->setRequired(true);
        $province->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $city = $this->createElement('text', 'city');
        $city->setLabel('City');
        $city->setRequired();
        $city->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $city->setAttrib('class', 'span8');
        
        $address = $this->createElement('text', 'address');
        $address->setLabel('Address');
        $address->setRequired(true);
        $address->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $address->setAttrib('class', 'span8');
        
        $postCode = $this->createElement('text', 'post_code');
        $postCode->setLabel('Post code');
        $postCode->setValidators(array('PostCode'));
        $postCode->setRequired(true);
        $postCode->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $postCode->setAttrib('class', 'span8');
        
        $nip = $this->createElement('text', 'nip');
        $nip->setLabel('NIP');
        $nip->setRequired(true);
        $nip->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $nip->setAttrib('class', 'span8');
        
        $krsEdg = $this->createElement('text', 'krs_edg');
        $krsEdg->setLabel('Number KRS or EDG');
        $krsEdg->setRequired(true);
        $krsEdg->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $krsEdg->setAttrib('class', 'span8');
        
        $businessCard = $this->createElement('select', 'business_card');
        $businessCard->setLabel('Type of business card');
        $businessCard->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $businessCard->setRequired(true);
        
        $basicActivityId = $this->createElement('select', 'basic_activity_id');
        $basicActivityId->setLabel('Main activity');
        $basicActivityId->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $basicActivityId->setRequired(true);
        
        $basicActivityName = $this->createElement('text', 'basic_activity_name');
        $basicActivityName->setLabel('Other basic activity name');
        $basicActivityName->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $basicActivityName->setAttrib('class', 'span8');
        
        $otherActivityId = $this->createElement('multiselect', 'other_activity_id');
        $otherActivityId->setLabel('Other activities');
        $otherActivityId->setRequired(false);
        $otherActivityId->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $otherActivityId->setAttrib('multiple', 'multiple');
        
        $otherActivity = $this->createElement('checkbox', 'other_activity');
        $otherActivity->setLabel('Other');
        $otherActivity->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $otherActivity->setAttrib('class', 'span8');
        
        $otherActivityName = $this->createElement('text', 'other_activity_name');
        $otherActivityName->setLabel('Other activity name');
        $otherActivityName->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $otherActivityName->setAttrib('class', 'span8');
        
        $anotherAddress = $this->createElement('checkbox', 'another_address');
        $anotherAddress->setLabel('Such as company address');
        $anotherAddress->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $anotherAddress->setAttrib('class', 'span8');
        
        $businessAddress = $this->createElement('text', 'business_address');
        $businessAddress->setLabel('Address');
        $businessAddress->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $businessAddress->setAttrib('class', 'span8');
        
        $businessPostCode = $this->createElement('text', 'business_post_code');
        $businessPostCode->setLabel('Post code');
        $businessPostCode->setValidators(array('PostCode'));
        $businessPostCode->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $businessPostCode->setAttrib('class', 'span8');
        
        $businessCity = $this->createElement('text', 'business_city');
        $businessCity->setLabel('City');
        $businessCity->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $businessCity->setAttrib('class', 'span8');
        
        $cordX = $this->createElement('hidden', 'cord_x');
        $cordX->setDecorators(array('ViewHelper'));
        
        $cordY = $this->createElement('hidden', 'cord_y');
        $cordY->setDecorators(array('ViewHelper'));
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $contactName,
            $contactPhone,
            $contactEmail,
            $email,
            $email2,
            $email3,
            $phone,
            $website,
            $website2,
            $website3,
            $province,
            $city,
            $cordX,
            $cordY,
            $address,
            $postCode,
            $nip,
            $krsEdg,
            $businessCard,
            $basicActivityId,
            $basicActivityName,
            $otherActivityId,
            $otherActivity,
            $otherActivityName,
            $anotherAddress,
            $businessAddress,
            $businessPostCode,
            $businessCity,
            $parentId,
            $submit,
        ));
    }
}
?>