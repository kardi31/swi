<?php

/**
 * Product
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Gallery_Form_Video extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
               

        $video = $this->createElement('text', 'url');
        $video->setLabel('Video url');
        $video->setRequired(false);
        $video->setDecorators(self::$textDecorators);
        $video->setAttrib('class', 'span8');
        
        $publish = $this->createElement('checkbox', 'publish');
        $publish->setLabel('Publish');
        $publish->setDecorators(self::$checkgroupDecorators);
        $publish->setAttrib('class', 'span8');
        $publish->setValue(1);
        
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
            $name->setLabel('Nazwa video');
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
         
         $dateFrom = $this->createElement('text', 'date_from');
        $dateFrom->setLabel('Date from');
        $dateFrom->setDecorators(self::$textDecorators);
//        $dateFrom->setRequired(true);
        $dateFrom->setAttrib('class', 'span8 combiner-picker');
        
	
        $publishDate = $this->createElement('text', 'publish_date');
        $publishDate->setLabel('Publish date');
        $publishDate->setDecorators(self::$datepickerDecorators);
        $publishDate->setAttrib('class', 'span8');
	
         $target_href = $this->createElement('text', 'target_href');
        $target_href->setLabel('Adres do klikania');
        $target_href->setDecorators(self::$textDecorators);
        $target_href->setRequired(false);
        $target_href->setAttrib('class', 'span8');
        
        $dateTo = $this->createElement('text', 'date_to');
        $dateTo->setLabel('Date to');
        $dateTo->setDecorators(self::$textDecorators);
//        $dateTo->setRequired(true);
        $dateTo->setAttrib('class', 'span8 combiner-picker');
        
         
        $ad = $this->createElement('select', 'ad_id');
        $ad->setLabel('Advertisment');
        $ad->setRequired(false);
        $ad->setDecorators(self::$selectDecorators);
        $ad->setAttrib('class', 'span8');
        
        
        $allow_skip = $this->createElement('checkbox', 'allow_skip');
        $allow_skip->setLabel('Pomijanie reklamy');
        $allow_skip->setRequired(false);
        $allow_skip->setDecorators(self::$checkboxDecorators);
        $allow_skip->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('id' => 'btnSubmit', 'class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $publish,
//            $dateFrom,
            $ad,
//            $dateTo,
	    $publishDate,
            $allow_skip,
            $target_href,
            $video,
            $submit,
        ));
    }
}
?>