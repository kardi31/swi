<?php

/**
 * Product
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class District_Form_Video extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
               

        $video = $this->createElement('text', 'url');
        $video->setLabel('Video url');
        $video->setRequired(false);
        $video->setDecorators(self::$textDecorators);
        $video->setAttrib('class', 'span8');
        
          $advertisment = $this->createElement('text', 'advert');
        $advertisment->setLabel('Advertisment');
        $advertisment->setRequired(false);
        $advertisment->setDecorators(self::$textDecorators);
        $advertisment->setAttrib('class', 'span8');
        
//        $languages = $i18nService->getLanguageList();
//
//        $translations = new Zend_Form_SubForm();
//
//        foreach($languages as $language) {
//            $translationForm = new Zend_Form_SubForm();
//            $translationForm->setName($language);
//            $translationForm->setDecorators(array(
//                'FormElements'
//            ));
//            
//            $name = $translationForm->createElement('text', 'name');
//            $name->setBelongsTo($language);
//            $name->setLabel('Video name');
//            $name->setDecorators(self::$textDecorators);
//            $name->setAttrib('class', 'span8');
//            
//            $description = $translationForm->createElement('textarea', 'description');
//            $description->setBelongsTo($language);
//            $description->setLabel('Description');
//            $description->setRequired(false);
//            $description->setDecorators(self::$tinymceDecorators);
//            $description->setAttrib('class', 'span8 tinymce');
//            
//            $translationForm->setElements(array(
//                $name,
//                $description
//            ));
//
//            $translations->addSubForm($translationForm, $language);
//        }
//        
//        $this->addSubForm($translations, 'translations');
         
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('id' => 'btnSubmit', 'class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $advertisment,
            $video,
            $submit,
        ));
    }
}
?>