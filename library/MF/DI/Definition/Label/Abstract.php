<?php 

abstract class Ext_DI_Definition_Label_Abstract
{
	protected $_type;
	protected $_params;
	
	public function __construct($params) {
		$this->_params = $params;
	}
	
	public function setType($type) {
		$this->_type = $type;
	}
	
	public function getType() {
		return $this->_type;
	}
	
	public function setParams(array $params) {
		$this->_params = $params;
	}
	
	public function getParams() {
		return $this->_params;
	}
} 