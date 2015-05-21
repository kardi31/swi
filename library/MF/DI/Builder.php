<?php 

class Ext_DI_Builder
{
	protected $_config;
	protected $_parameters;
	protected $_definitions;
	protected $_relations;
	
	public function __construct(array $config = array()) {
		$this->_config = $config;
	}
	
	public function getParameters() {
		if(null === $this->_parameters) {
			if(array_key_exists('parameters', $this->_config) && is_array($this->_config['parameters'])) {
				$this->_parameters = $this->_config['parameters'];	
			}
		}
		return $this->_parameters;
	}

	public function getDefinitions() {
		if(null === $this->_definitions) {
			$this->_buildDefinitions();
		}
		return $this->_definitions;
	}
	
	public function getRelations() {
		if(null === $this->_relations) {
			if(array_key_exists('relations', $this->_config) && is_array($this->_config['relations'])) {
				$this->_relations = $this->_config['relations'];	
			}
		}
		return $this->_relations;
	}
	
	protected function _addDefinition($name, $definition) {
		$key = strtolower($name);	
		$this->_definitions[$key] = $definition;
	}

	protected function _buildDefinitions() {
		if(!empty($this->_config) && is_array($this->_config) && array_key_exists('definitions', $this->_config)) {
			foreach($this->_config['definitions'] as $name => $data) {
				$key = strtolower($name);
				if(is_array($data)) {
					if(array_key_exists('class', $data)) {
						$definition = new Ext_DI_Definition();
						$definition->setClass($data['class']);
						if(array_key_exists('arguments', $data) && is_array($data['arguments'])) {
							foreach($data['arguments'] as $a) {
								if(array_key_exists('type', $a) && array_key_exists('id', $a)) {
									$argument = new Ext_DI_Definition_Method_Argument();
									$argument->setType($a['type']);
									$argument->setId($a['id']);
									$definition->addArgument($argument);	
								}
							}
						}
						if(array_key_exists('methods', $data) && is_array($data['methods'])) {
							foreach($data['methods'] as $m) {
								if(array_key_exists('name', $m) && array_key_exists('args', $m)) {
									$method = new Ext_DI_Definition_Method();
									$method->setName($m['name']);
									if(is_array($m['args'])) {
										foreach($m['args'] as $a) {
											$arg = new Ext_DI_Definition_Method_Argument();
											$arg->setType($a['type']);
											$arg->setId($a['id']);
											$method->addArg($arg);
										}
									}
									$definition->addMethod($method);
								}
							}
						}
						if(array_key_exists('labels', $data) && is_array($data['labels'])) {
							foreach($data['labels'] as $l) {
								if(array_key_exists('type', $l) && array_key_exists('params', $l) && is_array($l['params'])) {
									$label = Ext_DI_Definition_Label::factory($l['type'], $l['params']);
									$definition->addLabel($label);
								}
							}
						}
						$this->_addDefinition($key, $definition);		
					}
				}
			}
		}
	}
}