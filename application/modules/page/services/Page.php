<?php

/**
 * Page
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Page_Service_Page extends MF_Service_ServiceAbstract {
    
    protected $pageTable;
    
    public function init() {
        $this->pageTable = Doctrine_Core::getTable('Page_Model_Doctrine_Page');
    }
    
    public function getPage($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->pageTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function fetchPage($type, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $serviceBroker = $this->getServiceBroker();
        $translator = $serviceBroker->get('translate');
        
        $pageTypes = Page_Model_Doctrine_Page::getAvailableTypes();
        
        if(!$page = $this->getPage($type, 'type', $hydrationMode)) {
            $page = $this->pageTable->getRecord();
            $page->Translation[$language]->title = $translator->translate($pageTypes[$type], $language);
            $page->Translation[$language]->slug = MF_Text::createSlug($page->Translation[$language]->title);
            $page->setType($type);
            $page->save();
        }
        return $page;
    }
    
    public function getI18nPage($id, $field = 'id', $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->pageTable->getFullPageQuery();
        switch($field) {
            case 'slug':
            case 'title':
                $q->andWhere('t.' . $field . ' = ?', $id);
                break;
            default:
                $q->andWhere('p.' . $field . ' = ?', $id);
        }
        $q->andWhere('(t.lang = ? AND (mt.lang = ? OR mt.lang IS NULL))', array($language, $language));
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getAllPages() {
        return $this->pageTable->findAll();
    }
    
    public function getPageSelectOptions($language, $prependEmptyValue = false, $idPrefix = '') {
        $pages = $this->getAllPages();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = null;
        }
        foreach($pages as $page) {
            $result[$idPrefix . $page->getId()] = $page->get('Translation')->get($language)->title;
        }
        return $result;
    }
    
    public function getPageForm(Page_Model_Doctrine_Page $page = null) {
        $form = new Page_Form_Page();
        if(null !== $page) {
            $form->populate($page->toArray());
        }
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            $i18nSubform = $form->translations->getSubForm($language);
            if($i18nSubform) {
                $i18nSubform->getElement('title')->setValue($page->Translation[$language]->title);
                $i18nSubform->getElement('content')->setValue($page->Translation[$language]->content);
            }
        }
        return $form;
    }
    
    public function savePageFromArray(array $values) {
        $serviceBroker = $this->getServiceBroker();
        $translator = $serviceBroker->get('translate');
        
        $types = Page_Model_Doctrine_Page::getAvailableTypes();

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
                
        if($values['type']) {
            if(!$page = $this->getPage($values['type'], 'type')) {
                if(!$page = $this->pageTable->getProxy($values['id'])) {
                    $page = $this->pageTable->getRecord();
                }
            }
        } else {
            if(!$page = $this->pageTable->getProxy($values['id'])) {
                $page = $this->pageTable->getRecord();
            }
        }
        
        $page->fromArray($values);
        foreach($values['translations'] as $language => $translation) {
           // echo $language;
//            if($values['type']) {
//                $page->Translation[$language]->title = $translator->translate($types[$values['type']]);
//            } else {
//                $page->Translation[$language]->title = $translation['title'];
//            }
            $page->Translation[$language]->title = $translation['title'];
            $page->Translation[$language]->slug = MF_Text::createSlug($values['translations'][$language]['title']);
            $page->Translation[$language]->content = $translation['content'];
        }
//        echo $page->Translation['pl']->slug;
       // echo $page->Translation['en']->title;
     //   exit;
        $page->save();
        return $page;
    }
    
    public function removePage(Page_Model_Doctrine_Page $page) {
        $page->get('Translation')->delete();
        $page->delete();
    }
    
}

