<?php
/**
 * Responsible for remove system in Papertrail
 * 
 * @category   Application
 * @package    Model_Task
 * @subpackage Papertrail_User
 * @copyright  Copyright (c) 2012 RocketWeb USA Inc. (http://www.rocketweb.com)
 * @author     Marcin Kazimierczak <marcin@rocketweb.com>
 */
class Application_Model_Task_Papertrail_User_Remove 
extends Application_Model_Task_Papertrail 
implements Application_Model_Task_Interface {

    public function process(Application_Model_Queue $queueElement = null) {
        $this->_updateStoreStatus('removing-papertrail-system');

        $this->logger->log('Removing papertrail system.', Zend_Log::INFO);

        $id = $this->config->papertrail->prefix . $this->_storeObject->getDomain();
        
        $output = array((string)$id);
        $message = var_export($output, true);
        $this->logger->log($message, Zend_Log::DEBUG);

        try { 
            $response = $this->_service->removeSystem((string)$id);
        } catch(Zend_Service_Exception $e) {
            $this->logger->log($e->getMessage(), Zend_Log::CRIT);
            throw new Application_Model_Task_Exception($e->getMessage());
        }

        if(isset($response->status) && $response->status == 'ok') {
            //success
            $this->_storeObject->setPapertrailSyslogHostname(null);
            $this->_storeObject->setPapertrailSyslogPort(null);
            $this->_storeObject->save();
        }

    }

}