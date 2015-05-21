<?php

/**
 * Menu_DataTables_MenuItem
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Menu_DataTables_MenuItem extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Menu_DataTables_Adapter_MenuItem';
    }
}

