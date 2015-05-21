<?php 
class MF_View_Helper_Messages extends Zend_View_Helper_Abstract
{
	protected $_view;
	protected $_session;
	
	public function messages() {
		if(!isset($this->_view->messages)) {
			$this->_view->messages = $this;
			$this->_session = new Zend_Session_Namespace('Messages');
		}
		return $this->_view->messages;
	}
	
	public function render($partial = 'messages.phtml') {
		$messages = $this->get();
		if(!$this->count()) {
			return;
		}
		$this->clean();
		return $this->_view->partial($partial, array('messages' => $messages));
	}
	
    public function get($type = null) {
        $messages = $this->_session->messages;
        if(null != $type && isset($messages[$type])) {
            return $messages[$type];
        } else {
            return $messages;
        }
    }
    
	public function add($message, $type = 'success') {
		$messages = $this->_session->messages;
        $messages[$type][] = $message;
        $this->_session->messages = $messages;
        return $this;
	}
	
    public function count() {
        return count($this->_session->messages);
    }
    
    public function clean() {
        $this->_session->messages = array();
        return $this;
    }
    
	public function setView(Zend_View_Interface $view) {
        $this->_view = $view;
    }

	
}