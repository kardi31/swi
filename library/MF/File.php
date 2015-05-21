<?php

/**
 * MF_File
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_File {
    
    public static function createUniquefileName($fileName, $directory) {
        if(!is_dir($directory)) {
            throw new Exception('Invalid directory');
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
    
    public static function removeDirectory($directory, $recursive = false) {
        if(is_dir($directory)) {
            foreach(scandir($directory) as $file) { 
                if ($file == '.' || $file == '..') continue; 
                if(is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                    if(true === $recursive) {
                        self::removeDirectory($directory . DIRECTORY_SEPARATOR . $file, $recursive);
                    }
                    @rmdir($directory . DIRECTORY_SEPARATOR . $file);
                } else {
                    @unlink($directory . DIRECTORY_SEPARATOR . $file);
                }
            } 
            @rmdir($directory);
        }
    }
}

