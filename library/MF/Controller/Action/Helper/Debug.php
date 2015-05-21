<?php

/*
 * Helper wyświetla podaną zmienną na pasku ZFDebug w zakładca Custom
 * Sposób użycia:
 * $this->_helper->debug($var);
 */
class MF_Controller_Action_Helper_Debug extends Zend_Controller_Action_Helper_Abstract
{
	protected $_text;
	const TAB = 'Custom';
	
	public function init()
	{
		$front = Zend_Controller_Front::getInstance();
		$bootstrap = $front->getParam('bootstrap');
		if(!$bootstrap->hasResource('Zfdebug'))
		{
			throw new Exception('Resource Zfdebug not found');
			return;
		}
			
		$zfdebug = $bootstrap->getResource('Zfdebug');
		
		$this->_text = $zfdebug->getPlugin('Text');
		
		if(!$this->_text)
		{
			$zfdebug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Text(array('tab' => self::TAB)));
			$this->_text = $zfdebug->getPlugin('Text');
		}
			
	}

	public function direct($message, $echo = false)
	{
		$this->_text->setPanel(Zend_Debug::dump($message, null, $echo));
	}
}