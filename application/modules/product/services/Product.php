<?php

/**
 * Product_Service_Product
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_Service_Product extends MF_Service_ServiceAbstract {
    
    protected $productTable;
    protected $productRelatedTable;
    protected $productSetTable;
    public static $newestProductCountPerPage = 12;
    public static $productCountPerPage = 12;
    public static $promotionProductCountPerPage = 12;
    public static $reducedPriceProductCountPerPage = 12;
    
    public static function getNewestProductCountPerPage(){
        return self::$newestProductCountPerPage;
    }
    
    public static function getProductCountPerPage(){
        return self::$productCountPerPage;
    }
    
    public static function getPromotionProductCountPerPage(){
        return self::$promotionProductCountPerPage;
    }
    
    public static function getReducedPriceProductCountPerPage(){
        return self::$reducedPriceProductCountPerPage;
    }
    
    public function init() {
        $this->productTable = Doctrine_Core::getTable('Product_Model_Doctrine_Product');
        $this->productRelatedTable = Doctrine_Core::getTable('Product_Model_Doctrine_ProductRelated');
        $this->productSetTable = Doctrine_Core::getTable('Product_Model_Doctrine_ProductSet');
        parent::init();
    }
    
    public function getRelatedProduct($productId, $relateProductId) {    
        $q = $this->productRelatedTable->getRelatedProductQuery();
        $q->andWhere('rp.product_id = ?', $productId);
        $q->andWhere('rp.relate_product_id = ?', $relateProductId);
        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_RECORD);
    }
    
    public function getSetsProduct($productId, $setProductId) {    
        $q = $this->productSetTable->getSetProductQuery();
        $q->andWhere('sp.product_id = ?', $productId);
        $q->andWhere('sp.set_product_id = ?', $setProductId);
        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_RECORD);
    }
    
    public function getSetProduct($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->productSetTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getAllSets($hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {    
        $q = $this->productSetTable->createQuery('sp');
        return $q->execute(array(), $hydrationMode);
    }
    
     public function getRelateProduct($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->productRelatedTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getProduct($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->productTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getBook($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->bookTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullProduct($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->productTable->getProductQuery();
        $q->andWhere('tr.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getFullProductAdmin($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->productTable->getProductToAdminQuery();
        $q->andWhere('tr.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }

    public function getProductForm(Product_Model_Doctrine_Product $product = null) {
        $form = new Product_Form_Product();
        if(null != $product) { 
            $form->populate($product->toArray());
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($product->Translation[$language]->name);
                    $i18nSubform->getElement('short_description')->setValue($product->Translation[$language]->short_description);
                    $i18nSubform->getElement('description')->setValue($product->Translation[$language]->description);
                    $i18nSubform->getElement('ingredients')->setValue($product->Translation[$language]->ingredients);
                    $i18nSubform->getElement('how_to_use')->setValue($product->Translation[$language]->how_to_use);
                    $i18nSubform->getElement('reduced_price_text')->setValue($product->Translation[$language]->reduced_price_text);
                }
            }    
            
            $product = $product->toArray();
            $product['price'] = str_replace(".",",", $product['price']);
            $form->populate(array('price' => $product['price']));
            $product['promotion_price'] = str_replace(".",",", $product['promotion_price']);
            $form->populate(array('promotion_price' => $product['promotion_price']));
            $product['vat'] = str_replace(".",",", $product['vat']);
            $form->populate(array('vat' => $product['vat']));
        }
        return $form;
    }
    
    public function getRelateForm(Product_Model_Doctrine_Product $product = null) {
        $form = new Product_Form_Relate();
        if(null != $product) { 
            $form->populate($product->toArray());
        }
        return $form;
    }
    
    public function saveProductFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$product = $this->getProduct((int) $values['id'])) {
            $product = $this->productTable->getRecord();
        }
        
        $values['price'] = str_replace(",",".", $values['price']);
        $values['promotion_price'] = str_replace(",",".", $values['promotion_price']);
        if ($values['promotion_price'] == ""):
            $values['promotion_price'] = NULL;
        endif;
        $values['vat'] = str_replace(",",".", $values['vat']);
        if ($values['vat'] == ""):
            $values['vat'] = NULL;
        endif;
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $product->fromArray($values);
        
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['name'])) {
                $product->Translation[$language]->name = $values['translations'][$language]['name'];
                $product->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Product_Model_Doctrine_ProductTranslation', $values['translations'][$language]['name'], $product->getId());
                $product->Translation[$language]->short_description = $values['translations'][$language]['short_description'];
                $product->Translation[$language]->ingredients = $values['translations'][$language]['ingredients'];
                $product->Translation[$language]->how_to_use = $values['translations'][$language]['how_to_use'];
                $product->Translation[$language]->description = $values['translations'][$language]['description'];
                $product->Translation[$language]->reduced_price_text = $values['translations'][$language]['reduced_price_text'];
            }
        }
        
        if($values['type_of_trade'] != 2):
            $product->unlink('Book');
        else:
            $bookArray = $values;
            unset($bookArray['id']);
            $product->get('Book')->fromArray($bookArray);
        endif;
        
        if($values['type_of_trade'] != 3):
            $product->unlink('Supplement');
        else:
            $supplementArray = $values;
            unset($supplementArray['id']);
            $product->get('Supplement')->fromArray($supplementArray);
        endif;
        
        if($values['type_of_trade'] != 4):
            $product->unlink('Grocery');
        else:
            $groceryArray = $values;
            unset($groceryArray['id']);
            $product->get('Grocery')->fromArray($groceryArray);
        endif;
        
        $product->unlink('Categories');
        $product->link('Categories', $values['category_id']);
        
        
        $product->save();
        
        return $product;
    }  
    
    public function saveRelatedProductsFromArray($product, $values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        
        $oldRelatedProducts = $product->get("RelatedProducts")->getRelatesProducts();
        
        if(!$relateProduct = $this->getRelateProduct($product->getId(), 'product_id')){
            $relateProduct = $this->productRelatedTable->getRecord();
            $relateProduct->setProductId($product->getId());
        }
        $newRelates = array();
        foreach($values['product_id'] as $id7):
            if(array_key_exists($id7, $oldRelatedProducts)):
                $newRelates[$id7] =  $oldRelatedProducts[$id7];
            else:
                $newRelates[$id7] = 1;
            endif;
        endforeach;
        $relateProduct->setRelatesProducts($newRelates);
        $relateProduct->save();
    }   
    
    public function saveSetProductsFromArray($product, $values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        
        if(!$values['product_id'])
            return false;
        
        $oldSetProducts = $product->get("SetProducts")->getSetProducts();
        
        if(!$setProduct = $this->getSetProduct($product->getId(), 'product_id')){
            $setProduct = $this->productSetTable->getRecord();
            $setProduct->setProductId($product->getId());
        }
        $newSet = array();
        foreach($values['product_id'] as $id7):
            if(array_key_exists($id7, $oldSetProducts)):
                $newSet[$id7] =  $oldSetProducts[$id7];
            else:
                $newSet[$id7] = 1;
            endif;
        endforeach;
        $setProduct->setSetProducts($newSet);
        $setProduct->save();
        
        return $setProduct;
    }   
    
    public function saveRelates($productIds){
        foreach($productIds as $productId):
             $ids = $productIds;
             unset($ids[$productId]);
             if ($product = $this->getRelateProduct($productId, 'product_id')){
                $rel = $product->getRelatesProducts();
                $newRelates = $rel;
                foreach($ids as $id4):
                    if(array_key_exists($id4, $rel)):
                        $newRelates[$id4] =  $rel[$id4]+1;
                    else:
                        $newRelates[$id4] = 1;
                    endif;
                endforeach;
                $product->setRelatesProducts($newRelates);
             }
             else{
                 $product = $this->productRelatedTable->getRecord();
                 $newRelates = array();
                 foreach($ids as $id3):
                    $newRelates[$id3] = 1;
                 endforeach;
                 $product->setRelatesProducts($newRelates);
                 $product->setProductId($productId);
             }
             $product->save();
        endforeach;
    }
    
    public function refreshStatusProduct($product){
        if ($product->isStatus()):
            $product->setStatus(0);
        else:
            $product->setStatus(1);
        endif;
        $product->save();
    }
    
    public function refreshDistributorProduct($product){
        if ($product->isDistributor()):
            $product->setDistributor(0);
        else:
            $product->setDistributor(1);
        endif;
        $product->save();
    }
    
    public function refreshPromotionProduct($product){
        if ($product->isPromoted()):
            $product->setPromoted(0);
        else:
            $product->setPromoted(1);
        endif;
        $product->save();
    }
    
    public function removeProduct(Product_Model_Doctrine_Product $product) {
        $product->unlink('Categories');
        $product->get('Translation')->delete();
        $product->unlink('Book');
        $product->unlink('Supplement');
        $product->unlink('Grocery');
        $product->save();
        $product->delete();
    }
    
    public function getAllProducts($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllProductForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductForSiteMapQuery();
        return $q->execute(array(), $hydrationMode);
    }

    public function getAllNewProducts($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.new = ?', 1);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetProductSelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllProducts();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }

        foreach($items as $item) {
                $result[$item->getId()] = strip_tags($item->Translation[$language]->name);
        }
        
        return $result;
    }
    
    public function getAllNewProductsAndPromotionProducts($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.new = ? or pro.promotion = ?', array(1,1));
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetNewProductsSelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllNewProductsAndPromotionProducts();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }

        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->name;
        }
        
        return $result;
    }
    
    public function getTargetProductSelectOptionsToRelate($productId, $prependEmptyValue = false, $language = null) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.id != ?', $productId);
        $items = $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }

        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->name;
        }
        return $result;
    }
    
    public function getTargetProductSelectOptionsToSet($productId, $prependEmptyValue = false, $language = null) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.id != ?', $productId);
        $items = $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }

        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->name;
        }
        return $result;
    }
    
    public function getUnSelectedDiscountSelectOptions($discountId, $language = null) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.discount_id != ? OR pro.discount_id IS NULL', $discountId);
        $items = $q->execute(array(), $hydrationMode);
        $result = array();
        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->name;
        }
        return $result;
    }
    
    public function getSelectedDiscountSelectOptions($discountId, $language = null) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.discount_id = ?', $discountId);
        $items = $q->execute(array(), $hydrationMode);
        $result = array();
        foreach($items as $item) {
            $result[$item->getId()] = $item->Translation[$language]->name; 
        }
        return $result;
    }
    
    public function unSelectDiscountProducts($selectedProducts, $newSelectedProducts){
        foreach($selectedProducts as $key => $selectedProduct):
            $flag = false;
            foreach($newSelectedProducts as $newSelectedProduct):
                if ($key == $newSelectedProduct):
                    $flag = true;
                endif;
            endforeach;
            if ($flag == false):
                $product = $this->getProduct($key);
                $product->setDiscountId(NULL);
                $product->save();
            endif;
        endforeach;
    }
    
    public function saveAssignedDiscountsFromArray($values, $discountId){
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        $selectedProducts = $this->getSelectedDiscountSelectOptions($discountId);
        $this->unSelectDiscountProducts($selectedProducts, $values['product_selected']);
        foreach($values['product_selected'] as $value):
            $product = $this->getProduct($value);
            $product->setDiscountId($discountId);
            $product->save();
        endforeach;
    }
    
    public function getAllProductsForCount($countOnly = false) {
        if(true == $countOnly) {
            return $this->productTable->count();
        } else {
            return $this->productTable->findAll();
        }
    }
    
    public function getProductForCategory($categoryId,$orderArray = null) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('cat.id = ?', $categoryId);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->andWhere('pro.distributor = ?', 0);
        if($orderArray[0]!=null)
            $q->addOrderBy($orderArray[0]." ".strtoupper($orderArray[1]));
        else
            $q->addOrderBy('rand()');
        return $q;
    }
    
    public function getProductForDistributors($orderArray = null) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addWhere('pro.distributor = 1');
        if($orderArray!=null)
            $q->addOrderBy($orderArray[0]." ".strtoupper($orderArray[1]));
        else
            $q->addOrderBy('rand()');
        return $q;
    }
    
    public function getNewestProductsPaginationQuery(){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.new = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        return $q;
    }
    
    public function getProductPaginationQuery(){
        $q = $this->productTable->getProductQuery();
        $q->addOrderBy('a.id');
        return $q;
    }
    
    public function getProducerProducts($producerId) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('prod.id = ?', $producerId);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        return $q;
    }
    
    public function getPreSortedProductPaginationQuery($productIds,$orderArray = null) {
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($productIds));
        if($orderArray!=null)
            $q->addOrderBy($orderArray[0]." ".strtoupper($orderArray[1]));
        else
        {
            if(is_array($productIds)):
                $q->addOrderBy('FIELD(pro.id, '.implode(', ', $productIds).')');
            endif; 
        }
        return $q;  
    }
    
    public function getPreSortedProductDistributorPaginationQuery($productIds,$orderArray = null) {
        $q = $this->productTable->getProductQuery();
        $q->addWhere('pro.distributor = 1');
        $q->addWhere('pro.id IN ?', array($productIds));
        if($orderArray!=null)
            $q->addOrderBy($orderArray[0]." ".strtoupper($orderArray[1]));
        else
        {
            if(is_array($productIds)):
                $q->addOrderBy('FIELD(pro.id, '.implode(', ', $productIds).')');
            endif; 
        }
        return $q;  
    }
    
    public function getPreSortedProductCart($productIds) {
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($productIds));
        if(is_array($productIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $productIds).')');
        endif; 
        return $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);  
    }
    
    public function getSetProducts($setProductsIds,$hydrationMode = Doctrine_Core::HYDRATE_ARRAY){
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($setProductsIds));
        if(is_array($setProductsIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $setProductsIds).')');
        endif; 
        return $q->execute(array(), $hydrationMode);  
    }
    
    public function getSetAvailability($setProductsIds,$hydrationMode = Doctrine_Core::HYDRATE_ARRAY){
        $q = $this->productTable->createQuery('pro');
        $q->addSelect('pro.availability');
        $q->where('pro.id IN ?', array($setProductsIds));
        $q->orderBy('pro.availability ASC');
        $q->limit(1);
        return $q->fetchOne(array(), $hydrationMode);  
    }
    
    public function setSetAvailability($product_id){
        $prod = $this->getProduct($product_id);
       $setProducts = $this->getSetProductsIds($product_id, 10);
        $availability = $this->getSetAvailability($setProducts,Doctrine_Core::HYDRATE_SINGLE_SCALAR);
        $prod->setAvailability($availability);
        $prod->save();
    }
    
    public function updateProductParentSets($product_id){
        $sets = $this->getAllSets();
        foreach($sets as $set):
            if(array_key_exists($product_id, $set['set_products'])){
                $this->setSetAvailability($set['product_id']);
            }
        endforeach; 
    }
    
    public function getRelatedProducts($relatedProductsIds){
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($relatedProductsIds));
        if(is_array($relatedProductsIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $relatedProductsIds).')');
        endif; 
        return $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);  
    }
    
    public function getPreSortedProducerProductPaginationQuery($productIds) {
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($productIds));
        if(is_array($productIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $productIds).')');
        endif; 
        return $q;  
    }
    
    public function getIdProducts($categoryId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getIdsProductsQuery();
        $q->andWhere('cat.id = ?', $categoryId);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getMostFrequenltyPurchasedProducts($limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getMostFrequentlyForMainPageProductQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('pro.most_frequently_purchased = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->andWhere('pro.status = ?', array(1));
        $q->andWhere('pro.availability > ?', 0);
        $q->addOrderBy('pro.created_at DESC');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNewestProducts($limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getNewestProductForMainPageProductQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('pro.new = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('pro.created_at DESC');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getIdNewestProducts($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getIdsProductsQuery();
        $q->andWhere('pro.new = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getIdPromotionProducts($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getIdsProductsQuery();
        $q->andWhere('pro.discount_id IS NOT NULL OR prod.discount_id IS NOT NULL OR pro.promotion = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getIdReducedPriceProducts($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getIdsProductsQuery();
        $q->andWhere('pro.reduced_price = ?', 1);
        $q->addOrderBy('rand()');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getIdAyurvedaProducts($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getIdsProductsQuery();
        $q->andWhere('pro.ayurveda_product = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getPromotionProductsPaginationQuery(){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.discount_id IS NOT NULL OR prod.discount_id IS NOT NULL OR pro.promotion = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        return $q;
    }
    
    public function getReducedPriceProductsPaginationQuery(){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.reduced_price = ?', 1);
        $q->addOrderBy('rand()');
        return $q;
    }
    
    public function getAyurvedaProductsPaginationQuery(){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.ayurveda_product = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        return $q;
    }
    
    public function getPreSortedNewestProductPaginationQuery($newestProductsIds) {
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($newestProductsIds));
        if(is_array($newestProductsIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $newestProductsIds).')');
        endif; 
        return $q;  
    }
    
    public function getPreSortedPromotionProductPaginationQuery($promotionProductsIds) {
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($promotionProductsIds));
        if(is_array($promotionProductsIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $promotionProductsIds).')');
        endif; 
        return $q;  
    }
    
    public function getPreSortedReducedPriceProductPaginationQuery($reducedPriceProductsIds) {
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($reducedPriceProductsIds));
        if(is_array($reducedPriceProductsIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $reducedPriceProductsIds).')');
        endif; 
        return $q;  
    }
    
    public function getPreSortedAyurvedaProductPaginationQuery($ayurvedaProductsIds) {
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($ayurvedaProductsIds));
        if(is_array($ayurvedaProductsIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $ayurvedaProductsIds).')');
        endif; 
        return $q;  
    }
    
    public function getCategoryBestSellers($categoryId, $limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('cat.id = ?', $categoryId);
        $q->andWhere('pro.most_frequently_purchased = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('pro.created_at');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getCategoryProductsWithoutActiveProduct($productId, $categoryId, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('cat.id = ?', $categoryId);
        $q->andWhere('pro.id != ?', $productId);
        $q->addOrderBy('pro.created_at');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getCategoryBestSellersWithoutActiveProduct($productId, $categoryId, $limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('cat.id = ?', $categoryId);
        $q->andWhere('pro.id != ?', $productId);
        $q->andWhere('pro.most_frequently_purchased = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('pro.created_at');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    
    public function getRelatedProductsIds($productId, $limit){
        if($this->getRelateProduct($productId, 'product_id')):
            $relatesProductsIds = $this->getRelateProduct($productId, 'product_id')->getRelatesProducts();
        endif; 
        arsort($relatesProductsIds);
        $i = 0;
        $relatesProductsIdsready = array();
        foreach($relatesProductsIds as $key1=>$counter):
            if($i < $limit):
                $relatesProductsIdsReady[] = $key1;
            endif;
            $i++;
        endforeach;
        return $relatesProductsIdsReady;
    }
    
    public function getSetProductsIds($productId, $limit){
        if($this->getSetProduct($productId, 'product_id')):
            $setProductsIds = $this->getSetProduct($productId, 'product_id')->getSetProducts();
        endif; 
        arsort($setProductsIds);
        $i = 0;
        $setProductsIdsready = array();
        foreach($setProductsIds as $key1=>$counter):
            if($i < $limit):
                $setProductsIdsready[] = $key1;
            endif;
            $i++;
        endforeach;
        return $setProductsIdsready;
    }

    public function getProductPromotions($limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getProductPromotionsForMainPageQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('pro.discount_id IS NOT NULL OR prod.discount_id IS NOT NULL OR pro.promotion = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getProductNew($limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getProductPromotionsForMainPageQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('pro.new = 1');
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getProducerIdProducts($producerId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProducerIdsProductsQuery($producerId);
        $q->andWhere('pro.reduced_price = ?', 0);
        $q->addOrderBy('rand()');
        return $q->execute(array(), $hydrationMode);
    } 
    
    public function getPreSortedPredifiniedNewestProducts($limit, $productsIds) {
        $q = $this->productTable->getAllProductsQuery();
        $q->where('pro.id IN ?', array($productsIds));
        if(is_array($productsIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $productsIds).')');
        endif; 
        $q->limit($limit);
        return $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);  
    }
    
    public function searchProducts($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getAllProductsQuery();
        $q->addSelect('TRIM(pt.name) AS search_title, TRIM(pt.short_description) as search_content, "products" as search_type');
        $q->andWhere('pt.name LIKE ? OR pt.short_description LIKE ?', array("%$phrase%", "%$phrase%"));
        return $q->execute(array(), $hydrationMode);
    }
    
    public function searchProductsAjurweda($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getAllProductsQuery();
        $q->addSelect('TRIM(pt.name) AS search_title, TRIM(pt.short_description) as search_content, "products" as search_type');
        $q->andWhere('pt.name LIKE ? OR pt.short_description LIKE ?', array("%$phrase%", "%$phrase%"));
        //$q->addOrderBy('RANDOM()');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNumberOfNewestProductForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getNumberOfProductForSiteMapQuery();
        $q->andWhere('pro.new = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNumberOfPromotionProductForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getNumberOfProductForSiteMapQuery();
        $q->andWhere('pro.discount_id IS NOT NULL OR pro.promotion = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNumberOfReducedPriceProductForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getNumberOfProductForSiteMapQuery();
        $q->andWhere('pro.reduced_price = ?', 1);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNumberOfAyurvedaProductForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getNumberOfProductForSiteMapQuery();
        $q->andWhere('pro.ayurveda_product = ?', 1);
        $q->andWhere('pro.reduced_price = ?', 0);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllOtherProductForCeneo($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductForCeneoQuery();
        $q->andWhere('pro.type_of_trade = ?', 1);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllBooksForCeneo($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductForCeneoQuery();
        $q->andWhere('pro.type_of_trade = ?', 2);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllSupplementsForCeneo($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductForCeneoQuery();
        $q->andWhere('pro.type_of_trade = ?', 3);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllGroceriesForCeneo($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductForCeneoQuery();
        $q->andWhere('pro.type_of_trade = ?', 4);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllProductsForGoogle($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getAllProductsForGoogleQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllBooksForSkapiec($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getAllProductsForSkapiecQuery();
        $q->andWhere('pro.type_of_trade = ?', 2);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllOtherProductsForSkapiec($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getAllProductsForSkapiecQuery();
        $q->andWhere('pro.type_of_trade != ?', 2);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTypeOfTradeRadioOptions(){
         $typeOfTradeOptions = array(
            '1' => 'Inna',
            '2' => 'Książki',
            '3' => 'Leki, suplementy',
            '4' => 'Delikatesy'
         );
         return $typeOfTradeOptions;
    }
    
    public function getTargetCategoryGoolgeSelectOptions($prependEmptyValue = false) {
        $items = $this->getAllGoogleCategories();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }

        foreach($items as $item) {
                $result[$item->getId()] = $item->name;
        }
        
        return $result;
    }
    
    public function getTargetCategoryCeneoSelectOptions($prependEmptyValue = false) {
        $items = $this->getAllCeneoCategories();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }

        foreach($items as $item) {
                $result[$item->getId()] = $item->name;
        }
        
        return $result;
    }
    
    public function setSetItemsAvailability(Product_Model_Doctrine_Product $product, $count, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $setProducts = $this->getSetProductsIds($product->getId(), 10);
        if($setProducts):
            $setProductsReady = $this->getSetProducts($setProducts,Doctrine_Core::HYDRATE_RECORD);
        endif; 
        
        foreach($setProductsReady as $setProduct):
            $setProduct->setAvailability($setProduct->getAvailability()-$count);
            $setProduct->save();
        endforeach;
    }
}
?>