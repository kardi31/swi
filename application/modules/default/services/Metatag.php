<?php

/**
 * Metatag
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Default_Service_Metatag extends MF_Service_ServiceAbstract {
    
    protected $metatagTable;
    
    public function init() {
        $this->metatagTable = Doctrine_Core::getTable('Default_Model_Doctrine_Metatag');
    }
    
    public function getMetatags($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->metatagTable->getMetatagsQuery();
        $q->andWhere('m.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
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
    
    public function saveMetatagsFromArray(Default_Model_Doctrine_Metatag $metatag = null, $values, $fallback = array()) {
        foreach($values['metatags'] as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values['metatags'][$key] = NULL;
            }
        }
        if(null == $metatag) {
            $metatag = $this->metatagTable->getRecord();
        }
//        Zend_Debug::dump($values['metatags']);exit;
        $metatag->fromArray($values['metatags']);
        if(isset($values['metatags']['translations'])) {
            foreach($values['metatags']['translations'] as $language => $translation) {
                $fallbackData = isset($values['translations'][$language]) ? $values['translations'][$language] : $values;
                $data = $translation;
                if(!isset($data['meta_title']) || !strlen($data['meta_title'])) {
                    if(isset($fallback['title']) && strlen($fallbackData[$fallback['title']])) {
                        $data['meta_title'] = $fallbackData[$fallback['title']];
                    }
                }
                
                if(!isset($data['meta_description']) || !strlen($data['meta_description'])) {
                    if(isset($fallback['description']) && strlen($fallbackData[$fallback['description']])) {
                        $data['meta_description'] = $fallbackData[$fallback['description']];
                    }
                }
                $data['meta_description'] = strip_tags($data['meta_description']);

                $keywordsString = '';
                if(isset($data['meta_keywords']) && strlen($data['meta_keywords'])) {
                    $keywordsString = strip_tags($data['meta_keywords']);
                } elseif(isset($fallback['keywords']) && strlen($fallbackData[$fallback['keywords']])) {
                    $keywordsString = strip_tags($fallbackData[$fallback['keywords']]);
                } else {
                    $keywordsString = $data['meta_description'];
                }

                $keywords = MF_SEO::retrieveKeywords($keywordsString);

                $data['meta_keywords'] = implode(', ', $keywords);
                if(strlen($metatag['Translation'][$language]['title']) == 0 || strlen($translation['meta_title']) == 0 || $metatag['Translation'][$language]['title'] != $translation['meta_title'])
                    $metatag['Translation'][$language]['title'] = $data['meta_title'];
                if(strlen($metatag['Translation'][$language]['description']) == 0 || strlen($translation['meta_description']) == 0 || $metatag['Translation'][$language]['description'] != $translation['meta_description'])
                    $metatag['Translation'][$language]['description'] = $data['meta_description'];
                if(strlen($metatag['Translation'][$language]['keywords']) == 0 || strlen($translation['meta_keywords']) == 0 || $metatag['Translation'][$language]['keywords'] != $translation['meta_keywords'])
                    $metatag['Translation'][$language]['keywords'] = $data['meta_keywords'];
            }
        } elseif(isset($values['metatags'])) {
            $fallbackData = $values;
            $data = $values['metatags'];
            
            if(!isset($data['meta_title']) || !strlen($data['meta_title'])) {
                if(isset($fallback['title']) && strlen($fallbackData[$fallback['title']])) {
                    $data['meta_title'] = $fallbackData[$fallback['title']];
                }
            }

            if(!isset($data['meta_description']) || !strlen($data['meta_description'])) {
                if(isset($fallback['description']) && strlen($fallbackData[$fallback['description']])) {
                    $data['meta_description'] = $fallbackData[$fallback['description']];
                }
            }
            $data['meta_description'] = strip_tags($data['meta_description']);
           
            $keywordsString = '';
            if(isset($data['meta_keywords']) && strlen($data['meta_keywords'])) {
                $keywordsString = strip_tags($data['meta_keywords']);
            } elseif(isset($fallback['keywords']) && strlen($fallbackData[$fallback['keywords']])) {
                $keywordsString = strip_tags($fallbackData[$fallback['keywords']]);
            } else {
                $keywordsString = $data['meta_description'];
            }

            $keywords = MF_SEO::retrieveKeywords($keywordsString);

            $data['meta_keywords'] = implode(', ', $keywords);
            if(strlen($metatag['title']) == 0 || strlen($values['metatags']['title']) == 0)
                $metatag['title'] = $data['meta_title'];
            if(strlen($metatag['description']) == 0 || strlen($values['metatags']['description']) == 0)
                $metatag['description'] = $data['meta_description'];
            if(strlen($metatag['keywords']) == 0 || strlen($values['metatags']['keywords']) == 0)
                $metatag['keywords'] = $data['meta_keywords'];
        }
        $metatag->save();
        return $metatag;
    }
    
    public function setViewMetatags($metatags = null, $view) {
        if(null != $metatags) {
            if(is_numeric($metatags)) {
                $metatags = $this->getMetatags($metatags);
            }
            $metatags = $metatags->Translation[$view->language];
            
            if(strlen($metatags['title'])) {
                $view->headTitle($metatags['title'], 'SET');
                $view->headMeta($metatags['title'], 'DC.title');
            }
            if(strlen($metatags['description'])) {
                $view->headMeta($metatags['description'], 'description');
                $view->headMeta($metatags['description'], 'DC.description');
            }
            
            if(strlen($metatags['keywords'])) {
                $view->headMeta($metatags['keywords'], 'keywords');
                $view->headMeta($metatags['keywords'], 'DC.subject');
            }
        }
    }
    
    public function setOgMetatags($view,$title = null,$image_url = null,$description = null,$type = "article") {
            
        $view->doctype('XHTML1_RDFA');
       
        if(strlen($title)) {
            $view->headMeta($title." - UKS Jedynka Krzeszowice", 'og:title','property');
        }
        if(strlen($image_url)) {
            if(strpos($image_url,'http://')==false){
                $image_url = "http://".Zend_Controller_Front::getInstance()->getRequest()->getHttpHost().$image_url;
            }
            
            $view->headMeta($image_url, 'og:image','property');
        }

        if(strlen($description)) {
            $view->headMeta(strip_tags($description), 'og:description','property');
        }
        
        if(strlen($type)) {
            $view->headMeta($type, 'og:type','property');
        }
        
        $view->headMeta(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri(),'og:url','property');
            
    }
    
    public function getMetatag($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->metatagTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function removeMetatag($metatag) {
        if(is_integer($metatag)) {
            $metatag = $this->getMetatag($metatag);
        }
        
        if($metatag){
            $metatag['Translation']->delete();
            $metatag->delete();
        }
    }
    
    
    
}

