<?php

/**
 * MF_Date
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_Date {
    
    public static function convertTime($time, $outputFormat, $inputFormat = 'Y-m-d H:i:s') {
        if(!$time) 
            return false;
        if($dateTime = DateTime::createFromFormat($inputFormat, $time))
            return $dateTime->format($outputFormat);
    }
    
    public function formatDate($date, $format, $timeout = null) {
        if($date = DateTime::createFromFormat('Y-m-d H:i:s', $date)) {
            if(null !== $timeout) {
                $interval = strtotime($timeout, $date->getTimestamp());
                if($result = date($format, $interval))
                    return $result;
            } else {
                return date($format, date($date->getTimestamp()));
            }
        }
    }
}

