<?php

/* revision classes might need abstract clas for overall repository handling (like creating one within instance */

class Application_Model_Task_Revision_Commit 
extends Application_Model_Task_Revision 
implements Application_Model_Task_Interface {
   
    protected $_dbBackupPath = '';

    public function setup(Application_Model_Queue &$queueElement){
        parent::setup($queueElement);
        
        $extensionModel = new Application_Model_Extension();
        $extensionModel->find($queueElement->getExtensionId());
        $this->_extensionObject = $extensionModel;
                
    }
    
    public function process(Application_Model_Queue &$queueElement = null) {
        $this->_updateStatus('commiting-revision');
              
        $this->_createDbBackup();
        if ($this->_commit()){
            $hash = $this->_revisionHash;
            $this->_insertRevisionInfo();
            $this->_updateRevisionCount('+1');
        }
        $this->_updateStatus('ready');
    }

    protected function _commit() {
        
        $params = $this->_queueObject->getTaskParams();      
        
        $startCwd = getcwd();
        chdir($this->_instanceFolder.'/'.$this->_instanceObject->getDomain());
        if ($params['commit_type']=='manual'){
            return $this->_commitManual(); 
        } else {
            return $this->_commitAutomatic();
        }
       
        chdir($startCwd);
         
    }
    
    
    /* For now just an alias */
    protected function _commitManual(){
        $this->logger->log('Making manual GIT commit.', Zend_Log::INFO);

        exec('git add -A');
        
        $output = '';
        $params = $this->_queueObject->getTaskParams(); 
        
        if (trim($params['commit_comment'])==''){
            $params['commit_comment']='No comment given for this commit';
        }

        exec('git commit -m "'.$params['commit_comment'].'"',$output);
        
        if (!count($output)){
            $this->_updateStatus('ready', 'No changes have been made, manual commit aborted');
            return false;
        }
        //get revision committed
        preg_match("#\[(.*?) ([a-z0-9]+)\]#is", $output[0],$matches);
               
        if (!isset($matches[2])){
            $this->_updateStatus('ready', 'Could not find revision information, aborting');
            return false;
        }
        
        //insert revision entry
        $this->_revisionHash  = $matches[2];
        return true;
    }
    
    protected function _commitAutomatic(){
        $this->logger->log('Making automatic GIT commit.', Zend_Log::INFO);
        exec('git add -A');
        
        $output = '';
        $params = $this->_queueObject->getTaskParams(); 
        
        if (trim($params['commit_comment'])==''){
            $params['commit_comment']='No comment given for this commit';
        }

        exec('git commit -m "'.$params['commit_comment'].'"',$output);
                
        if (count($output) < 3 && trim($output[2])=='nothing to commit (working directory clean)'){
            $this->_updateStatus('ready', 'No changes have been made, manual commit aborted');
            return false;
        }
        //get revision committed
        preg_match("#\[(.*?) ([a-z0-9]+)\]#is", $output[0], $matches);
               
        if (!isset($matches[2])){
            $this->_updateStatus('ready', 'Could not find revision information, aborting');
            return false;
        }
        
        //insert revision entry
        $this->_revisionHash  = $matches[2];
        return true;
    }

    /* Not used yet */
    protected function _push() {
        
    }
    
    protected function _createDbBackup(){
        $startCwd = getcwd();
        chdir($this->_instanceFolder.'/'.$this->_instanceObject->getDomain());
        
        //export backup
        $this->logger->log('Creating database backup.', Zend_Log::INFO);
        $dbDir = $this->_instanceFolder.'/'.$this->_instanceObject->getDomain().'/var/db/';
        exec('sudo mkdir -p '.$dbDir);
        chdir($dbDir);
        $dbFileName = 'db_backup_'.date("Y_m_d_H_i_s");
        $command = 'sudo mysqldump -u'.$this->config->resources->db->params->username.' -p'.$this->config->resources->db->params->password.' '.$this->config->magento->instanceprefix.$this->_userObject->getLogin().'_'.$this->_instanceObject->getDomain().' > '.$dbFileName;
        exec($command);
        
        //pack it up
        $pathinfo = pathinfo($dbFileName);
        /* tar backup file */
        $this->logger->log('Packing database backup.', Zend_Log::INFO);
        exec('sudo tar -zcf '.$pathinfo['filename'].'.tgz '.$dbFileName);
        
        /* copy packed sql file to target dir */
        //exec('sudo mv '.$pathinfo['filename'].'.tgz '.$dbDir.$pathinfo['filename'].'.tgz');
        
        $this->logger->log('Removing not packed database backup.', Zend_Log::INFO);
        exec('sudo rm '.$dbFileName);
        
        chdir($startCwd);
        $this->_dbBackupPath = $dbFileName.'.tgz';
    }
    
    protected function _insertRevisionInfo(){
        $revisionModel = new Application_Model_Revision();
        $params = $this->_queueObject->getTaskParams();

        $revisionModel->setUserId($this->_userObject->getId());
        $revisionModel->setInstanceId($this->_instanceObject->getId());
        $revisionModel->setExtensionId($this->_queueObject->getExtensionId());
        $revisionModel->setHash($this->_revisionHash);
        $revisionModel->setType($params['commit_type']);
        $revisionModel->setDbBeforeRevision($this->_dbBackupPath);
        $revisionModel->setComment($params['commit_comment']);
        $revisionModel->setFileName('');    
        $revisionModel->save();
    }

}
