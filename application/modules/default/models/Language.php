<?php

/**
 * Default_Model_Language
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Model_Language implements Default_Model_LanguageInterface {
    
    protected $id;
    protected $name;
    protected $active;
    protected $default;
    protected $admin;
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setActive($active = true) {
        $this->active = $active;
    }
    
    public function isActive() {
        return $this->active;
    }
    
    public function setDefault($default = true) {
        $this->default = $default;
    }
    
    public function isDefault() {
        return $this->default;
    }
    
    public function setAdmin($admin = true) {
        $this->admin = $admin;
    }
    
    public function isAdmin() {
        return $this->admin;
    }
}

