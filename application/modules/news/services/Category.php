<?php

/**
 * News_Service_News
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_Service_Category extends MF_Service_ServiceAbstract{
    
    protected $categoryTable;
    
    public function init() {
        $this->categoryTable = Doctrine_Core::getTable('News_Model_Doctrine_Category');
    }
    
    public function getAllCategories($countOnly = false) {
        if(true == $countOnly) {
            return $this->categoryTable->count();
        } else {
            return $this->categoryTable->findAll();
        }
    }
    
    public function getCategory($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->categoryTable->findOneBy($field, $id, $hydrationMode);
    }
    
   
   
    
    public function getCategoryForm(News_Model_Doctrine_Category $category = null) {
         
       
        $form = new News_Form_Category();
        if($category!=null)
            $form->populate($category->toArray());
        
        return $form;
    }
    
    public function saveCategoryFromArray($values) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$category = $this->categoryTable->getProxy($values['id'])) {
            $category = $this->categoryTable->getRecord();
        }
       
        $category->slug = MF_Text::createUniqueTableSlug('News_Model_Doctrine_Category', $values['title'], $category->getId());
              
        
        $category->fromArray($values);
 
        $category->save();
       
        return $category;
    }
    
    public function removeCategory(News_Model_Doctrine_Category $category) {
        $category->delete();
    }
    
    public function prependCategoryOptions() {
       
       $options = array('' => '');
       $categories = $this->getAllCategories();
       
       foreach($categories as $category):
           $options[$category['id']] = $category['title'];
       endforeach;
       
       return $options;
    }
     
   
}

