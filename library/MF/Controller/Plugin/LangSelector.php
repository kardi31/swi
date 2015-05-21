<?php
/**
 * Class handles lang parameter changes.
 *
 * @author Michał Folga <michalfolga@gmail.com>
 *
 */
class MF_Controller_Plugin_LangSelector extends Zend_Controller_Plugin_Abstract
{
    public static $defaultLanguage = 'pl';
    
	public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{
            $locale = Zend_Registry::get('Zend_Locale');
            $translate = Zend_Registry::get('Zend_Translate');
            $session = new Zend_Session_Namespace('locale');

            if($request->getModuleName() == 'admin') {
                $language = self::$defaultLanguage;;
            } elseif($lang = $request->getParam('lang')) {
                $language = $lang;
                if($lang != $session->lang) {
                    $session->lang = $language;
                }
            } elseif(isset($session->lang) && in_array($session->lang, $translate->getList())) {
                $language = $session->lang;
            } else {
                $language = self::$defaultLanguage;
            }

            $languageNotKnown = false;
            if(!in_array($language, $translate->getList())) {
                $language = $locale->getLanguage();
                $languageNotKnown = true;
            }

            if(array_key_exists($language, Zend_Locale::getLocaleList())) {
                $front = Zend_Controller_Front::getInstance();
                $view = $front->getParam('bootstrap')->getResource('view');
                $view->assign('language', $language);
                
                $container = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getContainer();
                $locale->setLocale($language);
                $container->offsetSet('Zend_Locale', $locale);
                //Zend_Registry::set('Zend_Locale', $locale);

                $translate->setLocale($language);
                $container->offsetSet('Zend_Translate', $translate);
                //Zend_Registry::set('Zend_Translate', $translate);

                $routerTranslator = Zend_Controller_Router_Route::getDefaultTranslator();
                $routerTranslator->setLocale($locale);
                
                Zend_Form::setDefaultTranslator($translate);
                Zend_Controller_Front::getInstance()->getRouter()->setGlobalParam('lang', $language);

            }

            if($languageNotKnown) {
                $this->_redirect404($request);
            }
	}
    
    protected function _redirect404($request) {
        $request->setModuleName('default');
        $request->setControllerName('error');
        $request->setActionName('error404');
    }



}