<?php

/**
 * Service
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Service_Service extends MF_Service_ServiceAbstract {
    
    protected $serviceTable;
    
    public function init() {
        $this->serviceTable = Doctrine_Core::getTable('Default_Model_Doctrine_Service');
    }
    
    public function setService($id, $value) {
        if(!$setting = $this->getService($id)) {
            $setting = $this->serviceTable->getRecord();
            $setting->setId($id);
        }
        $setting->setValue($value);
        $setting->save();
    }
    
    public function getService($id, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->serviceTable->findOneById($id, $hydrationMode);
    }
    
    public function getAllServices($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->serviceTable->findAll($hydrationMode);
    }
    
    public function getAllAvailableServices() {
        $result = array();
        $translator = $this->getServiceBroker()->get('translate');
        $availableServices = Default_Model_Doctrine_Service::getAvailableServices();
        $settings = $this->getAllServices();
        $settings = $settings->toKeyValueArray('id', 'value');
        foreach($availableServices as $setting => $label) {
            $item = array();
            $item['id'] = $setting;
            $item['label'] = $translator->translate($label);
            $item['type'] = 'data';
            if(array_key_exists($setting, $settings)) {
                $item['value'] = $settings[$setting];
            }
            $result[] = $item;
        }
        return $result;
    }
    
    public function getServiceForm(Default_Model_Doctrine_Service $service = null) {
        $form = new Default_Form_ContactData();
        if(null != $service) { 
            $form->populate($service->toArray());
        }
        return $form;
    }

    public function saveServiceFromArray($values,$service_id=1) {
        unset($values['name']);
        if(!$service = $this->getService($service_id)) {
            $service = $this->serviceTable->getRecord();
        }
        $service->fromArray($values);
        $service->save();
        
        return $service;
    }
    public function sendMail($values,$mailTo,$mailerEmail)
    {
        $message = "Dane nadawcy:<br /><br />
            <b>Imie i nazwisko</b> ".$values['name']." ".$values['surname']."<br />
           <b>Email</b> ".$values['email']."<br /><b>Telefon</b> ".$values['phone']."<br /><br /> <br />".$values['message'];

        
         $mail = new Zend_Mail('UTF-8');
         $mail->setSubject($values['subject']);
         $mail->setFrom($mailerEmail,$values['name']." ".$values['surname']);
         $mail->setReplyTo($values['email']);
         $mail->addTo($mailTo);
         $mail->setBodyHtml($message);
         $mail->send();
         return true;
    }
    
}

