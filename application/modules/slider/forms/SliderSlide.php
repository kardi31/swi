<?php

/**
 * Slider_Form_SliderSlide
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Slider_Form_SliderSlide extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $transition = $this->createElement('select', 'transition');
        $transition->setLabel('Transition');
        $transition->setAttrib('class', 'span8');
        $transition->setDecorators(self::$selectDecorators);
        $transition->setDescription('The appearance transition of this slide');
        
        $slotAmount= $this->createElement('text', 'slot_amount');
        $slotAmount->setLabel('Slot amount');
        $slotAmount->setAttrib('class', 'span8');
        $slotAmount->setDecorators(self::$textDecorators);
        $slotAmount->setDescription('The number of slots or boxes the slide is divided into. If you use boxfade, over 7 slots can be juggy');
        
        $targetHref = $this->createElement('text', 'target_href');
        $targetHref->setLabel('Target href');
        $targetHref->setAttrib('class', 'span8');
        $targetHref->setDecorators(self::$textDecorators);
        
        $transitionDuration = $this->createElement('text', 'transition_duration');
        $transitionDuration->setLabel('Transition duration');
        $transitionDuration->setAttrib('class', 'span8');
        $transitionDuration->setDecorators(self::$textDecorators);
        $transitionDuration->setDescription('The duration of the transition (Default 300, min: 100, max: 2000)');
        
        $delay = $this->createElement('text', 'delay');
        $delay->setLabel('Delay');
        $delay->setAttrib('class', 'span8');
        $delay->setDecorators(self::$textDecorators);
        $delay->setDescription('A new Dealy value for the Slide. If no delay defined per slide, the dealy defined via Options will be used');
        
        $enableLink = $this->createElement('checkbox', 'enable_link');
        $enableLink->setLabel('Enable link');
        $enableLink->setDecorators(self::$textDecorators);
        $enableLink->setAttrib('class', 'span8');

        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id, 
            $transition,
            $slotAmount,
            $targetHref,
            $transitionDuration,
            $delay,
            $enableLink,
            $submit
        ));
    }
}

