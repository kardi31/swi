<?php 

/**
 * $this->_helper->js->assign('var', 'value');
 * $this->_helper->js->assign('array', array('a', 2));
 * $this->_helper->js->assign('array2', array('a' => 'b', 1 => 2));
 * $this->_helper->js->assign('alert(test3[1])');
 * 
 * 
 * 
 * Enter description here ...
 * @author Michał
 *
 */
class MF_Controller_Action_Helper_Js extends Zend_Controller_Action_Helper_Abstract
{
	protected $_params;
	
	public function init()
	{
		$this->_params = array();
	}
	
	public function postDispatch()
	{
		$this->getActionController()->getResponse()->appendBody($this->_getScript(), 'js');	
	}
	
	public function assign($key, $value = null)
	{
		if(null == $value)
		{
			$this->_params[] = $key;
		}
		elseif(is_string($value))
		{
			$this->_params[] = array($key, '"' . $value . '"');	
		}
		elseif(is_array($value))
		{
			if(($value !== array_values($value))) // is associative
			{
				foreach($value as $k => $v)
				{
					if(!is_numeric($k))
						$k = '"' . $k . '"';
					if(!is_numeric($v))
						$v = '"' . $v . '"';
					$pairs[] = ' ' . $k . ': ' . $v;
				}
				$arrayString = '{' . implode(',', $pairs) . ' }'; 	
				$this->_params[] = array($key, $arrayString);
			}
			else 
			{
				$arrayElems = array();
				foreach($value as $elem)
				{
					if(is_numeric($elem))
						$arrayElems[] = $elem;
					else
						$arrayElems[] = '"' . $elem . '"';
				}
				$arrayString = '[' . implode(',', $arrayElems) . ']';
				$this->_params[] = array($key, $arrayString);	
			}
			
		}
			
	}
	
	public function alert($message)
	{
		$this->_params[] = 'alert("' . $message . '")';
	}
		
	protected function _getScript()
	{
		$output = '<script type="text/javascript">' . "\n";
		foreach($this->_params as $key => $value)
		{
			if(is_array($value))
			{
				$output .= 'var ' . $value[0] . ' = ' . $value[1] . ';' . "\n";
			}
			elseif(is_string($value))
			{
				$output .= $value . ';' . "\n";
			}
		}
		$output .= '</script>' . "\n";
		
		return $output;
	}
	
}