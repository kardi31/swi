<?php

/**
 * PageMetatag
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Form_Metatag extends Admin_Form {
    
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

            $title = $translationForm->createElement('text', 'meta_title');
            $title->setBelongsTo($language);
            $title->setLabel('Title');
            $title->setDecorators(self::$textDecorators);
            $title->setAttrib('class', 'span8');

            $description = $this->createElement('textarea', 'meta_description');
            $description->setBelongsTo($language);
            $description->setLabel('Description');
            $description->setDecorators(self::$textareaDecorators);
            $description->setAttrib('class', 'span8');
            $description->setAttrib('rows', '3');

            $keywords = $this->createElement('textarea', 'meta_keywords');
            $keywords->setBelongsTo($language);
            $keywords->setLabel('Keywords');
            $keywords->setDecorators(self::$textareaDecorators);
            $keywords->setAttrib('class', 'span8');
            $keywords->setAttrib('rows', '3');
        
            $translationForm->setElements(array(
                $title,
                $description,
                $keywords
            ));

            $translations->addSubForm($translationForm, $language);
        }

        $this->addSubForm($translations, 'translations');
        
        
//        $title = $this->createElement('text', 'title');
//        $title->setLabel('Title');
//        $title->setDecorators(self::$textDecorators);
//        $title->setAttrib('class', 'span8');
//        
//        $description = $this->createElement('textarea', 'description');
//        $description->setLabel('Description');
//        $description->setDecorators(self::$textareaDecorators);
//        $description->setAttrib('class', 'span8');
//        $description->setAttrib('rows', '3');
//        
//        $keywords = $this->createElement('textarea', 'keywords');
//        $keywords->setLabel('Keywords');
//        $keywords->setDecorators(self::$textareaDecorators);
//        $keywords->setAttrib('class', 'span8');
//        $keywords->setAttrib('rows', '3');
        
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Ok');
        $submit->setDecorators(self::$submitDecorators);
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
//            $title,
//            $description,
//            $keywords,
            $submit
        ));
    }
}

