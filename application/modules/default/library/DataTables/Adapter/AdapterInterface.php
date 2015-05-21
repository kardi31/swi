<?php

/**
 * AdapterInterface
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
interface Default_DataTables_Adapter_AdapterInterface {

    public function getTable();
    public function getQuery();
    public function getData();
    public function getColumns();
    public function getSearchFields();
}

