<?php

class MF_View_Helper_A extends Zend_View_Helper_Abstract
{
	public function a($text = '', $attribs = array())
	{
		$result = '<a';
			
		if(is_array($attribs) && count($attribs)) {
			$result .= ' ';
			foreach($attribs as $attrib => $value) {
				$result .= $attrib . '="' . $value . '" ';
			}
		}
		$result .= '>';
		
		if(is_string($text)) {
			$result .= $text;
		}
		$result .= '</a>';
			
		return $result;
	}

	
}