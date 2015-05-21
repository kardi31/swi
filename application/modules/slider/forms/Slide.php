<?php

/**
 * Slider_Form_Slide
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Slider_Form_Slide extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        
        
        
        $title = $this->createElement('text', 'title');
        $title->setLabel('Title');
        //$title->setDecorators(self::$textDecorators);
        
        $titleSize = $this->createElement('text', 'title_size');
        $titleSize->setLabel('Font size');
//        $titleSize->setDecorators(self::$textDecorators);
        
        $titleColor = $this->createElement('text', 'title_color');
        $titleColor->setLabel('Title color');
//        $titleColor->setDecorators(self::$textDecorators);
        
        $titlePosX = $this->createElement('text', 'title_pos_x');
        $titlePosX->setLabel('Position X');
//        $titlePosX->setDecorators(self::$textDecorators);
        
        $titlePosY = $this->createElement('text', 'title_pos_y');
        $titlePosY->setLabel('Position Y');
//        $titlePosY->setDecorators(self::$textDecorators);
        
        
        
        $description = $this->createElement('text', 'description');
        $description->setLabel('Description');
//        $description->setDecorators(self::$textDecorators);
        
	$descriptionColor = $this->createElement('text', 'description_color');
        $descriptionColor->setLabel('Description color');
//        $descriptionColor->setDecorators(self::$textDecorators);
        
        $descriptionSize = $this->createElement('text', 'description_size');
        $descriptionSize->setLabel('Font size');
//        $descriptionSize->setDecorators(self::$textDecorators);
        
        $descriptionBgColor = $this->createElement('text', 'description_bg_color');
        $descriptionBgColor->setLabel('Description background color');
//        $descriptionBgColor->setDecorators(self::$textDecorators);
        
        $descriptionPosX = $this->createElement('text', 'description_pos_x');
        $descriptionPosX->setLabel('Position X');
//        $descriptionPosX->setDecorators(self::$textDecorators);
        
        $descriptionPosY = $this->createElement('text', 'description_pos_y');
        $descriptionPosY->setLabel('Position Y');
//        $descriptionPosY->setDecorators(self::$textDecorators);
        
        
        $animation = $this->createElement('select', 'animation');
        $animation->setLabel('Animacja');
//        $animation->setDecorators(self::$textDecorators);
        
        
        $link = $this->createElement('text', 'target_href');
        $link->setLabel('URL');
//        $link->setDecorators(self::$textDecorators);
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id, 
            $title,
            $titleSize,
            $titleColor,
            $titlePosX,
            $titlePosY,
            $description,
            $descriptionSize,
            $descriptionColor,
            $descriptionBgColor,
            $descriptionPosX,
            $descriptionPosY,
            $animation,
            $link,
            $submit
        ));
    }
}

