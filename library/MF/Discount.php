<?php

class MF_Discount
{
    public static function getPriceWithDiscount($price, array $discounts) {

        $maxDiscount = 0;
        foreach($discounts as $discount):
            if ($discount):
                $date = time();
                $startDate = new Zend_Date($discount['start_date'], 'dd/MM/yyyy HH:mm:00');
                $startDate1 = $startDate->getTimestamp();
                $finishDate = new Zend_Date($discount['finish_date'], 'dd/MM/yyyy HH:mm:00');
                $finishDate1 = $finishDate->getTimestamp();
                if ($startDate1 < $date && $finishDate1 > $date):
                    if ($maxDiscount < $discount['amount_discount']):
                        $maxDiscount = $discount['amount_discount'];
                    endif;
                endif;
             endif;
         endforeach;
         if ($maxDiscount != 0):
            $newPrice = $price-($price*$maxDiscount/100);
            $newPrice = round($newPrice, 0);
            return array('price' => $newPrice, 'flag' => true);
         else: 
            return array('price' => $price, 'flag' => false);
         endif;
         return array('price' => $price, 'flag' => false);
    }
    
}