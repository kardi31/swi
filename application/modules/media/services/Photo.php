<?php

/**
 * Photo
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Media_Service_Photo extends MF_Service_ServiceAbstract {
    
    const CROP_BEFORE = 'before';
    const CROP_AFTER = 'after';

    public $photosDir;
    public $watermarkFile;
    protected $photoTable;
    
    public function init() {
        $this->photoTable = Doctrine_Core::getTable('Media_Model_Doctrine_Photo');
        $bootstrap = $this->getServiceBroker()->get('FrontController')->getParam('bootstrap');
        if(null == $this->photosDir = $bootstrap->getOption('photosDir')) {
            throw new Zend_Controller_Action_Exception('Photos directory configuration missing');
        }
        $this->watermarkFile = $bootstrap->getOption('watermarkFile');
        parent::init();
    }
    
    public function getPhoto($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->photoTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getChildrenPhotos(Media_Model_Doctrine_Photo $root) {
        if($root->getNode()->isRoot()) {
            return $root->getNode()->getChildren();
        }
    }
    
    public function getPhotoForm(Media_Model_Doctrine_Photo $photo = null) {
        $form = new Media_Form_Photo();
        if(null != $photo) {
            $form->populate($photo->toArray());
        }
        return $form;
    }
    
    public function saveFromArray(array $data) {
        if($photo = $this->getPhoto((int) $data['id'])) {
            $photo->fromArray($data);
            $photo->save();
            return $photo;
        }
    }
    
    public function movePhoto(Media_Model_Doctrine_Photo $photo, $direction = 'down') {
        if($direction == 'up') {
            $prevSibling = $photo->getNode()->getPrevSibling();
            if($prevSibling) {
                $photo->getNode()->moveAsPrevSiblingOf($prevSibling);
            }
        } elseif($direction == 'down') {
            $nextSibling = $photo->getNode()->getNextSibling();
            if($nextSibling) {
                $photo->getNode()->moveAsNextSiblingOf($nextSibling);
            }
        }
    }
    
    public function removePhoto($photo) {
        if(is_integer($photo)) {
            $photo = $this->getPhoto($photo);
        }
        if($photo instanceof Media_Model_Doctrine_Photo ) {
            if($this->removePhotoFile($photo->getFilename(), $photo->getOffset())) {
                $photo->getNode()->delete();
            }
        }
    }
    
    public function removeGallery(Media_Model_Doctrine_Photo $root) {
        if($children = $this->getChildrenPhotos($root)) {
            foreach($children as $photo) {
                $this->removePhoto($photo);
            }
            $this->removePhoto($root);
        }
    }
    
    public function createPhotoRoot() {
        $tree = $this->photoTable->getTree();
        $photo = $this->photoTable->getRecord();
        $photo->save();
        $tree->createRoot($photo);
        return $photo;
    }
    
    public function createPhoto($filePath, $name, $title = null, $options = array(), $parent = null, $watermark = false) {
        
        $offset = self::createOffset();
        $offsetDir = $this->photosDir . DIRECTORY_SEPARATOR . $offset;
        if(!is_dir($offsetDir)) {
            @mkdir($this->photosDir . DIRECTORY_SEPARATOR . $offset);
        }
        
        $name = MF_Text::createSlug($name);
        $filename = $this->createUniquefileName($name, $offsetDir);
        $title = null == $title ? pathinfo($name, PATHINFO_FILENAME) : $title;

        $watermarkFile = (false != $watermark && null != $this->watermarkFile) 
            ? $this->watermarkFile : null;
      
        if($this->createPhotoFile($filePath, $offset, $filename, $options, $watermarkFile)) {
            $photo = $this->photoTable->getRecord();
            $photo->setFilename($filename);
            $photo->setTitle($title);
            $photo->setOffset($offset);
            $photo->save();
            if(null != $parent) {
                if(is_integer($parent)) {
                    $parent = $this->photoTable->find($parent);
                } 
                if($parent instanceof Media_Model_Doctrine_Photo) {
                    $photo->getNode()->insertAsLastChildOf($parent);
                }
            } else {
                $tree = $this->photoTable->getTree();
                $tree->createRoot($photo);
            }
            
            return $photo;
        }
    }
    
    public function updatePhoto($photo, $filePath, $offset = null, $filename = null, $title = null, $dimensions = array(), $watermark = false) {
        if(null == $offset) {
            $offset = self::createOffset();
            $offsetDir = $this->photosDir . DIRECTORY_SEPARATOR . $offset;
            if(!is_dir($offsetDir)) {
                @mkdir($this->photosDir . DIRECTORY_SEPARATOR . $offset);
            }
        }
        
        if(null != $filename) {
            $filename = MF_Text::createSlug($filename);
            $filename = $this->createUniquefileName($filename, $offsetDir);
            $title = null == $title ? pathinfo($filename, PATHINFO_FILENAME) : $title;
        } else {
            $filename = $photo->getFilename();
        }
        
        $watermarkFile = (false != $watermark && null != $this->watermarkFile) 
            ? $this->watermarkFile : null;
        
        if(null != $filePath) {
            $this->createPhotoFile($filePath, $offset, $filename, $dimensions, $watermarkFile);
        }

        $photo->setOffset($offset);
        $photo->setFilename($filename);
        $photo->setTitle($title);
        $photo->save();
        
        return $photo;
    }
    
    public function clearPhoto($photo) {
        $this->removePhotoFile($photo->getFilename(), $photo->getOffset(), true);
        $photo->setOffset(null);
        $photo->setFilename(null);
        $photo->setTitle(null);
        $photo->setCropData(null);
        $photo->save();
        return $photo;
    }
    
    public function createPhotoFromUpload($uploadFileIndex, $name, $title = null, $options = array(), $parent = null, $watermark = false) {
    
        $offset = self::createOffset();
        $offsetDir = $this->photosDir . DIRECTORY_SEPARATOR . $offset;
        if(!is_dir($offsetDir)) {
            @mkdir($this->photosDir . DIRECTORY_SEPARATOR . $offset);
        }

        $name = MF_Text::createSlug($name);
        $filename = $this->createUniquefileName($name, $offsetDir);
        $title = null == $title ? pathinfo($name, PATHINFO_FILENAME) : $title;

        $watermarkFile = (false != $watermark && null != $this->watermarkFile) 
            ? $this->watermarkFile : null;

        if(move_uploaded_file($_FILES[$uploadFileIndex]['tmp_name'], $offsetDir . DIRECTORY_SEPARATOR . $filename)) {
            if($this->createPhotoFile($offsetDir . DIRECTORY_SEPARATOR . $filename, $offset, $filename, $options, $watermarkFile)) {
                $photo = $this->photoTable->getRecord();
                $photo->setFilename($filename);
                $photo->setTitle($title);
                $photo->setOffset($offset);
                $photo->save();

                if(null != $parent) {
                    if(is_integer($parent)) {
                        $parent = $this->photoTable->find($parent);
                    } 
                    if($parent instanceof Media_Model_Doctrine_Photo) {
                        $photo->getNode()->insertAsLastChildOf($parent);
                    }
                } else {
                    $tree = $this->photoTable->getTree();
                    $tree->createRoot($photo);
                }

                return $photo;
            }
        }
    }
    
    public function createPhotoFile($filePath, $offset = '', $name = null, array $options, $watermarkFile = null) {
        if(file_exists($filePath)) {
            $offsetDir = realpath($this->photosDir . DIRECTORY_SEPARATOR . $offset);

            if(!is_dir($offsetDir)) {
                @mkdir($offsetDir);
            }
            
            if(null == $name) {
                $name = basename($filePath);
                if(realpath(dirname($filePath)) != realpath($offsetDir)) {
                    $name = $this->createUniquefileName($name, $offsetDir);
                }
            }
          
            if(realpath(dirname($filePath)) == realpath($offsetDir) || @copy($filePath, $offsetDir . DIRECTORY_SEPARATOR . $name)) {
              
                foreach($options as $cat) {
                    if(!is_dir($offsetDir . DIRECTORY_SEPARATOR . $cat)) {
                        @mkdir($offsetDir . DIRECTORY_SEPARATOR . $cat);
                    }

                    $match = array();
                    if(preg_match('/^(\d*)x(\d*)$/', $cat, $match)) {
                        
                        $width = strlen($match[1]) ? (int) $match[1] : null;
                        $height = strlen($match[2]) ? (int) $match[2] : null;
           
                        $this->makeThumbnail($filePath, $offsetDir . DIRECTORY_SEPARATOR . $cat . DIRECTORY_SEPARATOR . $name, $width, $height, self::CROP_AFTER);

                        if(null != $watermarkFile) {
                            if(file_exists($watermarkFile)) {
                                $this->addWatermark($offsetDir . DIRECTORY_SEPARATOR . $cat . DIRECTORY_SEPARATOR . $name, $watermarkFile);
                            }
                        }
                        
                    }
                }
                return true;
            }
        }
    }
    
    public function cropPhotoFile($fileName, $offset = '', $name = null, $x, $y, $x2, $y2, $destWidth = null, $destHeight = null) {
        $offsetDir = realpath($this->phtoosDir . DIRECTORY_SEPARATOR . $offset);
        $filePath = $offsetDir . DIRECTORY_SEPARATOR . $fileName;
        if(null == $name) {
            $name = basename($filePath);
        }

        $tmpWidth = $x2 - $x;
        $tmpHeight = $y2 - $y;
        
        $destRatio = $tmpWidth / $tmpHeight;
        if(null == $destHeight && (is_integer($destWidth))) {
            $destHeight = (int) ($destWidth / $destRatio);
        } elseif(null == $destWidth && (is_integer($destHeight))) {
            $destWidth = (int) ($destHeight * $destRatio);
        }
        
        $cat = $destWidth . 'x' . $destHeight;
        
        if(!is_dir($offsetDir . DIRECTORY_SEPARATOR . $cat)) {
            @mkdir($offsetDir . DIRECTORY_SEPARATOR . $cat);
        }
        
        $destPath = $offsetDir . DIRECTORY_SEPARATOR . $cat . DIRECTORY_SEPARATOR . $name;

        $this->crop($filePath, $destPath, $x, $y, $x2, $y2, $destWidth, $destHeight);
    }
    
    public function crop($filePath, $destPath, $x, $y, $x2, $y2, $destWidth = null, $destHeight = null) {
        if(file_exists($filePath)) {
            list($sourceWidth, $sourceHeight, $sourceImageType) = getimagesize($filePath);
            
            $tmpWidth = $x2 - $x;
            $tmpHeight = $y2 - $y;

            switch($sourceImageType) {
            case IMAGETYPE_GIF:
                $sourceGdImage = imagecreatefromgif($filePath);
                break;

            case IMAGETYPE_JPEG:
                $sourceGdImage = imagecreatefromjpeg($filePath);
                break;

            case IMAGETYPE_PNG:
                $sourceGdImage = imagecreatefrompng($filePath);
                break;
            }

            if ($sourceGdImage === false) {
                return false;
            }
            
            $destRatio = $tmpWidth / $tmpHeight;
            if(null == $destHeight && (is_integer($destWidth))) {
                $destHeight = (int) ($destWidth / $destRatio);
            } elseif(null == $destWidth && (is_integer($destHeight))) {
                $destWidth = (int) ($destHeight * $destRatio);
            }
            
            $destGdImage = imagecreatetruecolor($destWidth, $destHeight);
            imagecopyresampled($destGdImage, $sourceGdImage, 0, 0, $x, $y, $destWidth, $destHeight, $tmpWidth, $tmpHeight);

            imagejpeg( $destGdImage, $destPath, 90 );
            
            return true;
        }
    }
    
    public function makeThumbnail($sourceImagePath, $thumbnailImagePath, $width = null, $height = null, $crop = null) {
        if(!file_exists($sourceImagePath)) {
            throw new Exception('File ' . $sourceImagePath . ' does not exist');
        }
 
        list( $sourceWidth, $sourceHeight, $sourceImageType ) = getimagesize($sourceImagePath);
        
        $sourceAspectRatio = $sourceWidth / $sourceHeight;
        
        if(null === $width && is_integer($height)) {
            $width = (int) ($height * $sourceAspectRatio);
        } elseif(null === $height && is_integer($width)) {
            $height = (int) ($width / $sourceAspectRatio);
        }
        
        $origWidth = $width;
        $origHeight = $height;
        
        switch ( $sourceImageType ) {
        case IMAGETYPE_GIF:
            $sourceGdImage = imagecreatefromgif( $sourceImagePath );
            break;

        case IMAGETYPE_JPEG:
            $sourceGdImage = imagecreatefromjpeg( $sourceImagePath );
            break;

        case IMAGETYPE_PNG:
            $sourceGdImage = imagecreatefrompng( $sourceImagePath );
            break;
        }

        if ( $sourceGdImage === false ) {
            return false;
        }

        $thumbnailAspectRatio = $width / $height;
        $left = $top = 0;
        
        if(null !== $crop && $crop == self::CROP_BEFORE) {
            // crop before resize
            $thumbnailGdImage = imagecreatetruecolor( $origWidth, $origHeight );
            $left = ($sourceWidth - $origWidth) / 2;
            $top = ($sourceHeight - $origHeight) / 2;
            imagecopy( $thumbnailGdImage, $sourceGdImage, 0, 0, $left, $top, $origWidth, $origHeight );
        } elseif(null !== $crop && $crop == self::CROP_AFTER) {
            if ( $sourceAspectRatio < $thumbnailAspectRatio ) {
                $height = (int) ($width / $sourceAspectRatio);
                $top = (int) (($height - $origHeight) / 2);
            } else {
                $width = (int) ($height * $sourceAspectRatio);
                $left = (int) (($width - $origWidth) / 2);
            }
            $tmpGdImage = imagecreatetruecolor( $width, $height );
            imagecopyresampled( $tmpGdImage, $sourceGdImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight );
            
            $thumbnailGdImage = imagecreatetruecolor( $origWidth, $origHeight );
            imagecopy($thumbnailGdImage, $tmpGdImage, 0, 0, $left, $top, $origWidth, $origHeight);
        } else {
            // resize
            $thumbnailGdImage = imagecreatetruecolor( $width, $height );
            imagecopyresampled( $thumbnailGdImage, $sourceGdImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight );
        }

        if(file_exists($thumbnailImagePath)) {
            @unlink($thumbnailImagePath);
        }
        
        $r = imagejpeg( $thumbnailGdImage, $thumbnailImagePath, 90 );

        imagedestroy( $sourceGdImage );
        imagedestroy( $thumbnailGdImage );

        return true;
    }
    
    public function addWatermark($sourceImagePath, $watermarkImagePath) {
        if(file_exists($sourceImagePath) && file_exists($watermarkImagePath)) {
            list( $sourceWidth, $sourceHeight, $sourceImageType ) = getimagesize($sourceImagePath);
            list( $watermarkWidth, $watermarkHeight, $watermarkImageType) = getimagesize($watermarkImagePath);
            
            if(!$watermarkImageType == IMAGETYPE_PNG) {
                throw new Exception('Invalid watermark image type');
            }
            
            switch ( $sourceImageType ) {
                case IMAGETYPE_GIF:
                    $sourceGdImage = imagecreatefromgif( $sourceImagePath );
                    break;

                case IMAGETYPE_JPEG:
                    $sourceGdImage = imagecreatefromjpeg( $sourceImagePath );
                    break;

                case IMAGETYPE_PNG:
                    $sourceGdImage = imagecreatefrompng( $sourceImagePath );
                    break;
            }
            
            $destWatermarkHeight = round($sourceWidth / ($watermarkWidth / $watermarkHeight));
            $watermarkGdImage = imagecreatefrompng( $watermarkImagePath );
            $destWatermarkGdImage = imagecreatetruecolor( $sourceWidth, $destWatermarkHeight );
            $transparent = imagecolorallocatealpha($destWatermarkGdImage, 0, 0, 0, 127);
            imagefill($destWatermarkGdImage, 0, 0, $transparent);
            
            imagecopyresampled( $destWatermarkGdImage, $watermarkGdImage, 0, 0, 0, 0, $sourceWidth, $destWatermarkHeight, $watermarkWidth, $watermarkHeight );
            imagecopy($sourceGdImage, $destWatermarkGdImage, 0, $sourceHeight - $destWatermarkHeight, 0, 0, $sourceWidth, $sourceHeight);
            
            
            switch ( $sourceImageType ) {
            case IMAGETYPE_GIF:
                imagegif( $sourceGdImage, $sourceImagePath, 90 );
                break;
            case IMAGETYPE_JPEG:
                imagejpeg( $sourceGdImage, $sourceImagePath, 90 );
                break;
            case IMAGETYPE_PNG:
                imagepng( $sourceGdImage, $sourceImagePath, 90 );
                break;
            }

        }
    }
    
    public function removePhotoFile($fileName, $offset = '', $recursive = true) {
        $offsetDir = realpath($this->photosDir . DIRECTORY_SEPARATOR . $offset);
        if(file_exists($offsetDir . DIRECTORY_SEPARATOR . $fileName)) {
            @unlink($offsetDir . DIRECTORY_SEPARATOR . $fileName);
            if(true == $recursive) {
                $files = scandir($offsetDir);
                foreach($files as $file) {
                    if(is_dir($offsetDir . DIRECTORY_SEPARATOR . $file)) {
                        if(file_exists($offsetDir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $fileName)) {
                            @unlink($offsetDir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $fileName);
                        }
                    }
                }
            }
        }
        return true;
    }
    
    public function createUniquefileName($fileName, $directory = null) {
        if(null == $directory) {
            $directory = $this->photosDir;
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
    
    public static function createOffset() {
        return hexdec(hash('crc32', date('Y-m')));
    }
}

