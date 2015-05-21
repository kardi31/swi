<?php 

interface User_Model_User_Interface
{
    public function setId($id);
    public function getId();
    public function setFirstName($firstName);
    public function getFirstName();
    public function setLastName($lastName);
    public function getLastName();
    public function setEmail($email);
    public function getEmail();
    public function setPassword($password);
    public function getPassword();
    public function setRole($role);
    public function getRole();
    public function setToken($token);
    public function getToken();
    public function setActive($active);
    public function isActive();
    public function isStatus(); 
    public function setStatus($status); 
    public function setDiscountId($discountId);
}