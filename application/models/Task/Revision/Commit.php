<?php

/* revision classes might need abstract clas for overall repository handling (like creating one within instance */

class Application_Model_Task_Revision_Commit 
extends Application_Model_Task_Revision 
implements Application_Model_Task_Interface {
   
    protected $_dbBackupPath = '';


    /* Prevents from running contructor of Application_Model_Task */
    public function __construct(){
        
        $this->db = $this->_getDb();
        $this->config = $this->_getConfig();
    }
    
    public function process() {
        
        $this->_createDbBackup();
        $this->_commit();
        $hash = $this->_revisionHash;
        
        $startdir  = getcwd();
        $output = '';
        chdir($this->_instanceFolder.'/'.$this->_instanceObject->getDomain());
        
        /* fetch changed files */
        exec('git show --pretty="format:" --name-only '.$hash,$output);
        
        $changedFiles = array();
        foreach($output as $gitChangedFile){
            $changedFiles[]= $gitChangedFile;
        }
        
        /* create temporary directory */
        $tempdir = sys_get_temp_dir();
        chdir($tempdir);
        exec('mkdir '.$hash.';');
        chdir($tempdir.'/'.$hash);      
        /* clone repository into temporary directory */
        $output = array();
        //var_dump('sudo git clone '.$this->_instanceFolder.'/'.$this->_instanceObject->getDomain());
        exec('sudo git clone '.$this->_instanceFolder.'/'.$this->_instanceObject->getDomain(),$output);
        //var_dump($output);
        chdir($tempdir.'/'.$hash.'/'.$this->_instanceObject->getDomain());
        unset($output);
        
        exec('git checkout '.$hash,$output);
        //var_dump($output);
        chdir($tempdir.'/'.$hash);
        exec('sudo mkdir mageroot');
        chdir($tempdir);
        unset($output);
        exec('pwd',$output);
        //var_dump($output);
        
        foreach($changedFiles as $file){
            $pathinfo = pathinfo($file);
            /* create destination dir */
            if (!file_exists($hash.'/mageroot/'.$pathinfo['dirname']) || !is_dir($hash.'/mageroot/'.$pathinfo['dirname'])){
                exec('mkdir -p '.$hash.'/mageroot/'.$pathinfo['dirname']);
            }
            
            //echo 'sudo cp ' . $hash . '/' . $this->_instanceObject->getDomain().'/'.$file. ' '.$hash.'/mageroot/'.$file.PHP_EOL;
            exec('sudo cp ' . $hash . '/' . $this->_instanceObject->getDomain().'/'.$file . ' '.$hash.'/mageroot/'.$file);
        }
       
        /*TODO: decide if move to user revision/ folder next to public_html ? */
        $apppath = str_replace('application','', APPLICATION_PATH);
        exec('sudo tar -zcf '.$apppath.'/data/revision/'.$this->_userObject->getLogin().'/'.$hash.'.tgz '.$hash.'/mageroot');
               
        $this->_insertRevisionInfo();
        
        $this->_updateStatus('ready');
        
        $this->_updateRevisionCount('+1');
        
        chdir($startdir);
    }

    protected function _commit() {
        
        $params = $this->_queueObject->getTaskParams();      
        
        $startCwd = getcwd();
        chdir($this->_instanceFolder.'/'.$this->_instanceObject->getDomain());
        if ($params['commit_type']=='manual'){
            $this->_commitManual(); 
        } else {
            $this->_commitAutomatic();
        }
       
        chdir($startCwd);
         
    }
    
    
    /* For now just an alias */
    protected function _commitManual(){
        $this->_commitAutomatic();
    }
    
    protected function _commitAutomatic(){
        exec('git add -A');
        
        $output = '';
        $params = $this->_queueObject->getTaskParams();      
        exec('git commit -m "'.$params['commit_comment'].'"',$output);
        
        //get revision committed
        preg_match("#\[(.*?) ([a-z0-9]+)\]#is", $output[0],$matches);
        
        //insert revision entry
        $this->_revisionHash  = $matches[2];
        var_dump($output);
    }

    /* Not used yet */
    protected function _push() {
        
    }
    
    protected function _createDbBackup(){
        $startCwd = getcwd();
        chdir($this->_instanceFolder.'/'.$this->_instanceObject->getDomain());
        
        //export backup
        $apppath = str_replace('application','', APPLICATION_PATH);
        $dbDir = $apppath.'/data/revision/'.$this->_userObject->getLogin().'/'.$this->_instanceObject->getDomain().'/db/';
        exec('mkdir -p '.$dbDir);
        $dbFileName = $dbDir.'db_backup_'.date("Y_m_d_H_i_s");
        $command = 'mysqldump -u'.$this->config->resources->db->params->username.' -p'.$this->config->resources->db->params->password.' '.$this->config->magento->instanceprefix.$this->_userObject->getLogin().'_'.$this->_instanceObject->getDomain().' > '.$dbFileName;
        exec($command);
        
        //pack it up
        $pathinfo = pathinfo($dbFileName);
        exec('tar -zcf '.$pathinfo['filename'].'.tgz '.$dbFileName);
        
        chdir($startCwd);
        $this->_dbBackupPath = $dbFileName.'.tgz';
    }
    
    protected function _insertRevisionInfo(){
        $revisionModel = new Application_Model_Revision();
        $params = $this->_queueObject->getTaskParams();
               
        $revisionModel->setUserId($this->_userObject->getId());
        $revisionModel->setInstanceId($this->_instanceObject->getId());
        $revisionModel->setHash($this->_revisionHash);
        $revisionModel->setType($params['commit_type']);
        $revisionModel->setDbBeforeRevision($this->_dbBackupPath);
        $revisionModel->setComment('');
        $revisionModel->setFileName($this->_revisionHash.'.tgz');    
        $revisionModel->save();
    }

}
