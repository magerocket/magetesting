<?php

class Application_Model_CouponMapper {

    protected $_dbTable;

    protected $_error = '';
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     * @return Application_Model_DbTable_Coupon
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Coupon');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Coupon $coupon)
    {
        $data = $coupon->__toArray();

        if (null === ($id = $coupon->getId())) {
            $coupon->setId($this->getDbTable()->insert($data));
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
        
        return $coupon;
    }

    public function delete($id)
    {
        $this->getDbTable()->delete(array('id = ?' => $id));
    }

    public function find($id, Application_Model_Coupon $coupon)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        
        $row = $result->current();
        $coupon->setId($row->id)
             ->setCode($row->code)
             ->setUsedDate($row->used_date)
             ->setUserId($row->user_id)
             ->setPlanId($row->plan_id)
             ->setDuration($row->duration)
             ->setActiveTo($row->active_to)
             ->setExtensionKey($row->extension_key)
             ;
        return $coupon;
    }
    
    public function findByCode($code, Application_Model_Coupon $coupon)
    {
        $result = $this->getDbTable()->fetchAll($this->getDbTable()->select()->where('code = ?', $code));
        if (0 == count($result)) {
            return false;
        }
        
        $row = $result->current();
        $coupon->setId($row->id)
             ->setCode($row->code)
             ->setUsedDate($row->used_date)
             ->setUserId($row->user_id)
             ->setPlanId($row->plan_id)
             ->setDuration($row->duration)
             ->setActiveTo($row->active_to)
             ->setExtensionKey($row->extension_key)
             ;
        return true;
    }

    public function findByUser($user_id, Application_Model_Coupon $coupon)
    {
        $where = array(
            'user_id = ?' => $user_id,
            new Zend_Db_Expr('date(CURRENT_TIMESTAMP) <= date(active_to)')
        );
        $result = $this->getDbTable()->fetchAll($where, 'used_date DESC');
        if (0 == count($result)) {
            return false;
        }

        $row = $result->current();
        $coupon->setId($row->id)
               ->setCode($row->code)
               ->setUsedDate($row->used_date)
               ->setUserId($row->user_id)
               ->setPlanId($row->plan_id)
               ->setDuration($row->duration)
               ->setActiveTo($row->active_to)
               ->setExtensionKey($row->extension_key)
                ;
        return $coupon;
    }

    public function fetchAll($activeOnly = false)
    {
        if ($activeOnly===true){
            $resultSet = $this->getDbTable()->fetchAll($this->getDbTable()->select()->where('status = ?', 'active'));
        } else{
            $resultSet = $this->getDbTable()->fetchAll();
        }

        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Coupon();
            $entry->setId($row->id)
             ->setCode($row->code)
             ->setUsedDate($row->used_date)
             ->setUserId($row->user_id)
             ->setPlanId($row->plan_id)
             ->setDuration($row->duration)
             ->setActiveTo($row->active_to)
             ->setExtensionKey($row->extension_key)
                ;
            $entries[] = $entry;
        }
        return $entries;
    }
    
    public function fetchList(){
        $adapter = new Zend_Paginator_Adapter_Array($this->getDbTable()->fetchList());
        
        return new Zend_Paginator($adapter);
    }

    public function apply($coupon_id, $user_id, $start_time = 0)
    {
        $modelUser = new Application_Model_User();
        $user = $modelUser->find($user_id);
        $coupon = $this->find($coupon_id, new Application_Model_Coupon());
        
        if ($coupon){
            if (!$coupon->getUserId()){
                if(strtotime(date('Y-m-d', strtotime($coupon->getActiveTo()))) > date('Y-m-d')){
                    
                    //update user with new data
                    if(!$start_time) {
                        $start_time = time();
                    }
                    $user->setPlanActiveTo(date("Y-m-d H:i:s",strtotime("now " . $coupon->getDuration() . "", $start_time)));
                    $user->setPlanId($coupon->getPlanId());
                    $user->save();
                   
                    //update coupon info
                    $coupon->setUserId($user->getId());
                    $coupon->setUsedDate(date("Y-m-d",time()));
                    $coupon->save();
                    
                    return true; 
                    
                } else {
                    $this->setError('Coupon Expired');
                    return false;
                }
            } else {
                $this->setError('Coupon has been already used');
                return false;
            }
        } else {
            $this->setError('No coupon found');
            return false;
        }
    }
    
    private function setError($error){
        $this->_error = $error;
    }
    
    public function getError(){
        return $this->_error;
    }

    public function getNextFreeTrialDate($couponsPerDay) {
        $result = $this->getDbTable()->fetchNextFreeTrialDate($couponsPerDay);
        if($result) {
            if($result['coupons'] < $couponsPerDay) {
                return $result['date'];
            } else {
                return date('Y-m-d', strtotime('+1 day', strtotime($result['date'])));
            }
        } else {
            return date('Y-m-d');
        }
    }
}