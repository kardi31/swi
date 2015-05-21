<?php

/**
 * Media
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Media_Service_Media extends MF_Service_ServiceAbstract {
    
    const CROP_BEFORE = 'before';
    const CROP_AFTER = 'after';
    
    public $publicDir;
    public $mediaDir;
    public $photosDir;
    
    public function init() {
        $this->publicDir = realpath($this->getFrontController()->getParam('bootstrap')->getOption('publicDir'));
        $this->mediaDir = $this->publicDir . '/media';
        $this->photosDir = $this->publicDir . '/media/photos';
        parent::init();
    }
    
    public function createPhoto($filePath, $filename = null, array $options, $watermarkFile = null) {
        if(file_exists($filePath)) {
            if(null == $filename || @copy($filePath, $this->photosDir . DIRECTORY_SEPARATOR . $filename)) {
                if(null == $filename) {
                    $filename = basename($filePath);
                }
                foreach($options as $cat) {
                    if(!is_dir($this->photosDir . DIRECTORY_SEPARATOR . $cat)) {
                        @mkdir($this->photosDir . DIRECTORY_SEPARATOR . $cat);
                    }
                    $match = array();
                    if(preg_match('/^(\d*)x(\d*)$/', $cat, $match)) {
                        $width = strlen($match[1]) ? (int) $match[1] : null;
                        $height = strlen($match[2]) ? (int) $match[2] : null;
                        
                        $this->makeThumbnail($this->photosDir . DIRECTORY_SEPARATOR . $filename, $this->photosDir . DIRECTORY_SEPARATOR . $cat . DIRECTORY_SEPARATOR . $filename, $width, $height, self::CROP_AFTER);

                        if(null != $watermarkFile) {
                            if(file_exists($watermarkFile)) {
                                $this->addWatermark($this->photosDir . DIRECTORY_SEPARATOR . $cat . DIRECTORY_SEPARATOR . $filename, $watermarkFile);
                            }
                        }
                    }
                }
                return true;
            }
        }
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
    
    public function removePhoto($filename, $recursive = true) {
        if(file_exists($this->photosDir . DIRECTORY_SEPARATOR . $filename)) {
            @unlink($this->photosDir . DIRECTORY_SEPARATOR . $filename);
            if(true == $recursive) {
                $files = scandir($this->photoDir);
                foreach($files as $file) {
                    if(is_dir($this->photosDir . DIRECTORY_SEPARATOR . $file)) {
                        if(file_exists($this->photosDir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $filename)) {
                            @unlink($this->photosDir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $filename);
                        }
                    }
                }
            }
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
        
        imagejpeg( $thumbnailGdImage, $thumbnailImagePath, 90 );

        imagedestroy( $sourceGdImage );
        imagedestroy( $thumbnailGdImage );

        return true;
    }
    
    
    public static function cropImage($filename, $destName, $x, $y, $x2, $y2, $destWidth, $destHeight) {
        if(file_exists($filename)) {
            list($sourceWidth, $sourceHeight, $sourceImageType) = getimagesize($filename);
            
            $tmpWidth = $x2 - $x;
            $tmpHeight = $y2 - $y;

            switch($sourceImageType) {
            case IMAGETYPE_GIF:
                $sourceGdImage = imagecreatefromgif($filename);
                break;

            case IMAGETYPE_JPEG:
                $sourceGdImage = imagecreatefromjpeg($filename);
                break;

            case IMAGETYPE_PNG:
                $sourceGdImage = imagecreatefrompng($filename);
                break;
            }

            if ($sourceGdImage === false) {
                return false;
            }
            
            $destRatio = $tmpWidth / $tmpHeight;
            if(false == $destHeight && (is_integer($destWidth))) {
                $destHeight = (int) ($destWidth / $destRatio);
            } elseif(false == $destWidth && (is_integer($destHeight))) {
                $destWidth = (int) ($destHeight * $destRatio);
            }
            
            $destGdImage = imagecreatetruecolor($destWidth, $destHeight);
            imagecopyresampled($destGdImage, $sourceGdImage, 0, 0, $x, $y, $destWidth, $destHeight, $tmpWidth, $tmpHeight);

            imagejpeg( $destGdImage, $destName, 90 );
            
            return true;
        }
    }
    
}

