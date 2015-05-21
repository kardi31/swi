<?php

/**
 * News_Form_News
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_Form_News extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
      //  $serviceService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_Service');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $categoryId = $this->createElement('select', 'category_id');
        $categoryId->setLabel('Category');
        $categoryId->setRequired();
        $categoryId->setDecorators(self::$selectDecorators);
        
//        $groupId = $this->createElement('select', 'group_id');
//        $groupId->setLabel('Group');
//        $groupId->setDecorators(self::$selectDecorators);
        
      
//        $tagId = $this->createElement('select', 'tag_id');
//        $tagId->setLabel('Tags');
//        $tagId->setDecorators(self::$selectDecorators);
//        $tagId->setIsArray(true);
//        $tagId->setAttrib('multiple','multiple');
        
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

        $url = $this->createElement('text', 'url');
        $url->setLabel('Url');
        $url->setDecorators(self::$textDecorators);
        $url->setAttrib('class', 'span8');
        $url->setRequired(false);
        
        $publish = $this->createElement('checkbox', 'publish');
        $publish->setLabel('Publish');
        $publish->setDecorators(self::$checkgroupDecorators);
        $publish->setAttrib('class', 'span8');
        
//        $show_views = $this->createElement('checkbox', 'show_views');
//        $show_views->setLabel('Pokaż wyświetlenia');
//        $show_views->setDecorators(self::$checkgroupDecorators);
//        $show_views->setAttrib('class', 'span8');
//        $show_views->setValue(1);
//        $show_views->setAttrib('checked', 'checked');
        
        $publishDate = $this->createElement('text', 'publish_date');
        $publishDate->setLabel('Publish date');
        $publishDate->setDecorators(self::$datepickerDecorators);
        $publishDate->setAttrib('class', 'span8');
        
//        $services = $serviceService->getAllServices();
//                
//        $servicesDisplay = new Zend_Form_Element_MultiCheckbox('services_display');
//        $servicesDisplay->setLabel('Display in');
//        $servicesDisplay->setDecorators(self::$checkgroupDecorators);
////        $servicesDisplay->setAttrib('class', 'span8');
//        foreach($services as $value):
//            $servicesDisplay->addMultiOption($value['id'],$value['name']);
//        endforeach;
        
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $categoryId,
            $publish,
         //   $tagId,
         //   $groupId,
         //   $url,
            $publishDate,
         //   $show_views,
           // $servicesDisplay,
            $submit
        ));
    }
}

