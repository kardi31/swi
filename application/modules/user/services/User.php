<?php

/**
 * UserService
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_Service_User extends MF_Service_ServiceAbstract {
    
    protected $userTable;
    protected $userProfileTable;
    
    public function init() {
        $this->userTable = Doctrine_Core::getTable('User_Model_Doctrine_User');
        $this->userProfileTable = Doctrine_Core::getTable('User_Model_Doctrine_Profile');
        parent::init();
    }
    
    public function userExists($array) {
        return (!empty($array)) ? !!$this->userTable->findOneByConditions($array) : false;
    }
    
    public function getUser($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->userTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullUser($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->userTable->getUserQuery();
        $q->andWhere('u.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }

    public function getUsersByRole($role, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->userTable->findBy('role', $role, $hydrationMode);
    }
    
    public function getUserForm(User_Model_Doctrine_User $user = null) {
        $form = new User_Form_User();
        if(null != $user) {
            $form->populate($user->toArray());
        }
        return $form;
    }
    
    public function getClientForm($user = null) {
       $form = new User_Form_Client();
        if(null != $user) {
            if (is_array($user)):
                $form->populate($user); 
            else:
                $form->populate($user->toArray());
            endif;
        }
        return $form;
    }
    
    public function saveAdminFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                unset($values[$key]);
            }
        }
        if(null == $user) {
            if(!$user = $this->userTable->getProxy((int) $values['id'])) {
                $user = $this->userTable->getRecord();
            }
        }
        $user->fromArray($values);
        $user->save();
        
        return $user;
    }
    
    public function createUserProfile(User_Model_User_Interface $user, $values = null) {
        $profile = Doctrine_Core::getTable('User_Model_Doctrine_Profile')->getRecord();
        $profile->setUserId($user->getId());
        if(null != $values) {
            $profile->fromArray($values);
        }
        $profile->save();
        return $profile;
    }
   
    public function getProfile($userId, $field = 'user_id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->userProfileTable->findOneBy($field, $userId, $hydrationMode);
    }
    
    public function saveClientFromArray($values,$user_id=null) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if($user_id!=null)
        {
            $values['id']=$user_id;
        }
        if(!$user = $this->userTable->getProxy((int) $values['id'])) {
            $user = $this->userTable->getRecord();
        }
       
//        $userProfile = $this->getProfile($user->getId(), 'user_id', Doctrine_Core::HYDRATE_RECORD);
//  
        if($user_id==null){
            $userProfile = $this->userProfileTable->getRecord();
        }
        elseif(!$this->getProfile($user_id, 'user_id', Doctrine_Core::HYDRATE_RECORD) == false){
            $userProfile = $this->userProfileTable->getRecord();
        }
//        
        if(isset($values['client']))
        {
            unset($values['client']['email']);
            $newVal = array_merge($values,$values['client']);
        }
        else
        {
            $newVal = $values;
        }
        $user->fromArray($newVal);
        $user->save();
        
        $newVal['user_id'] = $user->getId();
        unset($values['id']);
        if(!isset($newVal['address'])){
        $newVal['address'] = $newVal['street']." ".$newVal['houseNr'];
        if(!empty($newVal['flatNr']))
            $newVal['address'] .= "/".$newVal['flatNr'];
        }
        $userProfile->fromArray($newVal);
        $userProfile->save();
        return $user;
    }
    
    public function createProfile(User_Model_User_Interface $user, $values = array()) {
        $profile = Doctrine_Core::getTable('User_Model_Doctrine_Profile')->getRecord();
        $profile->setUserId($user->getId());
        $profile->fromArray($values);
        $profile->save();
        return $profile;
    }
    
    public function connectWithFacebook(User_Model_Doctrine_User $user = null, $fbId, $userDataFacebook = null) {
        if(null == $user) {
            if(!$user = $this->getUser($userDataFacebook['email'], 'email')) {
                if(!$user = $this->getUser($fbId, 'fb_id')) {
                    $user = $this->userTable->getRecord();
                    $user->setEmail($userDataFacebook['email']);
                    $user->setFirstName($userDataFacebook['first_name']);
                    $user->setLastName($userDataFacebook['last_name']);
                    $user->setRole('client');
                }
            }
            $user->save();
        } 
        
        if(!$userProfile = $this->getProfile($user->getId())) {
            $userProfile = $this->userProfileTable->getRecord();
            
            $userProfile->setUserId($user->getId());
            $cityArray = explode(',', $userDataFacebook['location']['name']);
            $userProfile->setCity($cityArray[0]);
        }
            
        $user->setFbId($fbId);
        $user->save();
        $userProfile->save();
        return $user;
        
    }
    
    public function prepareUpdate($user, $subject) {
        $table = Doctrine_Core::getTable('User_Model_Doctrine_Update');
        
        $table->deleteUserTokensOfType($user->getId(), $subject);
        
        $update = $table->getRecord();
        $update->User = $user;
        $update->setType($subject);
        $update->setToken(MF_Text::createUniqueToken());
        $update->save();
        return $update;
    }

    public function completeUpdate($update, $user) {
        $property = $update->getType();
        if(!array_key_exists($property, $this->userTable->getColumns())) {
            throw new Exception("Culdn't set $property property");
        }
        $user->set($property, $update->getValue());
        $user->save();
        $update->delete();
        return true;
    }
    
    public function getUpdateForm($update, $subject) {
        $form = new User_Form_Update();
        switch($subject) {
            case User_Model_Doctrine_Update::TYPE_EMAIL:
                $form->removeElement('password');
                $form->removeElement('confirm_password');
                break;
            case User_Model_Doctrine_Update::TYPE_PASSWORD:
                $form->removeElement('email');
                break;
        }
        $form->getElement('user_id')->setValue($update->getUserId());
        $form->getElement('token')->setValue($update->getToken());
        return $form;
    }
    
    public function getUpdateOfToken($token) {
        $table = Doctrine_Core::getTable('User_Model_Doctrine_Update');
        return $table->findOneBy('token', $token);
    }
    
    public function sendAdminAddMail(User_Model_User_Interface $user, Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/admin-add.phtml') {
        $token = $user->getToken();                     
        $mail->setBodyHtml(
            $view->partial($partial, array('token' => $token))
        );
        $mail->send();
    }
    
    public function sendAdminEditMail(User_Model_User_Interface $user, Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/admin-edit.phtml') {
        $token = $user->getToken();      
        
        $mail->setBodyHtml(
            $view->partial($partial, array('token' => $token))
        );
        $mail->send();
    }
    
    public function sendRegistrationMail(User_Model_User_Interface $user, Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/register.phtml') {
        $token = $user->getToken();//                      
        $mail->setBodyHtml(
            $view->partial($partial, array('token' => $token))
        );
        $mail->send(); 
    }
    
    public function sendRegistrationCompleteMail(User_Model_User_Interface $user, Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/register-complete.phtml') {
        $mail->addTo($user->getEmail());
        $mail->setBodyText(
            $view->partial($partial)
        );
        $mail->send(); 
    }
    
    public function sendUpdateDataMail($user, Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/update-client-admin.phtml') {
        $mail->setBodyText(
                $view->partial($partial, array('user' => $user))
        );
        $mail->send(); 
    }
    
    public function sendUpdateMail($type = User_Model_Doctrine_Update::TYPE_PASSWORD, $user, $token, Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/update.phtml') {
        $mail->addTo($user->getEmail());
        $mail->setBodyText(
                $view->partial($partial, array('user' => $user, 'token' => $token, 'type' => $type))
        );
        $mail->send(); 
    }
    
    public function removeClient(User_Model_Doctrine_User $user, User_Model_Doctrine_Profile $profile) {
         $user->unlink('Groups');
         $user->save();
         $user->delete();
         $profile->delete();
    }
    
    public function removeAdmin(User_Model_Doctrine_User $user) {
        $user->delete();
    }
    
    public function refreshStatusClient($user){
        if ($user->isStatus()):
            $user->setStatus(0);
        else:
            $user->setStatus(1);
        endif;
        $user->save();
    }
    
    public function sendAdminChangePasswordMail(User_Model_User_Interface $user, Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/admin-change-password.phtml') {
        $token = $user->getToken();                     
        $mail->setBodyHtml(
            $view->partial($partial, array('token' => $token))
        );
        $mail->send();
    }
    
    public function getAllClients($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->userTable->getClientQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetClientSelectOptions($prependEmptyValue = false) {
        $items = $this->getAllClients();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
                $result[$item->getId()] = $item->first_name.' '.$item->last_name;
        }
        return $result;
    }
    
    public function getUnSelectedDiscountSelectOptions($discountId) {
        $q = $this->userTable->getClientQuery();
        $q->andWhere('cli.discount_id != ? OR cli.discount_id IS NULL', $discountId);
        $items = $q->execute(array(), $hydrationMode);
        $result = array();
        foreach($items as $item) {
                $result[$item->getId()] = $item->first_name.' '.$item->last_name;
        }
        return $result;
    }
    
    public function getSelectedDiscountSelectOptions($discountId) {
        $q = $this->userTable->getClientQuery();
        $q->andWhere('cli.discount_id = ?', $discountId);
        $items = $q->execute(array(), $hydrationMode);
        $result = array();
        foreach($items as $item) {
                $result[$item->getId()] = $item->first_name.' '.$item->last_name;
        }
        return $result;
    }
    
    public function unSelectDiscountUsers($selectedUsers, $newSelectedUsers){
        foreach($selectedUsers as $key => $selectedUser):
            $flag = false;
            foreach($newSelectedUsers as $newSelectedUser):
                if ($key == $newSelectedUser):
                    $flag = true;
                endif;
            endforeach;
            if ($flag == false):
                $user = $this->getUser($key);
                $user->setDiscountId(NULL);
                $user->save();
            endif;
        endforeach;
    }
    
    public function saveAssignedDiscountsFromArray($values, $discountId){
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        $selectedUsers = $this->getSelectedDiscountSelectOptions($discountId);
        $this->unSelectDiscountUsers($selectedUsers, $values['user_selected']);
        foreach($values['user_selected'] as $value):
            $user = $this->getUser($value);
            $user->setDiscountId($discountId);
            $user->save();
        endforeach;
    }
    
    public function getCommentForm(Product_Model_Doctrine_Comment $comment = null) {
        $form = new User_Form_Comment();
        if(null != $comment) { 
            $form->populate($comment->toArray());
        }
        return $form;
    }
    
}

