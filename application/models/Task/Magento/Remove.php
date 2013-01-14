<?php

class Application_Model_Task_Magento_Remove 
extends Application_Model_Task_Magento 
implements Application_Model_Task_Interface {
   
    public function setup(Application_Model_Queue $queueElement){
        parent::setup($queueElement);
    }
    
    public function process(Application_Model_Queue $queueElement = null) {
        
        $this->_updateStoreStatus('removing-magento');
        
        $DbManager = new Application_Model_DbTable_Privilege($this->db,$this->config);
        if ($DbManager->checkIfDatabaseExists($this->_dbname)){
            try {
                $this->logger->log('Dropping ' . $this->_dbname . ' database.', Zend_Log::INFO);
                $DbManager->dropDatabase($this->_dbname);
            } catch(PDOException $e){
                $message = 'Could not remove database for store.';
                $this->logger->log($message, Zend_Log::CRIT);
                flock($fp, LOCK_UN); // release the lock
                throw new Exception($message);
            }
        } else {
            $message = 'Store database does not exist, ignoring.';
            $this->logger->log($message, Zend_Log::NOTICE);
        }

        //remove folder recursively
        $startCwd =  getcwd();
        chdir(STORE_PATH);

        $this->logger->log('Removing store directory recursively.', Zend_Log::INFO);
        exec('sudo rm -R '.$this->_storeFolder.'/'.$this->_storeObject->getDomain());
        unlink($this->_storeObject->getDomain());
        chdir($this->_storeFolder);

        $this->logger->log('Removing store entries from Mage Testing database.', Zend_Log::INFO);
        $this->db->getConnection()->exec("use ".$this->config->resources->db->params->dbname);
      
        //remove store extensions
        $this->db->delete('store_extension','store_id='.$this->_storeObject->getId());
        
        //remove this queue element
        $this->db->delete('queue','id='.$this->_queueObject->getId());
        
        //remove any other queue elements related to this store
        $this->db->delete('queue','store_id='.$this->_storeObject->getId());
        
        //remove store
        $this->db->delete('store','id='.$this->_storeObject->getId());
        
        unlink(APPLICATION_PATH . '/../data/logs/'.$this->_userObject->getLogin().'_'.$this->_storeObject->getDomain().'.log');
    
    }

    protected function _removeDatabase() {
        
    }

    protected function _removeStoreFilesystem() {
        
    }

    protected function _cleanupMainDatabase() {
        
    }

}
