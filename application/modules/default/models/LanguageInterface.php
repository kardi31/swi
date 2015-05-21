<?php

/**
 * Default_Model_LanguageInterface
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
interface Default_Model_LanguageInterface {
    
    public function setId($id);
    
    public function getId();
    
    public function setName($name);
    
    public function getName();
    
    public function setActive($active = true);
    
    public function isActive();
    
    public function setDefault($default = true);
    
    public function isDefault();
    
    public function setAdmin($admin = true);
    
    public function isAdmin();
}

