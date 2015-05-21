<?php

/**
 * AdapterAbstract
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
abstract class Default_DataTables_Adapter_AdapterAbstract implements Default_DataTables_Adapter_AdapterInterface {
    
    protected $request;
    protected $table;
    protected $columns = array();
    protected $searchFields = array();
    protected $query;
    protected $data;
    
    public function __construct(Zend_Controller_Request_Abstract $request, Doctrine_Table $table) {
        $this->request = $request;
        $this->table = $table;
    }
    
    public function getTable() {
        return $this->table;
    }
    
    public function getQuery() {
        if(null == $this->query) {
            $q = $this->getBaseQuery();
            if($this->request->getParam('iDisplayLength')) {
                $q->limit($this->request->getParam('iDisplayLength'));
            }
            if($this->request->getParam('iDisplayStart')) {
                $q->offset($this->request->getParam('iDisplayStart'));
            }
            if(strlen($this->request->getParam('iSortCol_0'))) {
                $q = $this->returnSortQuery($q);
            }
            if($this->request->getParam('sSearch')) {
                $q = $this->returnSearchQuery($q);
            }
            $this->query = $q;
        }
        return $this->query->copy();
    }
    
    protected function getBaseQuery() {
        return $this->table->createQuery('x');
    }
    
    public function getData() {
        if(null == $this->data) {
            $this->data = $this->getQuery()->execute();
        }
        return $this->data;
    }
    
    public function setColumns(array $columns) {
        $this->columns = $columns;
    }
    
    public function getColumns() {
        return $this->columns;
    }
    
    public function setSearchFields(array $searchFields) {
        $this->searchFields = $searchFields;
    }
    
    public function getSearchFields() {
        return $this->searchFields;
    }
    
    protected function returnSortQuery(Doctrine_Query $q) {
        $columns = $this->getColumns();
        for($i=0; $i < intval($this->request->getParam('iSortingCols')); $i++) {
            if($this->request->getParam('bSortable_'.intval($this->request->getParam('iSortCol_'.$i))) == "true") {
                if(isset($columns[intval($this->request->getParam('iSortCol_'.$i))])) {
                    $order = $columns[intval($this->request->getParam('iSortCol_'.$i))];
                    $dir = $this->request->getParam('sSortDir_'.$i);
                    $q->addOrderBy("$order $dir");
                }
            }
        }

        return $q;
    }
    
    protected function returnSearchQuery(Doctrine_Query $q) {
        $phrase = $this->request->getParam('sSearch');
        $queryString = '';
        $queryParts = array();
        foreach($this->getSearchFields() as $field) {
            $queryParts[] = "$field LIKE ?";
            $queryString .= "$field LIKE ? "; 
        }
        $q->andWhere(implode(' OR ', $queryParts), array_fill(0, count($this->getSearchFields()), "%$phrase%"));
        return $q;
    }
}

