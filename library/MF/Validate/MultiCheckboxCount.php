<?php

class MF_Validate_MultiCheckboxCount extends Zend_Validate_Abstract
{
    const LESS_THAN_MIN   = 'lessThanMin';
    const MORE_THAN_MAX = 'moreThanMax';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::LESS_THAN_MIN => "Not enough elements selected",
        self::MORE_THAN_MAX => "Too many elements selected",
    );
    
    protected $formElement;
    protected $min;
    protected $max;
    
    public function __construct(Zend_Form_Element $formElement, $options) {
        
        $this->formElement = $formElement;
        
        if(isset($options['min'])) {
            $this->setMin($options['min']);
        }
        if(isset($options['max'])) {
            $this->setMax($options['max']);
        }
    }
    
    public function isValid($value) {

        if(is_integer($this->min) && (count($this->formElement->getValue()) < $this->min)) {
            $this->_error(self::LESS_THAN_MIN);
            return false;
        }
        if(is_integer($this->max) && (count($this->formElement->getValue()) > $this->max)) {
            $this->_error(self::MORE_THAN_MAX);
            return false;
        }

        return true;
    }
    
    public function setMin($min) {
        $this->min = $min;
        return $this;
    }
    
    public function setMax($max) {
        $this->max = $max;
        return $this;
    }
}