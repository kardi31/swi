<?php 

class Ext_DI_Definition_Label
{
	const TYPE_EVENT_LISTENER = 'event_listener';
	const TYPE_EVENT_DISPATCHER = 'event_dispatcher';
	
	public static function factory($type, array $params) {
		$file = preg_replace(array('/(^\w{1})/e', '/_(\w{1})/e'), 'strtoupper("$1")', $type);

		$class = 'Ext_DI_Definition_Label_' . $file;
		if(class_exists($class)) {
			return new $class($params);
		} else {
			throw new Exception("Undefined label type: $type");
		}
	}

}