<?php

/**
 * Slider_DataTables_Slide
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Slider_DataTables_Slide extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Slider_DataTables_Adapter_Slide';
    }
}

