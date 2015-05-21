<?php

/**
 * Media_Model_MediaManager
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Media_Model_MediaManager 
{
    public function removeFile($file, $dir, $withOffsets = true) {
        $filename = realpath($dir . DIRECTORY_SEPARATOR . $file);
        if(file_exists($filename)) {
            @unlink($filename);
            if($withOffsets) {
                $files = scandir($dir);
                foreach($files as $subfile) {
                    if(is_dir($subfile)) {
                        if(file_exists($dir . DIRECTORY_SEPARATOR . $subfile . DIRECTORY_SEPARATOR . $file)) {
                            @unlink($dir . DIRECTORY_SEPARATOR . $subfile . DIRECTORY_SEPARATOR . $file);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Retrieves name part of given file name
     * 
     * @param string $filename
     * @return string
     */
    public static function getNamePart($filename) {
		preg_match('/([^\.]+)(\.[^\.]+|$)/', $filename, $matches);
		return isset($matches[1]) ? $matches[1] : null;
	}
	
    /**
     * Retrieves extension part of given file name
     * 
     * @param string @filename
     * @return string
     */
	public static function getExtPart($filename) {
		preg_match('/\.([^\.]+)$/', $filename, $matches);
		return isset($matches[1]) ? $matches[1] : null;
	}
    
}

