<?php

/**
 * Setting
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Service_Setting extends MF_Service_ServiceAbstract {
    
    protected $settingTable;
    
    public function init() {
        $this->settingTable = Doctrine_Core::getTable('Default_Model_Doctrine_Setting');
    }
    
    public function setSetting($id, $value) {
        if(!$setting = $this->getSetting($id)) {
            $setting = $this->settingTable->getRecord();
            $setting->setId($id);
        }
        $setting->setValue($value);
        $setting->save();
    }
    
    public function getSetting($id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->settingTable->findOneById($id, $hydrationMode);
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

