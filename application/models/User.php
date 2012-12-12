<?php

class Application_Model_User {

    protected $_id;

    protected $_firstname;

    protected $_lastname;

    protected $_email;

    protected $_login;

    protected $_password;

    protected $_street;

    protected $_postal_code;

    protected $_city;

    protected $_state;

    protected $_country;

    protected $_group;

    protected $_addedDate;

    protected $_status;
     
    protected $_has_system_account;
     
    protected $_system_account_name;
     
    protected $_plan_id;
     
    protected $_subscr_id;
    
    protected $_plan_active_to;

    protected $_downgraded;
    
    protected $_braintree_vault_id;
    
    protected $_braintree_subscription_id;
    
    protected $_server_id;

    protected $_mapper;
    
    protected $_has_papertrail_account;
    
    protected $_papertrail_api_token;

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function setOptions(array $options)
    {
        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . $filter->filter($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setId($id)
    {
        $this->_id = (int)$id;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setFirstname($firstname)
    {
        $this->_firstname = $firstname;
        return $this;
    }

    public function getFirstname()
    {
        return $this->_firstname;
    }

    public function setLastname($lastname)
    {
        $this->_lastname = $lastname;
        return $this;
    }

    public function getLastname()
    {
        return $this->_lastname;
    }

    public function setEmail($email)
    {
        $this->_email = $email;
        return $this;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function setLogin($login)
    {
        $this->_login = $login;
        return $this;
    }

    public function getLogin()
    {
        return $this->_login;
    }

    public function setPassword($password)
    {
        $this->_password = $password;
        return $this;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function setStreet($street)
    {
        $this->_street = $street;
        return $this;
    }

    public function getStreet()
    {
        return $this->_street;
    }

    public function setPostalCode($postalCode)
    {
        $this->_postal_code = $postalCode;
        return $this;
    }

    public function getPostalCode()
    {
        return $this->_postal_code;
    }

    public function setCity($city)
    {
        $this->_city = $city;
        return $this;
    }

    public function getCity()
    {
        return $this->_city;
    }

    public function setState($state)
    {
        $this->_state = $state;
        return $this;
    }

    public function getState()
    {
        return $this->_state;
    }

    public function setCountry($country)
    {
        $this->_country = $country;
        return $this;
    }

    public function getCountry()
    {
        return $this->_country;
    }
    
    /**
     * Sets user group. This field is enum type
     * @param string $group
     * @return \Application_Model_User 
     */
    public function setGroup($group)
    {
        $this->_group = $group;
        return $this;
    }

    public function getGroup()
    {
        return $this->_group;
    }

    public function setAddedDate($addedDate)
    {
        $this->_addedDate = $addedDate;
        return $this;
    }

    public function getAddedDate()
    {
        return $this->_addedDate;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->_status;
    }
    
    public function getHasSystemAccount()
    {
        return $this->_has_system_account;
    }
    
    /**
     *
     * @param bool $hasSystemAccount
     * @return \Application_Model_User 
     */
    public function setHasSystemAccount($hasSystemAccount){
        $this->_has_system_account = $hasSystemAccount;
        return $this;
    }
    
    public function getSystemAccountName()
    {
        return $this->_system_account_name;
    }
    
    /**
     *
     * @param string $systemAccountName
     * @return \Application_Model_User 
     */
    public function setSystemAccountName($systemAccountName){
        $this->_system_account_name = $systemAccountName;
        return $this;
    }
         
    public function getPlanId()
    {
        return $this->_plan_id;
    }
    
    /**
     *
     * @param int $planId
     * @return \Application_Model_User 
     */
    public function setPlanId($planId){
        $this->_plan_id = $planId;
        return $this;
    }
    
    public function getSubscrId()
    {
        return $this->_subscr_id;
    }
    
    /**
     *
     * @param int $subscrId
     * @return \Application_Model_User 
     */
    public function setSubscrId($subscrId){
        $this->_subscr_id = $subscrId;
        return $this;
    }
    
    public function getPlanActiveTo()
    {
        return $this->_plan_active_to;
    }
    
    /**
     *
     * @param datetime $planActiveTo
     * @return \Application_Model_User 
     */
    public function setPlanActiveTo($planActiveTo){
        $this->_plan_active_to = $planActiveTo;
        return $this;
    }

    public function setDowngraded($downgraded)
    {
        $this->_downgraded = (int)$downgraded;
        return $this;
    }

    /**
     * is user downgraded
     * 0 - is not downgraded
     * 1 - downgraded with deleted symlinks
     * 2 - downgraded in fronted but symlinks exists
     * @return int 0|1|2
     */
    public function getDowngraded()
    {
        return $this->_downgraded;
    }

    public function setBraintreeVaultId($value)
    {
        $this->_braintree_vault_id = $value;
        return $this;
    }
    
    public function getBraintreeVaultId()
    {
        return $this->_braintree_vault_id;        
    }
    
    public function setBraintreeSubscriptionId($value)
    {
        $this->_braintree_subscription_id = $value;
        return $this;
    }
    
    public function getBraintreeSubscriptionId()
    {
        return $this->_braintree_subscription_id;        
    }
    
    public function setServerId($value)
    {
        $this->_server_id = $value;
        return $this;
    }
    
    public function getServerId()
    {
        return $this->_server_id;
    }
    
    public function getHasPapertrailAccount()
    {
        return $this->_has_papertrail_account;
    }
    
    public function setHasPapertrailAccount($hasPapertrailAccount){
        $this->_has_papertrail_account = (int)$hasPapertrailAccount;
        return $this;
    }
    
    public function setPapertrailApiToken($value)
    {
        $this->_papertrail_api_token = $value;
        return $this;
    }
    
    public function getPapertrailApiToken()
    {
        return $this->_papertrail_api_token;
    }
    
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        if (null === $this->_mapper) {
            $this->setMapper(new Application_Model_UserMapper());
        }
        return $this->_mapper;
    }

    public function save($withPassword = false)
    {
        return $this->getMapper()->save($this, $withPassword);
    }

    public function delete($id)
    {
        $this->getMapper()->delete($id);
    }

    public function find($id, $returnPassword = false)
    {
        $this->getMapper()->find($id, $this, $returnPassword);
        return $this;
    }

    public function fetchAll($activeOnly=false)
    {
        return $this->getMapper()->fetchAll($activeOnly);
    }
    
    public function fetchList()
    {
        return $this->getMapper()->fetchList();
    }

    /**
     * @method activateUser
     * @param int $id - User ID
     * @param sha1 $hash - sha1(login,email,added_date)
     * @return int [0-2]:<br />
     * 0 - successfully activated<br />
     * 1 - wrong data<br />
     * 2 - previously activated
     */
    public function activateUser($id, $hash)
    {
        return $this->getMapper()->activateUser($id, $hash);
    }

    public function resetPassword($email)
    {
        return $this->getMapper()->resetPassword($email, $this);
    }
    public function __toArray()
    {
        return array(
            'id'          => $this->getId(),
            'firstname'   => $this->getFirstname(),
            'lastname'    => $this->getLastname(),
            'email'       => $this->getEmail(),
            'login'       => $this->getLogin(),
            'street'      => $this->getStreet(),
            'postal_code' => $this->getPostalCode(),
            'city'        => $this->getCity(),
            'state'       => $this->getState(),
            'country'     => $this->getCountry(),
            'group'       => $this->getGroup(),
            'added_date'  => $this->getAddedDate(),
            'status'      => $this->getStatus(),
            'subscr_id'   => $this->getSubscrId(),
            'plan_id'     => $this->getPlanId(),
            'plan_active_to' => $this->getPlanActiveTo(),
            'has_system_account' =>$this->getHasSystemAccount(),
            'system_account_name' => $this->getSystemAccountName(),
            'downgraded' => $this->getDowngraded(),
            'server_id' => $this->getServerId(),
            'braintree_vault_id' => $this->getBraintreeVaultId(),
            'braintree_subscription_id' => $this->getBraintreeSubscriptionId(),
            'server_id' => $this->getServerId(),
            'has_papertrail_account' =>$this->getHasPapertrailAccount(),
            'papertrail_api_token' => $this->getPapertrailApiToken(),
        );
    }
    
    public function findByBraintreeSubscriptionId($subscription_id){
        return $this->getMapper()->findByBraintreeSubscriptionId($subscription_id,$this);
    }
    
    /**
     * Adds user to authorized ftp users
     * TODO: implement
     */
    public function enableFtp(){
        $config = Zend_Registry::get('config');
        exec('cd worker; sudo ./ftp-user-add.sh ' . $config->magento->userprefix . $this->getLogin() . ' ; cd ..');
    }
    
    /**
     * Removes user from authorized ftp users
     * used in downgrade_expired_users.php
     */
    public function disableFtp(){
        $config = Zend_Registry::get('config');
        exec('cd worker; sudo ./ftp-user-remove.sh ' . $config->magento->userprefix . $this->getLogin() . ' ; cd ..');
    }
    
    /**
     * Adds user to authorized phpmyadmin users
     * but currently, it just rebuilds denied user list
     * TODO:implement
     */
    public function enablePhpmyadmin(){
        $this->_rebuildPhpmyadminRules();
    }
        
    /**
     * Removes user from authorized phpmyadmin
     * but currently, just rebuilds denied user list
     * TODO: implement
     */
    public function disablePhpmyadmin(){
        $this->_rebuildPhpmyadminRules();
    }
       
    /**
     * Rebuild deny list in phpmyadmin custom configuration file.
     * @param object $config - Application config
     */
    protected function _rebuildPhpmyadminRules(){
        
        $config = Zend_Registry::get('config');
        
        $modelPlan = new Application_Model_Plan();
        $plans_without = $modelPlan->getAllByPhpmyadminAccess(0);
        
        foreach($plans_without as $plan){
            $plansIdsWithoutPma[] = $plan->getId();
        }
        /* Users without plan also have no phpmyadmin access */
        $plansIdsWithoutPma[] = 0; 
                
        $disabledUsers = $this->getMapper()->getAllByPlanId($plansIdsWithoutPma);
        
        $disableArray = array();

        foreach($disabledUsers as $user){
            $disableArray[]= "'".$config->magento->userprefix . $user->getLogin()."'";
        }
        
        $deniedList = implode(',',$disableArray);
        exec('cd worker; sudo ./phpmyadmin-user-rebuild.sh "'.$deniedList.'" ; cd ..',$output);
    }
}