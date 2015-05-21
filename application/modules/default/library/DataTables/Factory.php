<?php

/**
 * Factory
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_DataTables_Factory {
    
    public static function factory(array $options) {
        if(empty($options)) {
            throw new Exception('No options included');
        }
        
        if(!isset($options['class']) || !is_subclass_of($options['class'], 'Default_DataTables_DataTablesAbstract')) {
            throw new Exception('Adapter class does not extend Default_DataTables_DataTablesAbstract');
        }
        
        if(!class_exists($options['class'])) {
            throw new Exception('Adapter class not instantiable');
        }
        
        if(!isset($options['request']) || !$options['request'] instanceof Zend_Controller_Request_Abstract) {
            throw new Exception('Request not set');
        }
        
        if(!isset($options['table']) || !$options['table'] instanceof Doctrine_Table) {
            throw new Exception('Table not set');
        }
        
        $class = $options['class'];
        unset($options['class']);
        $object = new $class();
        $adapterClass = $object->getAdapterClass();
        $adapter = new $adapterClass($options['request'], $options['table']);
        if(isset($options['columns']))
            $adapter->setColumns($options['columns']);
        if(isset($options['searchFields'])) {
            $adapter->setSearchFields($options['searchFields']);
        }
        $object->setAdapter($adapter);
        return $object;
    }
}

