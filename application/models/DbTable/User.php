<?php

class Application_Model_DbTable_User extends Zend_Db_Table_Abstract
{

    protected $_name = 'user';

    public function findByEmail($email)
    {
        $select = $this->select()
                       ->where('email = ?', $email)
                       ->limit(1);
        return $this->fetchRow($select);
    }
    
    public function findByBraintreeTransactionId($value)
    {
        $select = $this->select()
                       ->where('braintree_transaction_id = ?', $value)
                       ->limit(1);
        return $this->fetchRow($select);
    }
    
    public function getStoreExtensionByUserId($storeDomain, $extensionId, $userId) {
        $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('u' => $this->_name), array())
                        ->where('se.extension_id = ?', (int)$extensionId)
                        ->where('u.id = ?', (int)$userId)
                        ->where('s.domain = ?', $storeDomain)
                        ->joinLeft(array('s' => 'store'), 'u.id = s.user_id', 's.id as store')
                        ->joinLeft(array('se' => 'store_extension'), 'se.store_id = s.id', array('se.reminder_sent', 'se.braintree_transaction_id'));
        
        return $this->fetchRow($select);
    }

    public function fetchUserByNameAndApikey($user, $key) {
        $select = $this->select()
                       ->where('login = ?', $user)
                       ->where('apikey = ?', $key);
        return $this->fetchRow($select);
    }

    public function fetchReadyActiveFromUsers() {
        $select =
            $this->select()
                 ->setIntegrityCheck(false)
                 ->from($this->_name)
                 ->where('date(active_from) = ?', date('Y-m-d'))
                 ->where('active_from_reminded = 0');
        return $this->fetchAll($select);
    }
}
