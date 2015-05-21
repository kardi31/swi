<?php

/**
 * Product_Form_Discount
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Form_Discount extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $startDate = $this->createElement('text', 'start_date');
        $startDate->setLabel('Start date');
        $startDate->setRequired();
        $startDate->setDecorators(self::$datepickerDecorators);
        $startDate->setAttrib('class', 'span8');
        
        $finishDate = $this->createElement('text', 'finish_date');
        $finishDate->setLabel('Finish date');
        $finishDate->setRequired();
        $finishDate->setDecorators(self::$datepickerDecorators);
        $finishDate->setAttrib('class', 'span8');
        
        $amountDiscount = $this->createElement('text', 'amount_discount');
        $amountDiscount->setLabel('Amount in "%"');
        $amountDiscount->setRequired();
        $amountDiscount->setDecorators(self::$textDecorators);
        $amountDiscount->setAttrib('class', 'span8');
        
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
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $startDate,
            $finishDate,
            $amountDiscount,
            $submit
        ));
    }
    
}

