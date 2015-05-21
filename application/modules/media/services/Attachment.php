<?php

/**
 * Attachment
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Media_Service_Attachment extends MF_Service_ServiceAbstract {
    
    protected $attachmentTable;
    public $attachmentsDir;
    
    public function init() {
        $this->attachmentTable = Doctrine_Core::getTable('Media_Model_Doctrine_Attachment');
        $bootstrap = $this->getServiceBroker()->get('FrontController')->getParam('bootstrap');
        if(null == $this->attachmentsDir = $bootstrap->getOption('attachmentsDir')) {
            throw new Zend_Controller_Action_Exception('Attachments directory configuration missing');
        }
        parent::init();
    }
    
    public function getAttachment($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->attachmentTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getSortedAttachmentsWithIds($ids, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->attachmentTable->findSortedAttachmentsByIds($ids, $hydrationMode);
    }
              
      public function getChildrenAttachments(Media_Model_Doctrine_Attachment $root) {
        if($root->getNode()->isRoot()) {
            return $root->getNode()->getChildren();
        }
    }
    public function createAttachmentRoot() {
        $tree = $this->attachmentTable->getTree();
        $attachment = $this->attachmentTable->getRecord();
        $attachment->save();
        $tree->createRoot($attachment);
        return $attachment;
    }
              
    public function createAttachment($filePath, $name, $title = null) {
        
        $offset = self::createOffset();
        $offsetDir = $this->attachmentsDir . DIRECTORY_SEPARATOR . $offset;
        if(!is_dir($offsetDir)) {
            @mkdir($this->attachmentsDir . DIRECTORY_SEPARATOR . $offset);
        }
      
        $name = MF_Text::createSlug($name);
        $filename = MF_File::createUniquefileName($name, $offsetDir);
        $title = null == $title ? pathinfo($name, PATHINFO_FILENAME) : $title;

        if($this->createAttachmentFile($filePath, $offset, $filename)) {
            $attachment = $this->attachmentTable->getRecord();
            $attachment->setFilename($filename);
            $attachment->setTitle($title);
            $attachment->setOffset($offset);
            $attachment->save();
            $tree = $this->attachmentTable->getTree();
            if(!$root = $tree->fetchRoot()) {
                $root = $this->createAttachmentRoot();
            }
            $attachment->getNode()->insertAsLastChildOf($root);
            
            return $attachment;
        }
    }
          
   public function createAttachmentFromUpload($uploadFileIndex, $name, $title = null, $languageAdmin) {
        $pathinfo = pathinfo($name);
        $extension = $pathinfo['extension'];
        $name = MF_Text::createSlug($name);
        $filename = $this->createUniquefileName($pathinfo['basename']);
        $title = null == $title ? pathinfo($name, PATHINFO_FILENAME) : $title;

        if(@move_uploaded_file($_FILES[$uploadFileIndex]['tmp_name'], $this->attachmentsDir . DIRECTORY_SEPARATOR . $filename)) {
//            if($this->createAttachmentFile($filename, $filename)) {
                $attachment = $this->attachmentTable->getRecord();
                $attachment->setFilename($filename);
                $attachment->Translation[$languageAdmin]->title = $title;
                $attachment->Translation[$languageAdmin]->slug = MF_Text::createUniqueTableSlug('Media_Model_Doctrine_AttachmentTranslation', $title, $attachment->getId());
                $attachment->setExtension($extension);
                $attachment->save();
                return $attachment;
//            }
        }
    }
    public function createAttachmentFile($filePath, $offset = '', $name = null) {
        if(file_exists($filePath)) {
            $offsetDir = realpath($this->attachmentsDir . DIRECTORY_SEPARATOR . $offset);

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
    
    public function createUniquefileName($fileName, $directory = null) {
        if(null == $directory) {
            $directory = $this->attachmentsDir;
        }
        if(is_dir($directory)) {
            $filePath = realpath($directory . DIRECTORY_SEPARATOR . $fileName);
            if(file_exists($filePath)) {
                $pathinfo = pathinfo($filePath);
                $name = $pathinfo['filename'];
                $ext = (isset($pathinfo['extension'])) ? $pathinfo['extension'] : '';
                $suffix = 1;
                $uniqueName = $fileName;
                do {
                    $uniqueName = $name . '-' . $suffix . '.' . $ext;
                    $suffix++;                    
                } while(file_exists($directory . DIRECTORY_SEPARATOR . $uniqueName));
                  
                return $uniqueName;
            }
            return $fileName;
        }
    }
    
    public function getAttachmentForm(Media_Model_Doctrine_Attachment $attachment = null) {
        $form = new Media_Form_Attachment();
        if(null != $attachment) {
            $form->populate($attachment->toArray());
        }
        return $form;
    }
    
    public function setAttachmentTitles($values,$attachment_id){
        if($attachment = $this->getAttachment($attachment_id)) {
            foreach($values as $language=>$value) {
                    $attachment->Translation[$language]->title = $value;
                    $attachment->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Media_Model_Doctrine_AttachmentTranslation', $value);
            }
         $attachment->save();
        }
        
    }
    
    public function saveFromArray($values) {
        if($attachment = $this->getAttachment((int) $values['id'])) {
            $attachment->fromArray($values);
            $attachment->save();
            return $attachment;
        }
    }

    public function moveAttachment(Media_Model_Doctrine_Attachment $attachment, $attachments, $direction = 'down') {
        if(!is_array($attachments)) {
            $attachments = $attachments->getPrimaryKeys();
        }
        $sortedAttachments = $this->getSortedAttachmentsWithIds($attachments);
        $currentKey = null;
        foreach($sortedAttachments as $key => $v) {
            if($v->getId() == $attachment->getId()) {
                $currentKey = $key;
                break;
            }
        }
        if(null === $currentKey) {
            throw new Exception('Attachment does not exist in Collection');
        }
        
        if($direction == 'up') {
            if(($currentKey - 1 >= 0) && ($prevAttachment = $sortedAttachments->get($currentKey - 1))) {
                $attachment->getNode()->moveAsPrevSiblingOf($prevAttachment);
            }
        } elseif($direction == 'down') {
            if(($currentKey + 1 < $sortedAttachments->count()) && ($nextAttachment = $sortedAttachments->get($currentKey + 1))) {
                $attachment->getNode()->moveAsNextSiblingOf($nextAttachment);
            }
        }
    }
    
    public function removeAttachment(Media_Model_Doctrine_Attachment $attachment) {
        if(is_integer($attachment)) {
            $attachment = $this->getAttachment($attachment);
        }
        if($attachment instanceof Media_Model_Doctrine_Attachment) {
            if($this->removeAttachmentFile($attachment->getFilename())) {
                if(isset($attachment['Translation']))
                    $attachment['Translation']->delete();
                $attachment->getNode()->delete();
            }
        }
    }
     public function removeAttachmentList(Media_Model_Doctrine_Attachment $root) {
        if($children = $this->getChildrenAttachments($root)) {
            foreach($children as $attachment) {
                $this->removeAttachment($attachment);
            }
            $this->removeAttachment($root);
        }
    }
    
    public function removeAttachmentFile($filename, $recursive = true) {
        $offsetDir = realpath($this->attachmentsDir);
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
}

