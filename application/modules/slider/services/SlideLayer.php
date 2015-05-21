<?php

/**
 * Slider_Service_SlideLayer
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Slider_Service_SlideLayer extends MF_Service_ServiceAbstract {
    
    protected $slideLayerTable;
    
    public function init() {
        $this->slideLayerTable = Doctrine_Core::getTable('Slider_Model_Doctrine_SlideLayer');
    }
    
    public function getSlideLayer($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->slideLayerTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getAll($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->slideLayerTable->getFullSlideLayerQuery();
        $q->orderBy('l.lft');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getSlideLayerTree() {
        return $this->slideLayerTable->getTree();
    }
    
    public function getSlideLayerRoot($slide) {
        $slideLayerRoot = $slide->get('SlideLayerRoot');
        if($slideLayerRoot->isInProxyState()) {
            $slideLayer = $this->saveSlideLayerFromArray(array());
            $slideLayerRoot = $this->getSlideLayerTree()->createRoot($slideLayer);
            $slide->set('SlideLayerRoot', $slideLayerRoot);
            $slide->save();
        }
        return $slideLayerRoot;
    }
    
    public function getLayersForSlide($slideId, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
        $q = $this->slideLayerTable->getFullSlideLayerQuery();
        $q->andWhere('l.level > ?', 0);
        $q->andWhere('l.slide_id = ?', $slideId);
        $q->orderBy('l.lft');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getLayerForm(Slider_Model_Doctrine_SlideLayer $slideLayer = null) {
        $form = new Slider_Form_Layer();
        if(null != $slideLayer) {
            $form->populate($slideLayer->toArray());
        }
        return $form;
    }
    
    public function saveSlideLayerFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$slideLayer = $this->slideLayerTable->getProxy($values['id'])) {
            $slideLayer = $this->slideLayerTable->getRecord();
        }
        
        $slideLayer->fromArray($values);
        $slideLayer->save();

        return $slideLayer;
    }
    
    public function moveSlideLayer($slideLayer, $direction = 'down') {
        if($direction == 'up') {
            $prevSibling = $slideLayer->getNode()->getPrevSibling();
            if($prevSibling) {
                $slideLayer->getNode()->moveAsPrevSiblingOf($prevSibling);
            }
        } elseif($direction == 'down') {
            $nextSibling = $slideLayer->getNode()->getNextSibling();
            if($nextSibling) {
                $slideLayer->getNode()->moveAsNextSiblingOf($nextSibling);
            }
        }
    }
    
    public function removeSlideLayer($slideLayer) {
        $slideLayer->getNode()->delete();
    }
    
    public function getTargetAnimationsSelectOptions() {
        $result = array(
            'sft' => 'Short from Top',
            'sfb' => 'Short from Bottom',
            'sfr' => 'Short from Right',
            'sfl' => 'Short from Left',
            'lft' => 'Long from Top',
            'lfb' => 'Long from Bottom',
            'lfr' => 'Long from Right',
            'lfl' => 'Long from Left',
            'fade' => 'fading',
            'randomrotate' => 'randomrotate'
        );
        return $result;
    }
    
    public function getTargetEasingSelectOptions() {
        $result = array(
            'easeOutBack' => 'easeOutBack',
            'easeInQuad' => 'easeInQuad',
            'easeOutQuad' => 'easeOutQuad',
            'easeInOutQuad' => 'easeInOutQuad',
            'easeInCubic' => 'easeInCubic',
            'easeOutCubic' => 'easeOutCubic',
            'easeInOutCubic' => 'easeInOutCubic',
            'easeInQuart' => 'easeInQuart',
            'easeOutQuart' => 'easeOutQuart',
            'easeInOutQuart' => 'easeInOutQuart',
            'easeInQuint' => 'easeInQuint',
            'easeOutQuint' => 'easeOutQuint',
            'easeInOutQuint' => 'easeInOutQuint',
            'easeInSine' => 'easeInSine',
            'easeOutSine' => 'easeOutSine',
            'easeInOutSine' => 'easeInOutSine',
            'easeInExpo' => 'easeInExpo',
            'easeOutExpo' => 'easeOutExpo',
            'easeInOutExpo' => 'easeInOutExpo',
            'easeInCirc' => 'easeInCirc',
            'easeOutCirc' => 'easeOutCirc',
            'easeInOutCirc' => 'easeInOutCirc',
            'easeInElastic' => 'easeInElastic',
            'easeOutElastic' => 'easeOutElastic',
            'easeInOutElastic' => 'easeInOutElastic',
            'easeInBack' => 'easeInBack',
            'easeOutBack' => 'easeOutBack',
            'easeInOutBack' => 'easeInOutBack',
            'easeInBounce' => 'easeInBounce',
            'easeOutBounce' => 'easeOutBounce',
            'easeInOutBounce' => 'easeInOutBounce'
        );
        return $result;
    }
    
    public function getTargetClassSelectOptions() {
        $result = array(
            '' => '',
            'color_grey_light_3' => 'color_grey_light_3',
            'color_light' => 'color_light',
            'big_white' => 'big_white',
            'big_orange' => 'big_orange',
            'big_black' => 'big_black',
            'medium_grey' => 'medium_grey',
            'small_text' => 'small_text',
            'medium_text' => 'medium_text',
            'large_text' => 'large_text',
            'large_black_text' => 'large_black_text',
            'very_large_text' => 'very_large_text',
            'very_large_black_text' => 'very_large_black_text',
            'bold_red_text' => 'bold_red_text',
            'bold_brown_text' => 'bold_brown_text',
            'bold_green_text' => 'bold_green_text',
            'very_big_white' => 'very_big_white',
            'very_big_black' => 'very_big_black'
        );
        return $result;
    }
}

