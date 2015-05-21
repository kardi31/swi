<?php 
	// https://github.com/beberlei/yadif
	// http://code.google.com/p/imind-php/wiki/Imind_Context

class Ext_DI_ServiceContainer
{
	protected $_builder;
	protected $_services = array();
	protected $_definitions = array();
	protected $_parameters = array();
	protected $_relations = array();
		
	public function __construct($builder = null) {
		if(null !== $builder) {
			$this->_builder = $builder;
			$this->_parameters = $builder->getParameters();
			$this->_definitions = $builder->getDefinitions();
			$this->_relations = $builder->getRelations();
		}
	}
	
	public function setBootstrap($bootstrap) {
		$this->_bootstrap = $bootstrap;
	}

	public function getBuilder() {
		return $this->_builder;
	}
	
	public function hasDefinition($name) {
		return array_key_exists(strtolower($name), $this->_definitions);
	}
	
	public function getDefinition($name) {
		return $this->_definitions[strtolower($name)];
	}
	
	public function addDefinition($name, $definition) {
		$key = strtolower($name);	
		$this->_definitions[$key] = $definition;
	}
	
	public function has($name) {
		$key = strtolower($name);
		return (array_key_exists($key, $this->_services) || array_key_exists($key, $this->_definitions));
	}
	
	public function get($name) {
		$key = strtolower($name);
		if(array_key_exists($key, $this->_services)) {
			return $this->_services[$key];
		} elseif(array_key_exists($key, $this->_definitions)) {
			$this->_prepareService($key);
			return $this->_services[$key];
		} elseif(null !== $this->_bootstrap && $this->_isResource($name)) {
			$this->_resolveRelations($name);
			$resource = $this->get('_bootstrap')->bootstrap($name)->getResource($name);
			return $resource;
		} else {
			throw new Exception("Service $name not found");
		}
	}

	protected function _resolveRelations($service) {
		$relations = $this->_getRelatedServices($service);
		foreach($relations as $resource) {
			$this->get($resource);
			if($this->_isResource($resource)) {
				$this->_bootstrap->bootstrap($resource);
			}
		}
	}
	
	protected function _getRelatedServices($service, $related = array()) {
		if(array_key_exists($service, $this->_relations)) {
			if(is_array($this->_relations[$service])) {
				foreach($this->_relations[$service] as $rel) {
					if(!in_array($rel, $related) && $rel != $service) {
						$related[] = $rel;
						$related = $this->_getRelatedServices($rel, $related);
					}
				}
			}
		}
		return array_unique($related);
	}

	protected function _isResource($name) {
		$bootstrapOptions = $this->_bootstrap->getOptions();
		return array_key_exists($name, $bootstrapOptions['resources']);
	}
	
	protected function _prepareService($key) {
		if($this->hasDefinition($key)) {
			$definition = $this->getDefinition($key);
			if(null !== $definition->getClass()) {
				$arguments = array();
				if(count($definition->getArguments())) {
					$definitionsArguments = $definition->getArguments();
					foreach($definitionsArguments as $a) {
						if(null !== $a->getType()) {
							if($a->getType() === Ext_DI_Definition_Method_Argument::TYPE_PARAMETER && array_key_exists($a->getId(), $this->_parameters)) {
								$arguments[] = $this->_parameters[$a->getId()];
							} elseif ($a->getType() === Ext_DI_Definition_Method_Argument::TYPE_SERVICE && $this->hasService($a->getId())) {
								$arguments[] = $this->get($a->getId());
							} elseif ($a->getType() === Ext_DI_Definition_Method_Argument::TYPE_LITERAL) {
								$arguments[] = $a->getId();
							}
						}	
					}
				}
				
				$class = $definition->getClass();
				if(is_array($class) && array_key_exists('id', $class)) {
					if(array_key_exists($class['id'], $this->_parameters)) {
						$class = $this->_parameters[$class['id']];
					}
				}
				$refClass = new ReflectionClass($class);

				$service = $refClass->newInstanceArgs($arguments);
				if(count($definition->getMethods())) {
					$definitionMethods = $definition->getMethods();
					foreach($definitionMethods as $method) {
						if(null !== $method->getName()) {
							if($refClass->hasMethod($method->getName())){
								$refMethod = new ReflectionMethod($definition->getClass(), $method->getName());
								$args = array();
								if(count($method->getArgs())) {
									foreach($method->getArgs() as $a) {
										if(null !== $a->getType()) {
											if($a->getType() === Ext_DI_Definition_Method_Argument::TYPE_PARAMETER && array_key_exists($a->getId(), $this->_parameters)) {
												$args[] = $this->_parameters[$a->getId()];
											} elseif ($a->getType() === Ext_DI_Definition_Method_Argument::TYPE_SERVICE && $this->hasService($a->getId())) {
												$args[] = $this->get($a->getId());
											}
										}	
									}
								}
								$refMethod->invokeArgs($service, $args);
							}
						}
					}
				}
				$this->_services[$key] = $service;
			}
		}
	}
	
	public function __isset($name) {
		$key = strtolower($name);
		return array_key_exists($key, $this->_services);
	}
	
	public function __set($name, $service) {
		$key = strtolower($name);
		$this->_services[$key] = $service;
	}
	
	public function __get($name) {
		$key = strtolower($name);
		if(array_key_exists($key, $this->_services)) {
			return $this->_services[$key];
		}
	}
}