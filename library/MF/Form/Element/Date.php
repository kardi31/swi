<?php

class MF_Form_Element_Date extends Zend_Form_Element_Xhtml
{
    public $helper = 'formDate';

    public function isValid ($value, $context = null)
    {
        return parent::isValid($value, $context);
    }

    public function getValue()
    {
        if(is_array($this->_value)) {
            $value = '';
            if(isset($this->_value['year']) && strlen($this->_value['year'])) {
                $value = $this->_value['year'];
                if(isset($this->_value['month']) && strlen($this->_value['month'])) {
                    $value = $value . '-' . $this->_value['month'];
                    if(isset($this->_value['day']) && strlen($this->_value['day'])) {
                        $value = $value . '-' . $this->_value['day'];
                    }
                }
            }

            return $value;
        }
        return parent::getValue();
    }
    
    public function setDay($value)
    {
        $this->_value['day'] = $value;
        return $this;
    }

    public function getDay()
    {
        return $this->_value['day'];
    }

    public function setMonth($value)
    {
        $this->_value['month'] = $value;
        return $this;
    }

    public function getMonth()
    {
        return $this->_value['month'];
    }

    public function setYear($value)
    {
        $this->_value['year'] = $value;
        return $this;
    }

    public function getYear()
    {
        return $this->_value['year'];
    }

    public function setValue($value)
    {
        if (is_int($value)) {
            $this->setDay(date('d', $value))
                 ->setMonth(date('m', $value))
                 ->setYear(date('Y', $value));
        } elseif (is_string($value)) {
            if(preg_match('/(\d{4})-(\d{2})-(\d{2})/', $value)) {
                $date = MF_Text::timeFormat($value, 'Y-m-d 00:00:00' ,'Y-m-d');
            } elseif(preg_match('/(\d{4})/', $value)) {
                $date = MF_Text::timeFormat($value, 'Y-00-00 00:00:00' ,'Y');
            } else {
                return $this;
            }
            
            $timeArray = MF_Text::timeToArray($date);
            if(isset($timeArray['day']))
                $this->setDay($timeArray['day']);
            if(isset($timeArray['month']))
                 $this->setMonth($timeArray['month']);
            if(isset($timeArray['year']))
                 $this->setYear($timeArray['year']);
        } elseif (is_array($value)
            && (isset($value['day']) 
                && isset($value['month']) 
                && isset($value['year'])
            )
        ) {
            $this->setDay($value['day'])
                 ->setMonth($value['month'])
                 ->setYear($value['year']);
        } elseif (is_null($value)) {
            $this->setDay('00')
                 ->setMonth('00')
                 ->setYear('0000');
        } else {
            throw new Exception('Invalid date value provided');
        }

        return $this;
    }
}