<?php

/**
 * News_Form_Article
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class News_Form_Article extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $categoryId = $this->createElement('select', 'category_id');
        $categoryId->setLabel('Category');
        $categoryId->setDecorators(self::$selectDecorators);
        
        
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
            $content->setAttrib('class', 'span8 tinymce');
            
            $translationForm->setElements(array(
                $title,
                $content
            ));

            $translations->addSubForm($translationForm, $language);
        }
        
        $this->addSubForm($translations, 'translations');

        $publish = $this->createElement('checkbox', 'publish');
        $publish->setLabel('Publish');
        $publish->setDecorators(self::$checkgroupDecorators);
        $publish->setAttrib('class', 'span8');
        
        $publishDate = $this->createElement('text', 'publish_date');
        $publishDate->setLabel('Publish date');
        $publishDate->setDecorators(self::$datepickerDecorators);
        $publishDate->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $categoryId,
            $publish,
            $publishDate,
            $submit
        ));
    }
}

