<?php

/**
 * Banner_Service_Ad
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Banner_Service_Ad extends MF_Service_ServiceAbstract {
    
    protected $adTable;
    
    public function init() {
        $this->adTable = Doctrine_Core::getTable('Banner_Model_Doctrine_Ad');
    }
      
    public function getAd($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->adTable->findOneBy($field, $id, $hydrationMode);
    }
   public function getAllAds(){
       return $this->adTable->findAll();
   }
   public function getAllActiveAds($hydrationMode = Doctrine_Core::HYDRATE_RECORD){
       $q = $this->adTable->createQuery('p');
       $q->addWhere('p.publish = 1');
       return $q->execute(array(),$hydrationMode);
   }
   
   public function getActiveAd($id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD){
       $q = $this->adTable->createQuery('p');
       $q->addWhere('p.id = ?',$id);
       $q->addWhere('p.publish = 1');
       $q->addWhere('p.date_from <= NOW()');
       $q->addWhere('p.date_to > NOW()');
       return $q->fetchOne(array(),$hydrationMode);
   }
   
   public function prependAds(){
       $ads = $this->getActiveAds(Doctrine_Core::HYDRATE_ARRAY);
       $adList = array();
       $adList[] = "";
       foreach($ads as $ad):
           $adList[$ad['id']] = $ad['Translation']['pl']['title'];
       endforeach;
       return $adList;
   }
   
   public function getActiveAds($hydrationMode = Doctrine_Core::HYDRATE_RECORD){
       $q = $this->adTable->createQuery('a');
       $q->leftJoin('a.Translation at');
       $q->addWhere('a.publish = 1');
//       $q->addWhere('a.date_from <= NOW()');
//       $q->addWhere('a.date_to > NOW()');
       return $q->execute(array(),$hydrationMode);
   }
   
    public function getAdForm(Banner_Model_Doctrine_Ad $ad = null) {
        $form = new News_Form_Video();
        
        if(null != $ad) {
            $bannerArray = $ad->toArray();
            $bannerArray['date_from'] = MF_Text::timeFormat($bannerArray['date_from'], 'd/m/Y H:i');
            $bannerArray['date_to'] = MF_Text::timeFormat($bannerArray['date_to'], 'd/m/Y H:i');
            $bannerArray['url'] = $ad['VideoRoot']['url'];
            $form->populate($bannerArray);
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($ad->Translation[$language]->title);
                }
            }
        }   
        return $form;
    }
    
    public function saveAdFromArray($values,$video_id = null) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$ad = $this->adTable->getProxy($values['id'])) {
            $ad = $this->adTable->getRecord();
        }
        
        if($video_id)
            $values['video_root_id'] = $video_id;
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        if(strpos($values['target_href'], 'http://') !== 0 && strlen($values['target_href'])>0) {
          $values['target_href'] = 'http://' . $values['target_href'];
        }
        $values['date_from'] = MF_Text::timeFormat($values['date_from'],'Y-m-d H:i:s', 'd/m/Y H:i');
        $values['date_to'] = MF_Text::timeFormat($values['date_to'],'Y-m-d H:i:s', 'd/m/Y H:i');
        
        $ad->fromArray($values);
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['name'])) {
                $ad->Translation[$language]->title = $values['translations'][$language]['name'];
                $ad->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Banner_Model_Doctrine_AdTranslation', $values['translations'][$language]['name'], $ad->get('id'));
                $ad->Translation[$language]->content = $values['translations'][$language]['description'];
            }
        }
        $ad->save();
        
        return $ad;
    }
    
    public function removeBanner(Banner_Model_Doctrine_Banner $ad) {
        $ad->delete();
    }
    
    public function refreshStatusAd($ad){
        if ($ad->get('publish')):
            $ad->set('publish',0);
        else:
            $ad->set('publish',1);
        endif;
        $ad->save();
    }
}

