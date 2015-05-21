<?php

/**
 * HydratorInterface
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
interface MF_Hydrator_HydratorInterface {
    
    public function hydrate(array $data, $object);
    public function extract($object);
    
}

