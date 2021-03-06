<?php

class Application_Model_RevisionMapper {

    protected $_dbTable;

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
     * @return Application_Model_DbTable_Revision
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Revision');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Revision $revision)
    {
        $data = $revision->__toArray();
        if ($revision->getExtensionId()==0){
            unset($data['extension_id']);
        }
            
        if (null === ($id = $revision->getId())) {
            unset($data['id']);
            $revision->setId($this->getDbTable()->insert($data));
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }

        return $revision;
    }

    public function find($id, Application_Model_Revision $revision)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $revision->setId($row->id)
              ->setUserId($row->user_id)
                ->setStoreId($row->store_id)
                ->setExtensionId($row->extension_id)
                ->setType($row->type)
                ->setHash($row->hash)
                ->setComment($row->comment)
                ->setFilename($row->filename)
                ->setDbBeforeRevision($row->db_before_revision);
        
        return $revision;
    }

    public function delete($id)
    {
        $this->getDbTable()->delete($id);
    }

    public function fetchAll($user_id=null)
    {
        $where = null;
        if(!$user_id) {
            $where = $this->getDbTable()->select()->where('user_id = ?', 0);
        }
        $resultSet = $this->getDbTable()->fetchAll($where);
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Revision();
            $entry->setId($row->id)
                ->setUserId($row->user_id)
                ->setStoreId($row->store_id)
                ->setExtensionId($row->extension_id)
                ->setType($row->type)
                ->setHash($row->hash)
                ->setComment($row->comment)
                ->setFilename($row->filename)
                ->setDbBeforeRevision($row->db_before_revision);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function getAllForStore($store_id)
    {
        $result = array();
        if((int)$store_id) {
            $tmp = $this->getDbTable()->getAllForStore($store_id);
            if($tmp) {
                $result = $tmp;
            }
        }
        return $result;
    }
    
    public function getPreLastForStore($store_id, Application_Model_Revision $revision)
    {
        if((int)$store_id) {
            $row = $this->getDbTable()->getPreLastForStore($store_id);
            if($row) {
                $revision->setId($row->id)
                ->setUserId($row->user_id)
                ->setStoreId($row->store_id)
                ->setExtensionId($row->extension_id)
                ->setType($row->type)
                ->setHash($row->hash)
                ->setComment($row->comment)
                ->setFilename($row->filename)
                ->setDbBeforeRevision($row->db_before_revision);
            }
        }
        return $revision;
    }
    
    public function getLastForStore($store_id, Application_Model_Revision $revision)
    {
        if((int)$store_id) {
            $row = $this->getDbTable()->getLastForStore($store_id);
            if($row) {
                $revision->setId($row->id)
                ->setUserId($row->user_id)
                ->setStoreId($row->store_id)
                ->setExtensionId($row->extension_id)
                ->setType($row->type)
                ->setHash($row->hash)
                ->setComment($row->comment)
                ->setFilename($row->filename)
                ->setDbBeforeRevision($row->db_before_revision);
            }
        }
        return $revision;
    }
}