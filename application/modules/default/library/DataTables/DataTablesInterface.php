<?php

/**
 * DataTablesInterface
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
interface Default_DataTables_DataTablesInterface {
    
    public function setAdapter(Default_DataTables_Adapter_AdapterInterface $adapter);
    public function getAdapterClass();
    public function getResult();
}

