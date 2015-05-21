<?php

/**
 * Producer
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Form_Producer extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $parentId = $this->createElement('hidden', 'parent_id');
        $parentId->setDecorators(self::$hiddenDecorators);
               
        $owner = $this->createElement('text', 'owner');
        $owner->setLabel('Owner');
        $owner->addValidators(array(
            array('alpha', false, array('allowWhiteSpace' => true))
        ));
        $owner->addFilters(array(
            array('alpha', array('allowWhiteSpace' => true))
        ));
        $owner->setRequired(false);
        $owner->setDecorators(self::$textDecorators);
        $owner->setAttrib('class', 'span8');
        
        $email = $this->createElement('text', 'email');
        $email->setLabel('Email');
        $email->setValidators(array('EmailAddress'));
        $email->setRequired(false);
        $email->setDecorators(self::$textDecorators);
        $email->setAttrib('class', 'span8');
        
        $phone = $this->createElement('text', 'phone');
        $phone->setLabel('Phone');
        $phone->setRequired(false);
        $phone->setDecorators(self::$textDecorators);
        $phone->setAttrib('class', 'span8');
                
        $website = $this->createElement('text', 'website');
        $website->setLabel('Website');
        $website->setRequired(false);
        $website->setDecorators(self::$textDecorators);
        $website->setAttrib('class', 'span8');
        
        $province = $this->createElement('text', 'province');
        $province->setLabel('Province');
        $province->setRequired(false);
        $province->setDecorators(self::$textDecorators);
        $province->setAttrib('class', 'span8');
        
        $city = $this->createElement('text', 'city');
        $city->setLabel('City');
        $city->setRequired(false);
        $city->setDecorators(self::$textDecorators);
        $city->setAttrib('class', 'span8');
        
        $address = $this->createElement('text', 'address');
        $address->setLabel('Address');
        $address->setRequired(false);
        $address->setDecorators(self::$textDecorators);
        $address->setAttrib('class', 'span8');
        
        $postCode = $this->createElement('text', 'post_code');
        $postCode->setLabel('Post code');
        $postCode->setValidators(array('PostCode'));
        $postCode->setRequired(false);
        $postCode->setDecorators(self::$textDecorators);
        $postCode->setAttrib('class', 'span8');
        
        $discountId = $this->createElement('select', 'discount_id');
        $discountId->setLabel('Discount');
        $discountId->setDecorators(self::$selectDecorators);
        
        $cordX = $this->createElement('hidden', 'cord_x');
        $cordX->setDecorators(array('ViewHelper'));
        
        $cordY = $this->createElement('hidden', 'cord_y');
        $cordY->setDecorators(array('ViewHelper'));
        
        $nip = $this->createElement('text', 'nip');
        $nip->setLabel('NIP');
        $nip->setRequired(false);
        $nip->setDecorators(self::$textDecorators);
        $nip->setAttrib('class', 'span8');

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
            $name->setLabel('Name of producer');
            $name->setDecorators(self::$textDecorators);
            $name->setAttrib('class', 'span8');
    
            $description = $translationForm->createElement('textarea', 'description');
            $description->setBelongsTo($language);
            $description->setLabel('Description');
            $description->setRequired(false);
            $description->setDecorators(self::$tinymceDecorators);
            $description->setAttrib('class', 'span8 tinymce');
            
            $translationForm->setElements(array(
                $name,
                $description
            ));

            $translations->addSubForm($translationForm, $language);
        }
        
        $this->addSubForm($translations, 'translations');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $parentId,
            $owner,
            $email,
            $phone,
            $website,
            $province,
            $city,
            $discountId,
            $cordX,
            $cordY,
            $address,
            $postCode,
            $nip,
            $submit,
        ));
    }
}
?>