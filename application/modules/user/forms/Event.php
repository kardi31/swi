<?php

/**
 * User_Form_Event
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_Form_Event extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $parentId = $this->createElement('hidden', 'parent_id');
        $parentId->setDecorators(self::$hiddenDecorators);
        
        $eventType = $this->createElement('radio', 'event_type');
        $eventType->setLabel('Event type');
        $eventType->setRequired(true);
      
        $languages = $i18nService->getLanguageList();
        
        $translations = new Zend_Form_SubForm();

        foreach($languages as $language) {
            $translationForm = new Zend_Form_SubForm();
            $translationForm->setName($language);
            $translationForm->setDecorators(array(
                'FormElements'
            ));

            $title = $translationForm->createElement('text', 'title');
            $title->setBelongsTo($language);
            $title->setLabel('What?');
            $title->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            $title->setAttrib('class', 'span8');
            
            $content = $translationForm->createElement('textarea', 'content');
            $content->setBelongsTo($language);
            $content->setLabel('Content');
            $content->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            $content->setAttrib('class', 'span8 tinymce');
            
            $translationForm->setElements(array(
                $title,
                $content
            ));

            $translations->addSubForm($translationForm, $language);
        }
        
        $this->addSubForm($translations, 'translations');
        
        $location = $this->createElement('text', 'location');
        $location->setLabel('Where?');
        $location->setRequired();
        $location->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $location->setAttrib('class', 'span8');
        
        $href = $this->createElement('text', 'href');
        $href->setLabel('Link to event');
        $href->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $href->setAttrib('class', 'span8');
        
        $contact = $this->createElement('text', 'contact');
        $contact->setLabel('Contact / telephone number');
        $contact->setRequired();
        $contact->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $contact->setAttrib('class', 'span8');
        
        $eventDate = $this->createElement('text', 'event_date');
        $eventDate->setLabel('When?');
        $eventDate->setRequired();
        $eventDate->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $eventDate->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $parentId,
            $location,
            $href,
            $contact,
            $eventType,
            $eventDate,
            $submit
        ));
    }
}

