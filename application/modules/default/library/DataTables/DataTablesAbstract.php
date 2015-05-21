<?php

/**
 * DataTables
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
abstract class Default_DataTables_DataTablesAbstract implements Default_DataTables_DataTablesInterface {
    
    protected $adapter;
    
    public function setAdapter(Default_DataTables_Adapter_AdapterInterface $adapter) {
        $this->adapter = $adapter;
    }
    
    public function getAdapter() {
        return $this->adapter;
    }
    
    public function getResult() {
        return $this->getAdapter()->getData();
    }
    
    public function getTotal() {
        return $this->getAdapter()->getTable()->count();
    }
    
    public function getDisplayTotal() {
        return $this->getAdapter()->getQuery()->count();
    }
    
}

