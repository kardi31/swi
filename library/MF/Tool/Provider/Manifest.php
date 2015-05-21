<?php 

require_once 'MF/Tool/Provider/TestProvider.php';

class MF_Tool_Provider_Manifest implements Zend_Tool_Framework_Manifest_Interface, 
										   Zend_Tool_Framework_Manifest_ProviderManifestable, 
										   Zend_Tool_Framework_Manifest_MetadataManifestable
{
	public function getProviders() {
		return array(
			new MF_Tool_Provider_TestProvider()
		);
	}   	
	
	public function getMetadata() {
		return array(
			new Zend_Tool_Framework_Metadata_Basic(
				array(
					'name' => 'argv',
					'value' => $_SERVER['argv'] 
				)
			)
		);
	}
}