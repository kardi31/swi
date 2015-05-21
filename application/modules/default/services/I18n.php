<?php

/**
 * I18n
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Service_I18n extends MF_Service_ServiceAbstract {
    
    protected $languageTable;
    protected $fallbackLanguage;
    
    public function init() {
        $this->languageTable = Doctrine_Core::getTable('Default_Model_Doctrine_Language');
    }
    
    public function setFallbackLanguage(Default_Model_LanguageInterface $fallbackLanguage) {
        $this->fallbackLanguage = $fallbackLanguage;
    }
    
    public function getFallbackLanguage() {
        return $this->fallbackLanguage;
    }
    
    public function getLanguage($id) {
        return $this->languageTable->find($id);
    }
    
    public function getAllLanguages() {
        return $this->languageTable->findAll();
    }
    
    public function getLanguageList() {
        $languages = $this->languageTable->findBy('active', 1);
        if(!$languages->count() && ($fallbackLanguage = $this->getFallbackLanguage())) {
            $languages = array($fallbackLanguage);
        }
        $result = array();
        foreach($languages as $language) {
            $result[] = $language->getId();
        }
        return $result;
    }
    
    public function getDefaultLanguage() {
        $language = $this->languageTable->findOneBy('default', 1);
        if(!$language && isset($this->fallbackLanguage)) {
            $language = $this->getFallbackLanguage();
        }
        return $language;
    }
    
    public function getAdminLanguage() {
        $language = $this->languageTable->findOneBy('admin', 1);
        if(!$language && isset($this->fallbackLanguage)) {
            $language = $this->getFallbackLanguage();
        }
        return $language;
    }
    
    public function getLanguageForm(Default_Model_LanguageInterface $language = null) {
        $form = new Default_Form_Language();
        $form->setDefault('active', true);
        if(null !== $language) {
            $form->getElement('id')->setValue($language->getId());
            $form->getElement('name')->setValue($language->getName());
            $form->getElement('active')->setChecked($language->isActive());
            $form->getElement('default')->setChecked($language->isDefault());
            $form->getElement('admin')->setChecked($language->isAdmin());
        }
        return $form;
    }
    
    public function saveLanguageFromArray(array $data) {
        if(!$language = $this->languageTable->getProxy($data['id'])) {
            $language = $this->languageTable->getRecord();
        }
        
        if(!$this->getDefaultLanguage()) {
            $data['default'] = 1;
        }
        if($data['default'] == 1) {
            $languages = $this->getAllLanguages();
            foreach($languages as $lang) {
                $lang->setDefault(false);
                $lang->save();
            }
        }
        
        if(!$this->getAdminLanguage()) {
            $data['admin'] = 1;
        }
        if($data['admin'] == 1) {
            $languages = $this->getAllLanguages();
            foreach($languages as $lang) {
                $lang->setAdmin(false);
                $lang->save();
            }
        }
        
        $language->fromArray($data);
        $language->save();
        return $language;
    }
    
    public function removeLanguage(Default_Model_Doctrine_Language $language) {
        if($this->getAllLanguages()->count() <= 1) {
            throw new Default_Model_LastLanguageDeleteException("Can't delete the only language");
        }
        $language->delete();
    }
    
}

