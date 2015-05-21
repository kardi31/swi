<?php

class User_Form_Order extends Admin_Form
{
    public function init() {
        $deliveryTypeService = MF_Service_ServiceBroker::getInstance()->getService('Order_Service_DeliveryType');
        $deliveryTypes = $deliveryTypeService->getDeliveryTypes();
        $paymentTypeService = MF_Service_ServiceBroker::getInstance()->getService('Order_Service_PaymentType');
        $paymentTypes = $paymentTypeService->getPaymentTypes();
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $firstName = $this->createElement('text', 'first_name');
        $firstName->setLabel('First name');
        $firstName->setRequired();
        $firstName->addValidators(array(
            array('alnum', false, array('allowWhiteSpace' => true))
        ));
        $firstName->addFilters(array(
            array('alnum', array('allowWhiteSpace' => true))
        ));
        $firstName->setDecorators(self::$textDecorators);
        $firstName->setAttrib('class', 'span8');

        $lastName = $this->createElement('text', 'last_name');
        $lastName->setLabel('Last name');
        $lastName->setRequired();
        $lastName->addValidators(array(
            array('alnum', false, array('allowWhiteSpace' => true))
        ));
        $lastName->addFilters(array(
            array('alnum', array('allowWhiteSpace' => true))
        ));
        $lastName->setDecorators(self::$textDecorators);
        $lastName->setAttrib('class', 'span8');

       
        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('Email');
        $email->setValidators(array('EmailAddress'));
        $email->setDecorators(self::$textDecorators);
        $email->setRequired();
        $email->setAttrib('class', 'span8');

        $delivery = $this->createElement('select', 'delivery_type_id');
        $delivery->setLabel('Sposób dostawy');
        $delivery->setRequired(true);
        $delivery->setDecorators(self::$textDecorators);
        $delivery->setAttrib('class', 'span8');
        foreach($deliveryTypes as $type):
            $delivery->addMultiOption($type['id'],$type['name']." - ".$type['price']);
        endforeach;
        
        $payment = $this->createElement('select', 'payment_type_id');
        $payment->setLabel('Sposób płatności');
        $payment->setRequired(true);
        $payment->setDecorators(self::$textDecorators);
        $payment->setAttrib('class', 'span8');
        foreach($paymentTypes as $type):
            $payment->addMultiOption($type['id'],$type['name']);
        endforeach;
        
        $address = $this->createElement('text', 'address');
        $address->setLabel('Address');
        $address->setRequired(true);
        $address->setDecorators(self::$textDecorators);
        $address->setAttrib('class', 'span8');
        
        $postalCode = $this->createElement('text', 'postal_code');
        $postalCode->setLabel('Kod pocztowy');
        $postalCode->setDecorators(self::$textDecorators);
        $postalCode->setValidators(array('PostCode'));
        $postalCode->setAttrib('class', 'span8');
        
        $city = $this->createElement('text', 'city');
        $city->setLabel('City');
        $city->setRequired(true);
        $city->setDecorators(self::$textDecorators);
        $city->setAttrib('class', 'span8');
        
        $province = $this->createElement('select', 'province_id');
        $province->setLabel('Province');
        $province->setRequired(true);
        $province->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        
        $wojewodztwa = array(
           1=>'dolnośląskie',
           2=>'kujawsko-pomorskie',
           3=>'lubelskie',
           4=>'lubuskie',
           5=>'łódzkie',
           6=>'małopolskie',
           7=>'mazowieckie',
           8=>'opolskie',
           9=>'podkarpackie',
           10=>'podlaskie',
           11=>'pomorskie',
           12=>'śląskie',
           13=>'świętokrzyskie',
           14=>'warmińsko-mazurskie',
           15=>'wielkopolskie',
           16=>'zachodniopomorskie');
        foreach($wojewodztwa as $key=>$woj):
            $province->addMultiOption($wojewodztwa[$key],$wojewodztwa[$key]);
        endforeach;
        
//        $nip = $this->createElement('text', 'nip');
//        $nip->setLabel('NIP');
//        $nip->setRequired(false);
//        $nip->setDecorators(self::$textDecorators);
//        $nip->setAttrib('class', 'span8');
//        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $firstName,
            $lastName,
            $address,
            $postalCode,
            $city,
            $province,
            $email,
            //$nip,
            $delivery,
            $payment,
            $submit
        ));
    }
}