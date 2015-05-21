<?php

/**
 * MF_Filter_Urldecode
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_Filter_Urldecode implements Zend_Filter_Interface
{
    public function filter($value) {
        return urldecode($value);
    }
}

