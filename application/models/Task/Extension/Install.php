<?php

class Application_Model_Task_Extension_Install 
extends Application_Model_Task_Extension
implements Application_Model_Task_Interface {

    protected $_extensionObject=  '';
    
    public function setup(Application_Model_Queue &$queueElement){
        parent::setup($queueElement);
        
        $extensionModel = new Application_Model_Extension();
        $extensionModel->find($queueElement->getExtensionId());
        $this->_extensionObject = $extensionModel;
    }
    
    public function process(Application_Model_Queue $queueElement = null) {
               
        $this->_updateStatus('installing-extension');
        
        //get instance data
        $magentoVersion = $this->_versionObject->getVersion();
        $sampleDataVersion = $this->_versionObject->getSampleDataVersion();
        
        //first check if we have that file
        $this->_checkPackage();
        
        //untar extension to instance folder
        $this->_install();
        
        //clear instance cache
        $this->_clearInstanceCache();
        
        //set extension as installed
        $this->_updateStatus('ready');        
                
    }

    protected function _checkPackage() {
        
        //if extension is commercial, check existence of encoded file, 
        if ($this->_extensionObject->getPrice() > 0 ){
            if (!file_exists($this->config->extension->directoryPath.'/'.$this->_versionObject->getEdition().'/encoded/'.$this->_extensionObject->getExtensionEncoded())){
                $message = 'Extension file for '.$this->_extensionObject->getName().' could not be found';
                $this->logger->log($message, Zend_Log::EMERG);
                throw new Exception($message); 
            } 
        } else {
            if (!file_exists($this->config->extension->directoryPath.'/'.$this->_versionObject->getEdition().'/open/'.$this->_extensionObject->getExtension())){
                $message = 'Extension file for '.$this->_extensionObject->getName().' could not be found';
                $this->logger->log($message, Zend_Log::EMERG);
                throw new Exception($message);
            } 
        }
        
        
    }

    protected function _install() {
        $output='';

        $this->logger->log('Unpacking and installing extension.', Zend_Log::INFO);

        if ($this->_extensionObject->getPrice() > 0 ){
            
            exec('tar -zxvf '.
                $this->config->extension->directoryPath.'/'.$this->_versionObject->getEdition().'/encoded/'.$this->_extensionObject->getExtensionEncoded().
                ' -C '.$this->config->magento->systemHomeFolder . '/' . $this->config->magento->userprefix . $this->_userObject->getLogin() . '/public_html/'.$this->_instanceObject->getDomain()
            ,$output);
            
        } else {
        
            exec('tar -zxvf '.
                $this->config->extension->directoryPath.'/'.$this->_versionObject->getEdition().'/open/'.$this->_extensionObject->getExtension().
                ' -C '.$this->config->magento->systemHomeFolder . '/' . $this->config->magento->userprefix . $this->_userObject->getLogin() . '/public_html/'.$this->_instanceObject->getDomain()
            ,$output);

        }
        
        //output contains unpacked files list, so it should never be empty if unpacking suceed
        $this->logger->log(var_export($output,true),Zend_Log::DEBUG);
        if (count($output)==0){
            
            $message = 'There was an error while installing extension '.$this->_extensionObject->getName();
            $this->_updateStatus('error',$message);
            unset($output);
            return false;
        }      
        
    }    

}
