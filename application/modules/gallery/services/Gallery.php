<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Gallery_Service_Gallery extends MF_Service_ServiceAbstract {
    
    protected $galleryTable;
    
    public function init() {
        $this->galleryTable = Doctrine_Core::getTable('Gallery_Model_Doctrine_Gallery');
    }
    
    public function getGallery($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->galleryTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function fetchGallery($type, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $serviceBroker = $this->getServiceBroker();
        $translator = $serviceBroker->get('translate');
        
        $galleryTypes = Gallery_Model_Doctrine_Gallery::getAvailableTypes();
        
        if(!$gallery = $this->getGallery($type, 'type', $hydrationMode)) {
            $gallery = $this->galleryTable->getRecord();
            $gallery->Translation[$language]->title = $translator->translate($galleryTypes[$type], $language);
            $gallery->Translation[$language]->slug = MF_Text::createSlug($gallery->Translation[$language]->title);
            $gallery->setType($type);
            $gallery->save();
        }
        return $gallery;
    }
    
    public function getI18nGallery($id, $field = 'id', $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->galleryTable->getFullGalleryQuery();
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
    
    public function getAllGallerys($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->galleryTable->createQuery('g');
        $q->orderBy('g.id DESC');
        return $q->execute(array(),$hydrationMode);
    }
    
    public function getLastGalleries($group_id,$limit,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->galleryTable->createQuery('g');
	$q->select('g.*,p.*,gt.*');
        $q->addWhere('g.group_id = ?',$group_id);
	$q->leftJoin('g.Translation gt');
	$q->leftJoin('g.Photos p');
        $q->orderBy('g.id DESC');
        $q->limit($limit);
        return $q->execute(array(),$hydrationMode);
    }
    
    
    public function getGallerySelectOptions($language, $prependEmptyValue = false, $idPrefix = '') {
        $gallerys = $this->getAllGallerys();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = null;
        }
        foreach($gallerys as $gallery) {
            $result[$idPrefix . $gallery->getId()] = $gallery->get('Translation')->get($language)->title;
        }
        return $result;
    }
    
    public function getGalleryForm(Gallery_Model_Doctrine_Gallery $gallery = null) {
        $form = new Gallery_Form_Gallery();
        if(null !== $gallery) {
            $form->populate($gallery->toArray());
        }
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            $i18nSubform = $form->translations->getSubForm($language);
            if($i18nSubform) {
                $i18nSubform->getElement('name')->setValue($gallery->Translation[$language]->name);
                $i18nSubform->getElement('description')->setValue($gallery->Translation[$language]->description);
            }
        }
        return $form;
    }
    
    public function saveGalleryFromArray(array $values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }    
        
        if(!$gallery = $this->galleryTable->getProxy($values['id'])) {
            $gallery = $this->galleryTable->getRecord();
        }
        
        $gallery->fromArray($values);
        foreach($values['translations'] as $language => $translation) {
            $gallery->Translation[$language]->name = $translation['name'];
            $gallery->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Gallery_Model_Doctrine_GalleryTranslation', $values['translations'][$language]['name'], $gallery->getId());
            $gallery->Translation[$language]->description = $translation['description'];
        }
        
        $gallery->save();
        return $gallery;
    }
    
    public function removeGallery(Gallery_Model_Doctrine_Gallery $gallery) {
        $gallery->get('Translation')->delete();
        $gallery->delete();
    }
    
    public function getGalleryPaginationQuery() {
        $q = $this->galleryTable->createQuery('g');
	
	$q->select('g.*,p.*,gt.*');
	$q->leftJoin('g.Translation gt');
	$q->leftJoin('g.Photos p');
        $q->addOrderBy('g.id DESC');
	
        return $q;
    }
}

