<?php 

class MF_View_Helper_FormDate extends Zend_View_Helper_FormElement
{
    public function formDate ($name, $value = null, $attribs = null)
    {
        // separate value into day, month and year
        $day = '';
        $month = '';
        $year = '';
        if (is_array($value)) {
            $day = $value['day'];
            $month = $value['month'];
            $year = $value['year'];
        } elseif (is_string($value)) {
            $array = explode('-', $value);
            $year = (isset($array[0])) ? $array[0] : '';
            $month = (isset($array[1])) ? $array[1] : '';
            $day = (isset($array[2])) ? $array[2] : '';
            //list($year, $month, $day) = explode('-', date('Y-m-d', strtotime($value)));
        }

        // build select options
        $dayAttribs = isset($attribs['dayAttribs']) ? $attribs['dayAttribs'] : array();
        $monthAttribs = isset($attribs['monthAttribs']) ? $attribs['monthAttribs'] : array();
        $yearAttribs = isset($attribs['yearAttribs']) ? $attribs['yearAttribs'] : array();

        $dayMultiOptions = array(null => '');
        for ($i = 1; $i < 32; $i ++)
        {
            $index = str_pad($i, 2, '0', STR_PAD_LEFT);
            $dayMultiOptions[$index] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        $monthMultiOptions = array(null => '');
        for ($i = 1; $i < 13; $i ++)
        {
            $index = str_pad($i, 2, '0', STR_PAD_LEFT);
//            $monthMultiOptions[$index] = $this->view->translate(date('F', mktime(null, null, null, $i, 01)));
            $monthMultiOptions[$index] = Zend_Locale::getTranslation(array('gregorian', 'stand-alone', 'wide', $i), 'month');
        }

        $startYear = date('Y');
        if (isset($attribs['startYear'])) {
            $startYear = $attribs['startYear'];
            unset($attribs['startYear']);
        }

        $stopYear = date('Y');
        if (isset($attribs['stopYear'])) {
            $stopYear = $attribs['stopYear'];
            unset($attribs['stopYear']);
        }

        $yearMultiOptions = array(null => '');

        if ($stopYear < $startYear) {
            for ($i = $startYear; $i >= $stopYear; $i--) {
                $yearMultiOptions[$i] = $i;
            }
        } else {
            for ($i = $startYear; $i <= $stopYear; $i++) {
                $yearMultiOptions[$i] = $i;
            }
        }

        // return the 3 selects separated by &nbsp;
        return
            $this->view->translate('Day') . '&nbsp;' .
            $this->view->formSelect(
                $name . '[day]',
                $day,
                $dayAttribs,
                $dayMultiOptions) . '&nbsp;' .
            $this->view->translate('Month') . '&nbsp;' .
            $this->view->formSelect(
                $name . '[month]',
                $month,
                $monthAttribs,
                $monthMultiOptions) . '&nbsp;' .
            $this->view->translate('Year') . '&nbsp;' .
            $this->view->formSelect(
                $name . '[year]',
                $year,
                $yearAttribs,
                $yearMultiOptions
            );
    }
}