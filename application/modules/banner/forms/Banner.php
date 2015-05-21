<?php

/**
 * Banner_Form_Banner
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Banner_Form_Banner extends Admin_Form {
    
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
            $name->setLabel('Name');
            $name->setDecorators(self::$textDecorators);
            $name->setAttrib('class', 'span8');
            
            $translationForm->setElements(array(
                $name
            ));

            $translations->addSubForm($translationForm, $language);
        }

        $this->addSubForm($translations, 'translations');
        
        $website = $this->createElement('text', 'website');
        $website->setLabel('Website');
        $website->setRequired(false);
        $website->setDecorators(self::$textDecorators);
        $website->setAttrib('class', 'span8');
        
       $file = $this->createElement('file', 'photo');
        $file->setLabel('Photo');
        $file->setDecorators(array('File', 'Errors','Label'));
        // $file->setDecorators(self::$fileDecorators);
        $file->setRequired(false);
        
        
        $dateFrom = $this->createElement('text', 'date_from');
        $dateFrom->setLabel('Date from');
        $dateFrom->setDecorators(self::$textDecorators);
        $dateFrom->setRequired(true);
        $dateFrom->setAttrib('class', 'span8 combiner-picker');
        
        $dateTo = $this->createElement('text', 'date_to');
        $dateTo->setLabel('Date to');
        $dateTo->setDecorators(self::$textDecorators);
        $dateTo->setRequired(true);
        $dateTo->setAttrib('class', 'span8 combiner-picker');
        
        $position = $this->createElement('select', 'position');
        $position->setLabel('Position');
        $position->setRequired(false);
        $position->setDecorators(self::$selectDecorators);
        $position->setAttrib('class', 'span8');
        $position->addMultiOption('','');
        $position->addMultiOption('Sidebar1','Sidebar1');
        $position->addMultiOption('OverNews','OverNews');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $website,
            $file,
            $dateFrom,
            $dateTo,
            $position,
            $submit
        ));
    }
}

