<?php

/**
 * Metatag
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Service_Newsletter extends MF_Service_ServiceAbstract {
    
    protected $newsletterTable;
    
    public function init() {
        $this->newsletterTable = Doctrine_Core::getTable('Default_Model_Doctrine_Newsletter');
    }
    
    public function getNewsletter($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->newsletterTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getMetatagsForm($metatag = null) {
        $serviceBroker = $this->getServiceBroker();
        $i18nService = $serviceBroker->get('Default_Service_I18n');
        
        $languages = $i18nService->getLanguageList();
        
        $form = new Default_Form_Metatag();
        if(null != $metatag) {
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('meta_title')->setValue($metatag->Translation[$language]->title);
                    $i18nSubform->getElement('meta_description')->setValue($metatag->Translation[$language]->description);
                    $i18nSubform->getElement('meta_keywords')->setValue($metatag->Translation[$language]->keywords);
                }
            }
            $form->populate($metatag->toArray());
        }
        
        return $form;
    }
    
    public function getMetatagsSubForm($metatag = null) {
        $form = $this->getMetatagsForm($metatag);
        $form->removeElement('id');
        $form->removeElement('submit');
        $form->setDecorators(array('FormElements'));
        $form->setIsArray(true);
        return $form;
    }
    
    public function saveNewsletterFromArray($values) {
        $newsletter = $this->newsletterTable->getRecord();
        $newsletter->fromArray($values);
        Zend_Debug::dump($values);
        $newsletter->save();
        return $newsletter;
    }
    
    public function setViewMetatags($metatags = null, $view) {
        if(null != $metatags) {
            if(is_numeric($metatags)) {
                $metatags = $this->getMetatags($metatags);
            }
            $metatags = $metatags->Translation[$view->language];
            
            if(strlen($metatags['title'])) {
                $view->headTitle($metatags['title'], 'SET');
            }
            if(strlen($metatags['description'])) {
                $view->headMeta($metatags['description'], 'description');
            }
            
            if(strlen($metatags['keywords'])) {
                $view->headMeta($metatags['keywords'], 'keywords');
            }
        }
    }
    
    public function getMetatag($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->metatagTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function removeMetatag($metatag) {
        $metatag->delete();
    }
    
}

