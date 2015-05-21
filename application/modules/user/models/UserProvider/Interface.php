<?php 

interface User_Model_UserProvider_Interface
{
	public function findUserByIdentity($identity);
}