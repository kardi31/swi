<?php

class MF_Db_Table extends Zend_Db_Table_Abstract
{
	public function insert(array $data)
    {
    	$_data = array_intersect_key($data, array_combine($this->info('cols'), $this->info('cols')));
    	parent::insert($_data);
    }
    
    public function update(array $data, $where)
    {
    	if(gettype($where) == 'integer')
		{
			$_where = $this->getAdapter()->quoteInto('id = ?', $where);	
		} 
		elseif(gettype($where) == 'array')
		{
			foreach($where as $key => $value)
			{
				$_where[] = $this->getAdapter()->quoteInto($key . ' = ?', $value);
			}
		}
		if(!isset($_where))	
			throw new Exception('Wrong type od data passed along');
		
		unset($data['id']);
		$_data = array_intersect_key($data, array_combine($this->info('cols'), $this->info('cols')));
		
		return parent::update($_data, $_where);
    }
    
    public function delete($id)
    {
    	if(gettype($id) == 'integer')
		{
			$_where = $this->getAdapter()->quoteInto('id = ?', $id);	
		} 
		elseif(gettype($id) == 'array')
		{
			foreach($id as $key => $value)
			{
				$_where[] = $this->getAdapter()->quoteInto($key . ' = ?', $value);
			}
		}
		if(!isset($_where))
			throw new Exception('Wrong type od data passed along');
		 
		return parent::delete($_where);
    }
    
    public function getColumns()
    {
    	return $this->info('cols');
    }
}