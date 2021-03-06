<?php

class Application_Model_StoreMapper {

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
     * @return Application_Model_DbTable_Store
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Store');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Store $store)
    {
        $data = $store->__toArray();
        
        if (null === ($id = $store->getId())) {
            unset($data['id']);
            $data['backend_password'] = '';
            $store->setId($this->getDbTable()->insert($data));
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }      
        return $store;
    }

    public function find($id, Application_Model_Store $store)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        
        $store->setId($row->id)
                ->setEdition($row->edition)
                ->setStatus($row->status)
                ->setVersionId($row->version_id)
                ->setUserId($row->user_id)
                ->setServerId($row->server_id)
                ->setDomain($row->domain)
                ->setStoreName($row->store_name)
                ->setDescription($row->description)
                ->setSampleData($row->sample_data)
                ->setBackendName($row->backend_name)
                ->setBackendPassword($row->backend_password, false)
                ->setCustomProtocol($row->custom_protocol)
                ->setCustomHost($row->custom_host)
                ->setCustomPort($row->custom_port)
                ->setCustomRemotePath($row->custom_remote_path)
                ->setCustomLogin($row->custom_login)
                ->setCustomPass($row->custom_pass, false)
                ->setCustomSql($row->custom_sql)
                ->setErrorMessage($row->error_message)
                ->setRevisionCount($row->revision_count)
                ->setType($row->type)
                ->setCustomFile($row->custom_file)
                ->setPapertrailSyslogPort($row->papertrail_syslog_port)
                ->setPapertrailSyslogHostname($row->papertrail_syslog_hostname)
        		->setDoHourlyDbRevert($row->do_hourly_db_revert);

        
        return $store;
    }

    public function delete($id)
    {
        $this->getDbTable()->delete($id);
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Store();
            $entry->setId($row->id)
                    ->setEdition($row->edition)
                    ->setStatus($row->status)
                    ->setVersionId($row->version_id)
                    ->setUserId($row->user_id)
                    ->setServerId($row->server_id)
                    ->setDomain($row->domain)
                    ->setStoreName($row->store_name)
                    ->setDescription($row->description)
                    ->setSampleData($row->sample_data)
                    ->setBackendName($row->backend_name)
                    ->setBackendPassword($row->backend_password, false)
                    ->setCustomProtocol($row->custom_protocol)
                    ->setCustomHost($row->custom_host)
                    ->setCustomPort($row->custom_port)
                    ->setCustomRemotePath($row->custom_remote_path)
                    ->setCustomLogin($row->custom_login)
                    ->setCustomPass($row->custom_pass, false)
                    ->setCustomSql($row->custom_sql)
                    ->setErrorMessage($row->error_message)
                    ->setRevisionCount($row->revision_count)
                    ->setType($row->type)
                    ->setCustomFile($row->custom_file)
                    ->setPapertrailSyslogPort($row->papertrail_syslog_port)
                    ->setPapertrailSyslogHostname($row->papertrail_syslog_hostname)
            		->setDoHourlyDbRevert($row->do_hourly_db_revert);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function getAll()
    {
        return $this->getDbTable()->getAllJoinedWithVersions();
    }

    public function changeStatusToClose($store, $byAdmin)
    {
        if($store->getUserId() AND $store->getDomain()) {
            if($byAdmin) {
                $this->getDbTable()->update(
                        array('status' => 'closed'),
                        array('domain = ?' => $store->getDomain())
                );
            } else {
                $this->getDbTable()->changeStatusToClose(
                        $store->getUserId(),
                        $store->getDomain()
                );
            }
        }
    }

    public function getAllForUser($user_id, $hideRemoved)
    {
        $select = $this->getDbTable()
                       ->findAllByUser($user_id, $hideRemoved);
        $adapter = new Zend_Paginator_Adapter_DbSelect($select);

        $paginator = new Zend_Paginator($adapter);
        $paginator->setFilter(new Application_Model_Filter_StoreUserDashboard());

        return $paginator;
    }

    public function countUserStores( $user_id, $hideRemoved)
    {
        $data = $this->getDbTable()
                     ->countUserStores( $user_id, $hideRemoved)
                     ->current();

        return (int)$data->stores;
    }
    
    public function getWholeQueue()
    {
        $select = $this->getDbTable()
                     ->getWholeQueueWithUsersName();
        $adapter = new Zend_Paginator_Adapter_DbSelect($select);
        
        return new Zend_Paginator($adapter);
    }

    public function findByDomain($domain){
        return $this->getDbTable()
                    ->findByDomain($domain);
    }
    
    public function getStatusFromTask($taskName){
        
        $possibleStatuses = array(
            'ExtensionInstall' => 'installing-extesion',
            'ExtensionOpensource' => 'installing-extension', /*special case*/
            'ExtensionConflict' => 'extension-conflict',
            'MagentoDownload' => 'downloading-magento',
            'MagentoInstall' => 'installing-magento',
            'MagentoRemove' => 'removing-magento',
            'MagentoHourlyrevert' => 'hourly-reverting-magento',
            'RevisionCommit' => 'committing-revision',
            'RevisionDeploy' => 'deploying-revision',
            'RevisionRollback' => 'rolling-back-revision',
            'RevisionInit' => 'committing-revision', /*special case*/
            'PapertrailUserCreate' => 'creating-papertrail-user',
            'PapertrailUserRemove' => 'removing-papertrail-user',
            'PapertrailSystemCreate' => 'creating-papertrail-system',
            'PapertrailSystemRemove' => 'removing-papertrail-system'
        );
        
        return $possibleStatuses[$taskName];
        
    }

}
