<?php

/**
 * DbPhraseFix
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_Controller_Action_Helper_DbPhraseUtfFix extends Zend_Controller_Action_Helper_Abstract {
    
    public function direct($phrase) {
        $dbPhrase = mb_convert_case(urldecode($phrase), MB_CASE_UPPER, "utf-8");
        $dbPhrase = str_replace(array('Ó', 'Ń'), array('ó', 'ń'), $dbPhrase); // uppercase Ó bug fix
        return $dbPhrase;
    }
    
}

