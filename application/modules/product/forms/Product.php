<?php

/**
 * Product
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Form_Product extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
               
        $code = $this->createElement('text', 'code');
        $code->setLabel('Product code');
      //  $code->setRequired();
        $code->setDecorators(self::$textDecorators);
        $code->setAttrib('class', 'span8');
        
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name of product');
        $name->setRequired();
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');
        
        $producerId = $this->createElement('select', 'producer_id');
        $producerId->setLabel('Producer');
        $producerId->setDecorators(self::$selectDecorators);
        
        $categoryId = $this->createElement('multiselect', 'category_id');
        $categoryId->setLabel('Categories');
        $categoryId->setRequired();
        $categoryId->setDecorators(self::$selectDecorators);
        $categoryId->setAttrib('multiple', 'multiple');
        
        $price = $this->createElement('text', 'price');
        $price->setLabel('Price');
        $price->setRequired();
        $price->setDecorators(self::$textDecorators);
        $price->setAttrib('class', 'span8');
        
        $availability = $this->createElement('text', 'availability');
        $availability->setLabel('Availability');
        $availability->addValidators(array(
            array('Int', true)
        ));
        $availability->setRequired();
        $availability->setDecorators(self::$textDecorators);
        $availability->setAttrib('class', 'span8');
        
        $discountId = $this->createElement('select', 'discount_id');
        $discountId->setLabel('Discount');
        $discountId->setDecorators(self::$selectDecorators);
        
        $description = $this->createElement('textarea', 'description');
        $description->setLabel('Description');
        $description->setDecorators(self::$tinymceDecorators);
        $description->setAttrib('class', 'span8 tinymce');
        
        $promotion = $this->createElement('checkbox', 'promotion');
        $promotion->setLabel('Promotion');
        $promotion->setDecorators(self::$checkboxDecorators);
        $promotion->setAttrib('class', 'span8');
        
        $promotionPrice = $this->createElement('text', 'promotion_price');
        $promotionPrice->setLabel('Promotion price');
        $promotionPrice->setRequired(false);
        
        $promotionPrice->setDecorators(self::$textDecorators);
        $promotionPrice->setAttrib('class', 'span8');
        
        $new = $this->createElement('checkbox', 'new');
        $new->setLabel('Last new');
        $new->setDecorators(self::$checkboxDecorators);
        $new->setAttrib('class', 'span8');
        
        $mostFrequentlyPurchased = $this->createElement('checkbox', 'most_frequently_purchased');
        $mostFrequentlyPurchased->setLabel('Most frequently purchased');
        $mostFrequentlyPurchased->setDecorators(self::$checkboxDecorators);
        $mostFrequentlyPurchased->setAttrib('class', 'span8');
        
        $productId = $this->createElement('multiselect', 'product_id');
        $productId->setLabel('Products set');
        $productId->setDecorators(self::$selectDecorators);
        $productId->setAttrib('multiple', 'multiple');
        
        $distributor = $this->createElement('checkbox', 'distributor');
        $distributor->setLabel('For distributors');
        $distributor->setDecorators(self::$checkboxDecorators);
        $distributor->setAttrib('class', 'span8');
        
        $reducedPrice = $this->createElement('checkbox', 'reduced_price');
        $reducedPrice->setLabel('Reduced price');
        $reducedPrice->setDecorators(self::$checkboxDecorators);
        $reducedPrice->setAttrib('class', 'span8');

        $youtube = $this->createElement('text', 'youtube');
        $youtube->setLabel('Youtube');
        $youtube->setRequired(false);
        $youtube->setDecorators(self::$textDecorators);
        $youtube->setAttrib('class', 'span8');
        
        $vat = $this->createElement('text', 'vat');
        $vat->setLabel('VAT [%]');
        $vat->setRequired(false);
        $vat->setDecorators(self::$textDecorators);
        $vat->setAttrib('class', 'span8');

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
            $name->setLabel('Name of product');
            $name->setDecorators(self::$textDecorators);
            $name->setAttrib('class', 'span8');
            
            $shortDescription = $translationForm->createElement('textarea', 'short_description');
            $shortDescription->setBelongsTo($language);
            $shortDescription->setLabel('Short description');
            $shortDescription->setRequired(false);
            $shortDescription->setDecorators(self::$tinymceDecorators);
            $shortDescription->setAttrib('class', 'span8 tinymce');
            
            $description = $translationForm->createElement('textarea', 'description');
            $description->setBelongsTo($language);
            $description->setLabel('Description');
            $description->setRequired(false);
            $description->setDecorators(self::$tinymceDecorators);
            $description->setAttrib('class', 'span8 tinymce');
            
            $ingredients = $translationForm->createElement('textarea', 'ingredients');
            $ingredients->setBelongsTo($language);
            $ingredients->setLabel('Ingredients');
            $ingredients->setRequired(false);
            $ingredients->setDecorators(self::$tinymceDecorators);
            $ingredients->setAttrib('class', 'span8 tinymce');
            
            $how_to_use = $translationForm->createElement('textarea', 'how_to_use');
            $how_to_use->setBelongsTo($language);
            $how_to_use->setLabel('How to use');
            $how_to_use->setRequired(false);
            $how_to_use->setDecorators(self::$tinymceDecorators);
            $how_to_use->setAttrib('class', 'span8 tinymce');
            
            $reducedPriceText = $translationForm->createElement('textarea', 'reduced_price_text');
            $reducedPriceText->setBelongsTo($language);
            $reducedPriceText->setLabel('Reduced price - text');
            $reducedPriceText->setRequired(false);
            $reducedPriceText->setDecorators(self::$tinymceDecorators);
            $reducedPriceText->setAttrib('class', 'span8 tinymce');
            
            $translationForm->setElements(array(
                $name,
                $shortDescription,
                $description,
                $ingredients,
                $how_to_use,
                $reducedPriceText
            ));

            $translations->addSubForm($translationForm, $language);
        }
        
        $this->addSubForm($translations, 'translations');
         
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('id' => 'btnSubmit', 'class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $code,
            $producerId, 
            $categoryId,
            $discountId,
            $price,
            $productId,
            $availability,
            $promotion,
            $promotionPrice,
            $distributor,
            $new,
            $mostFrequentlyPurchased,
            $youtube,
            $reducedPrice,
            $vat,
            $submit,
        ));
    }
}
?>
