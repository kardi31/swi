<?php 

class Ext_DI_Definition
{
	protected $_class;
	protected $_arguments = array();
	protected $_methods = array();
	protected $_labels = array();
	
	public function __construct() {
		
	}

	public function setClass($class) {
		$this->_class = $class;
	}
	
	public function getClass() {
		return $this->_class;
	}
	
	public function clearArguments() {
		$this->_arguments = array();
	}
	
	public function addArgument(Ext_DI_Definition_Method_Argument $argument) {
		$this->_arguments[] = $argument;
	}
	
	public function getArguments() {
		return $this->_arguments;
	}
	
	public function addMethod(Ext_DI_Definition_Method $method) {
		$this->_methods[] = $method;
	}
	
	public function getMethods() {
		return $this->_methods;
	}
	
	public function addLabel($label) {
		$this->_labels[] = $label;
	}
	
	public function hasLabels() {
		return (!empty($this->_labels));
	}
	
	public function hasLabel($label) {
		foreach($this->_labels as $l) {
			if($l->getType() === $label) {
				return true;
			}
		}
		return false;
	}
	
	public function getLabels() {
		return $this->_labels;
	}

	public function findLabel($label) {
		foreach($this->_labels as $l) {
			if($l->getType() === $label) {
				return $l;
			}
		}
		return null;
	}
}