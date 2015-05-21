<?php

/**
 * Media_Model_ImageResizer
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Media_Model_ImageResizer 
{
    const CROP_BEFORE = 'before';
    const CROP_AFTER = 'after';
    
    public static function makeThumbnail($sourceImagePath, $thumbnailImagePath, $width = null, $height = null, $crop = null) {
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
    
    public static function makeThumbnailSet($sourceImagePath, $photosDir, $dimentions) {
        if(file_exists($sourceImagePath)) {
            $pathinfo = pathinfo($sourceImagePath);
            
            $basename = $pathinfo['basename'];
            
            foreach($dimentions as $dimentionSet) {
                $width = $height = null;
                if(isset($dimentionSet[0])) {
                    $width = $dimentionSet[0];
                }
                if(isset($dimentionSet[1])) {
                    $height = $dimentionSet[1];
                }
                $dir = $width . 'x' . $height;
                
                $thumbnailImageDir = $photosDir . DIRECTORY_SEPARATOR . $dir;
                if(!is_dir($thumbnailImageDir)) {
                    mkdir($thumbnailImageDir);
                }
                
                $thumbnailImagePath = $thumbnailImageDir . DIRECTORY_SEPARATOR . $basename;
                
                if(!is_dir($thumbnailImageDir)) {
                    @mkdir($thumbnailImageDir);
                }
                if(is_dir($thumbnailImageDir)) {
                    self::makeThumbnail($sourceImagePath, $thumbnailImagePath, $width, $height);
                }
            }
        }
            
        
    }
}

