<?php

/**
 * MF_Math
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_Math {
    
    public static function roundToNearest($number, $nearest = 1) {
        $number = round($number);
 
        if($nearest > $number || $nearest <= 0)
            return $number;
 
        $x = ($number % $nearest);
     
        return ($x < ($nearest/2)) ? $number - $x : $number + ($nearest - $x);
    }
}

