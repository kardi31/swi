<?php

class MF_Controller_Action_Helper_Sorter extends Zend_Controller_Action_Helper_Abstract
{
	/**
	 * Nazwa parametru sortowania
	 * 
	 * @var string
	 */
	protected $_sort_param_name;
	
	/**
	 * Wartość parametru sortowania
	 * 
	 * @var string
	 */
	protected $_sort_param;
	
	/**
	 * Określa pozycję aktywnej kolumny
	 * 
	 * @var integer
	 */
	protected $_column_param;
	
	/**
	 * Określa kierunek sortowania aktywnej kolumny
	 * 
	 * @var string
	 */
	protected $_direction_param;
	
	/**
	 * Określa deklarowaną ilość kolumn. Parametr musi być ustawiony w przypadku 
	 * korzystania z metody toArray()
	 * 
	 * @var integer
	 */
	protected $_number_of_columns;
	
	/**
	 * Nazwa aktywnej klasy
	 * 
	 * @var string
	 */
	protected $_active_element_class_name = '';
	
	/**
	 * Nazwa nieaktywnej klasy
	 * 
	 * @var string
	 */
	protected $_inactive_element_class_name = '';
	
	/**
	 * Operator łączenia parametru "sort" i jego wartości
	 * 
	 * @var string
	 */
	protected $_oparator = '=';
	
	/**
	 * Symbol przypisany do parametru kolumny z sortowaniem typu ASC
	 * 
	 * @var string
	 */
	protected $_asc_symbol = 'a';
	
	/**
	 * Symbol przypisany do parametru kolumny z sortowaniem typu DESC
	 * 
	 * @var string
	 */
	protected $_desc_symbol = 'd';

	/**
	 * Metoda zwraca ciąg zawierający nazwę parametru sort oraz wartość przedzielone operatorem.
	 * Jeśli podany argument jest równy pozycji aktywnej kolumny, zwracany ciąg zawiera
	 * parametr wskazujący odwrotne sortowanie. W przyciwnym razie zwracany ciąg wskazuje
	 * na kolumnę z podanym indeksem i wskazuje na domyślne sortowanie.
	 * 
	 * @param integer $column_number
	 */
	public function get($column_number)
	{
		if(!is_string($this->_sort_param))
			return $column_number . $this->_asc_symbol; 

		$result = $this->_sort_param_name . $this->_oparator . $column_number;
				
		if($this->isActive($column_number))
		{
			if($this->_getDirectionParam() == $this->_asc_symbol)
				return $result .= $this->_desc_symbol;
			if($this->_getDirectionParam() == $this->_desc_symbol)
				return $result .= $this->_asc_symbol;
		}
		else 
		{
			return $result .= $this->_asc_symbol;
			//todo: dodać wybór domyślnego sortowania dla każdej z kolumn
		}
		
	}
	
	/**
	 * Metoda działa tak, jak metoda get() z tą różnicą, że zamiast ciągu zawierającego 
	 * nazwę parametru i wartość, zwraca tablicę, w której kluczem jest nazwa parametru
	 * a wartością pozycja kolumny i kierunek sortowania dla podanego indeksu.
	 * 
	 * @param array $column_number
	 */
	public function getArray($column_number)
	{
		if(!is_string($this->_sort_param))
			return array($this->_sort_param_name => $column_number . $this->_asc_symbol);
			
		if($this->isActive($column_number))
		{
			if($this->_getDirectionParam() == $this->_asc_symbol)
				return array($this->_sort_param_name => $column_number . $this->_desc_symbol);
			if($this->_getDirectionParam() == $this->_desc_symbol)
				return array($this->_sort_param_name => $column_number . $this->_asc_symbol);
		}
		else
		{
			return array($this->_sort_param_name => $column_number . $this->_asc_symbol);
		}
	}
	
	/**
	 * Metoda rozpoznaje, czy kolumna z podaną pozycją jest aktywna
	 * 
	 * @param integer $column_number
	 */
	public function isActive($column_number)
	{
		return $column_number == $this->_getColumnParam();
	}
	
	/**
	 * Zwraca odpowiednią klasę css dla kolumny z podaną pozycją w zależności, 
	 * czy kolumna jest aktywna czy nie.
	 * 
	 * @param integer $column_number
	 */
	public function get_css_class($column_number)
	{
		return ($this->isActive($column_number))
			? $this->_active_element_class_name
			: $this->_inactive_element_class_name;
		
	}
	
	public function __call($fnc, $args)
	{
		if($fnc == 'class')
		{
			return $this->get_css_class($args[0]);
		}
	}
	
	/**
	 * Zwraca indeks aktywnej kolumny na podstawie podanego parametru sort
	 * 
	 */
	protected function _getColumnParam()
	{
		if(!is_integer($this->_column_param))
		{
			preg_match('/^(\d+)(a|d?)$/', $this->_sort_param, $matches);
			if(isset($matches[1]) && is_string($matches[1]))
				$this->_column_param = (integer) $matches[1];
		}

		return $this->_column_param;
	}
	
	/**
	 * Zwraca kierunek sortowania aktywnej kolumny na podstawie podanego parametru sort
	 * 
	 */
	protected function _getDirectionParam()
	{
		if(!is_string($this->_direction_param))
		{
			preg_match('/^(\d+)(a|d?)$/', $this->_sort_param, $matches);
			if(isset($matches[2]) && is_string($matches[2]))
				$this->_direction_param = $matches[2];
		}
		return $this->_direction_param;
	}
	
	/**
	 * Ustawia nazwę i wartość parametru sort
	 * 
	 * @param string $sort_param_name
	 * @param string $sort_param
	 */
	public function setSortParam($sort_param_name = '', $sort_param = '')
	{
		$this->_sort_param_name = $sort_param_name;
		$this->_sort_param = $sort_param;
		return $this;
	}
	
	/**
	 * Ustawia nazwę klasy aktywnego elementu/aktywnej kolumny
	 * 
	 * @param string $class_name
	 */
	public function setActiveElementClassName($class_name)
	{
		$this->_active_element_class_name = $class_name;
		return $this;
	}

	/**
	 * Ustawia nazwę klasy nieaktywnego elementu/nieaktywnej kolumny
	 * 
	 * @param string $class_name
	 */
	public function setInactiveElementClassName($class_name)
	{
		$this->_inactive_element_class_name = $class_name;
		return $this;
	}
	
	/**
	 * Zwraca sorter w postaci tablicy
	 * 
	 */
	public function toArray()
	{
		$result = array();
		if(!is_int($this->_number_of_columns))
			return $result;
			
		for($i=1; $i <= $this->_number_of_columns; $i++)
		{
			$result[$i]['simple'] = $this->get($i);
			$result[$i]['array'] = $this->getArray($i);
			$result[$i]['class'] = $this->get_css_class($i);
		}
		return $result;
	}
	
	/**
	 * Ustawia operator łączenia nazwy parametru i wartości
	 * 
	 * @param string $oparator
	 */
	public function setOparator($oparator)
	{
		$this->_oparator = $oparator;
		return $this;
	}
	
	/**
	 * Ustawia liczbę kolumn do sortowania
	 * Potrzebne tylko do metody toArray()
	 * 
	 * @param integer $number_of_columns
	 */
	public function setNumberOfColumns($number_of_columns)
	{
		$this->_number_of_columns = $number_of_columns;
		return $this;
	}
}