<?php

class MF_Application_Resource_Doctrine2 extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        define('LIBRARY_PATH', realpath(__DIR__ . '/../../../'));
        
    	$bootstrapOptions = $this->getBootstrap()->getOptions();


        $options = $this->getOptions();
        $memcache = null;

        require_once LIBRARY_PATH . '/Doctrine/Common/ClassLoader.php';

        
        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine', LIBRARY_PATH);
		$classLoader->register();
		$classLoader = new \Doctrine\Common\ClassLoader('DoctrineExtensions', LIBRARY_PATH);
		$classLoader->register();
		$classLoader = new \Doctrine\Common\ClassLoader('Entities', realpath(APPLICATION_PATH . '/models'));
		$classLoader->register();
		$classLoader = new \Doctrine\Common\ClassLoader('Proxies', realpath(APPLICATION_PATH . '/models'));
		$classLoader->register();
		$classLoader = new \Doctrine\Common\ClassLoader('Repositories', realpath(APPLICATION_PATH . '/models'));
		$classLoader->register();
		$classLoader = new \Doctrine\Common\ClassLoader('Symfony', LIBRARY_PATH);
		$classLoader->register();
		
		
        $doctrineConfig = new \Doctrine\ORM\Configuration();
        if (!empty($options['options']['metadataCache'])) {
            $metaCache = new $options['options']['metadataCache']();
            if ($metaCache instanceof
                    \Doctrine\Common\Cache\MemcacheCache) {
                $memcache = new Memcache();
                $memcache->connect('localhost', 11211);
                $metaCache->setMemcache($memcache);
            }
            $doctrineConfig->setMetadataCacheImpl($metaCache);
        }
        if (!empty($options['options']['queryCache'])) {
            $queryCache = new $options['options']['queryCache']();
            if ($queryCache instanceof
                    \Doctrine\Common\Cache\MemcacheCache) {
                if (is_null($memcache)) {
                    $memcache = new Memcache();
                    $memcache->connect('localhost', 11211);
                }
                $queryCache->setMemcache($memcache);
            }
            $doctrineConfig->setQueryCacheImpl($queryCache);
        }

        $driverImpl =
            $doctrineConfig->newDefaultAnnotationDriver(
                array($options['paths']['entities']));
        $doctrineConfig->setMetadataDriverImpl($driverImpl);
        $doctrineConfig->setEntityNamespaces( $options['options']['entitiesNamespaces']);

        $doctrineConfig->setProxyDir($options['paths']['proxies']);
        $doctrineConfig->setProxyNamespace($options['options']['proxiesNamespace']);
        $doctrineConfig->setAutoGenerateProxyClasses((APPLICATION_ENV == "development"));

        $em =
            \Doctrine\ORM\EntityManager::create(
                $options['connections']['doctrine'],
                $doctrineConfig);

        $em->getEventManager()->addEventSubscriber(new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit('utf8','utf8_unicode_ci'));
        
        return $em;
    }
}