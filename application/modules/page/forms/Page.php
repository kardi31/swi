<?php

/**
 * Page_Form_Page
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Page_Form_Page extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $type = $this->createElement('hidden', 'type');
        $type->setDecorators(array('ViewHelper'));
        
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
            $title->setLabel('Title');
            $title->setDecorators(self::$textDecorators);
            $title->setAttrib('class', 'span8');

            $content = $translationForm->createElement('textarea', 'content');
            $content->setBelongsTo($language);
            $content->setLabel('Content');
            $content->setDecorators(self::$tinymceDecorators);

            $translationForm->setElements(array(
                $title,
                $content
            ));

            $translations->addSubForm($translationForm, $language);
        }

        $this->addSubForm($translations, 'translations');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $type,
            $submit
        ));
		
    }
    
}

