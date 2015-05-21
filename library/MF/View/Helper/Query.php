<?php

class MF_View_Helper_Query extends Zend_View_Helper_Abstract
{
	public function query($data, $base_url = '')
	{
		$result = '';
		if('' != $base_url)
			$result .= $base_url;
			
		if(is_array($data) && count($data))
		{
			$result .= '?';
			foreach($data as $key => $value)
			{
				if(is_array($value))
				{
					foreach($value as $key2 => $value2)
					{
						if(is_array($value2))
						{
							$result .= http_build_query($value2);
						}
						elseif(is_string($key2) || is_string($value2))
						{
							$result .= http_build_query(array($key2 => $value2));
						}
					}
				}
				elseif(is_string($key) || is_string($value))
				{
					$result .= http_build_query(array($key => $value));
				}
			}
			
		}
			
		return $result;
	}

	
}