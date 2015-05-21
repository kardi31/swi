<?php 

class MF_EventDispatcher_Event
{
	private $_propagationStopped = false;
	
	public function stopPropagation() {
		$this->_propagationStopped = true;
	} 
	
	public function isPropagationStopped() {
		return $this->_propagationStopped;
	}
}