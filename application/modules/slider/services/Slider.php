<?php

/**
 * Slider_Service_Slider
 *
 * @author Michał Folga <and.wilczynski@gmail.com>
 */
class Slider_Service_Slider extends MF_Service_ServiceAbstract {
    
    protected $sliderTable;
    protected $slideTable;
    
    public function init() {
        $this->sliderTable = Doctrine_Core::getTable('Slider_Model_Doctrine_Slider');
        $this->slideTable = Doctrine_Core::getTable('Slider_Model_Doctrine_Slide');
    }
    
    public function getSlider($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->sliderTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getSlide($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->slideTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getAll($hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
        $q = $this->sliderTable->getFullSliderQuery();
        $q->andWhere('sl.level > ?', 0);
        //$q->andWhere('l.level > ?', array(0));
        $q->orderBy('sl.lft');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllForSlider($slugSlider, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
        $q = $this->sliderTable->getFullSliderQuery();
        $q->andWhere('s.slug = ?', $slugSlider);
        $q->andWhere('sl.level > ?', 0);
        //$q->andWhere('l.level > ?', array(0));
        $q->orderBy('sl.lft');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllSlidesForSlider($sliderId, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
//        $tree = $this->slideTable->getTree();
//        $q = $this->slideTable->getTree()->getBaseQuery();
//        $q->andWhere('base.level > ?', 0);
//        $tree->setBaseQuery($q);
//        $slides = $tree->fetchTree(array('root_id' => 1));
//        var_dump("test"); exit;
    }
    
    public function getSlideTree() {
        return $this->slideTable->getTree();
    }
    
    public function getSliderSlideRoot($slider) {
        $slideRoot = $slider->get('SlideRoot');
        if($slideRoot->isInProxyState()) {
            $slide = $this->saveSlideFromArray(array());
            $slideRoot = $this->getSlideTree()->createRoot($slide);
            $slider->set('SlideRoot', $slideRoot);
            $slider->save();
        }
        return $slideRoot;
    }
    
    public function getSlideForm(Slider_Model_Doctrine_Slide $slide = null) {
        $form = new Slider_Form_SliderSlide();
        if(null != $slide) {
            $form->populate($slide->toArray());
        }
        return $form;
    }
    
    public function saveSliderFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$slider = $this->sliderTable->getProxy($values['id'])) {
            $slider = $this->sliderTable->getRecord();
        }
        
        $slider->fromArray($values);
        $slider->save();

        return $slider;
    }
    
    public function saveSlideFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }

        if(!$slide = $this->slideTable->getProxy($values['id'])) {
            $slide = $this->slideTable->getRecord();
        }
        
        $slide->fromArray($values);
        $slide->save();

        return $slide;
    }
    
    public function moveSliderSlide($slide, $direction = 'down') {
        if($direction == 'up') {
            $prevSibling = $slide->getNode()->getPrevSibling();
            if($prevSibling) {
                $slide->getNode()->moveAsPrevSiblingOf($prevSibling);
            }
        } elseif($direction == 'down') {
            $nextSibling = $slide->getNode()->getNextSibling();
            if($nextSibling) {
                $slide->getNode()->moveAsNextSiblingOf($nextSibling);
            }
        }
    }
    
    public function getTargetTransitionsSelectOptions() {
        $result = array(
            'boxslide' => 'boxslide',
            'boxfade' => 'boxfade',
            'slotzoom-horizontal' => 'slotzoom-horizontal',
            'slotslide-horizontal' => 'slotslide-horizontal',
            'slotfade-horizontal' => 'slotfade-horizontal',
            'slotzoom-vertical' => 'slotzoom-vertical',
            'slotslide-vertical' => 'slotslide-vertical',
            'slotfade-vertical' => 'slotfade-vertical',
            'curtain-1' => 'curtain-1',
            'curtain-2' => 'curtain-2',
            'curtain-3' => 'curtain-3',
            'slideleft' => 'slideleft',
            'slideright' => 'slideright',
            'slideup' => 'slideup',
            'slidedown' => 'slidedown',
            'fade' => 'fade',
            'random' => 'random'
        );
        return $result;
    }
    
}

