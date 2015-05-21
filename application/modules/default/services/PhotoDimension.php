<?php

/**
 * Setting
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Default_Service_PhotoDimension extends MF_Service_ServiceAbstract {
    
    protected $photoDimensions;
    
    public function init() {
        $this->photoDimensions = Doctrine_Core::getTable('Default_Model_Doctrine_PhotoDimensions');
    }
    
    public function setSetting($id, $value) {
        if(!$setting = $this->getSetting($id)) {
            $setting = $this->settingTable->getRecord();
            $setting->setId($id);
        }
        $setting->setValue($value);
        $setting->save();
    }
    
    public function getDimension($id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        
        // admin panel size
        $dimensionArray = array(
            '126x126'
        );
        
        // jeżeli nie ma wymiarów to defaultowo ustawiane są 200x200
        $dimension = $this->photoDimensions->findOneById($id, $hydrationMode);
        if(strlen($dimension->width)||strlen($dimension->height))
            $dimensionArray[1] = $dimension->width."x".$dimension->height;
        else
            $dimensionArray[1] = "200x200";
        
        return $dimensionArray;
    }
    
     public function getElementDimension($id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
                
        // jeżeli nie ma wymiarów to defaultowo ustawiane są 200x200
        $dimension = $this->photoDimensions->findOneById($id, $hydrationMode);
        if(strlen($dimension->width)||strlen($dimension->height))
            $dimensionResult = $dimension->width."x".$dimension->height;
        else
            $dimensionResult = "200x200";
        
        return $dimensionResult;
    }
    
    public function getElementDimensionWidth($id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
                
        // jeżeli nie ma wymiarów to defaultowo ustawiane są 200x200
        $dimension = $this->photoDimensions->findOneById($id, $hydrationMode);
        if(!empty($dimension->width))
            $dimensionResult = $dimension->width;
        else
            $dimensionResult = "200";
        
        return $dimensionResult;
    }
    
    public function getAllSettings($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->settingTable->findAll($hydrationMode);
    }
    
    public function getAllAvailableSettings() {
        $result = array();
        $translator = $this->getServiceBroker()->get('translate');
        $availableSettings = Default_Model_Doctrine_Setting::getAvailableSettings();
        $settings = $this->getAllSettings();
        $settings = $settings->toKeyValueArray('id', 'value');
        foreach($availableSettings as $setting => $label) {
            $item = array();
            $item['id'] = $setting;
            $item['label'] = $translator->translate($label);
            if(array_key_exists($setting, $settings)) {
                $item['value'] = $settings[$setting];
            }
            $result[] = $item;
        }
        return $result;
    }
    
    public function getSettingForm() {
        $form = new Default_Form_Setting();
        $translator = $this->getServiceBroker()->get('translate');
        $availableSettings = Default_Model_Doctrine_Setting::getAvailableSettings();
        $settings = $this->getAllSettings();
        $settings = $settings->toKeyValueArray('id', 'value');
        foreach($availableSettings as $setting => $label) {
            if($setting == "displayed_main_box"){
                $settingElement = $form->createElement('select', $setting);
                $settingElement->setDecorators(Admin_Form::$tableRowDecorators);
                $settingElement->setLabel($translator->translate($label));
                $settingElement->setAttrib('class', 'span12');
                $options = array(
                    'promocje' => 'Promocje',
                    'nowosci' => 'Nowości',
                    'wyroznione' => 'Wyróżnione'
                );
                
                $settingElement->addMultiOptions($options);
                $settingElement->setValue($settings[$setting]);
                $form->addElement($settingElement);
                continue;
            }
            $settingElement = $form->createElement('text', $setting);
            $settingElement->setDecorators(Admin_Form::$tableRowDecorators);
            $settingElement->setLabel($translator->translate($label));
            $settingElement->setAttrib('class', 'span12');
            if(array_key_exists($setting, $settings)) {
                $settingElement->setValue($settings[$setting]);
            }
            $form->addElement($settingElement);
        }
        return $form;
    }

    public function saveSettingsFromArray(array $data) {
        $availableSettings = Default_Model_Doctrine_Setting::getAvailableSettings();
        $settings = $this->getAllSettings();
        $settings = $settings->toKeyValueArray('id', 'value');
        $translator = $this->getServiceBroker()->get('Zend_Translate');
        foreach($availableSettings as $setting => $label) {
            if(in_array($setting, array_keys($data))) {
                $this->setSetting($setting, $data[$setting]);
            }
        }
    }
}

