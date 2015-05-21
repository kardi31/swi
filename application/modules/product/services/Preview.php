<?php

/**
 * Product_Service_Preview
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Service_Preview extends MF_Service_ServiceAbstract {
    
    protected $previewTable;
    
    public function init() {
        $this->previewTable = Doctrine_Core::getTable('Product_Model_Doctrine_Preview');
        parent::init();
    }
    
    public function getPreview($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->previewTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullPreview($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->previewTable->getPreviewQuery();
        $q->andWhere('p.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getPreviewForm(Product_Model_Doctrine_Preview $preview = null) {
        $form = new Product_Form_Preview();
        if(null != $preview) { 
            $preview = $preview->toArray();
            $form->populate($preview);
        }
        return $form;
    }
    
    public function savePreviewFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$preview = $this->getPreview((int) $values['id'])) {
            $preview = $this->previewTable->getRecord();
        }

        $preview->fromArray($values);
        $preview->unlink('Categories');
        $preview->link('Categories', $values['category_id']);
        $preview->save();
        
        return $preview;
    }   
    
    public function refreshStatusPreview($preview){
        if ($preview->isStatus()):
            $preview->setStatus(0);
        else:
            $preview->setStatus(1);
        endif;
        $preview->save();
    }
    
    public function removePreview(Product_Model_Doctrine_Preview $preview) {
        $preview->unlink('Categories');
        $preview->save();
        $preview->delete();
    }
}
?>