<?php

/**
 * MF_SEO
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_SEO {
    
    public static function retrieveKeywords($str, $minLength = 5) {
        $str = preg_replace('/[-_\/\\\.\,\!\?]+/', '', strip_tags($str));
        $words = explode(" ", $str); 
        
        $result = array();
        foreach($words as $word) {
            if(strlen(trim($word)) > $minLength) {
                $result[] = $word;
            }
        }
        return $result;
    }
}

