<?php

class Application_Model_Revision {

    protected $_id;

    protected $_store_id;

    protected $_user_id;
    
    protected $_extension_id;

    protected $_type;
    
    protected $_comment;
    
    protected $_hash;
    
    protected $_filename;
    
    protected $_db_before_revision;
    
    protected $_mapper;

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

    public function getId()
    {
        return $this->_id;
    }
    
    public function setId($value)
    {
        $this->_id = (int)$value;
        return $this;
    }

        public function getStoreId()
    {
        return $this->_store_id;
    }
    
    public function setStoreId($value)
    {
        $this->_store_id = (int)$value;
        return $this;
    }
    
    public function getUserId()
    {
        return $this->_user_id;
    }

    public function setUserId($value)
    {
        $this->_user_id = $value;
        return $this;
    }

    public function getExtensionId()
    {
        return $this->_extension_id;
    }

    public function setExtensionId($value)
    {
        $this->_extension_id = $value;
        return $this;
    }
    
    public function getType()
    {
        return $this->_type;
    }
    
    public function setType($value)
    {
        $this->_type =  $value;
        return $this;
    }   
    
    public function getComment()
    {
        return $this->_comment;
    }
    
    public function setComment($value)
    {
        $this->_comment =  $value;
        return $this;
    }   
    
    public function getHash()
    {
        return $this->_hash;
    }
    
    public function setHash($value)
    {
        $this->_hash =  $value;
        return $this;
    }   
    
    public function getFilename()
    {
        return $this->_filename;
    }
    
    public function setFilename($value)
    {
        $this->_filename = $value;
        return $this;
    }   
    
    public function getDbBeforeRevision()
    {
        return $this->_db_before_revision;
    }
    
    public function setDbBeforeRevision($value)
    {
        $this->_db_before_revision = $value;
        return $this;
    }  
    
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * @return Application_Model_RevisionMapper
     */
    public function getMapper()
    {
        if (null === $this->_mapper) {
            $this->setMapper(new Application_Model_RevisionMapper());
        }
        return $this->_mapper;
    }

    public function save()
    {
        return $this->getMapper()->save($this);
    }

    public function delete($id)
    {
        $this->getMapper()->delete($id);
    }

    public function find($id)
    {
        $this->getMapper()->find($id, $this);
        return $this;
    }

    /**
     * @param boolean $fetch_hidden - whether fetch also hidden plans
     */
    public function fetchAll($userId = false)
    {
        return $this->getMapper()->fetchAll($userId);
    }

    public function __toArray()
    {
        return array(
                'id'        => $this->getId(),
                'store_id'      => $this->getStoreId(),
                'user_id' => $this->getUserId(),
                'extension_id' => $this->getExtensionId(),
                'type' => $this->getType(),
                'comment' => $this->getComment(),
                'hash' => $this->getHash(),
                'filename' => $this->getFilename(),
                'db_before_revision'     => $this->getDbBeforeRevision(),
        );
    }

    /**
     * fetches array of all revisions for store<br />
     * joined with extension table to get extension name
     * @param int $store_id
     */
    public function getAllForStore($store_id)
    {
        return $this->getMapper()->getAllForStore($store_id);
    }
    
    public function getPreLastForStore($store_id){
        return $this->getMapper()->getPreLastForStore($store_id, $this);
    }
    
    public function getLastForStore($store_id){
        return $this->getMapper()->getLastForStore($store_id, $this);
    }
    
}