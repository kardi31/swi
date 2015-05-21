<?php

/**
 * User_Form_Profile
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_Form_Profile extends User_BootstrapForm
{
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $province = $this->createElement('select', 'province_id');
        $province->setLabel('Province');
        $province->setDecorators(self::$bootstrapElementDecorators);
        $province->setRequired(true);
        
        $city = $this->createElement('select', 'city_id');
        $city->setLabel('City');
        $city->setDecorators(self::$bootstrapElementDecorators);
        $city->setRequired(true);
        
        $companyName = $this->createElement('text', 'company_name');
        $companyName->setLabel('Company name');
        $companyName->setDecorators(self::$bootstrapElementDecorators);
        $companyName->setRequired(true);
        
        $address = $this->createElement('text', 'address');
        $address->setLabel('Address');
        $address->setDecorators(self::$bootstrapElementDecorators);
        $address->setRequired(true);
        
        $nip = $this->createElement('text', 'nip');
        $nip->setLabel('NIP');
        $nip->setDecorators(self::$bootstrapElementDecorators);
        $nip->setRequired(true);
        
        $website = $this->createElement('text', 'website');
        $website->setLabel('Website');
        $website->setDecorators(self::$bootstrapElementDecorators);
        $website->setRequired(true);
        
        $proxyName = $this->createElement('text', 'proxy_name');
        $proxyName->setLabel('Proxy name');
        $proxyName->setDecorators(self::$bootstrapElementDecorators);
        $proxyName->setRequired(true);
        
        $tags = $this->createElement('text', 'tags');
        $tags->setLabel('Tags');
        $tags->setDecorators(self::$bootstrapElementDecorators);
        $tags->setDescription('np. kredyty mieszkaniowe, mieszkania, Warszawa');

        $about = $this->createElement('textarea', 'about');
        $about->setLabel('Short description');
        $about->setDecorators(self::$bootstrapElementDecorators);
        
        $submit = $this->createElement('submit', 'submit');
		$submit->setLabel('Save');
        $submit->setDecorators(self::$bootstrapElementDecorators);
        $submit->removeDecorator('Label');
		$submit->setAttrib('type', 'submit');
        
        $this->setElements(array(
            $id,
            $province,
            $city,
            $companyName,
            $address,
            $nip,
            $website,
            $proxyName,
            $tags,
            $about,
            $submit
        ));
    }
}

