<?php 

class Ext_DI_Builder_Filter_Labels extends Ext_DI_Builder_Filter
{
	protected function _filter() {
		$definitions = $this->_definitions;

		foreach($definitions as $definition) {
			if($definition->hasLabels()) {
				$labels = $definition->getLabels();

				foreach($labels as $label) {
					if($label->getType() == Ext_DI_Definition_Label::TYPE_EVENT_LISTENER) {
						$this->_filterEventListener($definition, $label);
					}
					/*  */
				}
			}
		}
	}
	
	protected function _filterEventListener($definition, $label) {
		if(null !== $eventDispatcherDefinition = $this->_findEventDispatcher()) {
			// fetching existing argument of event dispatcher definition configuration			
			$arguments = $eventDispatcherDefinition->getArguments();
			$argument = (empty($arguments)) ? new Ext_DI_Definition_Method_Argument() : $arguments[0];
			// creating new listener config
			$params = $label->getParams();
			$listener = array('call' => array($definition->getClass(), $params['method']), 'priority' => $params['priority']);
			$argument->setType(Ext_DI_Definition_Method_Argument::TYPE_LITERAL);
			// passing the listener to event dispatcher definition
			$value = $argument->getId();
			$value[$params['event']][] = $listener;
			$argument->setId($value);
			// saving event dispatcher definition argument
			$eventDispatcherDefinition->clearArguments();
			$eventDispatcherDefinition->addArgument($argument);
		}
	}
	
	protected function _findEventDispatcher() {
		$definitions = $this->getDefinitions();
		foreach($definitions as $definition) {
			if($definition->hasLabel(Ext_DI_Definition_Label::TYPE_EVENT_DISPATCHER)) {
				return $definition;
			}
		}
		return null;
	}
}