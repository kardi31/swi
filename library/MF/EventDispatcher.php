<?php 

class MF_EventDispatcher
{
	protected $_events = array();
	protected $_event;
	
	public function __construct($config) {
		$this->_events = $config;
	}

	public function addEventListener($name, $call, $priority = 0) {
		$listener = array();
		$listener['call'] = $call;
		$listener['priority'] = $priority;
		$this->_events[$name][] = $listener;
	}
	
	public function dispatch($name, MF_EventDispatcher_Event $event) {
		$this->_event = $event;
		$listeners = $this->_events[$name];
		uasort($listeners, array($this, '_sortListenersByPriority')); 
		foreach($listeners as $listener) {
			if($this->_event->isPropagationStopped()) {
				break;
			}
			$call;
			if(is_array($listener['call'])) {
				if(count($listener['call']) == 2) {
					if(is_string($listener['call'][0])) { // callable passed in as string
						if(class_exists($listener['call'][0])) {
							$class = $listener['call'][0];
							$object = new $class();
							$call = array($object, $listener['call'][1]);		
						}
					} elseif(is_object($listener['call'][0])) { // callable passed as object
						$call = array($listener['call'][0], $listener['call'][1]);
					}
				} elseif(count($listener['call']) >= 3) { 
					if($listener['call'][2] == true) { // call statically if third array element is true
						$call = array($listener['call'][0], $listener['call'][1]);		
					}
				}
			} elseif($listener['call'] instanceof Closure) {
				$call = $listener['call'];
			}
			if(null !== $call && is_callable($call)) {
				call_user_func($call, $this->_event);
			}
		}
	}
	
	protected function _sortListenersByPriority($listener1, $listener2) {
		if($listener1['priority'] == $listener2['priority']) {
			return 0;
		}
		return ($listener1['priority'] > $listener2['priority']) ? -1 : 1;
	}
}