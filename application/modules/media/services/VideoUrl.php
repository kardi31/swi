<?php

/**
 * Video
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Media_Service_VideoUrl extends MF_Service_ServiceAbstract {
    
    protected $videoTable;
    public $videosDir;
    
    public function init() {
        $this->videoTable = Doctrine_Core::getTable('Media_Model_Doctrine_VideoUrl');
        $bootstrap = $this->getServiceBroker()->get('FrontController')->getParam('bootstrap');
        if(null == $this->videosDir = $bootstrap->getOption('videosDir')) {
            throw new Zend_Controller_Action_Exception('Videos directory configuration missing');
        }
        parent::init();
    }
    
    public function getVideo($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->videoTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getSortedVideosWithIds($ids, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->videoTable->findSortedVideosByIds($ids, $hydrationMode);
    }
              
      public function getChildrenVideos(Media_Model_Doctrine_VideoUrl $root) {
        if($root->getNode()->isRoot()) {
            return $root->getNode()->getChildren();
        }
    }
    
    public function createVideoRoot() {
        $tree = $this->videoTable->getTree();
        $video = $this->videoTable->getRecord();
        $video->save();
        $tree->createRoot($video);
        return $video;
    }
              
    public function createVideo($filePath, $name, $title = null) {
        
        $offset = self::createOffset();
        $offsetDir = $this->videosDir . DIRECTORY_SEPARATOR . $offset;
        if(!is_dir($offsetDir)) {
            @mkdir($this->videosDir . DIRECTORY_SEPARATOR . $offset);
        }
      
        $name = MF_Text::createSlug($name);
        $filename = MF_File::createUniquefileName($name, $offsetDir);
        $title = null == $title ? pathinfo($name, PATHINFO_FILENAME) : $title;

        if($this->createVideoFile($filePath, $offset, $filename)) {
            $video = $this->videoTable->getRecord();
            $video->setFilename($filename);
            $video->setTitle($title);
            $video->setOffset($offset);
            $video->save();
            $tree = $this->videoTable->getTree();
            if(!$root = $tree->fetchRoot()) {
                $root = $this->createVideoRoot();
            }
            $video->getNode()->insertAsLastChildOf($root);
            
            return $video;
        }
    }
          
    public function createVideoFromUpload($values,$parent = null) {
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        $languages = $i18nService->getLanguageList();
        
        if($parent)
            $values['id'] = $parent['id'];
        if(!isset($values['id'])||!$video = $this->getVideo($values['id'])){
            $video = $this->videoTable->getRecord();
        }
        $video->fromArray($values);
        foreach($languages as $language) {
            $video->Translation[$language]->lang = $language;
                $video->Translation[$language]->title = $values['translations'][$language]['name'];

                $video->Translation[$language]->description = $values['translations'][$language]['description'];
        }
        if(null != $parent) {
            if(is_integer($parent)) {
                $parent = $this->videoTable->find($parent);
            } 
        } else {
            $tree = $this->videoTable->getTree($video);
        }
         $video->save();
        return $video;
    }
    
    
    public function createVideoFile($filePath, $offset = '', $name = null) {
        if(file_exists($filePath)) {
            $offsetDir = realpath($this->videosDir . DIRECTORY_SEPARATOR . $offset);

            if(!is_dir($offsetDir)) {
                @mkdir($offsetDir);
            }
            
            if(null == $name) {
                $name = basename($filePath);
                if(realpath(dirname($filePath)) != realpath($offsetDir)) {
                    $name = MF_File::createUniquefileName($name, $offsetDir);
                }
            }

            if(realpath(dirname($filePath)) == realpath($offsetDir) || @copy($filePath, $offsetDir . DIRECTORY_SEPARATOR . $name)) {
                return true;
            }
        }
    }
    
    public function getVideoForm(Media_Model_Doctrine_VideoUrl $video = null) {
        $form = new Reference_Form_Video();
        if(null != $video) {
            $form->populate($video->toArray());
            
              $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            $i18nSubform = $form->translations->getSubForm($language);
            if($i18nSubform) {
                $i18nSubform->getElement('name')->setValue($video->Translation[$language]->title);
                $i18nSubform->getElement('description')->setValue($video->Translation[$language]->description);
            }
        }
            
        }
        return $form;
    }
    
     public function saveEditedVideoFromArray($values) {
        if($video = $this->getVideo((int) $values['video_root_id'])) {
            $video->fromArray($values);
            $video->save();
            return $video;
        }
    }
    
    public function saveFromArray($values) {
        if($video = $this->getVideo((int) $values['id'])) {
            $video->fromArray($values);
            $video->save();
            return $video;
        }
    }

    public function moveVideo(Media_Model_Doctrine_Video $video, $videos, $direction = 'down') {
        if(!is_array($videos)) {
            $videos = $videos->getPrimaryKeys();
        }
        $sortedVideos = $this->getSortedVideosWithIds($videos);
        $currentKey = null;
        foreach($sortedVideos as $key => $v) {
            if($v->getId() == $video->getId()) {
                $currentKey = $key;
                break;
            }
        }
        if(null === $currentKey) {
            throw new Exception('Video does not exist in Collection');
        }
        
        if($direction == 'up') {
            if(($currentKey - 1 >= 0) && ($prevVideo = $sortedVideos->get($currentKey - 1))) {
                $video->getNode()->moveAsPrevSiblingOf($prevVideo);
            }
        } elseif($direction == 'down') {
            if(($currentKey + 1 < $sortedVideos->count()) && ($nextVideo = $sortedVideos->get($currentKey + 1))) {
                $video->getNode()->moveAsNextSiblingOf($nextVideo);
            }
        }
    }
    
    public function removeVideo(Media_Model_Doctrine_VideoUrl $video) {
        if(is_integer($video)) {
            $video = $this->getVideo($video);
        }
        if($video instanceof Media_Model_Doctrine_VideoUrl) {
            
            
            if(isset($video['Translation']))
                $video['Translation']->delete();
            $video->getNode()->delete();
        }
        
    }
    
     public function removeVideoList(Media_Model_Doctrine_VideoUrl $root) {
        if($children = $this->getChildrenVideos($root)) {
            foreach($children as $video) {
                $this->removeVideo($video);
            }
            $this->removeVideo($root);
        }
    }
    
    public function removeVideoFile($filename, $offset = '', $recursive = true) {
        $offsetDir = realpath($this->videosDir . DIRECTORY_SEPARATOR . $offset);
        if(file_exists($offsetDir . DIRECTORY_SEPARATOR . $filename)) {
            @unlink($offsetDir . DIRECTORY_SEPARATOR . $filename);
            if(true == $recursive) {
                $files = scandir($offsetDir);
                foreach($files as $file) {
                    if(is_dir($offsetDir . DIRECTORY_SEPARATOR . $file)) {
                        if(file_exists($offsetDir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $filename)) {
                            @unlink($offsetDir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $filename);
                        }
                    }
                }
            }
        }
        return true;
    }
    
    public static function createOffset() {
        return hexdec(hash('crc32', date('Y-m')));
    }
    
    public function getVideosWithAd($id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD){
       $q = $this->videoTable->createQuery('v');
       $q->leftJoin('v.Ad a');
       $q->addWhere('v.root_id = ?',$id);
//       $q->addWhere('a.publish = 1');
//       $q->addWhere('a.date_from <= NOW()');
//       $q->addWhere('a.date_to > NOW()');
       $q->addWhere('v.level = 1');
       return $q->execute(array(),$hydrationMode);
   }
}

