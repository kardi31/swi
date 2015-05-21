<?php

/**
 * Slider_Form_Layer
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Slider_Form_Layer extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $textHtml= $this->createElement('textarea', 'text_html');
        $textHtml->setLabel('Text / html');
        $textHtml->setAttrib('class', 'span8 tinymce');
        $textHtml->setDecorators(self::$tinymceDecorators);
        
        $animation = $this->createElement('select', 'animation');
        $animation->setLabel('Animation');
        $animation->setAttrib('class', 'span8');
        $animation->setDecorators(self::$selectDecorators);
        
        $easing = $this->createElement('select', 'easing');
        $easing->setLabel('Easing');
        $easing->setAttrib('class', 'span8');
        $easing->setDecorators(self::$selectDecorators);
        $easing->setDescription('Special easing effect of the animation');
         
        $speed = $this->createElement('text', 'speed');
        $speed->setLabel('Speed');
        $speed->setAttrib('class', 'span8');
        $speed->setDecorators(self::$textDecorators);
        $speed->setDescription('Duration of the animation in milliseconds');

        $xPosition = $this->createElement('text', 'x_position');
        $xPosition->setLabel('X');
        $xPosition->setAttrib('class', 'span8');
        $xPosition->setDecorators(self::$textDecorators);
        $xPosition->setDescription('The horizontal position in the standard (via startwidth option defined) screen size (other screen sizes will be calculated)');
        
        $yPosition = $this->createElement('text', 'y_position');
        $yPosition->setLabel('Y');
        $yPosition->setAttrib('class', 'span8');
        $yPosition->setDecorators(self::$textDecorators);
        $yPosition->setDescription('The vertical position in the standard (via startheight option defined) screen size (other screen sizes will be calculated)');
        
        $targetHref = $this->createElement('text', 'target_href');
        $targetHref->setLabel('Target url');
        $targetHref->setAttrib('class', 'span8');
        $targetHref->setDecorators(self::$textDecorators);
        
        $start = $this->createElement('text', 'start');
        $start->setLabel('Start');
        $start->setAttrib('class', 'span8');
        $start->setDecorators(self::$textDecorators);
        $start->setDescription('How many milliseconds should this caption start to show');
        
        $widthIframe = $this->createElement('text', 'width_iframe');
        $widthIframe->setLabel('Iframe width');
        $widthIframe->setAttrib('class', 'span8');
        $widthIframe->setDecorators(self::$textDecorators);
        
        $heightIframe = $this->createElement('text', 'height_iframe');
        $heightIframe->setLabel('Iframe height');
        $heightIframe->setAttrib('class', 'span8');
        $heightIframe->setDecorators(self::$textDecorators);
        
        $class = $this->createElement('select', 'class');
        $class->setLabel('Class');
        $class->setAttrib('class', 'span8');
        $class->setDecorators(self::$textDecorators);
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id, 
            $textHtml,
            $animation,
            $easing,
            $speed,
            $xPosition,
            $yPosition,
            $targetHref,
            $start,
            $widthIframe,
            $heightIframe,
            $class,
            $submit
        ));
    }
}

