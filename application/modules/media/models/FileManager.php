<?php

class File_Model_FileManager
{
    protected $_mediaDir;
    
    public function __construct() {
         $this->_mediaDir = APPLICATION_PATH . '/../public/media/';
    }
    
    public function findOneById($id) {
        return Doctrine_Core::getTable('File_Model_Doctrine_File')->find($id);
    }
    
    public function findAll($ifExist = false) {
        $files = Doctrine_Core::getTable('File_Model_Doctrine_File')->findAll();
        $result = array();
        if($ifExist) {
            foreach($files as $key => $file) {
                $filename = $this->_mediaDir . $file->getLocation();
                if(file_exists($filename) && (preg_match('/^[^\/]+$/', $file->getLocation()))) {
                    $result[] = $file;
                }
            }
        } else {
            $result = $files->toArray();
        }
        return $result;
    }
    
    public function createEditForm($file) {
        $form = new File_Form_File();
        
        $form->getElement('id')->setValue($file->getId());
        $form->getElement('name')->setValue($file->getName());
        $form->getElement('title')->setValue($file->getTitle());
        return $form;
    }

    public function saveFromForm($form, $offset = '') {
        if($file = $this->findOneById($form->getValue('id'))) {
            if($form->getValue('name')) {
                $oldLocation = $file->getLocation();
                $file->setName($form->getValue('name'));
                $file->setTitle($form->getValue('title'));
                $location = $offset . MF_Text::createSlug($file->getFilename());
                $href = '/media/' . $location;
                $file->setHref($href);
                $file->setLocation($location);
                $file->save();
                $this->_refreshFile($file, $oldLocation);
            }
            return $file;
        }
    }
    
    public function save($data, $href, $location) {
        $file = new File_Model_Doctrine_File();
        $file->setName($this->_getNamePart($data['name']));
        $file->setTitle($this->_getNamePart($data['name']));
        $file->setExt($this->_getExtPart($data['name']));
        $file->setHref($href);
        $file->setLocation($location);
        $file->save();
        return $file;
    }
    
    public function createUniqueAlias() {
        return dechex(sprintf('%u', crc32(uniqid() . microtime())));
    }
    
    public function remove($file) {
        $filename = $this->_mediaDir . $file->getLocation();
        if(file_exists($filename)) {
            @unlink($filename);
        }
        return $file->delete();
    }
     
    public function _refreshFile($file, $location) {
        $path = $this->_mediaDir . $location;
        if(file_exists($path)) {
            
            $newPath = $this->_mediaDir . $file->getLocation();
            if(@copy($path, $newPath)) {
                @unlink($path);
            }
            
        }
    }
    
    /**
     * Retrieves name part of given file name
     * 
     * @param string $filename
     * @return string
     */
    protected function _getNamePart($filename) {
		preg_match('/([^\.]+)(\.[^\.]+|$)/', $filename, $matches);
		return isset($matches[1]) ? $matches[1] : '';
	}
	
    /**
     * Retrieves extension part of given file name
     * 
     * @param string @filename
     * @return string
     */
	protected function _getExtPart($filename) {
		preg_match('/\.([^\.]+)$/', $filename, $matches);
		return isset($matches[1]) ? $matches[1] : '';
	}
    
}
