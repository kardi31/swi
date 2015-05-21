<?php

/**
 * Form
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Admin_Form extends Zend_Form
{
    public static $textDecorators = array(
        'ViewHelper',
        array('Label', array('class' => 'form-label span4')),
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row-fluid')),
        array(array('SpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span12')),
        array(array('RowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-row row-fluid')),
    );
    
    public static $textareaDecorators = array(
        'ViewHelper',
        array('Label', array('class' => 'form-label span4')),
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row-fluid')),
        array(array('SpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span12')),
        array(array('RowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-row row-fluid')),
    );
    
    public static $tinymceDecorators = array(
        'ViewHelper',
        array('Label', array('class' => 'form-label')),
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row-fluid')),
        array(array('SpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span12')),
        array(array('RowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-row row-fluid')),
    );
    
     public static $selectDecorators = array(
        'ViewHelper',
        'Errors',
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span8 controls')),
        array('Label', array('class' => 'form-label span4')),
        array(array('RowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row-fluid')),
        array(array('SpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span12')),
        array(array('FormRowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-row row-fluid'))
    );
    
    public static $dimensionDecorators = array(
        'ViewHelper',
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span8 controls')),
        array('Label', array('class' => 'form-label span4')),
//        array(array('RowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row-fluid')),
        array(array('SpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span4')),
//        array(array('FormRowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-row row-fluid'))
    );
    public static $priceDecorators = array(
        'ViewHelper',
        array('Label', array('class' => 'form-label span2')),
//        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row-fluid')),
        array(array('SpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span4')),
//        array(array('RowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-row row-fluid')),
    );
    
    public static $checkboxDecorators = array(
        'ViewHelper',
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'left marginR10')),
        array(array('SpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span8 controls')),
        array('Label', array('class' => 'form-label span4')),
        array(array('RowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row-fluid')),
        array(array('OuterSpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span12')),
        array(array('FormRowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-row row-fluid'))
    );

    public static $checkgroupDecorators = array(
        'ViewHelper',
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'left marginT5')),
        array(array('SpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span8 controls')),
        array('Label', array('class' => 'form-label span4')),
        array(array('RowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row-fluid')),
        array(array('OuterSpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span12')),
        array(array('FormRowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-row row-fluid'))
    );
    
    public static $datepickerDecorators = array(
        'ViewHelper',
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span2')),
        array('Label', array('class' => 'form-label span4')),
        array(array('RowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row-fluid')),
        array(array('OuterSpanWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span12')),
        array(array('FormRowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-row row-fluid'))
    );

    public static $submitDecorators = array(
        'ViewHelper',
        array('Description', array('tag' => 'button', 'class' => 'btn', 'type' => 'reset')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-actions'))
    );
    
    public static $hiddenDecorators = array(
        'ViewHelper'
    );
    
    public static $tableRowDecorators = array(
        'ViewHelper',
        'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
        array('Label', array('tag' => 'td')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    );
    
    
    protected static $_standardFormDecorators = array(
        'ViewHelper',
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-input')),
        array('Label', array('class' => 'form-label', 'escape'  => false, 'requiredSuffix' => ' <em>*</em>')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clearfix'))
    );
    
    protected static $_checkgroupFormDecorators = array(
        'ViewHelper',
        array(array('Checkgroup' => 'HtmlTag'), array('tag' => 'div', 'class' => 'checkgroup')),
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-input')),
        array('Label', array('class' => 'form-label', 'escape'  => false, 'requiredSuffix' => ' <em>*</em>')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clearfix'))
    );

    protected static $_submitFormDecorators = array(
        'ViewHelper',
        array('Description', array('tag' => 'button', 'type' => 'reset')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-action clrearfix'))
    );
    
    protected static $_dateFormDecorators = array(
        'ViewHelper',
        array(array('DatePicker' => 'Description'), array('tag' => 'input', 'class' => 'datepicker', 'type' => 'hidden')),
        array('Label', array('class' => 'form-label', 'escape'  => false, 'requiredSuffix' => ' <em>*</em>')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clearfix'))
    );
    
    public function isValid($data)
    {
        $valid = parent::isValid($data);
 
        foreach ($this->getElements() as $element) {
            if ($element->hasErrors()) {
                $oldClass = $element->getAttrib('class');
                if (!empty($oldClass)) {
                    $element->setAttrib('class', $oldClass . ' error');
                } else {
                    $element->setAttrib('class', 'error');
                }
            }
        }
 
        return $valid;
    }
}

