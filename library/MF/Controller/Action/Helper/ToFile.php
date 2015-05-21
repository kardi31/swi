<?php 

class MF_Controller_Action_Helper_ToFile extends Zend_Controller_Action_Helper_Abstract
{
	protected $_file;
	protected $_request;
	protected $_date;
		
	public function init()
	{
		$this->_file = fopen(APPLICATION_PATH . '/file.txt', 'w+');
		$this->_request = $this->getActionController()->getRequest();
		$this->_date = new DateTime();
	}
	
	public function direct($data = null)
	{
		$string = $this->_date->format('H:i:s d-m-Y') . "\n\n";
		if(is_array($data))
		{
	    	foreach($data as $param => $value)
	    	{
	    		$string .= $param . ' : ' . $value . "\n";
	    	}
		}
		elseif(is_string($data) || is_numeric($data))
		{
			$string .= $data . "\n";
		}
		elseif($data !== null) 
		{
			$string = 'Wrong data type!' . "\n";
		}	
		$string .= "\n" . 'Request:' . "\n";
		foreach($this->_request->getParams() as $param => $value)
		{
			$string .= $param . ' : ' . $value . "\n";
		}
		fwrite($this->_file, $string);
	    fclose($this->_file);
	}
}