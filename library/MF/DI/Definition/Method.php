<?php 

class Ext_DI_Definition_Method
{
	protected $_name;
	protected $_args = array();
	
	public function setName($name) {
		$this->_name = $name;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function addArg($arg) {
		$this->_args[] = $arg;
	}
	
	public function getArgs() {
		return $this->_args;
	}
}