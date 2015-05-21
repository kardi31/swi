<?php

/**
 * User_ProfileController
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_ProfileController extends MF_Controller_Action {
    
    public static $offerItemCountPerPage = 20;
    public static $searchResultItemCountPerPage = 20;
    public static $dealMessageItemCountPerPage = 10;
    public static $articleItemCountPerPage = 10;
    public static $bannerItemCountPerPage = 10;
    
    protected $user;
    
    public function init() {
        $user = $this->_helper->user();
        $role = $user->getRole();
        $this->view->navigation()->setRole($role);
        $this->user = $user;
        
        $this->_helper->ajaxContext()
                ->addActionContext('upload-profile-photo', 'json')
                ->addActionContext('delete-photo', 'html')
                ->addActionContext('load-photo-list', 'html')
                ->initContext();
        
        parent::init();
    }
    
    public function preDispatch() {
        $invoiceService = $this->_service->getService('Invoice_Service_Invoice');
        
        if(!$this->user) {
            $this->_helper->redirector->gotoRoute(array(), 'domain-login');
        }
        
        // 
        if(!in_array($this->getRequest()->getParam('action'), array('subscribe'))) {
        
            if(in_array($this->getRequest()->getParam('action'), array(
                'articles-new', 'articles-edit', 'offers-new', 'offers-edit'
            ))) {
                if(!$invoice = $invoiceService->getActiveInvoice($this->user->getId())) {
                    $this->_helper->redirector->gotoRoute(array('action' => 'subscribe'), 'domain-user-profile');
                }
            }
        
        }
    }
    
    public function indexAction() {
   //     $this->_helper->actionStack('layout', 'index', 'default');
   //     $this->_helper->actionStack('layout');
    }
    
    public function subscribeAction() {
        $categoryService = $this->_service->getService('Offer_Service_Category');
        $invoiceService = $this->_service->getService('Invoice_Service_Invoice');
        
        $cart = $invoiceService->getCart();
        $cartItems = $cart->getItems('Offer_Model_Doctrine_Category');
        $sum = $cart->getSum();
        $this->view->assign('cartItems', $cartItems);
        $this->view->assign('sum', $sum);

        $categoryPriceTree = $categoryService->getCategoryPriceTree();
        $this->view->assign('categoryPriceTree', $categoryPriceTree);
        
        $periods = array_keys(Offer_Model_Doctrine_CategoryPrice::getAvailablePeriods());
        $this->view->assign('periods', $periods);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function subscribeCategoryAction() {
        $categoryService = $this->_service->getService('Offer_Service_Category');
        $invoiceService = $this->_service->getService('Invoice_Service_Invoice');
        
        $translator = $this->_service->get('translate');
        
        $cart = $invoiceService->getCart();
        
        $periods = Offer_Model_Doctrine_CategoryPrice::getAvailablePeriods();
        
        if($category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            
            $period = $this->getRequest()->getParam('period');
            
            if(in_array((int) $this->getRequest()->getParam('period'), array_keys($periods))) {
                if($categoryPrice = $categoryService->fetchCategoryPrice($category, $period)) {
                    if($item = $cart->get('Offer_Model_Doctrine_Category', $category->getId())) {
                        if($item['count'] == $period) {
                            $cart->remove('Offer_Model_Doctrine_Category', $category->getId());
                        } else {
//                            $name = sprintf("%s, %s", $category->getName(), $translator->translate($periods[$period]));
                            $cart->add('Offer_Model_Doctrine_Category', $category->getId(), $category->getName(), $categoryPrice->getPrice(), $period, true);
                        }
                    } else {
//                        $name = sprintf("%s, %s", $category->getName(), $translator->translate($periods[$period]));
                        $cart->add('Offer_Model_Doctrine_Category', $category->getId(), $category->getName(), $categoryPrice->getPrice(), $period, true);
                    }
                }
            }
        }
        
        $this->_helper->redirector->gotoRoute(array('action' => 'subscribe'), 'domain-user-profile', true);
    }
    
    public function checkoutAction() {
        $invoiceService = $this->_service->getService('Invoice_Service_Invoice');
        
        $user = $this->_helper->user();
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        
        $cart = $invoiceService->getCart();
        
        if(!$invoice = $invoiceService->getInvoice((int) $this->getRequest()->getParam('id'))) {
        
            if(!$invoice = $invoiceService->getCurrentInvoice($user->getId())) {
                
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $invoice = $invoiceService->purchaseCart();
                    $invoice->setUserId($user->getId());
                    $invoice->setSum($cart->getSum());
                    $invoice = $invoiceService->applyInvoice($invoice); // także po wpłacie
                    $invoice->save();

                    $invoice->setCode($this->createInvoiceCode($invoice));
                    $invoice->save();
        
                    $invoiceService->fetchPayment($invoice);

                    $cart->clean();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
                
            }
            
        }

        $payUForm = new Invoice_Form_PayU();
        $this->view->assign('form', $payUForm);
        
        $payUForm->getElement('first_name')->setValue($user->getFirstName());
        $payUForm->getElement('last_name')->setValue($user->getLastName());
        $payUForm->getElement('email')->setValue($user->getEmail());
        $payUForm->getElement('session_id')->setValue($invoice->getId());
        $payUForm->getElement('amount')->setValue($invoice->getSum() * 100); // wartość w groszach
        $payUForm->getElement('desc')->setValue('');
        $payUForm->getElement('client_ip')->setValue($_SERVER['REMOTE_ADDR']);
        
        $payUForm->setAction($options['UrlPlatnosci_pl'] . '/UTF/NewPayment');
        $payUForm->getElement('pos_id')->setValue($options['PosId']);
        $payUForm->getElement('pos_auth_key')->setValue($options['PosAuthKey']);
        
        $payUForm->setName('payform');
        $payUForm->setMethod('post');
        
        $this->view->assign('invoice', $invoice);
        $this->view->assign('payForm', $payUForm);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    // agent actions
    
    public function offersAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        
        $query = $offerService->getAgentOffersPaginationQuery($this->user->getId());

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(self::$offerItemCountPerPage);
        
        $this->view->assign('paginator', $paginator);

        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
        
    }
    
    public function offersNewAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $categoryService = $this->_service->getService('Offer_Service_Category');
        $templateService = $this->_service->getService('Offer_Service_Template');

        $translator = $this->_service->get('translate');
        
        $user = $this->_helper->user();
        
        
        $form = new Zend_Form();
        $form->setAction($this->_helper->url->url(array('action' => 'offers-new'), 'domain-user-profile', true));
        
        $offerForm = $offerService->getOfferForm();
        $offerForm->setTranslator($translator);
        
//        $form->getElement('province')->setMultiOptions($offerService->getProvinceSelectOptions())->setAttribs(array('title' => $translator->translate('Province'), 'class' => 'span10'));
//        $form->getElement('city')->setMultiOptions($offerService->getCitySelectOptions())->setAttribs(array('title' => $translator->translate('City'), 'class' => 'span10'));
        $offerForm->getElement('category')->setAttribs(array('title' => $translator->translate('Offer category'), 'class' => 'span10'));
        $offerForm->getElement('title')->setAttribs(array('title' => $translator->translate('Offer title'), 'class' => 'span10'));
        $offerForm->getElement('content')->setAttribs(array('title' => $translator->translate('Content'), 'class' => 'span10'));
        
        $offerForm->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $offerForm->getElement('category')->setMultiOptions($categoryService->getCategorySelectOptions());
        $offerForm->removeElement('submit');
        
        $form->addSubForm($offerForm, 'offer');
        
        $parameterForm = $offerService->getParameterSubForm(null, null);
        $form->addSubForm($parameterForm, 'parameters');
        
        $step = 1;
        if($this->getRequest()->getPost('prev')) {
            $step = $prev = (int) $this->getRequest()->getPost('prev');
        } elseif($this->getRequest()->getParam('next')) {
            $step = $next = (int) $this->getRequest()->getPost('next');
        }
        
        if($this->getRequest()->isPost()) {
            if($category = $categoryService->getCategory((int) $this->getRequest()->getPost('category'))) {
                if($offerTemplate = $templateService->getCategoryOfferTemplate($category)) {
                    $parameterTemplates = $templateService->getOfferTemplateParameterTemplates($offerTemplate);
                }
            }
            
            if($parameterTemplates) {
                $parameterForm = $offerService->getParameterSubForm(null, $parameterTemplates);
                $form->addSubForm($parameterForm, 'parameters');
            }
            
            $form->getSubForm('offer')->populate($this->getRequest()->getPost());
            $form->getSubForm('parameters')->populate($this->getRequest()->getPost());
            
            switch($next) {
                case 2:
                    $form->getSubForm('offer')->isValid($this->getRequest()->getPost());
                    break;
                case 3:
                    $form->getSubForm('offer')->isValid($this->getRequest()->getPost());
                    $form->getSubForm('parameters')->isValid($this->getRequest()->getPost());
                    break;
                case 4:
                    $form->getSubForm('offer')->isValid($this->getRequest()->getPost());
                    $form->getSubForm('parameters')->isValid($this->getRequest()->getPost());
                    if(!$form->getSubForm('offer')->isErrors() && !$form->getSubForm('parameters')->isErrors()) {
                        try {            

                            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();   

                            $values = $form->getSubForm('offer')->getValues();
                            $values['user_id'] = $user->getId();
                            $values['category_id'] = $category->getId();
                            $values['offer_template_id'] = $offerTemplate->getId();

                            $parameterValues = $form->getSubForm('parameters')->getValues();
                            
                            $parameters = array();
                            foreach($parameterValues as $name => $value) {
                                $match = array();

                                if(!is_array($value)) {
                                    preg_match('/^parameter(\d+)/', $name, $match);
                                    if(isset($match[1])) {
                                        $parameterTemplate = $templateService->getParameterTemplate((int) $match[1]);
                                        $parameter = $templateService->createOfferParameter($parameterTemplate, $value);
                                    }
                                } else {
                                    foreach($value as $rangeKey => $rangeValue) {
                                        preg_match('/^parameter(\d+)from/', $rangeKey, $match);
                                        if(isset($match[1]) && $parameterTemplate = $templateService->getParameterTemplate((int) $match[1])) {
                                            if(isset($value['parameter' . $match[1] . 'to'])) {
                                                $parameterValue = array($rangeValue);
                                                array_push($parameterValue, $value['parameter' . $match[1] . 'to']);
                                                $parameter = $templateService->createOfferParameter($parameterTemplate, $parameterValue);
                                            } else {
                                                continue;
                                            }

                                        }
                                    }
                                }
                                $parameters[] = $parameter;
                            }

                            if($offer = $offerService->saveOfferFromArray($values)) {
                                $offer->setCode($offerService->createOfferCode($offer));
                                $offer->save();
                                $offerService->bindOfferParameters($offer, $parameters);
                            }

                            $this->_service->get('doctrine')->getCurrentConnection()->commit();


                            $this->_helper->redirector->gotoRoute(array('action' => 'offers'), 'domain-user-profile');

                        } catch(Exception $e) {
                            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                            $this->_service->get('log')->log($e->getMessage(), 4);
                        }
                    }
                    break;
                    
            }
            
            if($form->getSubForm('offer')->isErrors() || $form->getSubForm('parameters')->isErrors()) {
                if($prev = (int) $this->getRequest()->getPost('prev')) {
                    $step = $prev + 1;
                } elseif($next = (int) $this->getRequest()->getPost('next')) {
                    $step = $next - 1;
                }
            }
            
//            if($form->isValidPartial($this->getRequest()->getPost())) {
//                
//                switch($step) {
//                    case 2:
//                        break;
//                    case 4:
//                        // prepare parameter templates sub form, validation and persisting
//                        try {            
//                        
//                        $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();   
//
//                        $values = $form->getValues();
//                        $values['user_id'] = $user->getId();
//                        $values['category_id'] = $category->getId();
//                        $values['offer_template_id'] = $offerTemplate->getId();
//
//                        $parameters = array();
//                        foreach($values['parameters'] as $name => $value) {
//                            $match = array();
//                            
//                            if(!is_array($value)) {
//                                preg_match('/^parameter(\d+)/', $name, $match);
//                                if(isset($match[1])) {
//                                    $parameterTemplate = $templateService->getParameterTemplate((int) $match[1]);
//                                    $parameter = $templateService->createOfferParameter($parameterTemplate, $value);
//                                }
//                            } else {
//                                foreach($value as $rangeKey => $rangeValue) {
//                                    preg_match('/^parameter(\d+)from/', $rangeKey, $match);
//                                    if(isset($match[1]) && $parameterTemplate = $templateService->getParameterTemplate((int) $match[1])) {
//                                        if(isset($value['parameter' . $match[1] . 'to'])) {
//                                            $parameterValue = array($rangeValue);
//                                            array_push($parameterValue, $value['parameter' . $match[1] . 'to']);
//                                            $parameter = $templateService->createOfferParameter($parameterTemplate, $parameterValue);
//                                        } else {
//                                            continue;
//                                        }
//                                        
//                                    }
//                                }
//                            }
//                            $parameters[] = $parameter;
//                        }
//
//                        if($offer = $offerService->saveOfferFromArray($values)) {
//                            $offer->setCode($offerService->createOfferCode($offer));
//                            $offer->save();
//                            $offerService->bindOfferParameters($offer, $parameters);
//                        }
//
//                        $this->_service->get('doctrine')->getCurrentConnection()->commit();
//
//
//                        $this->_helper->redirector->gotoRoute(array('action' => 'offers'), 'domain-user-profile');
//
//                        } catch(Exception $e) {
//                            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
//                            $this->_service->get('log')->log($e->getMessage(), 4);
//                        }
//
//                        break;
//                }
//                    
//            } else {
//                if($prev = (int) $this->getRequest()->getPost('prev')) {
//                    $step = $prev + 1;
//                } elseif($next = (int) $this->getRequest()->getPost('next')) {
//                    $step = $next - 1;
//                }
//            }
        }

        $this->view->assign('form', $form);
        $this->view->assign('step', $step);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function offersEditAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $categoryService = $this->_service->getService('Offer_Service_Category');
        $templateService = $this->_service->getService('Offer_Service_Template');

        $translator = $this->_service->get('translate');
        
        $user = $this->_helper->user();
        
        if(!$offer = $offerService->getAgentOffer((int) $this->getRequest()->getParam('offer-id'))) {
            throw new Zend_Controller_Action_Exception('Offer not found');
        }
        
        $form = new Zend_Form();
        $form->setAction($this->_helper->url->url(array('action' => 'offers-edit', 'offer-id' => $offer->getId()), 'domain-user-profile', true));
        
        $offerForm = $offerService->getOfferForm($offer);
        $offerForm->getElement('category')->setMultiOptions($categoryService->getCategorySelectOptions());

        $step = 1;
        if($this->getRequest()->getPost('prev')) {
            $step = $prev = (int) $this->getRequest()->getPost('prev');
        } elseif($this->getRequest()->getParam('next')) {
            $step = $next = (int) $this->getRequest()->getPost('next');
        }
        
        $form->addSubForm($offerForm, 'offer');
        
        if($this->getRequest()->isPost()) {
            if($category = $categoryService->getCategory((int) $this->getRequest()->getPost('category'))) {
                if($offerTemplate = $templateService->getCategoryOfferTemplate($category)) {
                    $parameterTemplates = $templateService->getOfferTemplateParameterTemplates($offerTemplate);
                }
            }
        
            if($parameterTemplates) {
                $parameterForm = $offerService->getParameterSubForm($offer, $parameterTemplates);
                $form->addSubForm($parameterForm, 'parameters');
            }
            
            $form->getSubForm('offer')->populate($this->getRequest()->getPost());
            $form->getSubForm('offer')->populate($this->getRequest()->getPost());
            
            switch($next) {
                case 2:
                    $form->getSubForm('offer')->isValid($this->getRequest()->getPost());
                    break;
                case 3:
                    $form->getSubForm('offer')->isValid($this->getRequest()->getPost());
                    $form->getSubForm('parameters')->isValid($this->getRequest()->getPost());
                    break;
                case 4:
                    if(!$form->getSubForm('offer')->isErrors() && !$form->getSubForm('parameters')->isErrors()) {
                        try {            

                            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();   

                            $values = $form->getSubForm('offer')->getValues();
                            $values['user_id'] = $user->getId();
                            $values['category_id'] = $category->getId();
                            $values['offer_template_id'] = $offerTemplate->getId();

                            $parameterValues = $form->getSubForm('parameters')->getValues();
                            
                            $parameters = array();
                            foreach($parameterValues as $name => $value) {
                                $match = array();

                                if(!is_array($value)) {
                                    preg_match('/^parameter(\d+)/', $name, $match);
                                    if(isset($match[1])) {
                                        $parameterTemplate = $templateService->getParameterTemplate((int) $match[1]);
                                        $parameter = $templateService->saveOfferParameter($offer, $parameterTemplate, $value);
                                    }
                                } else {
                                    foreach($value as $rangeKey => $rangeValue) {
                                        preg_match('/^parameter(\d+)from/', $rangeKey, $match);
                                        if(isset($match[1]) && $parameterTemplate = $templateService->getParameterTemplate((int) $match[1])) {
                                            if(isset($value['parameter' . $match[1] . 'to'])) {
                                                $parameterValue = array($rangeValue);
                                                array_push($parameterValue, $value['parameter' . $match[1] . 'to']);
                                                $parameter = $templateService->saveOfferParameter($offer, $parameterTemplate, $parameterValue);
                                            } else {
                                                continue;
                                            }

                                        }
                                    }
                                }
                                $parameters[] = $parameter;
                            }

                            if($offer = $offerService->saveOfferFromArray($values)) {
                                $offer->setCode($offerService->createOfferCode($offer));
                                $offer->save();
                                $offerService->bindOfferParameters($offer, $parameters);
                            }

                            $this->_service->get('doctrine')->getCurrentConnection()->commit();


                            $this->_helper->redirector->gotoRoute(array('action' => 'offers'), 'domain-user-profile');

                        } catch(Exception $e) {
                            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                            $this->_service->get('log')->log($e->getMessage(), 4);
                        }
                    }
                    break;
                    
            }
            
            if($form->getSubForm('offer')->isErrors() || $form->getSubForm('parameters')->isErrors()) {
                if($prev = (int) $this->getRequest()->getPost('prev')) {
                    $step = $prev + 1;
                } elseif($next = (int) $this->getRequest()->getPost('next')) {
                    $step = $next - 1;
                }
            }
        }
        

        $form->getSubForm('offer')->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getSubForm('offer')->setTranslator($translator);
        $form->getSubForm('offer')->removeElement('submit');
        $form->getSubForm('offer')->getElement('category')->setAttribs(array('title' => $translator->translate('Offer category'), 'class' => 'span10'));
        $form->getSubForm('offer')->getElement('title')->setAttribs(array('title' => $translator->translate('Offer title'), 'class' => 'span10'));
        $form->getSubForm('offer')->getElement('content')->setAttribs(array('title' => $translator->translate('Content'), 'class' => 'span10'));
        $form->getSubForm('offer')->getElement('category')->setMultiOptions($categoryService->getCategorySelectOptions());
        
        $this->view->assign('form', $form);
        $this->view->assign('step', $step);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
        
        
        
        
        
        
//        $offerService = $this->_service->getService('Offer_Service_Offer');
//        $categoryService = $this->_service->getService('Offer_Service_Category');
//        $templateService = $this->_service->getService('Offer_Service_Template');
//
//        $translator = $this->_service->get('translate');
//        
//        $step = 1;
//        if($this->getRequest()->getPost('prev')) {
//            $step = $prev = (int) $this->getRequest()->getPost('prev');
//        } elseif($this->getRequest()->getParam('next')) {
//            $step = $next = (int) $this->getRequest()->getPost('next');
//        }
//        
//        if(!$offer = $offerService->getAgentOffer((int) $this->getRequest()->getParam('id'))) {
//            throw new Zend_Controller_Action_Exception('Offer not found');
//        }
//
//        if($offerTemplate = $offer->get('OfferTemplate')) {
//            $parameterTemplates = $templateService->getOfferTemplateParameterTemplates($offerTemplate);
//        }
//
//        $form = $offerService->getOfferForm($offer, null, $parameterTemplates);
//        $form->setTranslator($translator);
//        
//        $form->getElement('category')->setAttribs(array('title' => $translator->translate('Offer category'), 'class' => 'span10'));
//        $form->getElement('title')->setAttribs(array('title' => $translator->translate('Offer title'), 'class' => 'span10'));
//        $form->getElement('content')->setAttribs(array('title' => $translator->translate('Content'), 'class' => 'span10'));
//        
//        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
//        $form->getElement('category')->setMultiOptions($categoryService->getCategorySelectOptions());
//        
//        
//        
//        $this->view->assign('form', $form);
//        $this->view->assign('step', $step);
//
//        $this->_helper->actionStack('layout', 'index', 'default');
//        $this->_helper->actionStack('layout');
        
    }
    
    public function offersShowAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $dealService = $this->_service->getService('Offer_Service_Deal');
        
        if(!$offer = $offerService->getAgentOffer((int) $this->getRequest()->getParam('offer-id'))) {
            throw new Zend_Controller_Action_Exception('Notice not found');
        }

        if($deal = $dealService->getDeal((int) $this->getRequest()->getParam('deal-id'))) {
            
            $query = $dealService->getDealMessagePaginationQuery($deal->getId());

            $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
            $paginator = new Zend_Paginator($adapter);
            $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
            $paginator->setItemCountPerPage(self::$dealMessageItemCountPerPage);

            $this->view->assign('paginator', $paginator);
            
            $notice = $offerService->getClientNotice($deal->getNoticeId());
            
            $this->view->assign('notice', $notice);
        } else {
            $query = $dealService->getDealPaginationQuery($offer->getId());

            $dealAdapter = new MF_Paginator_Adapter_Doctrine($query);
            $dealPaginator = new Zend_Paginator($dealAdapter);
            $dealPaginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
            $dealPaginator->setItemCountPerPage(self::$dealMessageItemCountPerPage);

            $this->view->assign('dealPaginator', $dealPaginator);
        
        }
        
        $this->view->assign('offer', $offer);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function noticesReplyMessageAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $dealService = $this->_service->getService('Offer_Service_Deal');
        
        if(!$notice = $offerService->getClientNotice((int) $this->getRequest()->getParam('notice-id'))) {
            throw new Zend_Controller_Action_Exception('Notice not found');
        }
        
        if(!$deal = $dealService->getDeal((int) $this->getRequest()->getParam('deal-id'))) {
            throw new Zend_Controller_Action_Exception('Deal not found');
        }
        
        if(!$message = $dealService->getDealMessage((int) $this->getRequest()->getParam('message-id'))) {
            throw new Zend_Controller_Action_Exception('Message not found');
        }
        
        $offer = $deal->get('Offer');
        
        $form = $dealService->getDealMessageForm($message);
        $form->setAction($this->_helper->url->url(array('action' => 'notices-reply-message', 'notice-id' => $notice->getId(), 'deal-id' => $deal->getId()), 'domain-user-profile'));
        $form->setMethod(Zend_Form::METHOD_POST);
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('subject')->setAttribs(array('class' => 'span10'));
        $form->getElement('content')->setAttribs(array('rows' => 10, 'class' => 'span10'));
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $dealMessage = $dealService->createMessage($deal, Offer_Model_Doctrine_DealMessage::RECIPIENT_AGENT, $values['subject'], $values['content']);
                    $dealService->sendDealMessageAgentEmail($deal, $dealMessage);
                    $dealService->sendDealMessageClientEmail($deal, $dealMessage);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoRoute(array('action' => 'notices-show', 'notice-id' => $notice->getId(), 'deal-id' => $deal->getId()), 'domain-user-profile');
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('notice', $notice);
        $this->view->assign('form', $form);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function noticesDealBatchProcessAction() {
        $dealService = $this->_service->getService('Offer_Service_Deal');
        
        if($this->getRequest()->isPost()) {
            if($this->getRequest()->getPost('deal') && count($this->getRequest()->getPost('deal'))) {
                foreach($this->getRequest()->getPost('deal') as $dealId) {
                    if($deal = $dealService->getDeal((int) $dealId)) {
                        switch($this->getRequest()->getPost('submit')) {
                            case 'reply':
                                $dealService->sendCard($deal, Offer_Model_Doctrine_DealMessage::RECIPIENT_AGENT);
                                $deal->setContactRevealed(true);
                                $deal->save();
                                break;
                            case 'observe':
                                $deal->setStatus(MF_Code::STATUS_OBSERVED);
                                $deal->save();

                                $dealService->sendStatusUpdateNotification($deal, MF_Code::STATUS_OBSERVED);
                                break;
                            case 'remove':
                                $deal->setStatus(MF_Code::STATUS_REJECTED);
                                $deal->save();
                                break;
                        }
                    }
                }
            }
        }
        
        $this->_helper->redirector->gotoRoute(array('action' => 'notices-show', 'notices-id' => $this->getRequest()->getQuery('id')), 'domain-user-profile');
    }
    
    public function offersReplyMessageAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $dealService = $this->_service->getService('Offer_Service_Deal');
        
        if(!$offer = $offerService->getAgentOffer((int) $this->getRequest()->getParam('offer-id'))) {
            throw new Zend_Controller_Action_Exception('Offer not found');
        }
        
        if(!$deal = $dealService->getDeal((int) $this->getRequest()->getParam('deal-id'))) {
            throw new Zend_Controller_Action_Exception('Deal not found');
        }
        
        $notice = $deal->get('Notice');
        
        $form = $dealService->getDealMessageForm();
        $form->setAction($this->_helper->url->url(array('action' => 'offers-reply-message', 'offer-id' => $offer->getId(), 'deal-id' => $deal->getId()), 'domain-user-profile'));
        $form->setMethod(Zend_Form::METHOD_POST);
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $dealMessage = $dealService->createMessage($deal, Offer_Model_Doctrine_DealMessage::RECIPIENT_CLIENT, $values['subject'], $values['content']);
                    $dealService->sendDealMessageAgentEmail($deal, $dealMessage);
                    $dealService->sendDealMessageClientEmail($deal, $dealMessage);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoRoute(array('action' => 'offers-show', 'offer-id' => $offer->getId(), 'deal-id' => $deal->getId()), 'domain-user-profile');
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function noticePreviewAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        
        if(!$notice = $offerService->getClientNotice((int) $this->getRequest()->getParam('notice-id'))) {
            throw new Zend_Controller_Action_Exception('Notice not found');
        }

        $this->view->assign('notice', $notice);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function offerPrepareAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        
        $user = $this->_helper->user();
        
        if(!$notice = $offerService->getClientNotice((int) $this->getRequest()->getParam('notice-id'))) {
            throw new Zend_Controller_Action_Exception('Notice not found');
        }
        
        $offers = $offerService->getAgentOffers($user->getId());
        
        $this->view->assign('notice', $notice);
        $this->view->assign('offers', $offers);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function offerSendAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $dealService = $this->_service->getService('Offer_Service_Deal');
        
        if(!$notice = $offerService->getClientNotice((int) $this->getRequest()->getParam('notice-id'))) {
            throw new Zend_Controller_Action_Exception('Notice not found');
        }
        
        $step = 1;
        if($this->getRequest()->getPost('prev')) {
            $step = $prev = (int) $this->getRequest()->getPost('prev');
        } elseif($this->getRequest()->getParam('next')) {
            $step = $next = (int) $this->getRequest()->getPost('next');
        }

        if(!$offer = $offerService->getAgentOffer((int) $this->getRequest()->getParam('offer-id'))) {
            throw new Zend_Controller_Action_Exception('Offer not found');
        }
        
        $form = $offerService->getOfferForm($offer);
        $form->setAction($this->_helper->url->url(array('action' => 'offer-send', 'notice-id' => $notice->getId())));
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        $form->removeElement('category');
        $form->removeElement('title');
        $form->getElement('content')->setLabel('Remark');
        $form->getElement('content')->setAttribs(array('cols' => 120, 'class' => 'span12 tinymce uniform'));
        $form->getElement('submit')->setLabel('Send');
        $form->addElement('hidden', 'category', array(
            'decorators' => array('ViewHelper'),
            'value' => $notice->getCategoryId()
        ));
            
        if($this->getRequest()->isPost()) {
            if($form->isValidPartial($this->getRequest()->getPost())) {
                try {            
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();   

                    $values = $form->getValues();

                    $offer->setContent($values['content']);

                    if(!$dealService->dealExists($offer, $notice)) {
                        $deal = $dealService->createDeal($offer, $notice);
                        $title = $dealService->createMessageTitle($offer);
                        $content = $dealService->createMessageContent($offer);

                        $message = $dealService->createMessage($deal, Offer_Model_Doctrine_DealMessage::RECIPIENT_CLIENT, $title, $content);
                        $dealService->sendDealMessageAgentEmail($deal, $message);
                        $dealService->sendDealMessageClientEmail($deal, $message);
                    }
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();

                    $this->_helper->redirector->gotoRoute(array('action' => 'offers'), 'domain-user-profile', true);
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
            
        $this->view->assign('notice', $notice);
        $this->view->assign('offer', $offer);
        $this->view->assign('form', $form);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function articlesAction() {
        $articleService = $this->_service->getService('Article_Service_Article');
        
        $user = $this->_helper->user();
        
        $query = $articleService->getUserArticlesQuery($user->getId());
        
        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(self::$articleItemCountPerPage);

        $this->view->assign('paginator', $paginator);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function articlesNewAction() {
        $articleService = $this->_service->getService('Article_Service_Article');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $user = $this->_helper->user();
        
        $form = $articleService->getArticleForm();
        $form->setAction($this->_helper->url->url(array('action' => 'articles-new'), 'domain-user-profile'));
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        $form->getElement('title')->setAttribs(array('class' => 'span10'));
        $form->getElement('content')->setDecorators(User_BootstrapForm::$bootstrapTinymceDecorators)->setAttribs(array('class' => 'span11 tinymce', 'rows' => 10));
        
        $fileForm = new Media_Form_Upload();
        $fileForm->setDecorators(array('FormElements'));
        $fileForm->removeElement('submit');
        $fileForm->getElement('file')->setValueDisabled(true);
        $form->addSubForm($fileForm, 'file');
        $form->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $values['user_id'] = $user->getId();
                    $values['status'] = MF_Code::STATUS_NEW;
                    $article = $articleService->saveArticleFromArray($values);
                    
                    if(null != $form->getSubForm('file')->getValue('file')) {
                        $photo = $photoService->createPhotoFromUpload($form->getSubForm('file')->getElement('file')->getName(), $form->getValue('file'), null, array_keys(Article_Model_Doctrine_Article::getArticlePhotoDimensions()));
                        $photoService->removePhoto($article->get('PhotoRoot'));
                        $article->set('PhotoRoot', $photo);
                        $article->save();
                    }
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoRoute(array('action' => 'articles'), 'domain-user-profile');
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function articlesEditAction() {
        $articleService = $this->_service->getService('Article_Service_Article');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $user = $this->_helper->user();
        
        if(!$article = $articleService->getArticle((int) $this->getRequest()->getParam('article-id'))) {
            throw new Zend_Controller_Action_Exception('Article not found');
        }
        
        $form = $articleService->getArticleForm($article);
        $form->setAction($this->_helper->url->url(array('action' => 'articles-edit'), 'domain-user-profile'));
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        $form->getElement('title')->setAttribs(array('class' => 'span10'));
        $form->getElement('content')->setDecorators(User_BootstrapForm::$bootstrapTinymceDecorators)->setAttribs(array('class' => 'span11 tinymce', 'rows' => 10));
        
        $fileForm = new Media_Form_Upload();
        $fileForm->setDecorators(array('FormElements'));
        $fileForm->removeElement('submit');
        $fileForm->getElement('file')->setValueDisabled(true);
        $form->addSubForm($fileForm, 'file');
        $form->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $values = $form->getValues();
                    $values['user_id'] = $user->getId();
                    $article = $articleService->saveArticleFromArray($values);
                    
                    if(null != $form->getSubForm('file')->getValue('file')) {
                        $photo = $photoService->createPhotoFromUpload($form->getSubForm('file')->getElement('file')->getName(), $form->getValue('file'), null, array_keys(Article_Model_Doctrine_Article::getArticlePhotoDimensions()));
                        $photoService->removePhoto($article->get('PhotoRoot'));
                        $article->set('PhotoRoot', $photo);
                        $article->save();
                    }
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoRoute(array('action' => 'articles'), 'domain-user-profile');
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $this->view->assign('article', $article);
        $this->view->assign('form', $form);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function articlesShowAction() {
        $articleService = $this->_service->getService('Article_Service_Article');
        
        if(!$article = $articleService->getArticle((int) $this->getRequest()->getParam('article-id'))) {
            throw new Zend_Controller_Action_Exception('Article not found');
        }
        
        $this->view->assign('article', $article);
    }
    
    public function articlesRemoveAction() {
        $articleService = $this->_service->getService('Article_Service_Article');
        
        if(!$article = $articleService->getArticle((int) $this->getRequest()->getParam('article-id'))) {
            throw new Zend_Controller_Action_Exception('Article not found');
        }
        
        try {
            $articleService->removeArticle($article);
        } catch(Exception $e) {
            $this->_service->get('log')->log($e->getMessage(), 4);
        }
    }
    
    public function bannersAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $user = $this->_helper->user();
        
        $query = $bannerService->getUserBannerQuery($user->getId());
                
        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(self::$bannerItemCountPerPage);

        $this->view->assign('paginator', $paginator);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function bannersNewAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $user = $this->_helper->user();
        
        $form = $bannerService->getBannerForm();
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        
        $fileForm = new Media_Form_Upload();
        $fileForm->setDecorators(array('FormElements'));
        $fileForm->removeElement('submit');
        $fileForm->getElement('file')->setValueDisabled(true);
        $fileForm->getElement('file')->addValidator('ImageSize', false, array('minwidth' => 280, 'maxwidth' => 280, 'minheight' => 100, 'maxheight' => 100));
        $fileForm->getElement('file')->addValidator('Extension', false, array('gif', 'png', 'jpg', 'jpeg'));
                
        $form->addSubForm($fileForm, 'file');
        $form->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $values['user_id'] = $user->getId();
                    $values['positions'] = 'side';
                    
                    $banner = $bannerService->saveBannerFromArray($values);
                    
                    if(null != $form->getSubForm('file')->getValue('file')) {
                        if($photo = $photoService->createPhotoFromUpload($form->getSubForm('file')->getElement('file')->getName(), $form->getSubForm('file')->getValue('file'), null, array_keys(Banner_Model_Doctrine_Banner::getBannerPhotoDimensions()))) {
                            $photoService->removePhoto($banner->get('PhotoRoot'));
                            $banner->set('PhotoRoot', $photo);
                            $banner->setName('Banner ' . $photo->getTitle());
                            $banner->save();
                        }
                    }
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoRoute(array('action' => 'banners'), 'domain-user-profile', true);
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function bannersEditAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $translator = $this->_service->get('translate');
        
        $user = $this->_helper->user();
        
        if(!$banner = $bannerService->getBanner($this->getRequest()->getParam('banner-id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        $form = $bannerService->getBannerForm($banner);
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        
        $fileForm = new Media_Form_Upload();
        $fileForm->setDecorators(array('FormElements'));
        $fileForm->removeElement('submit');
        $fileForm->getElement('file')->setValueDisabled(true);
        $fileForm->getElement('file')->setRequired(false);
        $fileForm->getElement('file')->addValidator('ImageSize', false, array('minwidth' => 280, 'maxwidth' => 280, 'minheight' => 100, 'maxheight' => 100));
        $fileForm->getElement('file')->addValidator('Extension', false, array('gif', 'png', 'jpg', 'jpeg'));
        $form->addSubForm($fileForm, 'file');
        $form->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $values['user_id'] = $user->getId();
                    $values['positions'] = 'side';
                    
                    $banner = $bannerService->saveBannerFromArray($values);
                            
                    if(null != $form->getSubForm('file')->getValue('file')) {
                        if($photo = $photoService->createPhotoFromUpload($form->getSubForm('file')->getElement('file')->getName(), $form->getSubForm('file')->getValue('file'), null, array_keys(Banner_Model_Doctrine_Banner::getBannerPhotoDimensions()))) {
                            $photoService->removePhoto($banner->get('PhotoRoot'));
                            $banner->set('PhotoRoot', $photo);
                            $banner->setName('Banner ' . $photo->getTitle());
                            $banner->save();
                        }
                    }
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoRoute(array('action' => 'banners'), 'domain-user-profile', true);
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('banner', $banner);
        $this->view->assign('form', $form);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function bannersShowAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $user = $this->_helper->user();
        
        if(!$banner = $bannerService->getUserFullBanner((int) $this->getRequest()->getParam('banner-id'), $user->getId())) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        $this->view->assign('banner', $banner);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function bannersRemoveAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$banner = $bannerService->getBanner((int) $this->getRequest()->getParam('banner-id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
            
            $photoService->removePhoto($banner->get('PhotoRoot'));
            $bannerService->removeBanner($banner);
            
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage(), 4);
        }
        
        $this->_helper->redirector->gotoRoute(array('action' => 'banners'), 'domain-user-profile', true);
    }
    
    // client actions
    
    public function noticeSearchAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');

        $user = $this->_helper->user();
        $profile = $user->get('Profile');
        
        $searchForm = $offerService->getNoticeSearchForm();
        $searchForm->setAction($this->_helper->url->url(array(), 'domain-user-profile'), true);
        $searchForm->setMethod(Zend_Form::METHOD_GET);
        $searchForm->getElement('term')->setAttrib('class', 'span10');
        
        $this->view->assign('searchForm', $searchForm);
        
        $values = array();
        if($this->getRequest()->getParam('submit')) {
            if($searchForm->isValid($this->getRequest()->getParams())) {
                $values = $searchForm->getValues();
                $values['province_id'] = $profile['province_id'];
            }
        }
        
        $query = $offerService->getNoticeSearchResultQuery($values);

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(self::$searchResultItemCountPerPage);

        $this->view->assign('paginator', $paginator);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function noticesAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        
        $query = $offerService->getClientNoticesPaginationQuery($this->user->getId());

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(self::$offerItemCountPerPage);
        
        $this->view->assign('paginator', $paginator);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }

    public function noticesNewAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $categoryService = $this->_service->getService('Offer_Service_Category');
        $templateService = $this->_service->getService('Offer_Service_Template');

        $translator = $this->_service->get('translate');
        
        $user = $this->_helper->user();
        
        $form = new Zend_Form();
        $form->setAction($this->_helper->url->url(array('action' => 'notices-new'), 'domain-user-profile', true));
        
        $noticeForm = $offerService->getNoticeForm();
        $noticeForm->setTranslator($translator);

        $noticeForm->getElement('province')->setMultiOptions($offerService->getProvinceSelectOptions())->setAttribs(array('title' => $translator->translate('Province'), 'class' => 'span10'));
        $noticeForm->getElement('city')->setMultiOptions($offerService->getCitySelectOptions())->setAttribs(array('title' => $translator->translate('City'), 'class' => 'span10'));
        $noticeForm->getElement('category')->setAttribs(array('title' => $translator->translate('Notice category'), 'class' => 'span10'));
        $noticeForm->getElement('title')->setAttribs(array('title' => $translator->translate('Notice title'), 'class' => 'span10'));
        $noticeForm->getElement('content')->setAttribs(array('title' => $translator->translate('Content'), 'class' => 'span10'));
        $noticeForm->getElement('publish_date')->setAttribs(array('class' => 'datepicker span10'));
        
        $noticeForm->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $noticeForm->getElement('category')->setMultiOptions($categoryService->getCategorySelectOptions());
        $noticeForm->removeElement('submit');

        $form->addSubForm($noticeForm, 'notice');
        
        $parametersform = $offerService->getNoticeParameterSubForm();
        $form->addSubForm($parametersform, 'parameters');
        
        $step = 1;
        if($this->getRequest()->getPost('prev')) {
            $step = $prev = (int) $this->getRequest()->getPost('prev');
        } elseif($this->getRequest()->getParam('next')) {
            $step = $next = (int) $this->getRequest()->getPost('next');
        }
        
        if($this->getRequest()->isPost()) {
            if($category = $categoryService->getCategory((int) $this->getRequest()->getPost('category'))) {
                if($noticeTemplate = $templateService->getCategoryNoticeTemplate($category)) {
                    $parameterTemplates = $templateService->getNoticeTemplateParameterTemplates($noticeTemplate);
                }
            }
          
            if($parameterTemplates) {
                $parametersform = $offerService->getNoticeParameterSubForm(null, $parameterTemplates);
                $form->addSubForm($parametersform, 'parameters');
            }
            
            $form->getSubForm('notice')->populate($this->getRequest()->getPost());
            $form->getSubForm('parameters')->populate($this->getRequest()->getPost());
            
            switch($next) {
                case 2:
                    $form->getSubForm('notice')->isValid($this->getRequest()->getPost());
                    break;
                case 3:
                    $form->getSubForm('notice')->isValid($this->getRequest()->getPost());
                    $form->getSubForm('parameters')->isValid($this->getRequest()->getPost());
                    if(!$form->getSubForm('notice')->isErrors() && !$form->getSubForm('parameters')->isErrors()) {
                        try {            

                            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();   

                            $values = $form->getSubForm('notice')->getValues();
                            $values['user_id'] = $user->getId();
                            $values['category_id'] = $category->getId();
                            $values['notice_template_id'] = $noticeTemplate->getId();

                            $parameterValues = $form->getSubForm('parameters')->getValues();
                            
                            $parameters = array();
                            foreach($parameterValues as $name => $value) {
                                $match = array();

                                if(!is_array($value)) {
                                    preg_match('/^parameter(\d+)/', $name, $match);
                                    if(isset($match[1])) {
                                        $parameterTemplate = $templateService->getParameterTemplate((int) $match[1]);
                                        $parameter = $templateService->createNoticeParameter($parameterTemplate, $value);
                                    }
                                } else {
                                    foreach($value as $rangeKey => $rangeValue) {
                                        preg_match('/^parameter(\d+)from/', $rangeKey, $match);
                                        if(isset($match[1]) && $parameterTemplate = $templateService->getParameterTemplate((int) $match[1])) {
                                            if(isset($value['parameter' . $match[1] . 'to'])) {
                                                $parameterValue = array($rangeValue);
                                                array_push($parameterValue, $value['parameter' . $match[1] . 'to']);
                                                $parameter = $templateService->createNoticeParameter($parameterTemplate, $parameterValue);
                                            } else {
                                                continue;
                                            }

                                        }
                                    }
                                }
                                $parameters[] = $parameter;

                            }

                            if($notice = $offerService->saveNoticeFromArray($values)) {
                                $offerService->bindNoticeParameters($notice, $parameters);
                            }

                            $this->_service->get('doctrine')->getCurrentConnection()->commit();


                            $this->_helper->redirector->gotoRoute(array('action' => 'notices'), 'domain-user-profile');

                        } catch(Exception $e) {
                            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                            $this->_service->get('log')->log($e->getMessage(), 4);
                        }
                    }
                    
                    break;
            }

            if($form->getSubForm('notice')->isErrors() || $form->getSubForm('parameters')->isErrors()) {
                if($prev = (int) $this->getRequest()->getPost('prev')) {
                    $step = $prev + 1;
                } elseif($next = (int) $this->getRequest()->getPost('next')) {
                    $step = $next - 1;
                }
            }
        }
        
        
        $this->view->assign('form', $form);
        $this->view->assign('step', $step);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function noticesEditAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $categoryService = $this->_service->getService('Offer_Service_Category');
        $templateService = $this->_service->getService('Offer_Service_Template');

        $translator = $this->_service->get('translate');
        
        $user = $this->_helper->user();
        
        if(!$notice = $offerService->getClientNotice((int) $this->getRequest()->getParam('notice-id'))) {
            throw new Zend_Controller_Action_Exception('Notice not found');
        }
        
        $form = new Zend_Form();
        $form->setAction($this->_helper->url->url(array('action' => 'notices-edit', 'notice-id' => $notice->getId()), 'domain-user-profile', true));
        
        $noticeForm = $offerService->getNoticeForm($notice);
        $noticeForm->setTranslator($translator);

        $noticeForm->getElement('province')->setMultiOptions($offerService->getProvinceSelectOptions())->setAttribs(array('title' => $translator->translate('Province'), 'class' => 'span10'));
        $noticeForm->getElement('city')->setMultiOptions($offerService->getCitySelectOptions())->setAttribs(array('title' => $translator->translate('City'), 'class' => 'span10'));
        $noticeForm->getElement('category')->setAttribs(array('title' => $translator->translate('Notice category'), 'class' => 'span10'));
        $noticeForm->getElement('title')->setAttribs(array('title' => $translator->translate('Notice title'), 'class' => 'span10'));
        $noticeForm->getElement('content')->setAttribs(array('title' => $translator->translate('Content'), 'class' => 'span10'));
        $noticeForm->getElement('publish_date')->setAttribs(array('class' => 'datepicker span10'));
        
        $noticeForm->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $noticeForm->getElement('category')->setMultiOptions($categoryService->getCategorySelectOptions());
        $noticeForm->removeElement('submit');

        $form->addSubForm($noticeForm, 'notice');
        
        $parametersform = $offerService->getParameterSubForm();
        $form->addSubForm($parametersform, 'parameters');
        
        
        $step = 1;
        if($this->getRequest()->getPost('prev')) {
            $step = $prev = (int) $this->getRequest()->getPost('prev');
        } elseif($this->getRequest()->getParam('next')) {
            $step = $next = (int) $this->getRequest()->getPost('next');
        }
        
        
        if($this->getRequest()->isPost()) {
            if($category = $categoryService->getCategory((int) $this->getRequest()->getPost('category'))) {
                if($noticeTemplate = $templateService->getCategoryNoticeTemplate($category)) {
                    $parameterTemplates = $templateService->getNoticeTemplateParameterTemplates($noticeTemplate);
                }
            }
          
            if($parameterTemplates) {
                $parametersform = $offerService->getParameterSubForm($notice, $parameterTemplates);
                $form->addSubForm($parametersform, 'parameters');
            }
            
            $form->getSubForm('notice')->populate($this->getRequest()->getPost());
            $form->getSubForm('parameters')->populate($this->getRequest()->getPost());
            
            switch($next) {
                case 2:
                    $form->getSubForm('notice')->isValid($this->getRequest()->getPost());
                    break;
                case 3:
                    $form->getSubForm('notice')->isValid($this->getRequest()->getPost());
                    $form->getSubForm('parameters')->isValid($this->getRequest()->getPost());
                    if(!$form->getSubForm('notice')->isErrors() && !$form->getSubForm('parameters')->isErrors()) {
                        try {            

                            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();   

                            $values = $form->getSubForm('notice')->getValues();
                            $values['user_id'] = $user->getId();
                            $values['category_id'] = $category->getId();
                            $values['notice_template_id'] = $noticeTemplate->getId();

                            $parameterValues = $form->getSubForm('parameters')->getValues();
                            
                            $parameters = array();
                            foreach($parameterValues as $name => $value) {
                                $match = array();

                                if(!is_array($value)) {
                                    preg_match('/^parameter(\d+)/', $name, $match);
                                    if(isset($match[1])) {
                                        $parameterTemplate = $templateService->getParameterTemplate((int) $match[1]);
                                        $parameter = $templateService->saveNoticeParameter($parameterTemplate, $value);
                                    }
                                } else {
                                    foreach($value as $rangeKey => $rangeValue) {
                                        preg_match('/^parameter(\d+)from/', $rangeKey, $match);
                                        if(isset($match[1]) && $parameterTemplate = $templateService->getParameterTemplate((int) $match[1])) {
                                            if(isset($value['parameter' . $match[1] . 'to'])) {
                                                $parameterValue = array($rangeValue);
                                                array_push($parameterValue, $value['parameter' . $match[1] . 'to']);
                                                $parameter = $templateService->saveNoticeParameter($parameterTemplate, $parameterValue);
                                            } else {
                                                continue;
                                            }

                                        }
                                    }
                                }
                                $parameters[] = $parameter;

                            }

                            if($notice = $offerService->saveNoticeFromArray($values)) {
                                $offerService->bindNoticeParameters($notice, $parameters);
                            }

                            $this->_service->get('doctrine')->getCurrentConnection()->commit();


                            $this->_helper->redirector->gotoRoute(array('action' => 'notices'), 'domain-user-profile');

                        } catch(Exception $e) {
                            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                            $this->_service->get('log')->log($e->getMessage(), 4);
                        }
                    }
                    
                    break;
            }
            
            if($form->getSubForm('notice')->isErrors() || $form->getSubForm('parameters')->isErrors()) {
                if($prev = (int) $this->getRequest()->getPost('prev')) {
                    $step = $prev + 1;
                } elseif($next = (int) $this->getRequest()->getPost('next')) {
                    $step = $next - 1;
                }
            }
            
        }
        
//        if($this->getRequest()->isPost()) {
//            if($category = $notice->get('Category')) {
//                if($noticeTemplate = $templateService->getCategoryNoticeTemplate($category)) {
//                    $parameterTemplates = $templateService->getNoticeTemplateParameterTemplates($noticeTemplate);
//                }
//            }
//
//            if($parameterTemplates) {
//                $form = $offerService->getNoticeForm($notice, $form, $parameterTemplates);
//            }
//        
//            if(1 == $prev) {
//                $form->clearSubForms();
//            }
//
//            if($form->isValidPartial($this->getRequest()->getPost())) {
//                switch($step) {
//                    case 2:
//                        break;
//                    case 3:
//                        // prepare parameter templates sub form, validation and persisting
//                        try {            
//                        
//                        $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();   
//
//                        $values = $form->getValues();
//                        $values['user_id'] = $user->getId();
//                        $values['category_id'] = $category->getId();
//                        $values['notice_template_id'] = $noticeTemplate->getId();
//
//                        foreach($values['parameters'] as $name => $value) {
//                            $match = array();
//                            
//                            if(!is_array($value)) {
//                                preg_match('/^parameter(\d+)/', $name, $match);
//                                if(isset($match[1])) {
//                                    $parameterTemplate = $templateService->getParameterTemplate((int) $match[1]);
//                                    $parameter = $templateService->saveNoticeParameter($notice, $parameterTemplate, $value);
//                                }
//                            } else {
//                                foreach($value as $rangeKey => $rangeValue) {
//                                    preg_match('/^parameter(\d+)from/', $rangeKey, $match);
//                                    if(isset($match[1]) && $parameterTemplate = $templateService->getParameterTemplate((int) $match[1])) {
//                                        if(isset($value['parameter' . $match[1] . 'to'])) {
//                                            $parameterValue = array($rangeValue);
//                                            array_push($parameterValue, $value['parameter' . $match[1] . 'to']);
//                                            $parameter = $templateService->saveNoticeParameter($notice, $parameterTemplate, $parameterValue);
//                                        } else {
//                                            continue;
//                                        }
//                                        
//                                    }
//                                }
//                            }
//                            
//                        }
//
//                        $notice = $offerService->saveNoticeFromArray($values);
//
//                        $this->_service->get('doctrine')->getCurrentConnection()->commit();
//
//
//                        $this->_helper->redirector->gotoRoute(array('action' => 'notices'), 'domain-user-profile');
//
//                        } catch(Exception $e) {
//                            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
//                            $this->_service->get('log')->log($e->getMessage(), 4);
//                        }
//
//                        break;
//                }
//                    
//            } else {
//                if($prev = (int) $this->getRequest()->getPost('prev')) {
//                    $step = $prev + 1;
//                } elseif($next = (int) $this->getRequest()->getPost('next')) {
//                    $step = $next - 1;
//                }
//            }
//        }

        $this->view->assign('form', $form);
        $this->view->assign('step', $step);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function noticesShowAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $dealService = $this->_service->getService('Offer_Service_Deal');
        
        if(!$notice = $offerService->getClientNotice((int) $this->getRequest()->getParam('notice-id'))) {
            throw new Zend_Controller_Action_Exception('Notice not found');
        }
        
        if($dealId = (int) $this->getRequest()->getParam('deal-id')) {
            
            // deal list
            $query = $dealService->getDealMessagePaginationQuery($dealId);

            $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
            $messagePaginator = new Zend_Paginator($adapter);
            $messagePaginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
            $messagePaginator->setItemCountPerPage(self::$dealMessageItemCountPerPage);
            
            $deal = $dealService->getDeal($dealId);
            $offer = $deal->get('Offer');
            $this->view->assign('offer', $offer);
            
            $this->view->assign('messagePaginator', $messagePaginator);
        } else {
            // deal message list
            $query = $dealService->getDealPaginationQuery(null, $notice->getId());

            $dealAdapter = new MF_Paginator_Adapter_Doctrine($query);
            $dealPaginator = new Zend_Paginator($dealAdapter);
            $dealPaginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
            $dealPaginator->setItemCountPerPage(self::$dealMessageItemCountPerPage);

            $this->view->assign('dealPaginator', $dealPaginator);
        }
        
        $this->view->assign('notice', $notice);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function settingsAction() {
        $user = $this->_helper->user();
        $profile = $user->get('Profile');
        
        $form = new User_Form_UserProfile();
        $form->populate($user->toArray());
//        $form->removeElement('role');
//        $form->removeElement('active');
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        $form->setDecorators(array('FormElements'));
        
        $session = new Zend_Session_Namespace('REGISTER_CSRF');
        $form->getElement('csrf')->setSession($session)->initCsrfValidator();
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try{
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

//                    $values = $form->getValues();
//                    $userService->saveUserFromArray($values);
                            
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoRoute(array('action' => 'settings'), 'domain-user-profile', true);
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('profile', $profile);
        $this->view->assign('form', $form);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->_helper->actionStack('layout');
    }
    
    public function profileMenuAction() {
        $this->_helper->viewRenderer->setResponseSegment('profile_menu');
    }
    
    public function layoutAction() {
//        $this->_helper->layout->setLayout('profile');
//        $this->_helper->actionStack('login-panel', 'index', 'default');
        
        $this->_helper->actionStack('profile-menu', 'profile', 'user');
        
        $this->_helper->viewRenderer->setNoRender();
    }
    
    // ajax requests
    
//    public function uploadArticlePhotoAction() {
//        $photoService = $this->_service->getService('Media_Service_Photo');
//        
//        $user = $this->_helper->user();
//
//        if(!$article = $articleService->getArticle((int) $this->getRequest()->getParam('id'))) {
//            throw new Zend_Controller_Action_Exception('Article not found');
//        }
//        
//        $photoForm = new Media_Form_Upload();
//        $photoForm->getElement('file')->setValueDisabled(true); // disable upload file when getValue
//        
//        $this->view->clearVars();
//        
//        if($this->getRequest()->isPost()) {
//            if($photoForm->isValid($this->getRequest()->getPost())) {
//                try {
//                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
//
//                    $photo = $photoService->createPhotoFromUpload($photoForm->getElement('file')->getName(), $photoForm->getValue('file'), null, array_keys(User_Model_Doctrine_Profile::getProfilePhotoDimensions()));
//                    
//                    if($photo) {
//                        $photoService->removePhoto($profile->get('PhotoRoot'));
//                        $profile->set('PhotoRoot', $photo);
//                        $profile->save();
//                        $this->view->assign('root', $photo->getId());
//                    }
//                    
//                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
//                } catch(Exception $e) {
//                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
//                    $this->_service->get('log')->log($e->getMessage(), 4);
//                }
//            }
//        }
//    }
    
    public function uploadProfilePhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $user = $this->_helper->user();
        $profile = $user->get('Profile');
        
        $photoForm = new Media_Form_Upload();
        $photoForm->getElement('file')->setValueDisabled(true); // disable upload file when getValue
        
        $this->view->clearVars();
        
        if($this->getRequest()->isPost()) {
            if($photoForm->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $photo = $photoService->createPhotoFromUpload($photoForm->getElement('file')->getName(), $photoForm->getValue('file'), null, array_keys(User_Model_Doctrine_Profile::getProfilePhotoDimensions()));
                    
                    if($photo) {
                        $photoService->removePhoto($profile->get('PhotoRoot'));
                        $profile->set('PhotoRoot', $photo);
                        $profile->save();
                        $this->view->assign('root', $photo->getId());
                    }
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
    }
    
    public function deletePhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $user = $this->_helper->user();
        $profile = $user->get('Profile');
        
        $this->view->clearVars();
        
        if($photo = $profile->get('PhotoRoot')) {
            $photoService->removePhoto($photo);
        }
        
        $this->view->assign('photos', array());
        
        $this->_helper->viewRenderer('load-photo-list');
        
        $this->_helper->layout->disableLayout();
    }
    
    public function loadPhotoListAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$photo = $photoService->getPhoto($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo not found');
        }
        
        $photos = ($photo->getNode()->getChildren()) ? $photoService->getChildrenPhotos($photo) : array($photo);
        $this->view->assign('photos', $photos);
        
        $this->_helper->layout->disableLayout();
    }
    
    
}

