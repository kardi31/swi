<?php

class MF_Application_Resource_Resourceautoloader extends Zend_Application_Resource_ResourceAbstract
{
	public function init()
	{
		$options = $this->getOptions();

        if (isset($options['basePath']) && isset($options['namespace']))
        {
            $autoloader = new Zend_Loader_Autoloader_Resource(array(
                'basePath' => $options['basePath'],
                'namespace' => $options['namespace'],
            ));
            
            if (isset($options['resourceType']))
            {
                foreach ($options['resourceType'] as $type => $config)
                {
                    if (isset($config['namespace']) && isset($config['path']))
                    {
                        $autoloader->addResourceType($type, $config['path'], $config['namespace']);
                    }

                }
            }


		return $autoloader;
            
        }
		        
	}
}