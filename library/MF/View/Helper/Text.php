<?php

class MF_View_Helper_Text extends Zend_View_Helper_Abstract
{
    public function text() {
        return $this;
    }
    
    public function truncate($str, $limit = 60, $type = 'letters', $delim = '...', $force = true) {
        return MF_Text::truncate($str, $limit, $type, $delim, $force);
    }
    
    public function offset($str, $offset = 60, $type = 'letters') {
        return MF_Text::offset($str, $offset, $type);
    }
    
    public function timeFormat($time, $outputFormat, $inputFormat = 'Y-m-d H:i:s') {
        return MF_Text::timeFormat($time, $outputFormat, $inputFormat);
    }
    
}