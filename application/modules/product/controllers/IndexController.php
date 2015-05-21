<?php

/**
 * Product_IndexController 
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Product_IndexController extends MF_Controller_Action {
    
    public function categoryAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $productService = $this->_service->getService('Product_Service_Product');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $productCountPerPage = $categoryService->getProductCountPerPage();
        
        if(!$category = $categoryService->getFullCategory($this->getRequest()->getParam('category'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Category not found', 404);
        }
        
        $metatagService->setViewMetatags($category->get('Metatags'), $this->view);

        if($this->getRequest()->getParam('count')){
            $counter = $this->getRequest()->getParam('count');
            $productCountPerPage =  $counter;
        }
        
        $orderParam = $this->getRequest()->getParam('order');
        $orderArray = explode('_', $orderParam);
        switch($orderArray[0]):
            case "new":
                $orderArray[0] = "pro.new";
                break;
            case "name":
                $orderArray[0] = "tr.name";
                break;
            case "price":
                $orderArray[0] = "pro.price";
                break;
        endswitch;
        
        $reverse = false;
        if ($orderArray[0] == "tr.name" && $orderArray[1] == 'asc'):
            $orderArray[1] = "desc";
            $reverse = true;
        endif;
//        if ($this->getRequest()->getParam('sorted')):
//           $productIds =  $this->_helper->user->get('productIds');
//        else:
//           $productIds = $productService->getIdProducts($category->getId(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
//           $this->_helper->user->set('productIds', $productIds);
//        endif;
//
//        if ($productIds): 
//            $query = $productService->getPreSortedProductPaginationQuery($productIds,$orderArray);
//        else:
         $query = $productService->getProductForCategory($category->getId(),$orderArray);
//        endif;
        $results = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        if ($reverse):
            $results = array_reverse($results);
        endif;
        $adapter = new Zend_Paginator_Adapter_Array($results);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage($productCountPerPage);

        $user = $this->_helper->user();
        
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;

        $this->view->assign('userDiscount', $userDiscount);
        $this->view->assign('userGroupDiscounts', $userGroupDiscounts);
        
        $this->view->assign('counter', $counter);
        $this->view->assign('paginator', $paginator);
        $this->view->assign('page', $this->getRequest()->getParam('page', 1));
        $this->view->assign('category', $category);
        $this->view->assign('orderParam', $orderParam);
        
        $this->view->assign('productCountPerPage', $productCountPerPage);
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function productAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $productService = $this->_service->getService('Product_Service_Product');
        $userService = $this->_service->getService('User_Service_User');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $pageService = $this->_service->getService('Page_Service_PageShop');
        $commentService = $this->_service->getService('Product_Service_Comment');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

        $translator = $this->_service->get('translate');
        
        $user = $this->_helper->user();
        
        if(!$category = $categoryService->getFullCategory($this->getRequest()->getParam('category'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Category not found', 404);
        }
        
       if(!$banner3 = $pageService->getI18nPage('banner3', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
           throw new Zend_Controller_Action_Exception('Banner3 page not found');
       }
        
        if(!$product = $productService->getFullProduct($this->getRequest()->getParam('product'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Product not found', 404);
        }
        
        $metatagService->setViewMetatags($product->get('Metatags'), $this->view);
        
        if ($product != NULL):
            $productPhotos = array();
            $root = $product->get('PhotoRoot');
            if ( $root != NULL){
                if(!$root->isInProxyState())
                    $productPhotos = $photoService->getChildrenPhotos($root);
            }
            else{
                $productPhotos = NULL;
            }
        endif;
        
        $categoryProducts= $productService->getCategoryProductsWithoutActiveProduct($product->getId(), $category->getId(), $this->view->language, Doctrine_Core::HYDRATE_ARRAY);
        
        $form = $userService->getCommentForm();
        
        $captchaDir = $this->getFrontController()->getParam('bootstrap')->getOption('captchaDir');
        
        $form->addElement('captcha', 'captcha',
            array(
            'label' => 'Rewrite the chars',  
            'captcha' => array(
                'captcha' => 'Image',  
                'wordLen' => 4,  
                'timeout' => 300,  
                'font' => APPLICATION_PATH . '/../data/arial.ttf',  
                'imgDir' => $captchaDir,  
                'imgUrl' => $this->view->serverUrl() . '/captcha/',  
                'width' => 170,
                'fontSize' => 25
            )   
        )); 
        
        if($user):
            $form->removeElement('nick');
        endif;
        
        if(isset($_POST['save_comment'])) {
            if($form->isValid($this->getRequest()->getParams())) {
                try{
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $values = $_POST;
                    $values['product_id'] = $product->getId();
                    $values['user_id'] = $user['id'];
                    if(!$values['nick']):
                        $values['nick'] = $user['username'];
                    endif;
               
                    $comment = $commentService->saveCommentFromArray($values); 
                    
                    $this->view->messages()->add($translator->translate('Evaluation added, waiting for moderation.'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                   
                
                   $this->_helper->redirector->gotoRoute(array('category' => $category->Translation[$this->view->language]->slug, 'product' => $product->Translation[$this->view->language]->slug), 'domain-i18n:product');
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }       

        $comments = $commentService->getPublishComments(3, $product->getId(), Doctrine_Core::HYDRATE_ARRAY);
        
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;
        if ($productPhotos):
            $productPhotos = $productPhotos->toArray();
        endif;

        $this->view->assign('userDiscount', $userDiscount);
        $this->view->assign('userGroupDiscounts', $userGroupDiscounts);

        $this->view->assign('comments', $comments);
       $this->view->assign('banner3', $banner3);

        $this->view->assign('form', $form);
        $this->view->assign('product', $product);
        $this->view->assign('productPhotos', $productPhotos);
        $this->view->assign('category', $category);
        $this->view->assign('hideSlider', true);
        $this->view->assign('categoryProducts', $categoryProducts);
        $this->view->assign('user', $user);
        
        $this->_helper->actionStack('layout', 'index', 'default');
	
	/* facebook */
        $couponService = $this->_service->getService('Order_Service_Coupon');	
	
	try{
	    require_once APPLICATION_PATH.'/../library/facebook-php-sdk/src/facebook.php';
	    $config = array();
	    $config['appId'] = '530955950330860';
	    $config['secret'] = '72762b999910f157d08bcf8f72e830bf';

	    $fb = new Facebook($config);
	}
	catch(Exception $e){
	    
	}
        
	$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	if (is_object($fb) && $fb->getUser()) { // sprawdza czy zalogowany
            $user = $fb->api('me');
	    if(!$user['email']||!strlen($user['email']))
		$_SESSION['no_email'] = true;
	    
            
	    
	    $msg_body = array(
		'link' => $url,
		'description' => strip_tags($product['Translation'][$this->view->language]['description']),
		'message' => 'Polecam',
		'title' => $product['Translation'][$this->view->language]['name'],
		'picture' => "http://$_SERVER[HTTP_HOST]/media/photos/".$product['PhotoRoot']['offset']."/".$product['PhotoRoot']['filename'],
		
	    );
	    try {
		$postResult = $fb->api('/me/feed', 'post', $msg_body );
	    } catch (FacebookApiException $e) {
		$this->view->messages()->add($translator->translate('Dziekujemy za udostępnienie. Kupon rabatowy został wysłany na Twój adres email'), 'success');
                
		$fb->destroySession();
		$this->_helper->redirector->gotoRoute(array('category' => $category->Translation[$this->view->language]->slug, 'product' => $product->Translation[$this->view->language]->slug), 'domain-i18n:product');
                
	    }
            if(!$_SESSION['no_email']){
                if(!$couponService->checkProductShared($user['email'],$product['id'])){
                    
	     $fb->destroySession();
                    $this->view->messages()->add($translator->translate('Już udostępniałeś ten produkt. Kolejny kod rabatowy nie został wysłany. Spróbuj udostępnić inny produkt.'), 'error');
                
                    $this->_helper->redirector->gotoRoute(array('category' => $category->Translation[$this->view->language]->slug, 'product' => $product->Translation[$this->view->language]->slug), 'domain-i18n:product');
                }
            }
            
	    if($postResult && !$_SESSION['no_email']){
		$code = $couponService->generateCouponCode();
		$start_validity_date = date('Y-m-d H:i:s');
		$finish_validity_date = date('Y-m-d H:i:s', strtotime("+6 months", strtotime($start_validity_date)));
		$couponValues = array(
		    'share_email' => $user['email'],
		    'share_product_id' => $product['id'],
		    'code' => $code,
		    'amount_coupon' => 5,
		    'type' => 'percent',
		    'start_validity_date' => $start_validity_date,
		    'finish_validity_date' => $finish_validity_date
		);
		
		
		$coupon = $couponService->saveShareCouponFromArray($couponValues);
		
		$mail = new Zend_Mail('UTF-8');
                $mail->setSubject("Ecoslimtea - kupon rabatowy");
                $mail->addTo($user['email']);
		$mail->setBodyHtml($this->view->partial('coupon.phtml', array('coupon' => $coupon)));
		$mail->send();
		$coupon->setSent(1);
		$coupon->save();
                    
		$this->view->messages()->add($translator->translate('Dziekujemy za udostępnienie. Kupon rabatowy został wysłany na Twój adres email'), 'success');
                
		$fb->destroySession();
		$this->_helper->redirector->gotoRoute(array('category' => $category->Translation[$this->view->language]->slug, 'product' => $product->Translation[$this->view->language]->slug), 'domain-i18n:product');
                
	    }
            else{
                $this->view->messages()->add($translator->translate('Wystąpił błąd. Skontaktuj się z administratorem. Napisz na biuro@kardimobile.pl'), 'error');
                $this->_helper->redirector->gotoRoute(array('category' => $category->Translation[$this->view->language]->slug, 'product' => $product->Translation[$this->view->language]->slug), 'domain-i18n:product');
                
            }
	    
	     $fb->destroySession();
	} 
	elseif(is_object($fb)) {
            $params = array(
                'scope' => 'public_profile, publish_actions, email',
                'redirect_uri' => $url
            );
            $this->view->assign('loginUrl',$fb->getLoginUrl($params));
	     $fb->destroySession();
        }
	else{
	    $this->view->assign('loginUrl','#');
	}
	
	
	
    }
    
    public function facebookProductAction(){
	
	
    }
    
    public function listProductsAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $pageService = $this->_service->getService('Page_Service_PageShop');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $productCountPerPage = $productService->getProductCountPerPage();
        
        if(!$page = $pageService->getI18nPage('products', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found', 404);
        }
        
        if(!$banner1 = $pageService->getI18nPage('banner1', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
           throw new Zend_Controller_Action_Exception('Banner1 page not found');
       }

       if(!$banner3 = $pageService->getI18nPage('banner3', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
           throw new Zend_Controller_Action_Exception('Banner3 page not found');
       }
        
        $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
	
        if($this->getRequest()->getParam('count')){
            $counter = $this->getRequest()->getParam('count');
            $productCountPerPage =  $counter;
        }
  
//        if ($this->getRequest()->getParam('sorted')):
//           $productIds =  $this->_helper->user->get('productIds');
//        else:
//           $productIds = $productService->getProducerIdProducts($producer->getId(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
//           $this->_helper->user->set('productIds', $productIds);
//        endif;  
//        
//        if ($productIds): 
//            $query = $productService->getPreSortedProducerProductPaginationQuery($productIds);
//        else:
//            $query = $productService->getProducerProducts($producer->getId());
//        endif;
//        
        $query = $productService->getProductPaginationQuery();

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage($productCountPerPage);
        
        $user = $this->_helper->user();
        
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;

        $this->view->assign('userDiscount', $userDiscount);
        $this->view->assign('userGroupDiscounts', $userGroupDiscounts);

        $this->view->assign('counter', $counter);
        $this->view->assign('paginator', $paginator);
        $this->view->assign('page', $page);
        $this->view->assign('hideSlider', true);
       $this->view->assign('banner1', $banner1);
       $this->view->assign('banner3', $banner3);

        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
     public function forDistributorsAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $productService = $this->_service->getService('Product_Service_Product');
        
        $productCountPerPage = $categoryService->getProductCountPerPage();
        

        if($this->getRequest()->getParam('count')){
            $counter = $this->getRequest()->getParam('count');
            $productCountPerPage =  $counter;
        }
        
        $orderParam = $this->getRequest()->getParam('order');
        $orderArray = explode('_', $orderParam);
        switch($orderArray[0]):
            case "new":
                $orderArray[0] = "pro.new";
                break;
            case "name":
                $orderArray[0] = "tr.name";
                break;
            case "price":
                $orderArray[0] = "pro.price";
                break;
        endswitch;
        
        $reverse = false;
        if ($orderArray[0] == "tr.name" && $orderArray[1] == 'asc'):
            $orderArray[1] = "desc";
            $reverse = true;
        endif;

        $query = $productService->getProductForDistributors($orderArray);
        
        $results = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        if ($reverse):
            $results = array_reverse($results);
        endif;
        $adapter = new Zend_Paginator_Adapter_Array($results);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage($productCountPerPage);

        $user = $this->_helper->user();
        
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;

        $this->view->assign('userDiscount', $userDiscount);
        $this->view->assign('userGroupDiscounts', $userGroupDiscounts);
        
        $this->view->assign('counter', $counter);
        $this->view->assign('paginator', $paginator);
        $this->view->assign('page', $this->getRequest()->getParam('page', 1));
        $this->view->assign('orderParam', $orderParam);
        
        $this->view->assign('productCountPerPage', $productCountPerPage);
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function showPdfAttachmentAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $attachmentService = $this->_service->getService('Product_Service_Attachment');
        
        if(!$attachment = $attachmentService->getFullAttachment($this->getRequest()->getParam('attachment'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Attachment not found', 404);
        }

        header ('Content-Type:', 'application/pdf');
        header ('Content-Disposition:', 'inline;');
        
        $fileName = "media/attachments/".$attachment->getFileName(); 
        $pdf = new Zend_Pdf($fileName, null, true); 

        echo $pdf->render();

    }
    
    public function newestProductsAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $pageService = $this->_service->getService('Page_Service_PageShop');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $newestProductCountPerPage = $productService->getNewestProductCountPerPage();
        
        $user = $this->_helper->user();
        
        if(!$page = $pageService->getI18nPage('last-news', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
        
        if($this->getRequest()->getParam('count')){
            $counter = $this->getRequest()->getParam('count');
            $newestProductCountPerPage =  $counter;
        }
        
        if ($this->getRequest()->getParam('sorted')):
           $newestProductsIds =  $this->_helper->user->get('newestProductsIds');
        else:
           $newestProductsIds = $productService->getIdNewestProducts(Doctrine_Core::HYDRATE_SINGLE_SCALAR);
           $this->_helper->user->set('newestProductsIds', $newestProductsIds);
        endif;

        if ($newestProductsIds): 
            $query = $productService->getPreSortedNewestProductPaginationQuery($newestProductsIds);
        else:
            $query = $productService->getNewestProductsPaginationQuery();
        endif;
        
        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage($newestProductCountPerPage);
        
        $this->view->assign('counter', $counter);
        $this->view->assign('paginator', $paginator);
        
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;

        $this->view->assign('userDiscount', $userDiscount);
        $this->view->assign('userGroupDiscounts', $userGroupDiscounts);

        $this->_helper->actionStack('layout-shop', 'index', 'default');
    }
    
    public function promotionProductsAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $pageService = $this->_service->getService('Page_Service_PageShop');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $promotionProductCountPerPage = $productService->getPromotionProductCountPerPage();
        
        $user = $this->_helper->user();
        
        if(!$page = $pageService->getI18nPage('promotions', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
        
        if($this->getRequest()->getParam('count')){
            $counter = $this->getRequest()->getParam('count');
            $promotionProductCountPerPage =  $counter;
        }
        
        if ($this->getRequest()->getParam('sorted')):
           $promotionProductsIds =  $this->_helper->user->get('promotionProductsIds');
        else:
           $promotionProductsIds = $productService->getIdPromotionProducts(Doctrine_Core::HYDRATE_SINGLE_SCALAR);
           $this->_helper->user->set('promotionProductsIds', $promotionProductsIds);
        endif;

        if ($promotionProductsIds): 
            $query = $productService->getPreSortedPromotionProductPaginationQuery($promotionProductsIds);
        else:
            $query = $productService->getPromotionProductsPaginationQuery();
        endif;
        
        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage($promotionProductCountPerPage);
        
        $this->view->assign('counter', $counter);
        $this->view->assign('paginator', $paginator);
        
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;

        $this->view->assign('userDiscount', $userDiscount);
        $this->view->assign('userGroupDiscounts', $userGroupDiscounts);

        $this->_helper->actionStack('layout-shop', 'index', 'default');
    }
    
    public function reducedPriceProductsAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $pageService = $this->_service->getService('Page_Service_PageShop');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $reducedPriceProductCountPerPage = $productService->getReducedPriceProductCountPerPage();
        
        $user = $this->_helper->user();
        
        if(!$page = $pageService->getI18nPage('reduced-price', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
        
        if($this->getRequest()->getParam('count')){
            $counter = $this->getRequest()->getParam('count');
            $reducedPriceProductCountPerPage =  $counter;
        }
        
        if ($this->getRequest()->getParam('sorted')):
           $reducedPriceProductsIds =  $this->_helper->user->get('reducedPriceProductsIds');
        else:
           $reducedPriceProductsIds = $productService->getIdReducedPriceProducts(Doctrine_Core::HYDRATE_SINGLE_SCALAR);
           $this->_helper->user->set('reducedPriceProductsIds', $reducedPriceProductsIds);
        endif;

        if ($reducedPriceProductsIds): 
            $query = $productService->getPreSortedReducedPriceProductPaginationQuery($reducedPriceProductsIds);
        else:
            $query = $productService->getReducedPriceProductsPaginationQuery();
        endif;
        
        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage($reducedPriceProductCountPerPage);
        
        $this->view->assign('counter', $counter);
        $this->view->assign('paginator', $paginator);
        
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;

        $this->view->assign('userDiscount', $userDiscount);
        $this->view->assign('userGroupDiscounts', $userGroupDiscounts);

        $this->_helper->actionStack('layout-shop', 'index', 'default');
    }
    
    public function ayurvedaProductsAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $pageService = $this->_service->getService('Page_Service_PageShop');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $ayurvedaProductCountPerPage = $productService->getAyurvedaProductCountPerPage();
        
        $user = $this->_helper->user();
        
        if(!$page = $pageService->getI18nPage('ayurveda-products', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
        
        if($this->getRequest()->getParam('count')){
            $counter = $this->getRequest()->getParam('count');
            $ayurvedaProductCountPerPage =  $counter;
        }
        
        if ($this->getRequest()->getParam('sorted')):
           $ayurvedaProductsIds =  $this->_helper->user->get('ayurvedaProductsIds');
        else:
           $ayurvedaProductsIds = $productService->getIdAyurvedaProducts(Doctrine_Core::HYDRATE_SINGLE_SCALAR);
           $this->_helper->user->set('ayurvedaProductsIds', $ayurvedaProductsIds);
        endif;

        if ($ayurvedaProductsIds): 
            $query = $productService->getPreSortedAyurvedaProductPaginationQuery($ayurvedaProductsIds);
        else:
            $query = $productService->getAyurvedaProductsPaginationQuery();
        endif;
        
        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage($ayurvedaProductCountPerPage);
        
         if($this->getRequest()->getParam('count')){
            $counter = $this->getRequest()->getParam('count');
            $ayurvedaProductCountPerPage =  $counter;
        }
        
        $this->view->assign('counter', $counter);
        $this->view->assign('paginator', $paginator);
        
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;

        $this->view->assign('userDiscount', $userDiscount);
        $this->view->assign('userGroupDiscounts', $userGroupDiscounts);

        $this->_helper->actionStack('layout-shop', 'index', 'default');
    }
    
    public function productCommentsAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getFullProduct($this->getRequest()->getParam('product'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Product not found', 404);
        }
        
        $this->view->headTitle($product->Translation[$this->view->language]['name']." - komentarze", 'SET');
        $this->view->headMeta('komentarz', 'description');
        $this->view->headMeta('komentarz', 'keywords');
        $this->view->assign('product', $product);
        
        $this->_helper->actionStack('layout-shop', 'index', 'default');
    }
    
    /* public function categoryFacebookAction() {
        $this->_helper->layout->setLayout('layout_facebook');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $productService = $this->_service->getService('Product_Service_Product');
        
         
        if(!$category = $categoryService->getFullCategory($this->getRequest()->getParam('category'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Category not found', 404);
        }
        $orderParam = $this->getRequest()->getParam('order');
        $orderArray = explode('_', $orderParam);
        switch($orderArray[0]):
            case "new":
                $orderArray[0] = "pro.new";
                break;
            case "name":
                $orderArray[0] = "tr.name";
                break;
            case "price":
                $orderArray[0] = "pro.price";
                break;
        endswitch;
        
        $reverse = false;
        if ($orderArray[0] == "tr.name" && $orderArray[1] == 'asc'):
            $orderArray[1] = "desc";
            $reverse = true;
        endif;
        $query = $productService->getProductForCategory($category->getId(),$orderArray);
        $results = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        if ($reverse):
            $results = array_reverse($results);
        endif;
        
        $this->view->assign('results', $results);
        $this->view->assign('category', $category);
        $this->view->assign('orderParam', $orderParam);
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    */
//    public function rateAction() {
//        $productService = $this->_service->getService('Product_Service_Product');
//        
//        $productId = $this->getRequest()->getParam('idBox');
//	$rate = $this->getRequest()->getParam('rate');
//        
//        if(!$product = $productService->getFullProduct($productId, 'id')) {
//            throw new Zend_Controller_Action_Exception('Product not found', 404);
//        }
//        
//        $userId = NULL;
//        $user = $this->_helper->user();
//        if ($user):
//            $userId = $user->getId();
//        endif;
//
//        $productService->saveRateProduct($product, $rate, $userId);
//        
//        $this->_helper->viewRenderer->setNoRender();
//                
//    }
}

