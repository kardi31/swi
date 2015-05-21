<?php

class Default_View_Helper_HeadTitle extends Zend_View_Helper_HeadTitle
{
    public function truncate($limit = 60, $delim = '...') {
        $title = strip_tags($this->__toString());

        $len = strlen($title);
        if ($len > $limit) {
            preg_match('/(.{' . $limit . '}.*?)\b/', $title, $matches);
            $title = rtrim($matches[1]) . $delim;
            $this->set($title);
        }
        return $this;
    }
    
}
