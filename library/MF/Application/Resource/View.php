<?php 
class MF_Application_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
	protected $_view;
       
    public function init()
    {
        // Return view so bootstrap will store it in the registry
        return $this->getView();
    }
       
    public function getView()
    {
        if (null === $this->_view) 
        {
            $options = $this->getOptions();
        	$this->_view = new Zend_View($options);

            $title   = '';
            if (array_key_exists('title', $options)) 
            {
                $title = $options['title'];
                unset($options['title']);
            }
            
            $this->_view->headTitle($title);
    
            
            if (isset($options['doctype'])) {
            	$this->_view->doctype()->setDoctype(strtoupper($options['doctype']));
            	if (isset($options['charset']) && $this->_view->doctype()->isHtml5()) {
            		$this->_view->headMeta()->setCharset($options['charset']);
            	}
	        }
	         
	        if (isset($options['contentType'])) 
	        {
	        	$this->_view->headMeta()->appendHttpEquiv('Content-Type', $options['contentType']);
	        }

            if (isset($options['basePaths']) && is_array($options['basePaths']))
            {
                foreach ($options['basePaths'] as $path)
                {
                    $this->_view->addBasePath($path);
                }
            }
	        
	        if (isset($options['css']))
	        {
	        	if (is_array($options['css']))
	        	{
	        		foreach ($options['css'] as $files => $stylesheet)
		        	{
		        		$this->_view->headLink()->appendStylesheet($stylesheet);
		        	}
	        	}
	        	else
	        	{
	        		$this->_view->headLink()->appendStylesheet($options['css']);	
	        	}
	        }
	       
	        if (isset($options['js']))
	        {
	        	if (is_array($options['js']))
	        	{
	        		foreach ($options['js'] as $files => $script)
		        	{
		        		$this->_view->headScript()->appendFile($script);		
		        	}
	        	}
	        	else
	        	{
	        		$this->_view->headScript()->appendFile($options['js']);	
	        	}
	        	
	        }
	        
	        if (isset($options['helperPath']))
	        {
	        	if (is_array($options['helperPath']))
	        	{
	        		foreach($options['helperPath'] as $prefix => $path)
	        		{
	        			$this->_view->addHelperPath($path, $prefix);
	        		}
	        	}
	        }


            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
            
            $viewRenderer->setView($this->_view);
       
        }
        return $this->_view;
    }
}