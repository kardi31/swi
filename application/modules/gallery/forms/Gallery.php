<?php

/**
 * Gallery_Form_Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Gallery_Form_Gallery extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
                
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
            $name->setLabel('Title');
            $name->setDecorators(self::$textDecorators);
            $name->setAttrib('class', 'span8');

            $description = $translationForm->createElement('textarea', 'description');
            $description->setBelongsTo($language);
            $description->setLabel('Content');
            $description->setDecorators(self::$tinymceDecorators);

            $translationForm->setElements(array(
                $name,
                $description
            ));

            $translations->addSubForm($translationForm, $language);
        }

        $group = $this->createElement('select', 'group_id');
        $group->setLabel('Grupa');
        $group->setDecorators(self::$selectDecorators);
        $group->setAttrib('class', 'span8');
        $group->addMultiOption(1,'Seniorzy');
        $group->addMultiOption(2,'Juniorzy Starsi');
        $group->addMultiOption(3,'Juniorzy Młodsi');
        $group->addMultiOption(4,'Trampkarze');
        $group->addMultiOption(5,'Młodzicy');
        $group->addMultiOption(6,'Orliki');
        
        $this->addSubForm($translations, 'translations');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $group,
            $submit
        ));
		
    }
    
}

