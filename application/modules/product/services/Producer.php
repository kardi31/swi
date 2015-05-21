<?php

/**
 * Product_Service_Producer
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Service_Producer extends MF_Service_ServiceAbstract {
    
    protected $producerTable;
    public static $producerProductCountPerPage = 12;
    
    public static function getProducerProductCountPerPage(){
        return self::$producerProductCountPerPage;
    }
    
    public function init() {
        $this->producerTable = Doctrine_Core::getTable('Product_Model_Doctrine_Producer');
        parent::init();
    }
    
    public function getProducer($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->producerTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullProducer($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->producerTable->getProducerQuery();
        $q->andWhere('pt.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
   
    public function getProducerForm(Product_Model_Doctrine_Producer $producer = null) {
        $form = new Product_Form_Producer();
        if(null != $producer) { 
            $form->populate($producer->toArray());
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($producer->Translation[$language]->name);
                    $i18nSubform->getElement('description')->setValue($producer->Translation[$language]->description);
                }
            }
        }
        return $form;
    }
    
    public function saveProducerFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$producer = $this->getProducer((int) $values['id'])) {
            $producer = $this->producerTable->getRecord();
        }
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $producer->fromArray($values);
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['name'])) {
                $producer->Translation[$language]->name = $values['translations'][$language]['name'];
                $producer->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Product_Model_Doctrine_ProducerTranslation', $values['translations'][$language]['name'], $producer->getId());
                $producer->Translation[$language]->description = $values['translations'][$language]['description'];
            }
        }
        
        $producer->save();
        
        if(isset($values['parent_id'])) {
            $parent = $this->getProducer((int) $values['parent_id']);
            $producer->getNode()->insertAsLastChildOf($parent);
        }
        
        return $producer;
    }
    
    public function removeProducer(Product_Model_Doctrine_Producer $producer) {
        $producer->unlink('Products');
        $producer->get('Translation')->delete();
        $producer->save();
        $producer->delete();
    }

    public function refreshStatusProducer($producer){
        if ($producer->isStatus()):
            $producer->setStatus(0);
        else:
            $producer->setStatus(1);
        endif;
        $producer->save();
    }
    
    public function getProducerTree() {
        return $this->producerTable->getTree();
    }
    
    public function getProducerRoot() {
        return $this->getProducerTree()->fetchRoot();
    }
    
    public function createProducerRoot() {
        $producer = $this->producerTable->getRecord();
        $producer->save();
        $tree = $this->getProducerTree();
        $tree->createRoot($producer);
        return $producer;
    }     
    
    public function moveProducer($producer, $mode) {
        switch($mode) {
            case 'up':
                $prevSibling = $producer->getNode()->getPrevSibling();
                if($prevSibling->getNode()->isRoot()) {
                    throw new Exception('Cannot move category on root level');
                }
                $producer->getNode()->moveAsPrevSiblingOf($prevSibling);
                break;
            case 'down':
                $nextSibling = $producer->getNode()->getNextSibling();
                if($nextSibling->getNode()->isRoot()) {
                    throw new Exception('Cannot move category on root level');
                }
                $producer->getNode()->moveAsNextSiblingOf($nextSibling);
                break;
        }
    }

    public function getAllProducers($language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->producerTable->getProducerForMainPageQuery();
        $q->andWhere('pt.lang = ?', $language);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetProducerSelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllProducers($language);
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
            $result[$item->getId()] = $item->owner.' '.$item->Translation[$language]->name;
        }

        return $result;
    }
    
    public function getUnSelectedDiscountSelectOptions($discountId) {
        $q = $this->producerTable->getProducerQuery();
        $q->andWhere('pro.discount_id != ? OR pro.discount_id IS NULL', $discountId);
        $items = $q->execute(array(), $hydrationMode);
        $result = array();
        foreach($items as $item) {
                $result[$item->getId()] = $item->owner.' '.$this->item->Translation[$this->language]->name;
        }
        return $result;
    }
    
    public function getSelectedDiscountSelectOptions($discountId) {
        $q = $this->producerTable->getProducerQuery();
        $q->andWhere('pro.discount_id = ?', $discountId);
        $items = $q->execute(array(), $hydrationMode);
        $result = array();
        foreach($items as $item) {
                $result[$item->getId()] = $item->owner.' '.$this->item->Translation[$this->language]->name;
        }
        return $result;
    }
    
    public function unSelectDiscountProducers($selectedProducers, $newSelectedProducers){
        foreach($selectedProducers as $key => $selectedProducer):
            $flag = false;
            foreach($newSelectedProducers as $newSelectedProducer):
                if ($key == $newSelectedProducer):
                    $flag = true;
                endif;
            endforeach;
            if ($flag == false):
                $producer = $this->getProducer($key);
                $producer->setDiscountId(NULL);
                $producer->save();
            endif;
        endforeach;
    }
    
    public function saveAssignedDiscountsFromArray($values, $discountId){
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        $selectedProducers = $this->getSelectedDiscountSelectOptions($discountId);
        $this->unSelectDiscountProducers($selectedProducers, $values['producer_selected']);
        //var_dump($values['producer_selected']); exit;
        //var_dump($selected); exit;
        foreach($values['producer_selected'] as $value):
            $producer = $this->getProducer($value);
            $producer->setDiscountId($discountId);
            $producer->save();
        endforeach;
    }
    
    public function getAllProducersForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->producerTable->getProducerForSiteMapQuery();
        $q->andWhere('prod.status = ?', 1);
        return $q->execute(array(), $hydrationMode);
    }
}
?>