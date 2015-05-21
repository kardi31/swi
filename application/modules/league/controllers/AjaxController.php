<?php

/**
 * Order_AjaxController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_AjaxController extends MF_Controller_Action {
    
    public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('add-to-cart', 'json')
                ->initContext();
        parent::init();
    }

    public function addToCartAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $orderService = $this->_service->getService('Order_Service_Order');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $this->view->clearVars();
        
        $language = $i18nService->getDefaultLanguage();
        
        $translator = $this->_service->get('translate');
        
        $cart = $orderService->getCart();
     
        $product = $productService->getFullProduct($this->getRequest()->getParam('product-id'));
   
        if($product = $productService->getFullProduct((int) $this->getRequest()->getParam('product-id'))) {
      
            $cart->remove('Product_Model_Doctrine_Product', $product->getId());
            $cart->add('Product_Model_Doctrine_Product', $product->getId(), $product->Translation[$language->getId()]->name, $product->getPrice(), 1, null);
        }
        $counter = $cart->getItems();
        var_dump($counter);
        Zend_Registry::set('counter',$counter);
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $this->view->assign('status', "success");
        $this->view->assign('counter', $counter);
             
    }
    public function updateBasketAction()
    {
        $id_item = $this->getRequest()->getParam('id');
        $number = $this->getRequest()->getParam('number');
        $orderService = $this->_service->getService('Order_Service_Order');
        $cart = $orderService->getCart();
        $cart->updateNumber($id_item,$number);
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    }
    public function showBasketAction()
    {
        $orderService  = $this->_service->getService('Order_Service_Order');
        
        $modelCart = $orderService->getCart();
        $countItems = $modelCart->count();
        $sum = $modelCart->getSum();
        $basketMsg = $countItems." prod. ".number_format($sum,2)."zł";
        echo $basketMsg;
        $this->view->assign('basketMsg',$basketMsg);
        // Zend_Registry::set('wartosc','helloWorld');
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    }
}

