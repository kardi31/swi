<?php 

abstract class Ext_DI_Builder_Filter
{
	protected $_parameters;
	protected $_definitions;
	protected $_relations;
	
	public function __construct($data) {
		if($data instanceof Ext_DI_Builder) {
			$this->_parameters = $data->getParameters();
			$this->_definitions = $data->getDefinitions();
			$this->_relations = $data->getRelations();
		} elseif(is_array($data)) {
			$this->_definitions = $data;
		} else {
			throw new Exception("Wrong definition's data tpye");
		}
		$this->_filter();
	}
	
	protected function _filter() {
		
	}
	
	public function getParameters() {
		return $this->_parameters;
	}
	
	public function getDefinitions() {
		return $this->_definitions;
	}
	
	public function getRelations() {
		return $this->_relations;
	}


}