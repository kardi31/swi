<?php

/**
 * PasswordEncoder
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class User_PasswordEncoder {
    
    public function encode($password, $salt = '', $algo = 'md5') {
        return hash($algo, $password . $salt);
    }
}

