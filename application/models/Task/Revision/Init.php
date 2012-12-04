<?php

class Application_Model_Task_Revision_Init
extends Application_Model_Task_Revision 
implements Application_Model_Task_Interface {
   
    /* Prevents from running contructor of Application_Model_Task */
    public function __construct(){
        
        $this->db = $this->_getDb();
        $this->config = $this->_getConfig();
    }
    
    public function process(Application_Model_Queue &$queueElement = null) {        
        $this->_prepareGitIgnore();
        $this->_initRepo();
        $this->_updateStatus('ready');
    }

    /**
     * This method should take place AFTER magento has been successfully installed
     */
     
    protected function _initRepo() {
        //init git repository
        $startCwd = getcwd();
        
        chdir($this->_instanceFolder.'/'.$this->_instanceObject->getDomain());
        exec('git init');
        
        chdir($startCwd);
    }
    
    protected function _prepareGitIgnore(){
        $data = "".
        "var/".PHP_EOL.
        "media/".PHP_EOL.
        "";
        
        file_put_contents($this->_instanceFolder.'/'.$this->_instanceObject->getDomain().'/.gitignore', $data);
    }

}
