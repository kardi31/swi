<?php 

class Ext_DI_Definition_Method_Argument
{
	const TYPE_PARAMETER = 'parameter';
	const TYPE_SERVICE = 'service';
	const TYPE_LITERAL = 'literal';
	
	protected $_type;
	protected $_id;
	
	public function setType($type) {
		$this->_type = $type;
	}
	
	public function getType() {
		return $this->_type;
	}
	
	public function setId($id) {
		$this->_id = $id;
	}

	public function getId() {
		return $this->_id;
	}
}